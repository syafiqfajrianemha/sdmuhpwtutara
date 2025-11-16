<?php
include 'db.php';  // memanggil koneksi database dari file db.php


$currentPage = 'page_prestasi.php';
// Set status aktif untuk masing-masing menu
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

// --- Ambil data prestasi ---
$tahunList = [];
$allPrestasi = [];

// Ambil maksimal 4 tahun pelajaran terbaru
$tahunQuery = $conn->query("SELECT DISTINCT tahun_pelajaran 
                            FROM prestasi 
                            ORDER BY tahun_pelajaran DESC 
                            LIMIT 4");
while ($row = $tahunQuery->fetch_assoc()) {
    $tahunList[] = $row['tahun_pelajaran'];
}


// Ambil semua prestasi sekaligus
$prestasiQuery = $conn->query("SELECT * FROM prestasi ORDER BY tahun_pelajaran DESC");
while ($row = $prestasiQuery->fetch_assoc()) {
    $allPrestasi[$row['tahun_pelajaran']][] = $row;
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
.container {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
}
.prestasi-header {
  position: relative;
  background-image: url('image/sekolah/gambar_sekolah1.jpg');
  background-size: cover;
  background-position: center;
  height: 300px;
  color: white;
  display: flex;
  align-items: center;
  text-align: left; /* penting: agar isi tidak rata tengah */
  padding-left: 40px; /* geser isi ke kiri */
}

.prestasi-header .overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(75, 115, 190, 0.7); /* warna biru transparan */
  z-index: 1;
}

.prestasi-header .header-content {
  position: relative;
  z-index: 2;
}

.breadcrumb {
  margin: 0;
  font-size: 16px;
  font-weight: 400;
}

.judul-prestasi {
  font-size: 40px;
  font-weight: 700;
  margin-top: 10px;
}


.judul-prestasi {
  font-size: 41px;
  font-weight: bold;
  margin: 0;
}

/*  PRESTASI */

  .container {
    max-width: 850px;
    margin: auto;
    padding: 40px 20px;
  }

  .prestasi-intro {
    background-color: #eaf2fb;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    text-align: center;
  }

  .prestasi-intro h2 {
    color: #003366;
    font-size: 28px;
    margin-bottom: 10px;
    
  }

  .prestasi-intro p {
    color: #1a3c5a;
    font-size: 1.05em;
  }

  .tahun-pelajaran {
  font-size: 20px;
  font-weight: bold;
  color: #003366;
  display: inline-block; /* supaya garis sesuai lebar teks */
  border-bottom: 5px solid #FFCC00; /* garis kuning */
  padding-bottom: 3px; /* jarak antara teks dan garis */
  margin-bottom: 20px; /* jarak dengan konten bawah */
}


  .collapsible {
    font-weight: bold;
    color: #003366;
    background-color: #f1f8ff;
    padding: 12px 16px;
    margin: 10px 0;
    cursor: pointer;
    border-left: 5px solid #003366;
    transition: all 0.2s ease-in-out;
  }

  .collapsible:hover {
    background-color: #dbefff;
  }

  .prestasi-table {
    display: none;
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    margin-bottom: 20px;
    font-size: 0.95em;
  }

  .prestasi-table th, .prestasi-table td {
    border: 1px solid #c0d4e8;
    padding: 10px;
    text-align: center;
  }

  .prestasi-table th {
    background-color: #d6e6f5;
    color: #003366;
  }

  .prestasi-table tr:nth-child(even) {
    background-color: #f2f7fc;
  }

  .prestasi-table tr:hover {
    background-color: #e0efff;
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
 <section class="prestasi-header">
  <div class="overlay"></div>
  <div class="header-content">
    <p class="breadcrumb">Prestasi</p>
    <h1 class="judul-prestasi">Prestasi</h1>
  </div>
</section>


<!-- KONTEN -->
<div class="container">
  <div class="prestasi-intro">
    <h2>Prestasi Siswa</h2>
    <p>
      Di SD Muhammadiyah Purwokerto, setiap siswa adalah bintang yang bersinar dengan cara unik mereka masing-masing. Kami percaya bahwa prestasi bukan hanya tentang medali dan piagam, tetapi tentang keberanian, kerja keras, dan semangat pantang menyerah. 
      Melalui bimbingan yang penuh kasih dan lingkungan belajar yang mendukung, siswa-siswi kami terus mengukir prestasi yang membanggakan baik di bidang akademik maupun non-akademik.
    </p>
  </div>

<?php foreach ($tahunList as $tahun): ?>
  <h3 class="tahun-pelajaran">
  Tahun Pelajaran <?= htmlspecialchars($tahun) ?>
  </h3>


  <div class="collapsible" onclick="toggleTable('table<?= $tahun ?>')">
    ▸ Daftar Prestasi Siswa
  </div>

  <table id="table<?= $tahun ?>" class="prestasi-table" style="display:none;">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Nama Prestasi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach ($allPrestasi[$tahun] as $row): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['kelas']) ?></td>
          <td><?= htmlspecialchars($row['nama_prestasi']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>

</div>


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
  


<!-- JavaScript -->
<script>
  function toggleTable(tableId) {
    const table = document.getElementById(tableId);
    table.style.display = (table.style.display === "table") ? "none" : "table";
  }
</script>

</body>
</html>
