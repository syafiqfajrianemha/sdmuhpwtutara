<?php
include '../db.php';


$currentPage = basename($_SERVER['PHP_SELF']);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul = $_POST['judul'] ?? '';
  $deskripsi = $_POST['deskripsi'] ?? '';
  $imageName = null;

  // Jika ada upload file gambar
  if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
      $uploadDir = '../image/sejarah/';
      if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0755, true);
      }

      // Ambil nama asli file (bersihkan nama file dari karakter aneh)
      $originalName = basename($_FILES['gambar']['name']);
      $originalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName);

      $uploadPath = $uploadDir . $originalName;

      // Simpan file ke folder upload
      move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath);

      $imageName = $originalName;

      // Update judul, deskripsi, dan gambar
      $stmt = $conn->prepare("UPDATE sejarah SET judul=?, deskripsi=?, image_sejarah=? WHERE id=1");
      $stmt->bind_param("sss", $judul, $deskripsi, $imageName);
  } else {
      // Update tanpa gambar
      $stmt = $conn->prepare("UPDATE sejarah SET judul=?, deskripsi=? WHERE id=1");
      $stmt->bind_param("ss", $judul, $deskripsi);
  }

  $stmt->execute();
  $stmt->close();

  header("Location: sejarah.php?sukses=1");
  exit;
}

// Ambil data sejarah untuk ditampilkan
$result = $conn->query("SELECT * FROM sejarah WHERE id=1");
$sejarah = $result->fetch_assoc();
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<style>
  body { margin: 0; padding: 0; }
  #wrapper { display: flex; width: 100%; }
  #sidebar-wrapper {
    min-width: 250px;
    max-width: 250px;
    background-color: #003366;
    color: white;
    min-height: 100vh;
  }
  .list-group-item {
    border: none;
    background-color: #003366;
    color: white;
  }
  .list-group-item:hover {
    background-color: #495057;
  }
  .list-group-item.active-page {
  background-color: #6c757d !important; /* abu-abu */
  color: white !important;
  border-radius: 6px;
}

  .collapse .list-group-item {
    padding-left: 2rem;
  }
  #page-content-wrapper {
    flex-grow: 1;
  }
  .navbar-dark .navbar-nav .nav-link {
    color: white;
  }
  .navbar-dark .navbar-nav .nav-link:hover {
    color: #ddd;
  }
  .btn-primary {
    background-color: #003366;
    border-color: #003366;
  }
  .btn-primary:hover {
    background-color: #002244;
    border-color: #002244;
  }
  .custom-glow-btn {
    background-color: #1a1a2e;
    color: #fff;
    border: 2px solid #3c8dbc;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(60, 141, 188, 0.5);
    transition: all 0.3s ease-in-out;
  }
  .custom-glow-btn:hover {
    background-color: #3c8dbc;
    box-shadow: 0 0 15px rgba(60, 141, 188, 0.7);
  }
  #wrapper.toggled #sidebar-wrapper {
  margin-left: -250px;
  transition: margin 0.3s ease;
  }
  #sidebar-wrapper {
  transition: margin 0.3s ease;
  }

</style>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
  <div id="sidebar-wrapper" class="p-3">
    <div class="sidebar-heading text-white fw-bold mb-4">Dashboard Admin</div>
    <div class="list-group list-group-flush">
      <a href="index.php" class="list-group-item list-group-item-action"> <i class="fas fa-home me-2"></i>Home</a>
      <!-- PROFIL SEKOLAH -->
    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
      data-bs-toggle="collapse" 
      href="#profilMenu" 
      aria-expanded="<?= in_array($currentPage, ['sejarah.php', 'visi_misi.php', 'struktur_organisasi.php']) ? 'true' : 'false' ?>">
      <div><i class="fas fa-school me-2"></i> Profil </div>
      <i class="fas fa-caret-down"></i>
    </a>

    <div class="collapse <?= in_array($currentPage, ['sejarah.php', 'visi_misi.php', 'struktur_organisasi.php']) ? 'show' : '' ?>" id="profilMenu">
      <a href="sejarah.php" class="list-group-item list-group-item-action <?= ($currentPage == 'sejarah.php') ? 'active-page' : '' ?>">
        <i class="fas fa-book me-2"></i> Sejarah
      </a>
      <a href="visi_misi.php" class="list-group-item list-group-item-action <?= ($currentPage == 'visi_misi.php') ? 'active-page' : '' ?>">
        <i class="fas fa-lightbulb me-2"></i> Visi & Misi
      </a>
      <a href="struktur_organisasi.php" class="list-group-item list-group-item-action <?= ($currentPage == 'struktur_organisasi.php') ? 'active-page' : '' ?>">
        <i class="fas fa-sitemap me-2"></i> Struktur Organisasi
      </a>
    </div>

      <a href="berita.php" class="list-group-item list-group-item-action"><i class="fas fa-newspaper me-2"></i>Berita</a>
      <a href="ppdb.php" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i> PPDB</a>
      <a href="prestasi.php" class="list-group-item list-group-item-action"><i class="fas fa-trophy me-2"></i> Prestasi</a>
      <a class="list-group-item list-group-item-action d-flex align-items-center"
        data-bs-toggle="collapse" data-bs-target="#informasiMenu" role="button" aria-expanded="false">
        <i class="fas fa-info-circle me-2"></i> Informasi <i class="fas fa-caret-down ms-auto"></i>
      </a>
      <div class="collapse" id="informasiMenu">
        <a href="ekstrakulikuler.php" class="list-group-item list-group-item-action"><i class="fas fa-swimmer me-2"></i> Ekstrakurikuler</a>
        <a href="fasilitas.php" class="list-group-item list-group-item-action"><i class="fas fa-building me-2"></i> Fasilitas</a>
        <a href="guru_staff.php" class="list-group-item list-group-item-action"><i class="fas fa-chalkboard-teacher me-2"></i> Guru dan Staff</a>
        <a href="alumni.php" class="list-group-item list-group-item-action"><i class="fas fa-user-graduate me-2"></i> Alumni</a>
      </div>
      <a href="kelola_admin.php" class="list-group-item list-group-item-action <?= ($currentPage == 'kelola_admin.php') ? 'active-page' : '' ?>">
        <i class="fas fa-user-shield me-2"></i> Kelola Admin
      </a>
    </div>
  </div>

  <!-- Page Content -->
  <div id="page-content-wrapper" class="w-100">
    <nav class="navbar navbar-expand navbar-dark bg-dark px-3 border-bottom">
      <!-- Tombol toggle sidebar -->
      <button class="btn btn-light me-2" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Profil (kanan atas) -->
      <!-- Profil (kanan atas) -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user fa-fw" style="color: white;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
                <a class="dropdown-item d-flex align-items-center" href="profil_admin.php">
                <i class="fas fa-id-card me-2 text-secondary"></i> Profil Saya
                </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
                <a class="dropdown-item d-flex align-items-center text-danger" href="logout.html">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
            </ul>
        </li>
        </ul>
    </nav>

  <!-- Form dan konten lainnya disimpan di sini -->

    
   <div class="container my-5">
    <h3 class="fw-bold text-dark">Sejarah Sekolah</h3>

    <?php if (isset($_GET['sukses'])): ?>
  <div class="alert alert-secondary alert-dismissible fade show" role="alert">
      <strong>Info:</strong> Data sejarah berhasil diperbarui.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
   

    <form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label for="judul" class="form-label">Judul</label>
    <input type="text" id="judul" name="judul" class="form-control" value="<?= htmlspecialchars($sejarah['judul'] ?? '') ?>" required>
  </div>

  <div class="mb-3">
    <label for="deskripsi" class="form-label">Deskripsi</label>
    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="10" required><?= htmlspecialchars($sejarah['deskripsi'] ?? '') ?></textarea>
  </div>

  <div class="mb-3">
    <label for="gambar" class="form-label">Upload Gambar Sejarah</label>
    <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
  </div>

  <?php if (!empty($sejarah['image_sejarah'])): ?>
    <div class="mb-3">
      <label class="form-label d-block">Gambar Saat Ini:</label>
      <img src="../image/sejarah/<?=htmlspecialchars($sejarah['image_sejarah']) ?>" class="img-fluid" style="max-width:300px;">
    </div>
  <?php endif; ?>

  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("sidebarToggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });

</script>

</body>
</html>
