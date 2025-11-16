<?php
include 'db.php';  // memanggil koneksi database dari file db.php

$currentPage = 'index.php';

$isHome = ($currentPage == 'index.php');
$isProfil = in_array($currentPage, ['page_sejarah.php', 'page_visimisi.php', 'page_strukturorganis.php']);
$isBerita = ($currentPage == 'page_berita.php');
$isPPDB = ($currentPage == 'page_ppdb.php');
$isPrestasi = ($currentPage == 'page_prestasi.php');
$isInformasi = in_array($currentPage, ['page_ekstrakulikuler.php', 'page_fasilitas.php', 'page_gurustaff.php', 'page_alumni.php']);
$isAdmin     = ($currentPage==='login.php');

// Ambil nama sekolah
$sqlNama = "SELECT nama_sekolah FROM kontak LIMIT 1";
$resultNama = $conn->query($sqlNama);
$namaSekolah = "Nama Sekolah"; // default

if ($resultNama && $resultNama->num_rows > 0) {
    $rowNama = $resultNama->fetch_assoc();
    $namaSekolah = $rowNama['nama_sekolah'];

    // pecah jadi array kata
    $parts = explode(" ", $namaSekolah);

    // ambil kata terakhir
    $lastWord = array_pop($parts);

    // gabung sisanya
    $firstPart = implode(" ", $parts);

    // gabungkan dengan <br>
    $namaSekolah = $firstPart . "<br>" . $lastWord;
}

// Ambil gambar header
$sqlGambar = "SELECT gambar_header FROM header LIMIT 1";
$resultGambar = $conn->query($sqlGambar);
$gambarHeader = "default-header.jpg";
if ($resultGambar && $resultGambar->num_rows > 0) {
    $rowGambar = $resultGambar->fetch_assoc();
    $gambarHeader = $rowGambar['gambar_header'];
}

// Ambil data dari tabel keunggulan_sekolah
$queryKeunggulan = "SELECT * FROM keunggulan_sekolah";
$resultKeunggulan = $conn->query($queryKeunggulan);


// Ambil data sejarah dari database
$sejarah = [
    'judul' => '',
    'deskripsi' => '',
    'gambar' => ''
];
$result = $conn->query("SELECT * FROM sejarah WHERE id=1");
if ($result && $result->num_rows > 0) {
    $sejarah = $result->fetch_assoc();
}

// Ambil 5 berita terbaru berdasarkan tanggal (atau bisa ganti ke created_at sesuai kebutuhan)
$query = "SELECT * FROM berita ORDER BY tanggal DESC LIMIT 3";
$resultBerita = mysqli_query($conn, $query);

$berita = [];
if ($resultBerita && mysqli_num_rows($resultBerita) > 0) {
    $berita = mysqli_fetch_all($resultBerita, MYSQLI_ASSOC);
}


// Query untuk ambil program unggulan
$query_program = "SELECT * FROM program_unggulan ORDER BY id DESC"; // sesuaikan nama tabel
$result_program = $conn->query($query_program);

// Cek jika terjadi error
if (!$result_program) {
    die("Query error: " . $conn->error);
}

// ambil data gambar dari tabel ekstrakulikuler
$ekstrakulikuler = [];
$result = $conn->query("SELECT id, image, nama FROM ekstrakulikuler ORDER BY id ASC LIMIT 4");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // hanya ambil data yang ada gambarnya
        if (!empty($row['image'])) {
            $ekstrakulikuler[] = $row;
        }
    }
}

  // Ambil data demografi dari database
  $result = $conn->query("SELECT * FROM data_demografi LIMIT 1"); // atau WHERE id=... jika spesifik
  $row = $result->fetch_assoc();

  $kuota = $row['kuota_murid_baru'];
  $guruStaff = $row['jumlah_guru_staff'];
  $jumlahSiswa = $row['jumlah_siswa'];

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
   <link rel="stylesheet" href="style.css">
</head>
<body>
 <nav>
  <div class="nav-container">
    <ul>
      <li>
        <a href="" class="<?= $isHome ? 'active' : '' ?>">HOME</a>
      </li>

      <li>
  <a href="" class="no-link">PROFIL</a>
  <ul class="dropdown-menu">
    <li><a href="page_sejarah">Sejarah</a></li>
    <li><a href="page_visimisi">Visi dan Misi</a></li>
    <li><a href="page_strukturorganis">Struktur Organisasi</a></li>
  </ul>
</li>

      <li><a href="page_berita" class="<?= $isBerita ? 'active' : '' ?>">BERITA</a></li>
      <li><a href="page_ppdb" class="<?= $isPPDB ? 'active' : '' ?>">PPDB</a></li>
      <li><a href="page_prestasi" class="<?= $isPrestasi ? 'active' : '' ?>">PRESTASI</a></li>

      <li>
  <a href="" class="<?= 'no-link' . ($isInformasi ? ' parent-active' : '') ?>">INFORMASI</a>
  <ul class="dropdown-menu">
    <li><a href="page_ekskul" class="<?= $currentPage == 'ekstrakulikuler.php' ? 'active' : '' ?>">Ekstrakulikuler</a></li>
    <li><a href="page_fasilitas" class="<?= $currentPage == 'fasilitas.php' ? 'active' : '' ?>">Fasilitas</a></li>
    <li><a href="page_guru_staff" class="<?= $currentPage == 'guru_staff.php' ? 'active' : '' ?>">Guru dan Staff</a></li>
    <li><a href="page_alumni" class="<?= $currentPage == 'alumni.php' ? 'active' : '' ?>">Alumni</a></li>
  </ul>
</li>
<li><a href="" class="<?= 'no-link' . ($isAdmin ? ' parent-active' : '') ?>">ADMIN</a>
<ul class="dropdown-menu">
  <li><a href="../dashboard_admin/login" class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">Login</a></li>
</ul>
</li>
    </ul>
  </div>
</nav>




<!-- Header Section -->
<div class="header-section">
  <div class="header-overlay"></div> <!-- lapisan hitam transparan -->
  <img src="image/header/<?php echo htmlspecialchars($gambarHeader); ?>" alt="Header Image">
  <div class="header-content">
     <h1 class="nama_sekolah"><?php echo $namaSekolah; ?></h1>
  </div>
</div>


<form action="index.php" method="post" enctype="multipart/form-data">
  <!-- KEUNGGULAN SEKOLAH -->
<section class="highlight-section">
  <div class="highlight">
    <?php if ($resultKeunggulan && $resultKeunggulan->num_rows > 0): ?>
      <?php while ($row = $resultKeunggulan->fetch_assoc()): ?>
        <div class="highlight-box">
          <?php
            // Tentukan ikon berdasarkan judul fitur
            $icon = 'fas fa-star'; // default
            if (stripos($row['judul'], 'islami') !== false) $icon = 'fas fa-mosque';
            elseif (stripos($row['judul'], 'modern') !== false) $icon = 'fas fa-laptop-code';
            elseif (stripos($row['judul'], 'akreditasi') !== false) $icon = 'fas fa-certificate';
          ?>
          <i class="<?= $icon ?>"></i>
          <h3><?= htmlspecialchars($row['judul']) ?></h3>
          <p><?= htmlspecialchars($row['deskripsi']) ?></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Tidak ada keunggulan yang ditampilkan.</p>
    <?php endif; ?>
  </div>
</section>

<!-- SEJARAH SEKOLAH -->
<section class="sejarah">
  <div class="sejarah-content">
    <?php if (!empty($sejarah['image_sejarah'])): ?>
      <div class="sejarah-img-wrapper">
        <img src="image/sejarah/<?= htmlspecialchars($sejarah['image_sejarah']) ?>" alt="Gambar Sejarah Sekolah" />
      </div>
    <?php endif; ?>

    <div class="sejarah-text">
      <h2><?= htmlspecialchars($sejarah['judul']) ?></h2>
      <p>
        <?php
        function limit_words($string, $word_limit) {
          $words = explode(' ', strip_tags($string));
          if(count($words) > $word_limit) {
            return implode(' ', array_slice($words, 0, $word_limit)) . '...';
          }
          return $string;
        }
        echo htmlspecialchars(limit_words($sejarah['deskripsi'], 40));
        ?>
      </p>
      <a href="page_sejarah.php" class="btn-selengkapnya">Selengkapnya <span class="arrow">➜</span></a>
    </div>
  </div>
</section>



<!-- berita -->
<section class="py-5 container-berita"> 
  <div class="text-center">
    <h2 class="mb-4 fw-bold">Berita dan Pengumuman</h2>
  </div>

  <div class="berita-grid">
    <?php foreach ($berita as $b): ?>
      <?php
        $gambar = !empty($b['foto']) ? 'image/berita/' . htmlspecialchars($b['foto']) : 'image/berita/default.jpg';
        // Asumsikan kamu punya kolom 'slug' di database
        $slug = htmlspecialchars($b['slug']);
      ?>
      <div class="berita-item">
        <a href="berita_detail.php?slug=<?= $slug ?>">
          <img src="<?= $gambar ?>" alt="Gambar Berita" class="berita-img" />
        </a>
        <div class="berita-content">
          <div class="berita-tanggal">
            <i class="fa fa-calendar"></i> <?= htmlspecialchars($b['tanggal']) ?>
          </div>
          <a href="berita_detail.php?slug=<?= $slug ?>" class="berita-link">
            <h3><?= htmlspecialchars($b['judul']) ?></h3>
            <p><?= substr(strip_tags($b['deskripsi']), 0, 150) ?>...</p>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="berita-viewall mt-4 text-center">
    <a href="page_berita.php" class="btn-lihat-semua">View All</a>
  </div>
</section>



<hr class="section-divider">

<!-- PROGRAM UNGGULAN --> 
<section class="py-5 bg-light program-unggulan">
  <div class="text-center">
    <h2 class="mb-4 text-center fw-bold">Program Unggulan</h2>
  </div>

  <div class="swiper programSwiper">
    <div class="swiper-wrapper">
      <?php while ($program = $result_program->fetch_assoc()): ?>
        <div class="swiper-slide">
          <div class="program-card-wrapper">
            <img src="image/program/<?= htmlspecialchars($program['image']) ?>" alt="Program" class="program-image" />
            <div class="program-card text-center">
              <h6 class="mt-3"><?= htmlspecialchars($program['judul']) ?></h6>
              <p class="desc"><?= htmlspecialchars($program['deskripsi']) ?></p>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Swiper navigation -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
  </div>
</section>



<hr class="section-divider">

<!-- EKSTRAKULIKULER -->
<section class="ekstrakulikuler-section">
  <h2 class="mb-4 text-center fw-bold">Ekstrakulikuler</h2>
  <div class="ekstra-grid">
    <?php foreach ($ekstrakulikuler as $ekstra): ?>
      <div class="ekstra-item">
        <a href="page_ekskul.php?id=<?= htmlspecialchars($ekstra['id']) ?>">
        
          <?php 
            if (!empty($ekstra['image'])) {
              $images = explode(',', $ekstra['image']);
              foreach ($images as $img) {
                $img = trim($img);
                $src = "" . $img;
          ?>
                <img 
                  src="<?= htmlspecialchars($src) ?>" 
                  alt="<?= htmlspecialchars($ekstra['nama']) ?>" 
                  style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;" />
          <?php
              }
            } else {
          ?>
            <img 
              src="image/ekstrakulikuler/" 
              alt="Gambar Ekstrakulikuler" 
              style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;" />
          <?php } ?>
          <!-- Tambahin nama ekstra di sini -->
          <h5 class="ekstra-nama text-center mt-2">
            <?= htmlspecialchars($ekstra['nama']) ?>
          </h5>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- DATA DEMOGRAFI -->
<section class="stats-section text-white text-center">
  <div class="container position-relative container-flex">
    <h2 class="section-title fw-bold mb-4">
      Data Demografi <br> SD Muhammadiyah Purwokerto Tes
    </h2>
    <div class="stats-items">
      <div class="stat-item">
        <h3 class="counter" data-target="<?= $kuota; ?>">0</h3>
        <p>Kuota Murid Baru</p>
      </div>
      <div class="stat-item">
        <h3 class="counter" data-target="<?= $guruStaff; ?>">0</h3>
        <p>Guru dan Karyawan</p>
      </div>
      <div class="stat-item">
        <h3 class="counter" data-target="<?= $jumlahSiswa; ?>">0</h3>
        <p>Jumlah Siswa</p>
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
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<script>
  // === Counter Animasi ===
  const counters = document.querySelectorAll('.counter');
  const options = { threshold: 0.5 };

  const animateCounter = (counter) => {
    const updateCount = () => {
      const target = +counter.getAttribute('data-target');
      const count = +counter.innerText;
      const speed = 200;

      const increment = Math.ceil(target / speed);
      if (count < target) {
        counter.innerText = count + increment;
        setTimeout(updateCount, 10);
      } else {
        counter.innerText = target;
      }
    };
    updateCount();
  };

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        obs.unobserve(entry.target);
      }
    });
  }, options);

  counters.forEach(counter => observer.observe(counter));

  // Inisialisasi Swiper
  var swiper = new Swiper(".programSwiper", {
    slidesPerView: 3,
    slidesPerGroup: 3,
    spaceBetween: 30,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    on: {
      init: function () {
        updateNavVisibility(this);
      },
      slideChange: function () {
        updateNavVisibility(this);
      },
    },
  });

  // Fungsi untuk sembunyikan/munculkan tombol
  function updateNavVisibility(swiper) {
    const prev = swiper.navigation.prevEl;
    const next = swiper.navigation.nextEl;

    // Sembunyikan kiri di awal
    if (swiper.isBeginning) {
      prev.style.display = "none";
    } else {
      prev.style.display = "flex";
    }

    // Sembunyikan kanan di akhir
    if (swiper.isEnd) {
      next.style.display = "none";
    } else {
      next.style.display = "flex";
    }
  }
</script>


</body>
</html>
