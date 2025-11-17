<?php
// koneksi database
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$edit_data = null;

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $tahun_masuk = $_POST['tahun_masuk'];
    $tahun_lulus = $_POST['tahun_lulus'];
    $pesan = $_POST['pesan'];

    if (isset($_GET['edit_id'])) {
        // UPDATE data
        $id = $_GET['edit_id'];
        $stmt = $conn->prepare("UPDATE alumni SET nama=?, tahun_masuk=?, tahun_lulus=?, pesan=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $tahun_masuk, $tahun_lulus, $pesan, $id);
        $stmt->execute();

        header("Location: alumni?sukses=edit");
    } else {
        // INSERT data baru
        $stmt = $conn->prepare("INSERT INTO alumni (nama, tahun_masuk, tahun_lulus, pesan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $tahun_masuk, $tahun_lulus, $pesan);
        $stmt->execute();

        header("Location: alumni?sukses=tambah");
    }
    exit();
}

// ==== PROSES HAPUS ====
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    $conn->query("DELETE FROM alumni WHERE id = $id");
    header("Location: alumni?sukses=hapus");
    exit();
}

// Ambil data
$data = $conn->query("SELECT * FROM alumni ORDER BY tahun_lulus DESC");

// Ambil data untuk edit
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM alumni WHERE id = $id");
    $edit_data = $result->fetch_assoc();
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
      <a href="berita" class="list-group-item list-group-item-action"><i class="fas fa-newspaper me-2"></i> Berita</a>
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


  <!-- DATA ALUMNI -->
    <div class="content-wrapper p-4">
      <h3 class="fw-bold text-dark">Data Alumni</h3>

      <?php if (isset($_GET['sukses'])): ?>
        <?php
          switch ($_GET['sukses']) {
              case 'tambah':
                  $pesan = "Data alumni berhasil <strong>ditambahkan</strong>.";
                  $warna = "success";
                  break;
              case 'edit':
                  $pesan = "Data alumni berhasil <strong>diperbarui</strong>.";
                  $warna = "info";
                  break;
              case 'hapus':
                  $pesan = "Data alumni berhasil <strong>dihapus</strong>.";
                  $warna = "danger";
                  break;
              default:
                  $pesan = "Operasi berhasil dilakukan.";
                  $warna = "secondary";
                  break;
          }
        ?>
        <div class="alert alert-<?= $warna ?> alert-dismissible fade show" role="alert">
          <?= $pesan ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>


      <!-- Form Tambah/Edit -->
      <form action="alumni<?= isset($_GET['edit_id']) ? '?edit_id=' . $_GET['edit_id'] : '' ?>" method="post" class="mb-4 p-3 bg-light rounded shadow-sm">
        <div class="mb-3">
          <label for="nama" class="form-label">Nama:</label>
          <input type="text" name="nama" id="nama" class="form-control" required value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label for="tahun_masuk" class="form-label">Tahun Masuk:</label>
          <input type="number" name="tahun_masuk" id="tahun_masuk" class="form-control" required min="1900" max="2099" value="<?= htmlspecialchars($edit_data['tahun_masuk'] ?? date('Y')) ?>">
        </div>
        <div class="mb-3">
          <label for="tahun_lulus" class="form-label">Tahun Lulus:</label>
          <input type="number" name="tahun_lulus" id="tahun_lulus" class="form-control" required min="1900" max="2099" value="<?= htmlspecialchars($edit_data['tahun_lulus'] ?? date('Y')) ?>">
        </div>
        <div class="mb-3">
          <label for="pesan" class="form-label">Pesan / Testimoni:</label>
          <textarea name="pesan" id="pesan" class="form-control" rows="3" placeholder="Masukkan pesan atau testimoni alumni..."><?= htmlspecialchars($edit_data['pesan'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= isset($edit_data) ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if (isset($edit_data)): ?>
          <a href="alumni" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </form>

      <!-- Tabel Data Alumni -->
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>No.</th>
              <th>Nama</th>
              <th>Tahun Masuk</th>
              <th>Tahun Lulus</th>
              <th>Pesan / Testimoni</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($data->num_rows > 0): ?>
              <?php $no = 1; while ($row = $data->fetch_assoc()): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['tahun_masuk']) ?></td>
                  <td><?= htmlspecialchars($row['tahun_lulus']) ?></td>
                  <td><?= nl2br(htmlspecialchars($row['pesan'])) ?></td>
                  <td>
                    <a href="alumni?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="alumni?hapus_id=<?= $row['id'] ?>" 
                      class="btn btn-sm btn-danger" 
                      onclick="return confirm('Yakin ingin menghapus data ini?')">
                      <i class="fas fa-trash-alt me-1"></i> Hapus
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
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

