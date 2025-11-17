<?php
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);


// ========== Hapus Data ==========
if (isset($_GET['hapus_id'])) {
    $hapus_id = $_GET['hapus_id'];
    $stmt = $conn->prepare("DELETE FROM visi_misi WHERE id=?");
    $stmt->bind_param("i", $hapus_id);
    $stmt->execute();
    $stmt->close();
    header("Location: visi_misi.php?sukses=deleted");
    exit;
}

// ========== Simpan Data (Insert / Update) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $visi = $_POST['visi'] ?? '';
    $misi = $_POST['misi'] ?? '';

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE visi_misi SET visi=?, misi=? WHERE id=?");
        $stmt->bind_param("ssi", $visi, $misi, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: visi_misi.php?sukses=updated");
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO visi_misi (visi, misi) VALUES (?, ?)");
        $stmt->bind_param("ss", $visi, $misi);
        $stmt->execute();
        $stmt->close();
        header("Location: visi_misi.php?sukses=inserted");
    }
    exit;
}

// ========== Ambil Data untuk Edit ==========
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM visi_misi WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// ========== Ambil Semua Data ==========
$data = $conn->query("SELECT * FROM visi_misi ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin - Visi & Misi</title>
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
  #page-content-wrapper { flex-grow: 1; }
  .btn-primary {
    background-color: #003366;
    border-color: #003366;
  }
  .btn-primary:hover {
    background-color: #002244;
    border-color: #002244;
  }
  #formTambah {
      max-width: 650px;
      margin-bottom: 2rem;
      padding: 1.5rem;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #f8f9fa;
  }
  #formTambah textarea {
      height: 80px;
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
      <!-- PROFIL SEKOLAH -->
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
        data-bs-toggle="collapse" 
        href="#profilMenu" 
        aria-expanded="<?= in_array($currentPage, ['sejarah.php', 'visi_misi.php', 'struktur_organisasi.php']) ? 'true' : 'false' ?>">
        <div><i class="fas fa-school me-2"></i> Profil</div>
        <i class="fas fa-caret-down"></i>
      </a>

      <div class="collapse <?= in_array($currentPage, ['sejarah.php', 'visi_misi.php', 'struktur_organisasi.php']) ? 'show' : '' ?>" id="profilMenu">
        <a href="sejarah" class="list-group-item list-group-item-action <?= ($currentPage == 'sejarah.php') ? 'active-page' : '' ?>">
          <i class="fas fa-book me-2"></i> Sejarah
        </a>
        <a href="visi_misi" class="list-group-item list-group-item-action <?= ($currentPage == 'visi_misi.php') ? 'active-page' : '' ?>">
          <i class="fas fa-lightbulb me-2"></i> Visi & Misi
        </a>
        <a href="struktur_organisasi" class="list-group-item list-group-item-action <?= ($currentPage == 'struktur_organisasi.php') ? 'active-page' : '' ?>">
          <i class="fas fa-sitemap me-2"></i> Struktur Organisasi
        </a>
      </div>

      <a href="berita" class="list-group-item list-group-item-action"><i class="fas fa-newspaper me-2"></i> Berita</a>
      <a href="ppdb" class="list-group-item list-group-item-action"><i class="fas fa-users me-2"></i> PPDB</a>
      <a href="prestasi" class="list-group-item list-group-item-action"><i class="fas fa-trophy me-2"></i> Prestasi</a>
      <a class="list-group-item list-group-item-action d-flex align-items-center"
        data-bs-toggle="collapse" data-bs-target="#informasiMenu" role="button" aria-expanded="false">
        <i class="fas fa-info-circle me-2"></i> Informasi <i class="fas fa-caret-down ms-auto"></i>
      </a>
      <div class="collapse" id="informasiMenu">
        <a href="ekstrakulikuler" class="list-group-item list-group-item-action"><i class="fas fa-swimmer me-2"></i> Ekstrakurikuler</a>
        <a href="fasilitas" class="list-group-item list-group-item-action"><i class="fas fa-building me-2"></i> Fasilitas</a>
        <a href="guru_staff" class="list-group-item list-group-item-action"><i class="fas fa-chalkboard-teacher me-2"></i> Guru dan Staff</a>
        <a href="alumni" class="list-group-item list-group-item-action"><i class="fas fa-user-graduate me-2"></i> Alumni</a>
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

        <div class="container-fluid mt-4">
            <?php if (isset($_GET['sukses'])): ?>
                <?php
                    $msg = [
                        'inserted' => 'Data berhasil ditambahkan.',
                        'updated'  => 'Data berhasil diperbarui.',
                        'deleted'  => 'Data berhasil dihapus.'
                    ];
                ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Berhasil!</strong> <?= $msg[$_GET['sukses']] ?? '' ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ✅ Form Tambah / Edit -->
            <div id="formTambah">
                <h5 class="fw-bold text-dark mb-3">
                    <?= $edit_data ? 'Edit Visi dan Misi (ID '.$edit_data['id'].')' : 'Tambah Visi dan Misi'; ?>
                </h5>
                <form method="POST" action="visi_misi.php">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Visi</label>
                        <textarea name="visi" class="form-control" required><?= htmlspecialchars($edit_data['visi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Misi</label>
                        <textarea name="misi" class="form-control" required><?= htmlspecialchars($edit_data['misi'] ?? '') ?></textarea>
                    </div>

                    <?php if ($edit_data): ?>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        <a href="visi_misi.php" class="btn btn-secondary btn-sm ms-2">Batal</a>
                        <a href="visi_misi.php?hapus_id=<?= $edit_data['id'] ?>" 
                           class="btn btn-danger btn-sm ms-2"
                           onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success btn-sm">Tambah</button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- 🧾 Tabel Data -->
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="35%">Visi</th>
                        <th width="40%">Misi</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($data->num_rows > 0): ?>
                        <?php while ($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $row['id'] ?></td>
                                <td><?= nl2br(htmlspecialchars($row['visi'])) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['misi'])) ?></td>
                                <td class="text-center">
                                  <a href="visi_misi.php?edit_id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                  </a>
                                  <a href="visi_misi.php?hapus_id=<?= $row['id'] ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash-alt me-1"></i>Hapus
                                  </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Belum ada data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("sidebarToggle").addEventListener("click", function () {
  document.getElementById("wrapper").classList.toggle("toggled");
});

// 🧹 Hapus parameter 'sukses' dari URL setelah alert muncul
document.addEventListener("DOMContentLoaded", function() {
    if (window.location.search.includes('sukses')) {
        const url = new URL(window.location.href);
        url.searchParams.delete('sukses');
        window.history.replaceState({}, document.title, url.pathname);
    }
});
</script>

</body>
</html>
