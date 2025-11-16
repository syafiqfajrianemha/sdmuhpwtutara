<?php
// koneksi database
session_start();
include '../db.php';


$currentPage = basename($_SERVER['PHP_SELF']);
$editData = null;

// Handle hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM prestasi WHERE id=$id");
    $_SESSION['success'] = "Data prestasi berhasil dihapus.";
    header("Location: prestasi.php");
    exit;
}

// Handle edit ambil data
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM prestasi WHERE id=$id");
    $editData = $res->fetch_assoc();
}

// Handle tambah
if (isset($_POST['tambah'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $nama_prestasi = $conn->real_escape_string($_POST['nama_prestasi']);
    $tahun_pelajaran = $conn->real_escape_string($_POST['tahun_pelajaran']);

    $conn->query("INSERT INTO prestasi (nama, kelas, nama_prestasi, tahun_pelajaran) 
                  VALUES ('$nama', '$kelas', '$nama_prestasi', '$tahun_pelajaran')");
    $_SESSION['success'] = "Data prestasi berhasil ditambahkan.";
    header("Location: prestasi.php");
    exit;
}


// Handle update
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $nama_prestasi = $conn->real_escape_string($_POST['nama_prestasi']);
    $tahun_pelajaran = $conn->real_escape_string($_POST['tahun_pelajaran']);

    $conn->query("UPDATE prestasi 
                  SET nama='$nama', kelas='$kelas', nama_prestasi='$nama_prestasi', tahun_pelajaran='$tahun_pelajaran' 
                  WHERE id=$id");
    $_SESSION['success'] = "Data prestasi berhasil diperbarui.";
    header("Location: prestasi.php");
    exit;
}

// Ambil data prestasi
$data = $conn->query("SELECT * FROM prestasi ORDER BY id DESC");
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
.action-btn {
  min-width: 80px;
  padding: 6px 10px;      /* tinggi tombol lebih besar */
  margin-top: 3px;        /* jarak atas */
  margin-bottom: 3px;     /* jarak bawah */
  display: inline-flex;
  align-items: center;
  justify-content: center;
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
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
        data-bs-toggle="collapse" href="#profilMenu">
        <div class="d-flex align-items-center">
          <i class="fas fa-school me-2"></i> <span>Profil</span>
        </div>
        <i class="fas fa-caret-down"></i>
      </a>

      <div class="collapse" id="profilMenu">
        <a href="sejarah.php" class="list-group-item list-group-item-action"><i class="fas fa-book me-2"></i>Sejarah</a>
        <a href="visi_misi.php" class="list-group-item list-group-item-action"><i class="fas fa-lightbulb me-2"></i> Visi dan Misi</a>
        <a href="struktur_organisasi.php" class="list-group-item list-group-item-action"><i class="fas fa-sitemap me-2"></i>Struktur Organisasi</a>
      </div>
      <a href="berita.php" class="list-group-item list-group-item-action"><i class="fas fa-newspaper me-2"></i> Berita</a>
      <a href="ppdb.php" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i> PPDB</a>
      <a href="prestasi.php" class="list-group-item list-group-item-action <?= ($currentPage == 'prestasi.php') ? 'active-page' : '' ?>"><i class="fas fa-trophy me-2"></i> Prestasi</a>
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


    <!-- PRESTASI -->
 <div class="content-wrapper py-4 bg-light rounded shadow-sm">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold text-dark mb-0">
        <i class="bi bi-trophy-fill me-2"></i> Data Prestasi
      </h3>
    </div>



    <div id="formPrestasi" class="card p-4 mb-5">
  <h4 class="mb-3"><?= $editData ? "Edit Prestasi" : "Tambah Prestasi" ?></h4>

      <!-- FORM DISINI -->

  <?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-secondary alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <form method="POST" autocomplete="off">
        <?php if ($editData): ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Kelas</label>
          <input type="text" name="kelas" class="form-control" required value="<?= htmlspecialchars($editData['kelas'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Prestasi</label>
          <input type="text" name="nama_prestasi" class="form-control" required value="<?= htmlspecialchars($editData['nama_prestasi'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Tahun Pelajaran</label>
          <input type="text" name="tahun_pelajaran" class="form-control" required 
                placeholder="contoh: 2024/2025"
                value="<?= htmlspecialchars($editData['tahun_pelajaran'] ?? '') ?>">
        </div>

        <button type="submit" name="<?= $editData ? 'update' : 'tambah' ?>" class="btn btn-primary">
          <?= $editData ? 'Simpan Perubahan' : 'Tambah' ?>
        </button>
        <?php if ($editData): ?>
          <a href="prestasi.php" class="btn btn-secondary ms-2">Batal</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <h5>Daftar Prestasi</h5>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>No.</th>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Nama Prestasi</th>
          <th>Tahun Pelajaran</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($data->num_rows > 0): $no = 1; while ($row = $data->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['kelas']) ?></td>
          <td><?= htmlspecialchars($row['nama_prestasi']) ?></td>
          <td><?= htmlspecialchars($row['tahun_pelajaran']) ?></td>
          <td class="text-center">
            <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm d-block mb-2 action-btn">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="?hapus=<?= $row['id'] ?>" 
              onclick="return confirm('Yakin ingin menghapus?')" 
              class="btn btn-danger btn-sm d-block action-btn">
              <i class="fas fa-trash-alt me-1"></i>Hapus
            </a>
          </td>

        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="6" class="text-center">Belum ada data prestasi.</td></tr>
      <?php endif; ?>
      </tbody>

    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("sidebarToggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });

</script>

</body>
</html>

