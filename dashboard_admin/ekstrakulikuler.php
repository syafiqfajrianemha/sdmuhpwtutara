<?php
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$editData = null;
$data = [];

// Ambil semua data ekstrakulikuler
$result = $conn->query("SELECT * FROM ekstrakulikuler ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Jika edit di klik
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM ekstrakulikuler WHERE id = ?");
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fungsi bantu cek hash file sudah ada
function findImageByHash($uploadDir, $tmpFilePath) {
    $hashBaru = md5_file($tmpFilePath);
    foreach (glob($uploadDir . '*') as $existingFile) {
        if (md5_file($existingFile) === $hashBaru) {
            return $existingFile;
        }
    }
    return false;
}

// Handle tambah data
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../image/ekstrakulikuler/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $originalName = basename($_FILES['image']['name']);
$targetPath = $uploadDir . $originalName;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $imagePath = $targetPath;
}
}

    $stmt = $conn->prepare("INSERT INTO ekstrakulikuler (nama, deskripsi, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $deskripsi, $imagePath);
    $stmt->execute();
    $stmt->close();

    header("Location: ekstrakulikuler?sukses=added");
    exit;
}

// update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $imageLama = $_POST['image_lama'];

    $imagePath = $imageLama;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../image/ekstrakulikuler/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $originalName = basename($_FILES['image']['name']);
$targetPath = $uploadDir . $originalName;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    $imagePath = $targetPath;

    // Hapus file lama kalau beda
    if ($imageLama && file_exists($imageLama) && $imageLama != $imagePath) {
        unlink($imageLama);
    }
}
    }

    $stmt = $conn->prepare("UPDATE ekstrakulikuler SET nama = ?, deskripsi = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $deskripsi, $imagePath, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ekstrakulikuler?sukses=updated");
    exit;
}


// Handle hapus data
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT image FROM ekstrakulikuler WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && $res['image'] && file_exists($res['image'])) {
        unlink($res['image']);
    }

    $stmt = $conn->prepare("DELETE FROM ekstrakulikuler WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ekstrakulikuler?sukses=deleted");
    exit;
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
.action-btn {
  min-width: 85px;          /* biar ukuran tombol seragam */
  padding: 7px 10px;        /* bikin tombol lebih tinggi */
  margin-top: 3px;          /* jarak atas */
  margin-bottom: 3px;       /* jarak bawah */
  display: inline-flex;     /* ikon & teks sejajar */
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


  <div class="container mt-4">
    <h2>Ekstrakulikuler</h2>

   <?php if (isset($_GET['sukses'])): ?>
    <div class="alert alert-secondary alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['sukses'] === 'updated') {
            echo 'Data ekstrakulikuler berhasil diupdate.';
        } elseif ($_GET['sukses'] === 'added') {
            echo 'Data ekstrakulikuler berhasil ditambahkan.';
        } elseif ($_GET['sukses'] === 'deleted') {
            echo 'Data ekstrakulikuler berhasil dihapus.';
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<script>
  if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete('sukses');
    window.history.replaceState({}, document.title, url.toString());
  }
</script>

    <!-- Form Tambah -->
    <div class="card mb-4" style="max-width: 600px;">
        <div class="card-header"><?= $editData ? 'Edit Ekstrakulikuler' : 'Tambah Ekstrakulikuler' ?></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                <input type="hidden" name="image_lama" value="<?= $editData['image'] ?? '' ?>">
                
                <div class="mb-3">
                    <label class="form-label">Nama Ekstrakulikuler</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="image" class="form-control">
                    <?php if (!empty($editData['image'])): ?>
                        <small>Gambar sekarang:<br><img src="<?= $editData['image'] ?>" alt="Image" style="max-height: 80px; margin-top: 5px;"></small>
                    <?php endif; ?>
                </div>

                <button type="submit" name="<?= $editData ? 'update' : 'tambah' ?>" class="btn btn-primary">
                    <?= $editData ? 'Update' : 'Tambah' ?>
                </button>
                <?php if ($editData): ?>
                    <a href="ekstrakulikuler" class="btn btn-secondary ms-2">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- TABEL -->
    <h5>Daftar Ekstrakulikuler</h5>
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Gambar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): $no=1; foreach ($data as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                <td>
                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= $row['image'] ?>" alt="Image" style="max-height: 60px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="ekstrakulikuler?edit=<?= $row['id'] ?>" 
                    class="btn btn-warning btn-sm action-btn me-2">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  <a href="ekstrakulikuler?hapus=<?= $row['id'] ?>" 
                    onclick="return confirm('Yakin hapus data ini?')" 
                    class="btn btn-danger btn-sm action-btn">
                    <i class="fas fa-trash-alt me-1"></i>Hapus
                  </a>
                </td>


            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center">Belum ada data.</td></tr>
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

