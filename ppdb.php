<?php
include 'db.php';  // memanggil koneksi database dari file db.php


$currentPage = 'page_ppdb.php';

$isHome = ($currentPage == 'index.php');
$isProfil = in_array($currentPage, ['page_sejarah.php', 'page_visimisi.php', 'page_strukturorganis.php']);
$isBerita = ($currentPage == 'page_berita.php');
$isPPDB = ($currentPage == 'page_ppdb.php');
$isPrestasi = ($currentPage == 'page_prestasi.php');
$isInformasi = in_array($currentPage, ['page_ekstrakulikuler.php', 'page_fasilitas.php', 'page_gurustaff.php', 'page_alumni.php']);

// Ambil gambar header
$sqlGambar = "SELECT gambar_header FROM header LIMIT 1";
$resultGambar = $conn->query($sqlGambar);
$gambarHeader = "default-header.jpg";
if ($resultGambar && $resultGambar->num_rows > 0) {
    $rowGambar = $resultGambar->fetch_assoc();
    $gambarHeader = $rowGambar['gambar_header'];
}

$pesan = "";

// Ambil link PPDB dari database
$queryLink = "SELECT link_ppdb FROM ppdb ORDER BY updated_at DESC LIMIT 1";
$resultLink = mysqli_query($conn, $queryLink);

$linkPPDB = "#"; // default jika belum ada link

if ($resultLink && mysqli_num_rows($resultLink) > 0) {
    $rowLink = mysqli_fetch_assoc($resultLink);
    $linkPPDB = $rowLink['link_ppdb']; // perbaikan di sini
}



/// Ambil data kontak (misalnya hanya 1 data, karena kontak biasanya satu set)
$query = "SELECT alamat, email, no_whatsapp, instagram, facebook, youtube, link_gmaps FROM kontak LIMIT 1";
$result = mysqli_query($conn, $query);

// Inisialisasi default
$kontak = [
    'alamat' => '',
    'email' => '',
    'no_whatsapp' => '',
    'instagram' => '',
    'facebook' => '',
    'youtube' => '',
    'link_gmaps' => ''
];

// Ambil data dari database jika tersedia
if ($result && mysqli_num_rows($result) > 0) {
    $kontak = mysqli_fetch_assoc($result);
}

// Tutup koneksi database
mysqli_close($conn);
?>




<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>

<style>
    body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}
/* === NAVIGATION STYLE === */
    nav {
  width: 100%;
  position: fixed;
  background: #003366;
  padding: 8px 0;
  top: 0;
  left: 0;
  z-index: 10;
  box-sizing: border-box;
}
.nav-container {
  max-width: 1300px; /* biar lebih lebar */
  margin: 0 40px 0 auto; /* dorong ke kanan */
  display: flex;
  justify-content: flex-end; /* tetap kanan */
  align-items: center;
  height: 40px;
}


nav ul {
  list-style: none;
  display: flex;
  margin: 0;
  margin-left: auto;
  padding: 0;
  font-size: 14px;
}
nav ul li {
  position: relative;
  margin-left: 15px; /* jarak antar menu lebih renggang */
}

nav ul li:first-child {
  margin-left: 0;
}
/* === Umum === */
nav ul li a {
  text-decoration: none;
  color: white;
  font-weight: bold;
  padding: 5px 10px;
  border-radius: 5px;
  transition: background-color 0.3s, color 0.3s;
  display: inline-block;
}

/* Hover semua menu aktif */
nav ul li a:hover {
  background-color: #FFCC00;
  color: #003366;
}

/* Aktif menu biasa (HOME, BERITA, dsb) */
nav ul li a.active {
  background-color: transparent;
  color: #FFCC00 !important;
  font-weight: bold;
}

/* Aktif untuk parent menu (PROFIL, INFORMASI) */
nav ul li a.parent-active {
  background-color: transparent;
  color: #FFCC00 !important;
  font-weight: bold;
}

/* === DROPDOWN STYLE === */
.nav-container ul li .dropdown-menu {
  display: none;
  position: absolute;
  background-color: white;
  top: 100%;
  left: 0;
  min-width: 180px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  padding: 0;
  border-radius: 5px;
}

.nav-container ul li:hover .dropdown-menu {
  display: block;
}

.nav-container ul li .dropdown-menu li {
  width: 100%;
  margin: 0;
}

.nav-container ul li .dropdown-menu li a {
  color: #003366;
  padding: 10px 15px;
  display: inline-block;
  font-weight: normal;
  text-decoration: none;
  position: relative;
  transition: color 0.3s ease, font-weight 0.3s ease;
}

/* Efek underline kuning saat hover submenu */
.nav-container ul li .dropdown-menu li a::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 3px;
  transform: translateX(-50%) scaleX(0);
  transform-origin: center;
  width: 80%;
  height: 2px;
  background-color: #FFCC00;
  transition: transform 0.3s ease;
}

.nav-container ul li .dropdown-menu li a:hover {
  font-weight: bold;
}

.nav-container ul li .dropdown-menu li a:hover::after {
  transform: translateX(-50%) scaleX(1);
}

/* ===== Header PPDB - Versi Kotak ===== */
.ppdb-header {
  position: relative;
  background-image: url('image/sekolah/gambar_sekolah1.jpg');
  background-position: center;
  height: 350px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.ppdb-header::after {
  content: "";
  position: absolute;
  top: 0; left: 0;
  width: 100%;
  height: 100%;
  background: rgba(28, 68, 139, 0.7); /* Overlay gelap */
  z-index: 1;
}

.ppdb-header .header-content {
  position: relative;
  z-index: 2;
  color: #fff;
}
.ppdb-header .judul-header {
  font-size: 36px;       /* Ukuran huruf judul */
  font-weight: 700;
  color: #f9fafb;         /* Warna putih terang */
  margin: 15px;
}

.ppdb-header .subjudul {
  font-size: 30px;        /* Ukuran huruf subjudul */
  font-weight: 400;
  color: #e0e7ff;         /* Biru-putih lembut */
  margin-top: 8px;
}
/* ===== Form PPDB ===== */
.ppdb-form {
  max-width: 650px;
  margin: 60px auto;
  padding: 30px 25px;
  font-family: 'Poppins', sans-serif;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.ppdb-form form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.ppdb-form label {
  font-weight: 600;
  font-size: 15px;
}

.ppdb-form input,
.ppdb-form select,
.ppdb-form textarea {
  padding: 12px 16px;
  font-size: 15px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.ppdb-form input:focus,
.ppdb-form select:focus,
.ppdb-form textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  outline: none;
}

.ppdb-form textarea {
  min-height: 100px;
}

/* Tombol Kirim */
.ppdb-form button {
  background-color: #003366;
  color: white;
  padding: 14px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.ppdb-form button:hover {
  background-color: #1d4ed8;
}

/* ===== Responsive ===== */
@media screen and (max-width: 600px) {
  .ppdb-header {
    height: 220px;
    padding: 0 20px;
  }

  .ppdb-header .judul-header {
    font-size: 26px;
  }

  .ppdb-header .subjudul {
    font-size: 16px;
  }

  .ppdb-form {
    margin: 40px 20px;
    padding: 20px;
  }
}


/* Footer Container */
.footer-map-content {
  background-color: #003366;
  color: #fafafa;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  padding: 30px 40px;
  margin-top: 80px;
  gap: 50px;
}

/* Kolom Kiri, Tengah, Kanan */
.footer-map-content > div {
  flex: 1;
  min-width: 250px;
}

/* Judul */
.footer-map-content h4 {
  margin-bottom: 10px;
  font-size: 18px;
  color: #ffffff;
}

/* Paragraf */
.footer-map-content p {
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 10px;
  color: #f0f0f0;
}

/* Map iframe */
.map-iframe iframe {
  width: 100%;
  height: 180px;
  margin-top: 20px;
  border: 0;
  border-radius: 8px;
}
.map-contact {
  margin-left: 60px; 
}
/* Ikon Sosial */
.footer-icons {
  display: flex;
  gap: 15px;
  font-size: 26px;
  margin-top: 30px;
}

.footer-icons a {
 color: #fafafa;
  transition: color 0.3s;
}
/* Warna muncul hanya saat hover */
.footer-icons a[title="WhatsApp"]:hover { color: #25D366; }
.footer-icons a[title="Instagram"]:hover { color: #C13584; }
.footer-icons a[title="Facebook"]:hover { color: #1877F2; }
.footer-icons a[title="Email"]:hover { color: #aa2215; }
.footer-icons a[title="YouTube"]:hover {color: #FF0000;}


.footer-copyright {
  max-width: 100%;
  background-color: #064c91; /* biru medium lebih terang */
  padding: 10px 40px;
  font-size: 17px;
  color: #fefefe; /* warna teks putih kebiruan */
  text-align: center;
  font-family: Arial, sans-serif;
  user-select: none;
  box-sizing: border-box;
}

/* Responsive: stacked on small screens */
@media (max-width: 650px) {
  .footer {
    flex-direction: column;
    align-items: center;
  }
  .footer-map, .footer-icons {
    flex: unset;
    width: 100%;
  }
  .footer-icons {
    flex-direction: row;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
  }
}

</style>
</head>
<body>
 <nav>
  <div class="nav-container">
    <ul>
      <li>
        <a href="index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">HOME</a>
      </li>

      <li>
        <a href="#" class="no-link">PROFIL</a>
        <ul class="dropdown-menu">
          <li><a href="page_sejarah.php">Sejarah</a></li>
          <li><a href="page_visimisi.php">Visi dan Misi</a></li>
          <li><a href="page_strukturorganis.php">Struktur Organisasi</a></li>
        </ul>
      </li>

          <li><a href="page_berita.php" class="<?= $isBerita ? 'active' : '' ?>">BERITA</a></li>
          <li><a href="page_ppdb.php" class="<?= $isPPDB ? 'active' : '' ?>">PPDB</a></li>
          <li><a href="page_prestasi.php" class="<?= $isPrestasi ? 'active' : '' ?>">PRESTASI</a></li>

          <li>
            <a href="#" class="<?= 'no-link' . ($isInformasi ? ' parent-active' : '') ?>">INFORMASI</a>
            <ul class="dropdown-menu">
              <li><a href="page_ekskul.php" class="<?= $currentPage == 'ekstrakulikuler.php' ? 'active' : '' ?>">Ekstrakulikuler</a></li>
              <li><a href="page_fasilitas.php" class="<?= $currentPage == 'fasilitas.php' ? 'active' : '' ?>">Fasilitas</a></li>
              <li><a href="page_guru_staff.php" class="<?= $currentPage == 'guru_staff.php' ? 'active' : '' ?>">Guru dan Staff</a></li>
              <li><a href="page_alumni.php" class="<?= $currentPage == 'alumni.php' ? 'active' : '' ?>">Alumni</a></li>
            </ul>
          </li>
          <li><a href="#" class="<?= 'no-link' . ($isAdmin ? ' parent-active' : '') ?>">ADMIN</a>
<ul class="dropdown-menu">
  <li><a href="dashboard_admin/login.php" class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">Login</a></li>
</ul>
</li>
      </ul>
    </div>
  </nav>

  
<!-- HEADER -->
<div class="ppdb-header">
  <div class="header-content">
    <h1 class="judul-header">Penerimaan Peserta Didik Baru (PPDB)</h1>
    <p class="subjudul">SD Muhammadiyah Purwokerto Tahun Ajaran 2025/2026</p>
  </div>
</div>

<!-- INFORMASI & TIMELINE -->
<section class="info-ppdb" style="padding: 60px 20px; background-color: #f7f7f7;">
  <div class="container" style="max-width: 800px; margin: auto; text-align: center;">
    <h2 style="margin-bottom: 20px; font-size: 28px;">Informasi Penting PPDB</h2>
    <p style="margin-bottom: 40px; font-size: 16px; color: #555;">
      Penerimaan Peserta Didik Baru (PPDB) SD Muhammadiyah Purwokerto dapat dilakukan secara <strong>online</strong> maupun dengan <strong>datang langsung ke sekolah</strong>. Berikut tahapan pendaftaran:
    </p>

    <div class="timeline" style="display: inline-block; text-align: left; margin: auto;">
      <ol style="list-style: none; padding: 0; counter-reset: step;">
        
        <!-- Langkah 1 -->
        <li style="margin-bottom: 30px; position: relative; padding-left: 50px;">
          <span style="position: absolute; left: 0; top: 0; background: rgb(38, 103, 215); color: #fff; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 32px; font-weight: bold; counter-increment: step;">1</span>
          <strong>Pendaftaran Calon Siswa</strong><br>
          Pendaftaran dapat dilakukan melalui formulir online yang tersedia atau dengan datang langsung ke sekolah.
        </li>

        <!-- Langkah 2 -->
        <li style="margin-bottom: 30px; position: relative; padding-left: 50px;">
          <span style="position: absolute; left: 0; top: 0; background: rgb(38, 103, 215); color: #fff; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 32px; font-weight: bold; counter-increment: step;">2</span>
          <strong>Penyerahan Dokumen Persyaratan</strong><br>
          Calon siswa menyerahkan dokumen administrasi, meliputi:<br>
          - Fotokopi Akta Kelahiran<br>
          - Fotokopi Kartu Keluarga (KK)<br>
          - Fotokopi KTP Ayah dan Ibu<br>
        </li>

        <!-- Langkah 3 -->
        <li style="margin-bottom: 30px; position: relative; padding-left: 50px;">
          <span style="position: absolute; left: 0; top: 0; background: rgb(38, 103, 215); color: #fff; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 32px; font-weight: bold; counter-increment: step;">3</span>
          <strong>Verifikasi dan Seleksi</strong><br>
          Pihak sekolah melakukan verifikasi kelengkapan berkas serta seleksi administrasi sesuai ketentuan yang berlaku.
        </li>

        <!-- Langkah 4 -->
        <li style="margin-bottom: 30px; position: relative; padding-left: 50px;">
          <span style="position: absolute; left: 0; top: 0; background:rgb(38, 103, 215); color: #fff; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 32px; font-weight: bold; counter-increment: step;">4</span>
          <strong>Pengumuman Hasil Seleksi</strong><br>
          Hasil seleksi akan diinformasikan melalui WhatsApp kepada orang tua/wali. Selanjutnya, orang tua/wali akan masuk ke dalam grup resmi wali murid.
      </ol>
    </div>

    <!-- TOMBOL LANGSUNG KE GOOGLE FORM -->
    <div style="margin-top: 30px;">
      <a href="<?= htmlspecialchars($linkPPDB ?? '#') ?>" target="_blank"
        style="background-color:#003366; color:white; text-decoration:none; padding:12px 24px; font-size:16px; border-radius:8px; display:inline-block;">
        Daftar Online Sekarang
      </a>

    </div>
  </div>
</section>





<!-- FOOTER -->

<div class="footer-map-content">
  <!-- Kiri: Judul & Alamat -->
  <div class="map-text">
    <h4>Lokasi Kami</h4>
    <p style="color:rgb(250, 250, 250); font-size: 14px; margin-bottom: 10px;">
      <?= htmlspecialchars($kontak['alamat'] ?? 'Alamat belum tersedia') ?>
    </p>
  </div>

  <!-- Tengah: Google Maps -->
  <div class="map-iframe">
    <?php if (!empty($kontak['link_gmaps'])): ?>
      <iframe 
        src="<?= htmlspecialchars($kontak['link_gmaps']) ?>" 
        width="100%" height="180" style="border:0; border-radius:8px;" 
        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    <?php else: ?>
      <p>Alamat lokasi belum tersedia.</p>
    <?php endif; ?>
  </div>

  <!-- Kanan: Kontak Kami + Ikon Sosial -->
  <div class="map-contact">
    <h4>Kontak Kami</h4>
    <div class="footer-icons">
      <?php if (!empty($kontak['email'])): ?>
        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($kontak['email']) ?>" 
          target="_blank" 
          title="Email">
          <i class="fas fa-envelope"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($kontak['no_whatsapp'])): ?>
        <a href="https://wa.me/<?= preg_replace('/\D/', '', $kontak['no_whatsapp']) ?>" target="_blank" title="WhatsApp">
          <i class="fab fa-whatsapp"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($kontak['instagram'])): ?>
        <a href="https://instagram.com/<?= htmlspecialchars($kontak['instagram']) ?>" target="_blank" title="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($kontak['facebook'])): ?>
        <a href="<?= htmlspecialchars($kontak['facebook']) ?>" target="_blank" title="Facebook">
          <i class="fab fa-facebook"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($kontak['youtube'])): ?>
        <a href="<?= htmlspecialchars($kontak['youtube']) ?>" target="_blank" title="YouTube">
          <i class="fab fa-youtube"></i>
        </a>
      <?php endif; ?>

    </div>
  </div>
</div>




<div class="footer-copyright">
    &copy; <?= date('Y') ?> SD Muhammadiyah Purwokerto. All rights reserved.
  </div>

</form>
  


<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.onload = function() {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Pendaftaran berhasil dikirim.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        // Ini menghapus ?success=1 dari URL
        window.history.replaceState(null, '', window.location.pathname);
      });
    };
  </script>
<?php endif; ?>

</body>
</html>
