<?php
include 'db.php';  // memanggil koneksi database dari file db.php


$currentPage = basename($_SERVER['PHP_SELF']);

// STATUS ACTIVE MENU
$isHome      = ($currentPage == 'index.php');
$isProfil    = in_array($currentPage, ['sejarah.php', 'visi-misi.php', 'struktur-organisasi.php']);
$isBerita    = ($currentPage == 'berita.php');
$isPPDB      = ($currentPage == 'ppdb.php');
$isPrestasi  = ($currentPage == 'prestasi.php');
$isInformasi = in_array($currentPage, ['ekstrakulikuler.php', 'fasilitas.php', 'guru-dan-staff.php', 'alumni.php']);
$IsAdmin     = ($currentPage == 'login.php');

// Ambil gambar header
$sqlGambar = "SELECT gambar_header FROM header LIMIT 1";
$resultGambar = $conn->query($sqlGambar);
$gambarHeader = "default-header.jpg";
if ($resultGambar && $resultGambar->num_rows > 0) {
    $rowGambar = $resultGambar->fetch_assoc();
    $gambarHeader = $rowGambar['gambar_header'];
}

// Ambil data dari tabel visi_misi
$queryVisiMisi = "SELECT * FROM visi_misi ORDER BY id ASC";
$resultVisiMisi = $conn->query($queryVisiMisi);


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
  margin: 0 60px 0 auto; /* dorong ke kanan */
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

/* HEADER VISI MISI */
.visi-misi-header {
  position: relative;
  background-image: url('image/sekolah/gambar_sekolah1.jpg');
  background-size: cover;
  background-position: center;
  height: 300px;
  color: white;
  display: flex;
  align-items: center;
  text-align: left;
  padding-left: 40px;
  font-family: 'Poppins', sans-serif;
}

.visi-misi-header .overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(75, 115, 190, 0.7); /* overlay biru transparan */
  z-index: 1;
}

.visi-misi-header .header-content {
  position: relative;
  z-index: 2;
}

.visi-misi-header .breadcrumb {
  margin: 0;
  font-size: 16px;
  font-weight: 400;
}

.visi-misi-header .judul-header {
  font-size: 48px;
  font-weight: bold;
  margin: 0;
}


/* KONTEN VISI MISI */
.visi-misi-content {
    font-family: 'Poppins', sans-serif;
    padding: 30px 30px;
  }

  .visi-misi-item {
    max-width: 800px;
    margin: 0 auto;
  }

  .visi-title,
  .misi-title {
    font-size: 28px;
    font-weight: bold;
    color: #000;
    margin-top: 30px;
    margin-bottom: 15px;
  }

  .visi-text ul,
  .misi-text ul {
    padding-left: 20px;
    margin-top: 0;
    margin-bottom: 20px;
  }

  .visi-text li,
  .misi-text li {
    font-size: 16px;
    color: #555;
    margin-bottom: 8px;
    line-height: 1.6;
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
        <a href="index" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">HOME</a>
      </li>

      <li>
        <a class="no-link <?= $isProfil ? 'parent-active' : '' ?>">PROFIL</a>
        <ul class="dropdown-menu">
          <li><a href="sejarah" class="<?= $currentPage == 'sejarah.php' ? 'active' : '' ?>">Sejarah</a></li>
          <li><a href="visi-misi" class="<?= $currentPage == 'visi-misi.php' ? 'active' : '' ?>">Visi dan Misi</a></li>
          <li><a href="struktur-organisasi" class="<?= $currentPage == 'struktur-organisasi.php' ? 'active' : '' ?>">Struktur Organisasi</a></li>
        </ul>
      </li>

      <li><a href="berita" class="<?= $currentPage == 'berita.php' ? 'active' : '' ?>">BERITA</a></li>
      <li><a href="ppdb" class="<?= $currentPage == 'ppdb.php' ? 'active' : '' ?>">PPDB</a></li>
      <li><a href="prestasi" class="<?= $currentPage == 'prestasi.php' ? 'active' : '' ?>">PRESTASI</a></li>

      <li>
        <a class="no-link <?= $isInformasi ? 'parent-active' : '' ?>">INFORMASI</a>
        <ul class="dropdown-menu">
          <li><a href="ekstrakulikuler" class="<?= $currentPage == 'ekstrakulikuler.php' ? 'active' : '' ?>">Ekstrakulikuler</a></li>
          <li><a href="fasilitas" class="<?= $currentPage == 'fasilitas.php' ? 'active' : '' ?>">Fasilitas</a></li>
          <li><a href="guru-dan-staff" class="<?= $currentPage == 'guru-dan-staff.php' ? 'active' : '' ?>">Guru dan Staff</a></li>
          <li><a href="alumni" class="<?= $currentPage == 'alumni.php' ? 'active' : '' ?>">Alumni</a></li>
        </ul>
      </li>

      <li>
        <a class="no-link <?= $isAdmin ? 'parent-active' : '' ?>">ADMIN</a>
        <ul class="dropdown-menu">
          <li><a href="dashboard_admin/login" class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">Login</a></li>
        </ul>
      </li>

    </ul>
  </div>
</nav>


<!-- HEADER VISI MISI -->
<section class="visi-misi-header">
  <div class="overlay"></div>
  <div class="container header-content">
    <p class="breadcrumb">Profil / Visi & Misi</p>
    <h1 class="judul-header">Visi & Misi</h1>
  </div>
</section>


<!-- KONTEN VISI MISI -->
<section class="visi-misi-content">
  <div class="container">
    <?php
    $semuaVisi = '';
    $semuaMisi = '';

    while ($row = $resultVisiMisi->fetch_assoc()) {
        $semuaVisi .= $row['visi'] . "\n";
        $semuaMisi .= $row['misi'] . "\n";
    }

    function formatToList($text) {
        $items = array_filter(array_map('trim', explode("\n", $text)));
        $html = "<ul>";
        foreach ($items as $item) {
            $html .= "<li>" . htmlspecialchars($item) . "</li>";
        }
        $html .= "</ul>";
        return $html;
    }
    ?>

    <div class="visi-misi-item">
      <h3 class="visi-title">Visi Sekolah</h3>
      <div class="visi-text">
        <?= formatToList($semuaVisi) ?>
      </div>

      <h3 class="misi-title">Misi Sekolah</h3>
      <div class="misi-text">
        <?= formatToList($semuaMisi) ?>
      </div>
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
  




</body>
</html>
