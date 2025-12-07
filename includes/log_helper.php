<?php
// AplikasiWebKripto/includes/log_helper.php

if (!function_exists('catat_log_aktivitas')) {
    /**
     * Mencatat aktivitas ke tabel activity_logs.
     * Membutuhkan koneksi $mysqli yang sudah aktif.
     */
    function catat_log_aktivitas(
        mysqli $mysqli_conn, 
        ?int $pelaku_user_id, // ID pengguna yang melakukan aksi (bisa null)
        string $action_type, 
        string $description, 
        ?int $target_document_id = null, 
        ?int $target_user_id = null // ID pengguna yang menjadi target dari aksi ini (misal: saat user baru dibuat)
    ) {
        $username_pelaku_log = 'Sistem/Anonim'; // Default

        if ($pelaku_user_id !== null) {
            // Ambil username dari pelaku_user_id
            $stmt_user = $mysqli_conn->prepare("SELECT username FROM users WHERE id = ?");
            if ($stmt_user) {
                $stmt_user->bind_param("i", $pelaku_user_id);
                $stmt_user->execute();
                $result_user = $stmt_user->get_result();
                if ($user_info = $result_user->fetch_assoc()) {
                    $username_pelaku_log = $user_info['username'];
                }
                $stmt_user->close();
            }
        } elseif (strpos(strtolower($description), 'gagal untuk username:') !== false) {
            // Jika login gagal dan user_id pelaku null
            preg_match('/username: "([\w.-]+)"/i', $description, $matches);
            if (isset($matches[1])) {
                $username_pelaku_log = $matches[1] . " (percobaan)";
            }
        } elseif (strpos(strtolower($description), 'Pengguna baru "') !== false && $target_user_id !== null) {
        }


        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Tidak Diketahui';

        $sql_log = "INSERT INTO activity_logs 
                        (user_id, username, action_type, target_document_id, target_user_id, description, ip_address, timestamp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt_log = $mysqli_conn->prepare($sql_log);
        if ($stmt_log) {
            $stmt_log->bind_param("ississs", 
                $pelaku_user_id,
                $username_pelaku_log,
                $action_type,
                $target_document_id,
                $target_user_id,
                $description,
                $ip_address
            );
            // Eksekusi tanpa memeriksa hasil untuk log, tapi idealnya ada error handling
            $stmt_log->execute(); 
            $stmt_log->close();
            return true;
        } else {
            // error_log("Gagal mempersiapkan statement untuk mencatat log: " . $mysqli_conn->error);
            return false;
        }
    }
}
?>