<?php
// File: AplikasiWebKripto/includes/aes128.php
// Implementasi AES-128 dalam mode CBC dengan PKCS#7 Padding.
// Dibuat untuk tujuan pemahaman algoritma.

class AES128_CBC {
    // Ukuran blok standar untuk AES adalah 16 byte (128 bit).
    public const AES_BLOCK_SIZE = 16;

    // Tabel substitusi (S-Box) standar AES.
    // Ini adalah array 1D, diakses dengan nilai byte (0-255) sebagai indeks.
    private const S_BOX = [
        0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76,
        0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0,
        0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15,
        0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75,
        0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84,
        0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf,
        0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8,
        0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2,
        0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73,
        0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb,
        0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79,
        0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08,
        0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a,
        0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e,
        0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf,
        0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16
    ];

    // Konstanta Putaran (Rcon) untuk Key Expansion AES-128.
    // Rcon[i] = [rc_val, 0, 0, 0] dalam bentuk integer 32-bit.
    private const RCON = [
        0x00000000, // Indeks 0 tidak digunakan, Rcon[1] sampai Rcon[10] yang dipakai.
        0x01000000, 0x02000000, 0x04000000, 0x08000000, 
        0x10000000, 0x20000000, 0x40000000, 0x80000000, 
        0x1B000000, 0x36000000
    ];

    // Properti untuk parameter standar AES.
    private int $Nb; // Jumlah kolom (32-bit words) dalam State (selalu 4).
    private int $Nk; // Jumlah 32-bit words dalam Kunci Cipher (4 untuk AES-128).
    private int $Nr; // Jumlah putaran enkripsi (10 untuk AES-128).

    // Properti untuk menyimpan key schedule (semua round keys).
    // Disimpan sebagai array dari integer 32-bit (words).
    private array $w_schedule = [];

    /**
     * Konstruktor kelas AES128_CBC.
     * Menerima kunci 16 byte, melakukan validasi, dan menjalankan key expansion.
     */
    public function __construct(string $key) {
        if (strlen($key) !== self::AES_BLOCK_SIZE) {
            // Melempar exception jika panjang kunci tidak sesuai.
            throw new InvalidArgumentException("Panjang kunci harus tepat 16 byte untuk AES-128.");
        }

        $this->Nb = 4; 
        $this->Nk = 4; 
        $this->Nr = 10;

        // Memanggil metode untuk menghasilkan semua round keys.
        $this->keyExpansion($key);
    }

    /**
     * Fungsi lookup S-Box.
     * Mengambil nilai dari S_BOX berdasarkan byte input.
     */
    private function _sBoxLookup(int $byte): int {
        // Memastikan byte adalah unsigned (0-255).
        return self::S_BOX[$byte & 0xFF];
    }
    
    /**
     * Melakukan Key Expansion untuk AES-128.
     * Menghasilkan semua round keys (key schedule) dan menyimpannya di $this->w_schedule.
     */
    private function keyExpansion(string $key): void {
        // Total words dalam key schedule: Nb * (Nr + 1) = 4 * (10 + 1) = 44 words.
        $this->w_schedule = array_fill(0, $this->Nb * ($this->Nr + 1), 0);

        // Mengubah string kunci 16 byte menjadi 4 integer 32-bit (words) dengan format big-endian.
        // Fungsi unpack("N*", $key) menghasilkan array 1-indexed.
        $key_words = array_values(unpack("N*", $key)); 

        // Menyalin 4 word pertama dari kunci asli ke key schedule.
        for ($i = 0; $i < $this->Nk; $i++) {
            $this->w_schedule[$i] = $key_words[$i];
        }

        // Menghasilkan sisa word dalam key schedule.
        // Loop dari Nk (4) hingga jumlah total word (44).
        for ($i = $this->Nk; $i < count($this->w_schedule); $i++) {
            $temp = $this->w_schedule[$i - 1]; // Ambil word sebelumnya.

            // Jika i adalah kelipatan dari Nk, lakukan transformasi khusus pada temp.
            if ($i % $this->Nk == 0) {
                // 1. RotWord: geser byte-byte dalam word ke kiri secara sirkular.
                //    Contoh: 0xA0B0C0D0 menjadi 0xB0C0D0A0.
                $temp = (($temp << 8) & 0xFFFFFF00) | (($temp >> 24) & 0x000000FF);

                // 2. SubWord: setiap byte dari temp (yang sudah di-RotWord) dilewatkan ke S-Box.
                $b0 = $this->_sBoxLookup(($temp >> 24) & 0xFF); // Byte paling signifikan
                $b1 = $this->_sBoxLookup(($temp >> 16) & 0xFF);
                $b2 = $this->_sBoxLookup(($temp >> 8)  & 0xFF);
                $b3 = $this->_sBoxLookup($temp & 0xFF);        // Byte paling tidak signifikan
                $temp = ($b0 << 24) | ($b1 << 16) | ($b2 << 8) | $b3; // Gabungkan kembali menjadi word

                // 3. XOR dengan Rcon (Round Constant).
                //    Rcon hanya berlaku pada byte paling signifikan dari word temp.
                //    Indeks Rcon adalah ($i / $this->Nk).
                $temp ^= self::RCON[$i / $this->Nk];
            } 
            // Untuk AES-128, tidak ada langkah SubWord tambahan di sini (berbeda dengan AES-256).
            
            // Word baru adalah XOR dari w[i - Nk] dengan temp yang sudah diproses.
            $this->w_schedule[$i] = $this->w_schedule[$i - $this->Nk] ^ $temp;
        }
    }

// --- Akhir Bagian 1 ---
// Metode-metode berikutnya (untuk transformasi putaran, enkripsi/dekripsi blok,
// mode CBC, dan padding)

    // Bagian 2: Transformasi Putaran Inti AES (ShiftRows, MixColumns, AddRoundKey)
    //           dan Fungsi Helper GF(2^8) serta Inverse S-Box.

    // Tabel Inverse S-Box standar AES (digunakan untuk dekripsi).
    // Array 1D, diakses dengan nilai byte (0-255) sebagai indeks.
    private const INV_S_BOX = [
        0x52, 0x09, 0x6a, 0xd5, 0x30, 0x36, 0xa5, 0x38, 0xbf, 0x40, 0xa3, 0x9e, 0x81, 0xf3, 0xd7, 0xfb,
        0x7c, 0xe3, 0x39, 0x82, 0x9b, 0x2f, 0xff, 0x87, 0x34, 0x8e, 0x43, 0x44, 0xc4, 0xde, 0xe9, 0xcb,
        0x54, 0x7b, 0x94, 0x32, 0xa6, 0xc2, 0x23, 0x3d, 0xee, 0x4c, 0x95, 0x0b, 0x42, 0xfa, 0xc3, 0x4e,
        0x08, 0x2e, 0xa1, 0x66, 0x28, 0xd9, 0x24, 0xb2, 0x76, 0x5b, 0xa2, 0x49, 0x6d, 0x8b, 0xd1, 0x25,
        0x72, 0xf8, 0xf6, 0x64, 0x86, 0x68, 0x98, 0x16, 0xd4, 0xa4, 0x5c, 0xcc, 0x5d, 0x65, 0xb6, 0x92,
        0x6c, 0x70, 0x48, 0x50, 0xfd, 0xed, 0xb9, 0xda, 0x5e, 0x15, 0x46, 0x57, 0xa7, 0x8d, 0x9d, 0x84,
        0x90, 0xd8, 0xab, 0x00, 0x8c, 0xbc, 0xd3, 0x0a, 0xf7, 0xe4, 0x58, 0x05, 0xb8, 0xb3, 0x45, 0x06,
        0xd0, 0x2c, 0x1e, 0x8f, 0xca, 0x3f, 0x0f, 0x02, 0xc1, 0xaf, 0xbd, 0x03, 0x01, 0x13, 0x8a, 0x6b,
        0x3a, 0x91, 0x11, 0x41, 0x4f, 0x67, 0xdc, 0xea, 0x97, 0xf2, 0xcf, 0xce, 0xf0, 0xb4, 0xe6, 0x73,
        0x96, 0xac, 0x74, 0x22, 0xe7, 0xad, 0x35, 0x85, 0xe2, 0xf9, 0x37, 0xe8, 0x1c, 0x75, 0xdf, 0x6e,
        0x47, 0xf1, 0x1a, 0x71, 0x1d, 0x29, 0xc5, 0x89, 0x6f, 0xb7, 0x62, 0x0e, 0xaa, 0x18, 0xbe, 0x1b,
        0xfc, 0x56, 0x3e, 0x4b, 0xc6, 0xd2, 0x79, 0x20, 0x9a, 0xdb, 0xc0, 0xfe, 0x78, 0xcd, 0x5a, 0xf4,
        0x1f, 0xdd, 0xa8, 0x33, 0x88, 0x07, 0xc7, 0x31, 0xb1, 0x12, 0x10, 0x59, 0x27, 0x80, 0xec, 0x5f,
        0x60, 0x51, 0x7f, 0xa9, 0x19, 0xb5, 0x4a, 0x0d, 0x2d, 0xe5, 0x7a, 0x9f, 0x93, 0xc9, 0x9c, 0xef,
        0xa0, 0xe0, 0x3b, 0x4d, 0xae, 0x2a, 0xf5, 0xb0, 0xc8, 0xeb, 0xbb, 0x3c, 0x83, 0x53, 0x99, 0x61,
        0x17, 0x2b, 0x04, 0x7e, 0xba, 0x77, 0xd6, 0x26, 0xe1, 0x69, 0x14, 0x63, 0x55, 0x21, 0x0c, 0x7d
    ];

    /**
     * Fungsi lookup Inverse S-Box.
     * Mengambil nilai dari INV_S_BOX berdasarkan byte input.
     */
    private function _invSBoxLookup(int $byte): int {
        return self::INV_S_BOX[$byte & 0xFF];
    }

    /**
     * Operasi ShiftRows pada state.
     * State direpresentasikan sebagai array 4x4 [kolom][baris].
     * Baris-baris (indeks kedua) digeser secara siklik ke kiri.
     */
    private function shiftRows(array $state): array {
        $newState = $state; // Salin state awal
        // Baris 0 tidak digeser

        // Baris 1 (indeks 1) digeser 1 posisi ke kiri
        // newState[c][1] = state[(c+1)%Nb][1]
        $temp = $newState[0][1];
        for ($c = 0; $c < $this->Nb - 1; $c++) {
            $newState[$c][1] = $newState[$c + 1][1];
        }
        $newState[$this->Nb - 1][1] = $temp;

        // Baris 2 (indeks 2) digeser 2 posisi ke kiri
        // newState[c][2] = state[(c+2)%Nb][2]
        for ($s = 0; $s < 2; $s++) {
            $temp = $newState[0][2];
            for ($c = 0; $c < $this->Nb - 1; $c++) {
                $newState[$c][2] = $newState[$c + 1][2];
            }
            $newState[$this->Nb - 1][2] = $temp;
        }

        // Baris 3 (indeks 3) digeser 3 posisi ke kiri (atau 1 ke kanan)
        // newState[c][3] = state[(c+3)%Nb][3]
        for ($s = 0; $s < 3; $s++) {
            $temp = $newState[0][3];
            for ($c = 0; $c < $this->Nb - 1; $c++) {
                $newState[$c][3] = $newState[$c + 1][3];
            }
            $newState[$this->Nb - 1][3] = $temp;
        }
        return $newState;
    }

    /**
     * Operasi Inverse ShiftRows pada state.
     * State direpresentasikan sebagai array 4x4 [kolom][baris].
     * Baris-baris (indeks kedua) digeser secara siklik ke kanan.
     */
    private function invShiftRows(array $state): array {
        $newState = $state; // Salin state awal
        // Baris 0 tidak digeser

        // Baris 1 (indeks 1) digeser 1 posisi ke kanan
        // newState[c][1] = state[(c-1+Nb)%Nb][1]
        $temp = $newState[$this->Nb - 1][1];
        for ($c = $this->Nb - 1; $c > 0; $c--) {
            $newState[$c][1] = $newState[$c - 1][1];
        }
        $newState[0][1] = $temp;

        // Baris 2 (indeks 2) digeser 2 posisi ke kanan
        for ($s = 0; $s < 2; $s++) {
            $temp = $newState[$this->Nb - 1][2];
            for ($c = $this->Nb - 1; $c > 0; $c--) {
                $newState[$c][2] = $newState[$c - 1][2];
            }
            $newState[0][2] = $temp;
        }
        
        // Baris 3 (indeks 3) digeser 3 posisi ke kanan (atau 1 ke kiri)
        for ($s = 0; $s < 3; $s++) {
            $temp = $newState[$this->Nb - 1][3];
            for ($c = $this->Nb - 1; $c > 0; $c--) {
                $newState[$c][3] = $newState[$c - 1][3];
            }
            $newState[0][3] = $temp;
        }
        return $newState;
    }

    /**
     * Operasi xtime (perkalian dengan 0x02) di GF(2^8).
     */
    private function xtime(int $byte): int {
        $result = ($byte << 1) & 0xFF;
        if ($byte & 0x80) { 
            $result ^= 0x1B;
        }
        return $result;
    }

    /**
     * Perkalian dua byte di GF(2^8) menggunakan xtime.
     */
    private function gfMultiply(int $a, int $b): int {
        if ($a === 1) return $b;
        if ($a === 2) return $this->xtime($b);

        $b2 = $this->xtime($b);
        if ($a === 3) return $b2 ^ $b;

        $b4 = $this->xtime($b2);
        $b8 = $this->xtime($b4);

        if ($a === 9) return $b8 ^ $b;
        if ($a === 0x0b) return $b8 ^ $b2 ^ $b;
        if ($a === 0x0d) return $b8 ^ $b4 ^ $b;
        if ($a === 0x0e) return $b8 ^ $b4 ^ $b2;

        return 0; // Fallback jika konstanta tidak dikenal
    }

        // Implementasi sebelumnya berdasarkan konstanta spesifik AES:
        // if ($a === 0x01) return $b;
        // if ($a === 0x02) return $this->xtime($b);
        // $b2 = $this->xtime($b);
        // if ($a === 0x03) return $b2 ^ $b;
        // $b4 = $this->xtime($b2);
        // $b8 = $this->xtime($b4);
        // if ($a === 0x09) return $b8 ^ $b;
        // if ($a === 0x0b) return $b8 ^ $b2 ^ $b;
        // if ($a === 0x0d) return $b8 ^ $b4 ^ $b;
        // if ($a === 0x0e) return $b8 ^ $b4 ^ $b2;
        // return 0; // Fallback jika konstanta tidak dikenal

    /**
     * Operasi MixColumns pada state.
     * State direpresentasikan sebagai array 4x4 [kolom][baris].
     */
    private function mixColumns(array $state): array {
        $mixedState = array_fill(0, 4, array_fill(0, 4, 0));
        for ($c = 0; $c < $this->Nb; $c++) {
            $mixedState[$c][0] = $this->gfMultiply(0x02, $state[$c][0]) ^ $this->gfMultiply(0x03, $state[$c][1]) ^ $state[$c][2] ^ $state[$c][3];
            $mixedState[$c][1] = $state[$c][0] ^ $this->gfMultiply(0x02, $state[$c][1]) ^ $this->gfMultiply(0x03, $state[$c][2]) ^ $state[$c][3];
            $mixedState[$c][2] = $state[$c][0] ^ $state[$c][1] ^ $this->gfMultiply(0x02, $state[$c][2]) ^ $this->gfMultiply(0x03, $state[$c][3]);
            $mixedState[$c][3] = $this->gfMultiply(0x03, $state[$c][0]) ^ $state[$c][1] ^ $state[$c][2] ^ $this->gfMultiply(0x02, $state[$c][3]);
        }
        return $mixedState;
    }

    /**
     * Operasi Inverse MixColumns pada state (untuk dekripsi).
     * State direpresentasikan sebagai array 4x4 [kolom][baris]
     * @param array $state Array 4x4 byte.
     * @return array State setelah Inverse MixColumns.
     */
    private function invMixColumns(array $state): array {
        $unmixedState = array_fill(0, 4, array_fill(0, 4, 0)); // Inisialisasi array hasil
        for ($c = 0; $c < $this->Nb; $c++) { // Nb adalah 4
            // Mengambil byte dari kolom saat ini untuk kejelasan
            $s0 = $state[$c][0];
            $s1 = $state[$c][1];
            $s2 = $state[$c][2];
            $s3 = $state[$c][3];

            // Matriks perkalian untuk Inverse MixColumns:
            // [0e 0b 0d 09]
            // [09 0e 0b 0d]
            // [0d 09 0e 0b]
            // [0b 0d 09 0e]
            $unmixedState[$c][0] = $this->gfMultiply(0x0e, $s0) ^ $this->gfMultiply(0x0b, $s1) ^ $this->gfMultiply(0x0d, $s2) ^ $this->gfMultiply(0x09, $s3);
            $unmixedState[$c][1] = $this->gfMultiply(0x09, $s0) ^ $this->gfMultiply(0x0e, $s1) ^ $this->gfMultiply(0x0b, $s2) ^ $this->gfMultiply(0x0d, $s3);
            $unmixedState[$c][2] = $this->gfMultiply(0x0d, $s0) ^ $this->gfMultiply(0x09, $s1) ^ $this->gfMultiply(0x0e, $s2) ^ $this->gfMultiply(0x0b, $s3);
            $unmixedState[$c][3] = $this->gfMultiply(0x0b, $s0) ^ $this->gfMultiply(0x0d, $s1) ^ $this->gfMultiply(0x09, $s2) ^ $this->gfMultiply(0x0e, $s3);
        }
        return $unmixedState;
    }

    /**
     * Operasi AddRoundKey.
     * State (array 4x4 [kolom][baris]) di-XOR dengan round key.
     */
    private function addRoundKey(array $state, int $round): array {
        $newState = $state;
        for ($c = 0; $c < $this->Nb; $c++) { 
            $roundKeyWord = $this->w_schedule[$round * $this->Nb + $c];
            $newState[$c][0] ^= ($roundKeyWord >> 24) & 0xFF;
            $newState[$c][1] ^= ($roundKeyWord >> 16) & 0xFF;
            $newState[$c][2] ^= ($roundKeyWord >> 8)  & 0xFF;
            $newState[$c][3] ^= $roundKeyWord & 0xFF;        
        }
        return $newState;
    }

    // --- Akhir Bagian 2 ---
    // Metode inti _encryptBlockInternal dan _decryptBlockInternal, serta metode publik encrypt() dan decrypt() 
    // dengan mode CBC dan padding akan ada di Bagian 3 dan 4.
// setelah akhir dari kode Bagian 2.
    // Bagian 3: Fungsi Konversi State, Transformasi SubBytes Keseluruhan,
    //           dan Proses Enkripsi/Dekripsi Blok Inti.

    /**
     * Konversi string 16-byte menjadi state array 4x4 (kolom-mayor).
     * State direpresentasikan sebagai state[kolom][baris].
     */
    private function _stateFromString(string $stringBlock): array {
        $state = array_fill(0, 4, array_fill(0, 4, 0));
        // Unpack string menjadi array byte (integer 0-255)
        $bytes = array_values(unpack("C*", $stringBlock)); 
        $k = 0; // Indeks untuk array bytes
        for ($c = 0; $c < $this->Nb; $c++) { // Iterasi per kolom state
            for ($r = 0; $r < 4; $r++) { // Iterasi per baris state
                $state[$c][$r] = $bytes[$k++];
            }
        }
        return $state;
    }

    /**
     * Konversi state array 4x4 (kolom-mayor) menjadi string 16-byte.
     */
    private function _stringFromState(array $state): string {
        $stringBlock = "";
        for ($c = 0; $c < $this->Nb; $c++) { // Iterasi per kolom state
            for ($r = 0; $r < 4; $r++) { // Iterasi per baris state
                $stringBlock .= chr($state[$c][$r]);
            }
        }
        return $stringBlock;
    }

    /**
     * Transformasi SubBytes untuk seluruh state.
     * Mengganti setiap byte dalam state menggunakan S-Box via _sBoxLookup.
     */
    private function _transformSubBytes(array $state): array {
        $newState = array_fill(0, 4, array_fill(0, 4, 0));
        for ($c = 0; $c < $this->Nb; $c++) {
            for ($r = 0; $r < 4; $r++) {
                $newState[$c][$r] = $this->_sBoxLookup($state[$c][$r]);
            }
        }
        return $newState;
    }

    /**
     * Transformasi Inverse SubBytes untuk seluruh state.
     * Mengganti setiap byte dalam state menggunakan Inverse S-Box via _invSBoxLookup.
     */
    private function _transformInvSubBytes(array $state): array {
        $newState = array_fill(0, 4, array_fill(0, 4, 0));
        for ($c = 0; $c < $this->Nb; $c++) {
            for ($r = 0; $r < 4; $r++) {
                $newState[$c][$r] = $this->_invSBoxLookup($state[$c][$r]);
            }
        }
        return $newState;
    }

    /**
     * Proses enkripsi inti AES untuk satu blok 16-byte.
     * Ini adalah implementasi cipher dasar (tanpa mode operasi).
     * @throws Exception Jika panjang input tidak 16 byte.
     */
    private function _encryptBlockInternal(string $plaintextBlock): string {
        if (strlen($plaintextBlock) !== self::AES_BLOCK_SIZE) {
            throw new Exception("Input untuk enkripsi blok internal harus " . self::AES_BLOCK_SIZE . " byte.");
        }

        // Konversi input string 16-byte ke format state 4x4 [kolom][baris]
        $state = $this->_stateFromString($plaintextBlock);

        // 1. Initial AddRoundKey (menggunakan round key ke-0)
        $state = $this->addRoundKey($state, 0);

        // 2. Putaran Utama (Nr - 1 putaran)
        // Untuk AES-128, Nr = 10, jadi 9 putaran utama (putaran 1 sampai 9)
        for ($round = 1; $round < $this->Nr; $round++) {
            $state = $this->_transformSubBytes($state);
            $state = $this->shiftRows($state);
            $state = $this->mixColumns($state);
            $state = $this->addRoundKey($state, $round);
        }

        // 3. Putaran Final (tanpa MixColumns)
        // Putaran ke-Nr (putaran ke-10 untuk AES-128)
        $state = $this->_transformSubBytes($state);
        $state = $this->shiftRows($state);
        $state = $this->addRoundKey($state, $this->Nr);

        // Konversi state hasil enkripsi kembali ke string 16-byte
        return $this->_stringFromState($state);
    }

    /**
     * Proses dekripsi inti AES untuk satu blok 16-byte.
     * Ini adalah implementasi cipher dasar invers (tanpa mode operasi).
     * @throws Exception Jika panjang input tidak 16 byte.
     */
    private function _decryptBlockInternal(string $ciphertextBlock): string {
        if (strlen($ciphertextBlock) !== self::AES_BLOCK_SIZE) {
            throw new Exception("Input untuk dekripsi blok internal harus " . self::AES_BLOCK_SIZE . " byte.");
        }

        // Konversi input string 16-byte ke format state 4x4 [kolom][baris]
        $state = $this->_stateFromString($ciphertextBlock);

        // 1. Initial AddRoundKey (menggunakan round key terakhir, yaitu round key ke-Nr)
        $state = $this->addRoundKey($state, $this->Nr);

        // 2. Putaran Utama (Nr - 1 putaran), urutan operasi invers
        // Loop dari putaran ke-(Nr - 1) turun ke 1
        for ($round = ($this->Nr - 1); $round >= 1; $round--) {
            $state = $this->invShiftRows($state);
            $state = $this->_transformInvSubBytes($state);
            $state = $this->addRoundKey($state, $round); // Menggunakan round key yang sesuai
            $state = $this->invMixColumns($state);
        }

        // 3. Putaran Final (tanpa InvMixColumns)
        $state = $this->invShiftRows($state);
        $state = $this->_transformInvSubBytes($state);
        $state = $this->addRoundKey($state, 0); // Menggunakan round key ke-0 (kunci awal)

        // Konversi state hasil dekripsi kembali ke string 16-byte
        // Plaintext ini mungkin masih mengandung padding PKCS#7
        return $this->_stringFromState($state);
    }

    // --- Akhir Bagian 3 ---
    // Metode publik encrypt() dan decrypt() dengan mode CBC dan penanganan PKCS#7 padding
    // akan ditambahkan di Bagian 4 (bagian terakhir dari kelas ini). 
// setelah akhir dari kode Bagian 3.

    // Bagian 4: Metode Publik Enkripsi dan Dekripsi dengan Mode CBC dan PKCS#7 Padding.

    /**
     * Melakukan padding PKCS#7 pada data.
     * @param string $data Data yang akan di-padding.
     * @param int $blockSize Ukuran blok (dalam byte).
     * @return string Data setelah di-padding.
     */
    private function _pkcs7Pad(string $data, int $blockSize): string {
        $paddingLength = $blockSize - (strlen($data) % $blockSize);
        // Karakter padding adalah byte yang nilainya sama dengan panjang padding.
        $paddingChar = chr($paddingLength);
        return $data . str_repeat($paddingChar, $paddingLength);
    }

    /**
     * Menghapus padding PKCS#7 dari data.
     * @param string $data Data yang sudah di-dekripsi dan mungkin mengandung padding.
     * @return string|false Data tanpa padding, atau false jika padding tidak valid.
     */
    private function _pkcs7Unpad(string $data) {
        $length = strlen($data);
        if ($length === 0) {
            return false; // Data kosong tidak bisa di-unpad.
        }
        // Ambil nilai byte terakhir sebagai panjang padding yang diharapkan.
        $paddingValue = ord($data[$length - 1]);

        // Validasi nilai padding.
        if ($paddingValue === 0 || $paddingValue > self::AES_BLOCK_SIZE || $paddingValue > $length) {
            return false; // Padding tidak valid.
        }

        // Validasi bahwa semua byte padding memiliki nilai yang benar.
        for ($i = 1; $i <= $paddingValue; $i++) {
            if (ord($data[$length - $i]) !== $paddingValue) {
                return false; // Byte padding tidak konsisten.
            }
        }
        
        return substr($data, 0, $length - $paddingValue);
    }

    /**
     * Mengenkripsi plaintext menggunakan AES-128 mode CBC dengan PKCS#7 padding.
     * @param string $plaintext Plaintext yang akan dienkripsi.
     * @param string $iv Initialization Vector 16 byte (string biner).
     * @return string Ciphertext (string biner).
     * @throws InvalidArgumentException Jika panjang IV tidak 16 byte.
     * @throws Exception Jika terjadi error pada enkripsi blok internal.
     */
    public function encrypt(string $plaintext, string $iv): string {
        if (strlen($iv) !== self::AES_BLOCK_SIZE) {
            throw new InvalidArgumentException("Panjang IV harus " . self::AES_BLOCK_SIZE . " byte untuk mode CBC.");
        }

        // 1. Lakukan PKCS#7 padding pada plaintext.
        $paddedPlaintext = $this->_pkcs7Pad($plaintext, self::AES_BLOCK_SIZE);
        $plaintextBlocks = str_split($paddedPlaintext, self::AES_BLOCK_SIZE);
        
        $ciphertext = '';
        $previousCiphertextBlock = $iv; // Untuk blok pertama, XOR dengan IV.

        // 2. Proses enkripsi per blok dengan mode CBC.
        foreach ($plaintextBlocks as $block) {
            // P_i XOR C_{i-1} (atau IV untuk blok pertama)
            $blockToEncrypt = '';
            for ($i = 0; $i < self::AES_BLOCK_SIZE; $i++) {
                $blockToEncrypt .= chr(ord($block[$i]) ^ ord($previousCiphertextBlock[$i]));
            }
            
            // Enkripsi blok yang sudah di-XOR.
            $encryptedBlock = $this->_encryptBlockInternal($blockToEncrypt);
            $ciphertext .= $encryptedBlock;
            
            // Simpan ciphertext blok ini untuk XOR dengan plaintext blok berikutnya.
            $previousCiphertextBlock = $encryptedBlock;
        }
        
        return $ciphertext;
    }

    /**
     * Mendekripsi ciphertext menggunakan AES-128 mode CBC dan menghapus PKCS#7 padding.
     * @param string $ciphertext Ciphertext yang akan didekripsi (string biner).
     * @param string $iv Initialization Vector 16 byte (string biner) yang digunakan saat enkripsi.
     * @return string|false Plaintext asli, atau false jika terjadi error (misal padding salah).
     * @throws InvalidArgumentException Jika panjang IV atau ciphertext tidak valid.
     * @throws Exception Jika terjadi error pada dekripsi blok internal.
     */
    public function decrypt(string $ciphertext, string $iv) {
        if (strlen($iv) !== self::AES_BLOCK_SIZE) {
            throw new InvalidArgumentException("Panjang IV harus " . self::AES_BLOCK_SIZE . " byte untuk mode CBC.");
        }
        if (strlen($ciphertext) % self::AES_BLOCK_SIZE !== 0 || strlen($ciphertext) === 0) {
            throw new InvalidArgumentException("Panjang ciphertext harus kelipatan dari " . self::AES_BLOCK_SIZE . " byte dan tidak boleh kosong.");
        }

        $ciphertextBlocks = str_split($ciphertext, self::AES_BLOCK_SIZE);
        $decryptedPaddedPlaintext = '';
        $previousCiphertextBlock = $iv; // Untuk blok pertama, XOR dengan IV.

        // 1. Proses dekripsi per blok dengan mode CBC.
        foreach ($ciphertextBlocks as $block) {
            // Dekripsi blok ciphertext saat ini.
            $decryptedBlockIntermediate = $this->_decryptBlockInternal($block);
            
            // Hasil dekripsi di-XOR dengan C_{i-1} (atau IV untuk blok pertama).
            $plaintextBlockPadded = '';
            for ($i = 0; $i < self::AES_BLOCK_SIZE; $i++) {
                $plaintextBlockPadded .= chr(ord($decryptedBlockIntermediate[$i]) ^ ord($previousCiphertextBlock[$i]));
            }
            $decryptedPaddedPlaintext .= $plaintextBlockPadded;
            
            // Simpan ciphertext blok ini untuk XOR dengan hasil dekripsi blok berikutnya.
            $previousCiphertextBlock = $block;
        }
        
        // 2. Hapus PKCS#7 padding dari hasil dekripsi.
        $plaintext = $this->_pkcs7Unpad($decryptedPaddedPlaintext);
        
        // Jika unpadding gagal, itu bisa jadi indikasi kunci/IV salah atau data rusak.
        if ($plaintext === false) {
            // throw new Exception("Gagal melakukan unpadding PKCS#7. Data mungkin rusak atau kunci/IV salah.");
            // Untuk aplikasi, mengembalikan false mungkin lebih disukai daripada exception.
            return false; 
        }
        
        return $plaintext;
    }

} // <-- AKHIR DARI KELAS AES128_CBC
?>