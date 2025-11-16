<?php
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Ambil data link PPDB
$result = $conn->query("SELECT * FROM ppdb ORDER BY updated_at DESC");
$data = $result->fetch_assoc();

// Perbaikan: definisikan $linkData agar tidak undefined
$linkData = $data;

// Jika tombol simpan ditekan
if (isset($_POST['simpan'])) {
    $link_ppdb = $conn->real_escape_string($_POST['link_ppdb']);

    if ($data) {
        // Update data jika sudah ada
        $conn->query("UPDATE ppdb SET link_ppdb='$link_ppdb', updated_at=CURRENT_TIMESTAMP WHERE id=" . $data['id']);
    } else {
        // Tambah data baru jika belum ada
        $conn->query("INSERT INTO ppdb (link_ppdb) VALUES ('$link_ppdb')");
    }

    // Refresh halaman
    header("Location: ppdb.php");
    exit;
}

// Ambil semua data untuk ditampilkan di tabel
$list = $conn->query("SELECT * FROM ppdb ORDER BY updated_at DESC");
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
  .btn-primary.btn-sm {
  border-radius: 6px;
  font-weight: 500;
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
      <a href="index.php" class="list-group-item list-group-item-action"> <i class="fas fa-home me-2"></i> Home</a>
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
      <a href="ppdb.php" class="list-group-item list-group-item-action <?= ($currentPage == 'ppdb.php') ? 'active-page' : '' ?>"><i class="fas fa-users me-2"></i> PPDB</a>
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


    <!-- FORM EDIT LINK PPDB -->
<div class="container my-5">
  <h4 class="mb-4">Tautan Penerimaan Peserta Didik Baru</h4>

  <form method="POST" class="card p-4 shadow-sm mb-5">
    <div class="mb-3">
      <label for="link_ppdb" class="form-label">Tautan:</label>
      <input type="url" name="link_ppdb" id="link_ppdb" class="form-control" placeholder="https://docs.google.com/forms/xxxx" required>
    </div>

    <div class="mt-2 text-start">
      <button type="submit" name="simpan" class="btn btn-primary btn-sm px-4">Simpan</button>
    </div>
  </form>


  <h5 class="mb-3">Data Tautan PPDB Saat Ini</h5>

  <table class="table table-bordered table-striped">
    <thead class="table-primary text-center">
      <tr>
        <th>No</th>
        <th>Tautan PPDB</th>
        <th>Updated At</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($list->num_rows > 0): ?>
        <?php $no = 1; while ($row = $list->fetch_assoc()): ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><a href="<?= htmlspecialchars($row['link_ppdb']) ?>" target="_blank"><?= htmlspecialchars($row['link_ppdb']) ?></a></td>
            <td class="text-center"><?= date('d-m-Y H:i:s', strtotime($row['updated_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="3" class="text-center text-muted">Belum ada data link PPDB</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

      

    </div>
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
