<?php
// AplikasiWebKripto/public/dashboard/enkripsi.php

// 1. Variabel spesifik halaman dan pemanggilan header utama
$currentPageTitle = "Enkripsi File";
$pageSpecificCssFilename = "enkripsi_styles.css"; // Akan kita buat nanti
$hideSearchInTopbar = true; // Search bar tidak ditampilkan di halaman ini

require_once '../layouts/page_setup_header.php'; // $mysqli, $role_loggedin, $dashboard_link_base, catat_log_aktivitas() tersedia

// 2. Otorisasi: Hanya Super Admin yang boleh mengakses halaman enkripsi
if ($role_loggedin !== 'super_admin') {
    $_SESSION['dashboard_error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Enkripsi File.";
    header('Location: ' . ($dashboard_link_base ?? 'index.php') . '/index.php'); 
    exit();
}

// 3. Menampilkan pesan feedback dari proses enkripsi (jika ada)
$encrypt_success_message = '';
$encrypt_error_message = '';

if (isset($_SESSION['encrypt_success'])) {
    $encrypt_success_message = $_SESSION['encrypt_success'];
    unset($_SESSION['encrypt_success']); // Hapus setelah ditampilkan
}
if (isset($_SESSION['encrypt_error'])) {
    $encrypt_error_message = $_SESSION['encrypt_error'];
    unset($_SESSION['encrypt_error']); // Hapus setelah ditampilkan
}

// 4. Panggil file layout untuk Topbar (milik Anda: header.php)
require_once '../layouts/header.php'; 
?>

<div class="dashboard-body-wrapper"> <?php // Wrapper untuk sidebar & konten utama ?>
    <?php
        // 5. Panggil file layout untuk Sidebar (milik Anda: sidebar.php)
        require_once '../layouts/sidebar.php'; 
    ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            
            <div class="card">
                <h2>Formulir Enkripsi Dokumen (AES-128)</h2>
                <p>Unggah file yang ingin Anda enkripsi. Masukkan password enkripsi (chiperkey) dan deskripsi untuk file tersebut.</p>

                <?php if (!empty($encrypt_success_message)): ?>
                    <div class="message success-message">
                        <h4>Enkripsi Berhasil!</h4>
                        <?php 
                        // Pesan sukses bisa jadi array atau string, kita coba pecah jika formatnya spesifik
                        if (is_array($encrypt_success_message)) {
                            echo "<p><strong>File Asli:</strong> " . htmlspecialchars($encrypt_success_message['original_filename'] ?? '-') . "</p>";
                            echo "<p><strong>File Terenkripsi:</strong> " . htmlspecialchars($encrypt_success_message['encrypted_filename'] ?? '-') . "</p>";
                            echo "<p><strong>Ukuran Asli:</strong> " . htmlspecialchars($encrypt_success_message['original_size_kb'] ?? '-') . " KB</p>";
                            echo "<p><strong>Ukuran Terenkripsi:</strong> " . htmlspecialchars($encrypt_success_message['encrypted_size_kb'] ?? '-') . " KB</p>";
                            echo "<p><strong>Durasi Enkripsi:</strong> " . htmlspecialchars($encrypt_success_message['duration'] ?? '-') . " detik</p>";
                            if (!empty($encrypt_success_message['description'])) {
                                echo "<p><strong>Deskripsi:</strong> " . nl2br(htmlspecialchars($encrypt_success_message['description'])) . "</p>";
                            }
                        } else {
                            echo nl2br(htmlspecialchars($encrypt_success_message)); 
                        }
                        ?>
                        <p style="margin-top:10px;"><a href="daftar_dokumen.php" class="btn-link-to-list">Lihat Daftar Dokumen</a></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($encrypt_error_message)): ?>
                    <div class="message error-message">
                        <h4>Enkripsi Gagal!</h4>
                        <p><?php echo nl2br(htmlspecialchars($encrypt_error_message)); ?></p>
                    </div>
                <?php endif; ?>

                <?php // Hanya tampilkan form jika tidak ada pesan sukses (agar tidak langsung enkripsi lagi)
                if (empty($encrypt_success_message)): ?>
                <form action="enkripsi_proses.php" method="POST" enctype="multipart/form-data" class="form-encrypt">
                    <div class="form-group">
                        <label for="file_to_encrypt">Pilih File:</label>
                        <input type="file" id="file_to_encrypt" name="file_to_encrypt" required>
                        <small class="form-text">Format yang diizinkan: docx, doc, txt, pdf, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif, mp3, mp4, mov, mpg. Maks: 8MB.</small>
                    </div>

                    <div class="form-group">
                        <label for="encryption_password">Password Enkripsi (Chiperkey):</label>
                        <input type="password" id="encryption_password" name="encryption_password" required minlength="3"> 
                    </div>
                    
                    <div class="form-group">
                        <label for="file_description">Deskripsi File (Opsional):</label>
                        <textarea id="file_description" name="file_description" rows="3" placeholder="Catatan singkat mengenai file ini..."></textarea>
                    </div>

                    <button type="submit" name="encrypt_submit_button" class="btn-submit-encrypt">Enkripsi File Sekarang</button>
                </form>
                <?php endif; ?>

            </div> </div> </main> </div> <?php // Penutup .dashboard-body-wrapper ?>

<?php
// 7. Panggil file script dan penutup HTML utama kita
require_once '../layouts/page_scripts_footer.php';
?>