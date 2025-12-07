# Web-Based File Security System using AES-128

![Language](https://img.shields.io/badge/language-PHP%207.x-purple.svg)
![Database](https://img.shields.io/badge/database-MySQL-blue.svg)
![Status](https://img.shields.io/badge/status-Active-success.svg)

Aplikasi pengamanan dokumen berbasis web yang dibangun untuk melindungi kerahasiaan file digital menggunakan algoritma kriptografi **Advanced Encryption Standard (AES)** dengan panjang kunci 128-bit.

Proyek ini bertujuan untuk menyediakan solusi praktis bagi pengguna dalam mengamankan berkas penting dari akses yang tidak sah melalui antarmuka web yang sederhana dan mudah digunakan.

## 📋 Fitur Utama

### 🔐 Modul Kriptografi
* **Algoritma AES-128:** Implementasi logika enkripsi dan dekripsi menggunakan standar AES dengan blok data 128-bit.
* **Keamanan File:** File yang dienkripsi akan dikonversi menjadi format yang tidak dapat dibaca tanpa kunci yang tepat.
* **Integritas:** Memastikan file hasil dekripsi kembali ke format aslinya tanpa kerusakan data.

### 💻 Fungsionalitas Sistem
* **Manajemen Pengguna:** Sistem Login dan Registrasi untuk membatasi akses aplikasi.
* **Dashboard User:** Antarmuka untuk melihat daftar file yang telah diproses.
* **File Processing:**
    * Upload dokumen.
    * Enkripsi dokumen (mengunci file).
    * Dekripsi dokumen (membuka file).
    * Download hasil pemrosesan.

## ⚙️ Spesifikasi Teknis

* **Bahasa Pemrograman:** PHP Versi 7.x
* **Database:** MySQL
* **Server:** Apache (via XAMPP/WAMP)
* **Frontend:** HTML5, CSS3, JavaScript Dasar

## 📦 Panduan Instalasi & Penggunaan

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal (Localhost):

### 1. Persiapan Lingkungan
Pastikan Anda telah menginstal **XAMPP** atau server lokal sejenis yang mendukung **PHP 7**.

### 2. Setup Database
1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Buat database baru dengan nama:
   `db_kripto_web`
3. Import file `database.sql` yang tersedia di dalam folder repository ini ke dalam database tersebut.

### 3. Konfigurasi Koneksi
Buka file konfigurasi database (biasanya di `config/koneksi.php` atau `config.php`) dan sesuaikan pengaturan berikut jika diperlukan:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kripto_web";


## 📸 Screenshots
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/b3bc2e94-156f-49da-bf9b-85e29d0c7195" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/9f55c93b-d8a2-424b-a81e-217fdc162a69" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/610e51db-0dbd-4a61-9353-7b04abb539d9" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/6b4ae4f9-b432-47a4-a780-505c995378a5" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/766a88a9-209b-4eaa-b176-eb5903e05fa5" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/058ff287-c95b-4eab-bc07-e8393c79b273" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/641fb27f-4124-4c03-8e3a-ffcca927281b" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/719bf2c0-8b46-4c7f-a6aa-99bb2f3a41c2" />

