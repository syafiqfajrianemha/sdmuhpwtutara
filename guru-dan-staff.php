<?php
include 'db.php';  // koneksi database

$currentPage = basename($_SERVER['PHP_SELF']);

$isHome = ($currentPage == 'index.php');
$isProfil = in_array($currentPage, ['page_sejarah.php', 'page_visimisi.php', 'page_strukturorganis.php']);
$isBerita = ($currentPage == 'page_berita.php');
$isPPDB = ($currentPage == 'page_ppdb.php');
$isPrestasi = ($currentPage == 'page_prestasi.php');
$isInformasi = in_array($currentPage, ['page_ekskul.php', 'page_fasilitas.php', 'page_guru_staff.php', 'page_alumni.php']);

// Ambil gambar header
$sqlGambar = "SELECT gambar_header FROM header LIMIT 1";
$resultGambar = $conn->query($sqlGambar);
$gambarHeader = "default-header.jpg";
if ($resultGambar && $resultGambar->num_rows > 0) {
    $rowGambar = $resultGambar->fetch_assoc();
    $gambarHeader = $rowGambar['gambar_header'];
}

// Ambil data guru dan staff
// Ambil data guru & staff urut berdasarkan urutan dan nama
$query = "SELECT * FROM guru_staff ORDER BY urutan ASC, nama ASC";
$result = $conn->query($query);

// Siapkan array untuk kategori
$data = [
    'Kepala Sekolah' => [],
    'Wakil Kepala Sekolah' => [],
    'Guru' => [],
    'Staff' => [],
];

// Masukkan data ke kategori sesuai urutan
while ($row = $result->fetch_assoc()) {
    switch ($row['urutan']) {
        case 1:
            $data['Kepala Sekolah'][] = $row;
            break;
        case 2:
            $data['Wakil Kepala Sekolah'][] = $row;
            break;
        case 3:
            $data['Guru'][] = $row;
            break;
        case 4:
            $data['Staff'][] = $row;
            break;
    }
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

/* Header */
.guru-staff-header {
  position: relative;
  background-image: url('image/sekolah/gambar_sekolah1.jpg');
  background-size: cover;
  background-position: center;
  height: 300px;
  display: flex;
  align-items: center;
  padding-left: 40px;
  color: white;
  font-family: 'Poppins', sans-serif;
}

.guru-staff-header .overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(75, 115, 190, 0.7);
  z-index: 1;
}

.guru-staff-header .header-content {
  position: relative;
  z-index: 2;
}

.breadcrumb {
  font-size: 16px;
  margin: 0;
}

.judul-header {
  font-size: 40px;
  font-weight: bold;
  margin: 10px 0 0 0;
}
/*  GURU DAN STAFF */
.guru-staff-section {
  padding: 60px 120px;
}
.kategori-title {
  font-size: 24px;
  margin-bottom: 20px;
  font-weight: bold;
  color: #222;
  border-bottom: 5px solid rgb(3, 80, 156); /* garis bawah abu */
  padding-bottom: 8px;
  margin-top: 40px;
}

.guru-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 24px; /* jarak antar kartu */
  justify-content: flex-start;
}
.guru-card {
  background-color: #f0f0f0; /* bingkai abu muda */
  border-radius: 16px;
  border: 1px solid #ddd; /* border abu tipis */
  width: 220px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05); /* efek shadow lembut */
  transition: transform 0.3s ease;
}

.guru-card:hover {
  transform: translateY(-5px);
}


.guru-image-wrapper {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 12px;
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
  height: 250px; /* tinggi tetap */
  overflow: hidden; /* sembunyikan bagian yang kelebihan */
}

.guru-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* foto otomatis crop supaya penuh */
  border-radius: 8px;
}

.guru-info {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 12px 8px;
  width: 100%;
  text-align: center;
}

.guru-nama {
  font-weight: bold;
  margin-bottom: 4px;
  font-size: 16px;
  color: #222;
}

.guru-jabatan {
  color: #555;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
  .guru-card img {
    height: 180px;
  }
}

@media (max-width: 480px) {
  .guru-card img {
    height: 150px;
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
              <li><a href="page_ekskul.php" class="<?= $currentPage == 'page_ekskul.php' ? 'active' : '' ?>">Ekstrakulikuler</a></li>
              <li><a href="page_fasilitas.php" class="<?= $currentPage == 'page_fasilitas.php' ? 'active' : '' ?>">Fasilitas</a></li>
              <li><a href="page_guru_staff.php" class="<?= $currentPage == 'page_guru_staff.php' ? 'active' : '' ?>">Guru dan Staff</a></li>
              <li><a href="page_alumni.php" class="<?= $currentPage == 'page_alumni.php' ? 'active' : '' ?>">Alumni</a></li>
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

  
<!-- HEADER Struktur Organisasi -->
<section class="guru-staff-header">
  <div class="overlay"></div>
  <div class="container header-content">
    <p class="breadcrumb">Informasi / Guru dan Staff</p>
    <h1 class="judul-header">Guru dan Staff</h1>
  </div>
</section>


<!-- KONTEN -->
<section class="guru-staff-section">

  <!-- Kepala Sekolah -->
  <?php if (count($data['Kepala Sekolah']) > 0): ?>
    <h3 class="kategori-title">Kepala Sekolah</h3>
    <div class="guru-grid">
      <?php foreach ($data['Kepala Sekolah'] as $row): ?>
        <div class="guru-card">
          <div class="guru-image-wrapper">
            <img src="<?= $row['image']; ?>" alt="<?= $row['nama']; ?>">
          </div>
          <div class="guru-info">
            <div class="guru-nama"><?= $row['nama']; ?></div>
            <div class="guru-jabatan"><?= $row['jabatan']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>


  <!-- Wakil Kepala Sekolah -->
  <?php if (count($data['Wakil Kepala Sekolah']) > 0): ?>
    <h3 class="kategori-title">Wakil Kepala Sekolah</h3>
    <div class="guru-grid">
      <?php foreach ($data['Wakil Kepala Sekolah'] as $row): ?>
        <div class="guru-card">
          <div class="guru-image-wrapper">
            <img src="<?= $row['image']; ?>" alt="<?= $row['nama']; ?>">
          </div>
          <div class="guru-info">
            <div class="guru-nama"><?= $row['nama']; ?></div>
            <div class="guru-jabatan"><?= $row['jabatan']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>


  <!-- Staff -->
  <?php if (count($data['Staff']) > 0): ?>
    <h3 class="kategori-title">Staff</h3>
    <div class="guru-grid">
      <?php foreach ($data['Staff'] as $row): ?>
        <div class="guru-card">
          <div class="guru-image-wrapper">
            <img src="<?= $row['image']; ?>" alt="<?= $row['nama']; ?>">
          </div>
          <div class="guru-info">
            <div class="guru-nama"><?= $row['nama']; ?></div>
            <div class="guru-jabatan"><?= $row['jabatan']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>


  <!-- Guru -->
  <?php if (count($data['Guru']) > 0): ?>
    <h3 class="kategori-title">Guru</h3>
    <div class="guru-grid">
      <?php foreach ($data['Guru'] as $row): ?>
        <div class="guru-card">
          <div class="guru-image-wrapper">
            <img src="<?= $row['image']; ?>" alt="<?= $row['nama']; ?>">
          </div>
          <div class="guru-info">
            <div class="guru-nama"><?= $row['nama']; ?></div>
            <div class="guru-jabatan"><?= $row['jabatan']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

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
