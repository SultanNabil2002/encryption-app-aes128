<?php

// 1. Definisikan variabel spesifik untuk Halaman Informasi ini
$currentPageTitle = "Informasi Perusahaan"; // Judul untuk tab browser
$pageSpecificCssFilename = "informasi-styles.css"; // Nama file CSS khusus untuk halaman ini
$hideSearchInTopbar = true; // Sembunyikan search bar di topbar untuk halaman ini

require_once '../layouts/page_setup_header.php';

require_once '../layouts/header.php'; 
?>

<div class="dashboard-body-wrapper"> <?php // Wrapper untuk sidebar & konten utama ?>
    <?php
        // 4. Panggil file layout untuk Sidebar Anda (milik Anda: sidebar.php)
        require_once '../layouts/sidebar.php'; 
    ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars($currentPageTitle); ?></h1>
        </div>
        <div class="content-body">
            <div class="about-content">
                <div class="about-card">
                    <div class="cardHeader">
                        <h2>PT Laz Coal Mandiri (LCM)</h2>
                    </div>

                    <div class="company-logo-container">
                        <img src="../Assets/img/logoPT_LazCoalMandiri.png" alt="Logo PT Laz Coal Mandiri" class="company-logo">
                    </div>

                    <section class="about-section">
                        <h3>Pengantar</h3>
                        <p>PT Laz Coal Mandiri (LCM) adalah perusahaan yang bergerak di bidang jasa kontraktor pertambangan batubara. Sejak awal berdirinya, LCM telah berkomitmen untuk memberikan kontribusi dalam industri pertambangan nasional dengan standar operasional yang tinggi.</p>
                        <p>Perusahaan memulai perjalanannya dari site Angsana di bawah naungan PT Angsana Jaya Energy, yang kemudian bertransformasi dan pada tahun 2015 menjadi CV. Laz Coal, hingga akhirnya berkembang menjadi PT Laz Coal Mandiri. LCM berupaya menjadi perusahaan yang bermanfaat bagi semua kalangan, mulai dari perusahaan sendiri, karyawan, masyarakat setempat, hingga negara.</p>
                    </section>

                    <section class="about-section">
                        <h3>Visi Perusahaan</h3>
                        <p>"Dalam segala kegiatan berkomitmen untuk melindungi setiap orang, aset perusahaan, lingkungan dan masyarakat sekitar dengan tujuan menciptakan bisnis berjalan dengan aman, ramah lingkungan dan tidak ada kerugian akibat dari kecelakaan dan risiko operasi dapat di minimum kan serta menciptakan citra yang baik di masyarakat dan klien."</p>
                        <p><em>(Sumber: demo.lazcoalmandiri.co.id)</em></p>
                    </section>

                    <section class="about-section">
                        <h3>Misi Perusahaan</h3>
                        <p>Mewujudkan iklim kerja dengan penerapan aspek Keselamatan dan Kesehatan Kerja (K3) di PT Laz Coal Mandiri yang akan dicapai dengan:</p>
                        <ul>
                            <li>Berusaha menghilangkan semua kecelakaan kelas 1 dan kerusakan harta benda serta berkomitmen untuk mengurangi dan mencegah semua kecelakaan dan kerusakan harta benda dilingkungan kerja perusahaan.</li>
                            <li>Meletakkan tujuan dan sasaran program K3 untuk mencapai peningkatan produktivitas dengan menggunakan metode kerja yang terbaik, aman dan berkelanjutan.</li>
                            <li>Memastikan bahwa kebutuhan K3 selalu diberikan dan diutamakan dengan pertimbangan yang tepat sasaran dan sesuai dengan kebutuhan.</li>
                            <li>Menyediakan pelatihan serta peralatan dan alat yang tepat untuk karyawan agar terciptanya pekerjaan yang aman dan meningkatnya kinerja karyawan.</li>
                        </ul>
                        <p><em>(Sumber: demo.lazcoalmandiri.co.id)</em></p>
                    </section>
                    
                    <section class="about-section">
                        <h3>Nilai Inti Perusahaan</h3>
                        <p>Meskipun tidak disebutkan secara eksplisit dalam satu daftar "Nilai Inti" pada sumber yang terbatas, komitmen PT Laz Coal Mandiri terhadap aspek Keselamatan dan Kesehatan Kerja (K3), perlindungan lingkungan, serta kontribusi kepada masyarakat dan klien menunjukkan adanya nilai-nilai yang berpusat pada tanggung jawab, profesionalisme, dan keberlanjutan. Sebagai perusahaan di Indonesia, nilai-nilai AKHLAK (Amanah, Kompeten, Harmonis, Loyal, Adaptif, dan Kolaboratif) juga dapat menjadi pedoman.</p>
                        <p><em>(Catatan: Anda mungkin perlu mengonfirmasi nilai inti spesifik dari internal perusahaan jika tersedia.)</em></p>
                    </section>

                    <section class="about-section">
                        <h3>Produk dan Layanan (Utama & Aplikasi Ini)</h3>
                        <p>Sebagai kontraktor pertambangan, layanan utama PT Laz Coal Mandiri meliputi berbagai aspek dalam operasional penambangan batubara, termasuk namun tidak terbatas pada:
                            <ul>
                                <li>Jasa Penambangan (Mining Services)</li>
                                <li>Pengangkutan Batubara (Coal Hauling)</li>
                                <li>Manajemen dan perencanaan tambang</li>
                            </ul>
                        </p>
                        <p>Aplikasi Kriptografi internal ini merupakan salah satu wujud komitmen perusahaan dalam menjaga keamanan informasi. Layanan utama aplikasi ini:</p>
                        <ul>
                            <li>Enkripsi Dokumen Internal menggunakan AES-128.</li>
                            <li>Dekripsi Dokumen Internal yang telah terenkripsi.</li>
                            <li>Manajemen Pengguna untuk akses terkontrol.</li>
                        </ul>
                    </section>

                    <section class="about-section">
                        <h3>Informasi Kontak (Kantor Pusat)</h3>
                        <p>
                            <strong>PT Laz Coal Mandiri</strong><br>
                            Jalan Ir. P. M. Noor, Komplek Citra Garden City,<br>
                            Blok E No. 18 RT. 009 RW. 003,<br>
                            Kelurahan Sungai Ulin, Kecamatan Banjarbaru Utara,<br>
                            Kota Banjarbaru - Kalimantan Selatan, Indonesia.<br>
                        </p>
                        <p>
                            Website (Demo/Informasi): <a href="https://www.lazcoalmandiri.co.id" target="_blank">www.lazcoalmandiri.co.id</a> atau <a href="https://demo.lazcoalmandiri.co.id" target="_blank">demo.lazcoalmandiri.co.id</a>
                        </p>
                    </section>
                     <hr>
                    <section class="about-section">
                        <h3>Tentang Aplikasi Ini (Untuk Skripsi)</h3>
                        <p>Aplikasi Web Kriptografi ini dikembangkan oleh <strong>Rafli Adhies Attha (NIM: 2111501363)</strong> sebagai bagian dari pemenuhan Tugas Akhir Program Studi Teknik Informatika, Fakultas Teknologi Informasi, [Universitas Budi Luhur].</p>
                        <p>Aplikasi ini menggunakan algoritma AES-128 untuk enkripsi dan dekripsi file, dengan tujuan untuk menyediakan alat bantu pengamanan dokumen digital untuk PT LazCoal Mandiri.</p>
                    </section>
                </div>
            </div>
        </div>
        </div>
    </main>
</div> <?php // Penutup .dashboard-body-wrapper ?>

<?php
// 6. Panggil file script dan penutup HTML utama kita
require_once '../layouts/page_scripts_footer.php';
?>