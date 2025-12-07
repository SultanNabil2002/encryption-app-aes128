<?php
if (!isset($base_assets_url)) {
    // Definisi ulang sederhana jika dipanggil terpisah
    // ... (kode definisi ulang $base_assets_url seperti sebelumnya) ...
    $protocol_footer = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host_footer = $_SERVER['HTTP_HOST'];
    $script_name_footer = $_SERVER['SCRIPT_NAME'];
    $app_root_url_path_footer = '';
    $public_keyword_footer = '/public/';
    $public_pos_footer = strpos($script_name_footer, $public_keyword_footer);
    if ($public_pos_footer !== false) {
        $app_root_url_path_footer = substr($script_name_footer, 0, $public_pos_footer);
    } else {
        $path_parts_footer = explode('/', trim($script_name_footer, '/'));
        $projectNameGuess_footer = 'AplikasiWebKripto'; 
        if (isset($path_parts_footer[0]) && $path_parts_footer[0] === $projectNameGuess_footer) {
            $app_root_url_path_footer = '/' . $path_parts_footer[0];
        }
    }
    $app_root_url_path_footer = rtrim($app_root_url_path_footer, '/');
    $base_app_url_footer = $protocol_footer . $host_footer . $app_root_url_path_footer;
    $base_assets_url = $base_app_url_footer . '/public/assets';
}
?>
    <script src="<?php echo htmlspecialchars($base_assets_url); ?>/js/toggle.js"></script>
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    </body>
</html>