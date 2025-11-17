<?php
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Proses tambah fasilitas
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_fasilitas'];
    $foto = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $uploadDir = '../image/fasilitas/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Cek apakah file sudah ada (berdasarkan hash)
        $hashBaru = md5_file($tmpName);
        $found = false;

        foreach (glob($uploadDir . '*') as $existingFile) {
            if (md5_file($existingFile) === $hashBaru) {
                $foto = basename($existingFile); // hanya ambil nama file-nya saja
                $found = true;
                break;
            }
        }

        if (!$found) {
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($tmpName, $targetFile)) {
                $foto = $fileName; // simpan nama file asli
            }
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO fasilitas (nama_fasilitas, image) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $nama, $foto);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['success'] = 'Fasilitas berhasil ditambahkan.';
    header("Location: fasilitas?sukses=added");
    exit;
}

// Proses update fasilitas
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_fasilitas'];
    $foto = $_POST['foto_lama']; // default pakai foto lama

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $uploadDir = '../image/fasilitas/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $hashBaru = md5_file($tmpName);
        $found = false;

        foreach (glob($uploadDir . '*') as $existingFile) {
            if (md5_file($existingFile) === $hashBaru) {
                $foto = basename($existingFile); // simpan nama file asli
                $found = true;
                break;
            }
        }

        if (!$found) {
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($tmpName, $targetFile)) {
                $foto = $fileName; // simpan nama file asli
            }
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE fasilitas SET nama_fasilitas=?, image=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $foto, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['success'] = 'Fasilitas berhasil diupdate.';
    header("Location: fasilitas?sukses=updated");
    exit;
}

// 🔹 Proses hapus fasilitas
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // ambil data untuk hapus file
    $result = mysqli_query($conn, "SELECT image FROM fasilitas WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    if ($row && !empty($row['image'])) {
        $filePath = '../image/fasilitas/' . $row['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    mysqli_query($conn, "DELETE FROM fasilitas WHERE id=$id");

    $_SESSION['success'] = 'Fasilitas berhasil dihapus.';
    header("Location: fasilitas?sukses=deleted");
    exit;
}

// Ambil semua data fasilitas
$result = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY id ASC");
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk form edit
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM fasilitas WHERE id=$editId");
    $editData = mysqli_fetch_assoc($result);
}
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
      <a href="index" class="list-group-item list-group-item-action"> <i class="fas fa-home me-2"></i>Home</a>
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
        data-bs-toggle="collapse" href="#profilMenu">
        <div class="d-flex align-items-center">
          <i class="fas fa-school me-2"></i> <span>Profil</span>
        </div>
        <i class="fas fa-caret-down"></i>
      </a>

      <div class="collapse" id="profilMenu">
        <a href="sejarah" class="list-group-item list-group-item-action"><i class="fas fa-book me-2"></i>Sejarah</a>
        <a href="visi_misi" class="list-group-item list-group-item-action"><i class="fas fa-lightbulb me-2"></i> Visi dan Misi</a>
        <a href="struktur_organisasi" class="list-group-item list-group-item-action"><i class="fas fa-sitemap me-2"></i>Struktur Organisasi</a>
      </div>
      <a href="berita" class="list-group-item list-group-item-action"> <i class="fas fa-newspaper me-2"></i>Berita</a>
      <a href="ppdb" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i> PPDB</a>
      <a href="prestasi" class="list-group-item list-group-item-action"><i class="fas fa-trophy me-2"></i> Prestasi</a>
      <!-- INFORMASI MENU -->
          <a class="list-group-item list-group-item-action d-flex align-items-center" 
              data-bs-toggle="collapse" 
              href="#informasiMenu" 
              aria-expanded="<?= in_array($currentPage, ['ekstrakulikuler.php', 'fasilitas.php', 'guru_staff.php', 'alumni.php']) ? 'true' : 'false' ?>">
              <i class="fas fa-info-circle me-2"></i> Informasi <i class="fas fa-caret-down ms-auto"></i>
            </a>

          <div class="collapse <?= in_array($currentPage, ['ekstrakulikuler.php', 'fasilitas.php', 'guru_staff.php', 'alumni.php']) ? 'show' : '' ?>" id="informasiMenu">
            <a href="ekstrakulikuler" class="list-group-item list-group-item-action <?= ($currentPage == 'ekstrakulikuler.php') ? 'active-page' : '' ?>"><i class="fas fa-swimmer me-2"></i> Ekstrakurikuler</a>
            <a href="fasilitas" class="list-group-item list-group-item-action <?= ($currentPage == 'fasilitas.php') ? 'active-page' : '' ?>"><i class="fas fa-building me-2"></i> Fasilitas</a>
            <a href="guru_staff" class="list-group-item list-group-item-action <?= ($currentPage == 'guru_staff.php') ? 'active-page' : '' ?>"><i class="fas fa-chalkboard-teacher me-2"></i> Guru & Staff</a>
            <a href="alumni" class="list-group-item list-group-item-action <?= ($currentPage == 'alumni.php') ? 'active-page' : '' ?>"><i class="fas fa-user-graduate me-2"></i> Alumni</a>
          </div>
          <a href="kelola_admin" class="list-group-item list-group-item-action <?= ($currentPage == 'kelola_admin.php') ? 'active-page' : '' ?>">
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
            <a class="nav-link dropdown-toggle text-white" id="navbarDropdown" href="" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user fa-fw" style="color: white;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
                <a class="dropdown-item d-flex align-items-center" href="profil_admin">
                <i class="fas fa-id-card me-2 text-secondary"></i> Profil Saya
                </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
                <a class="dropdown-item d-flex align-items-center text-danger" href="logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
            </ul>
        </li>
        </ul>
    </nav>


  <!-- FASILITAS -->
  <div class="container mt-4">
    <h2>Fasilitas Sekolah</h2>

   <?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-secondary alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

 <!-- FORM -->
    <div class="card mb-4">
      <div class="card-header"><?= $editData ? 'Edit Fasilitas' : 'Tambah Fasilitas' ?></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
          <input type="hidden" name="foto_lama" value="<?= $editData['image'] ?? '' ?>">
          <div class="mb-2">
            <label>Nama Fasilitas</label>
            <input type="text" name="nama_fasilitas" class="form-control" value="<?= $editData['nama_fasilitas'] ?? '' ?>" required>
          </div>
          <div class="mb-2">
            <label>Gambar</label>
            <input type="file" name="image" class="form-control">
            <?php if (!empty($editData['image'])): ?>
              <small>Gambar sekarang: <img src="../image/fasilitas/<?= $editData['image'] ?>" style="max-height: 60px;"></small>
            <?php endif; ?>
          </div>
          <button type="submit" name="<?= $editData ? 'update' : 'tambah' ?>" class="btn btn-primary"><?= $editData ? 'Update' : 'Tambah' ?></button>
          <?php if ($editData): ?>
            <a href="fasilitas" class="btn btn-secondary">Batal</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- TABEL -->
    <h5>Daftar Fasilitas</h5>
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Fasilitas</th>
          <th>Gambar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($data)): foreach ($data as $row): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nama_fasilitas']) ?></td>
            <td>
              <?php if (!empty($row['image'])): ?>
                <img src="../image/fasilitas/<?= $row['image'] ?>" style="max-height: 60px;">
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <a href="fasilitas?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
              </a>
              <a href="fasilitas?hapus=<?= $row['id'] ?>" 
                class="btn btn-sm btn-danger" 
                onclick="return confirm('Yakin ingin menghapus fasilitas ini?')">
                <i class="fas fa-trash-alt me-1"></i> Hapus
              </a>
            </td>

          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="4" class="text-center">Belum ada data fasilitas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("sidebarToggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });

</script>

</body>
</html>

