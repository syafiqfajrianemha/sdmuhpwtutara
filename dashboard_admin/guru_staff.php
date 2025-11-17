<?php
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$editData = null;
$data = [];

// Ambil semua data guru/staff
$result = $conn->query("SELECT * FROM guru_staff ORDER BY id ASC"); // urut dari id kecil ke besar
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Jika edit diklik
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM guru_staff WHERE id = ?");
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fungsi upload dan cek hash image
function uploadImageWithHash($fileInputName, $uploadDir = '../image/guru_staff/') {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null; // Tidak ada file diupload
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES[$fileInputName]['tmp_name'];
    $fileName = basename($_FILES[$fileInputName]['name']);

    // Hitung hash file yang diupload
    $hashBaru = md5_file($tmpName);

    // Cari file dengan hash sama di folder upload
    foreach (glob($uploadDir . '*') as $existingFile) {
        if (md5_file($existingFile) === $hashBaru) {
            // File sama sudah ada, pakai file ini
            return $existingFile;
        }
    }

    // Kalau belum ada file sama, pindahkan file baru
    $targetFile = $uploadDir . time() . '_' . $fileName;
    if (move_uploaded_file($tmpName, $targetFile)) {
        return $targetFile;
    }

    return null; // Upload gagal
}

// Proses tambah data
if (isset($_POST['tambah'])) {
    // Ambil nilai ID manual jika ada
    $customId = isset($_POST['custom_id']) && $_POST['custom_id'] !== '' ? intval($_POST['custom_id']) : null;
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];

    $imagePath = uploadImageWithHash('image');

    // Gunakan custom ID jika diisi
    if ($customId !== null) {
        $stmt = $conn->prepare("INSERT INTO guru_staff (id, nama, jabatan, image) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("isss", $customId, $nama, $jabatan, $imagePath);
    } else {
        $stmt = $conn->prepare("INSERT INTO guru_staff (nama, jabatan, image) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("sss", $nama, $jabatan, $imagePath);
    }

    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Data berhasil ditambahkan.";
    header("Location: guru_staff");
    exit;
}


// Proses update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $imageLama = $_POST['image_lama'];

    $imagePath = $imageLama;

    $uploadResult = uploadImageWithHash('image');
    if ($uploadResult !== null) {
        $imagePath = $uploadResult;

        // Hapus file lama jika ada dan berbeda dengan file baru
        if ($imageLama && file_exists($imageLama) && $imageLama !== $imagePath) {
            unlink($imageLama);
        }
    }

    $stmt = $conn->prepare("UPDATE guru_staff SET nama = ?, jabatan = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $jabatan, $imagePath, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Data berhasil diupdate.";
    header("Location: guru_staff");
    exit;
}

// Proses hapus data
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Ambil data gambar lama
    $stmt = $conn->prepare("SELECT image FROM guru_staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res && $res['image'] && file_exists($res['image'])) {
        unlink($res['image']);
    }

    // Hapus data
    $stmt = $conn->prepare("DELETE FROM guru_staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Data berhasil dihapus.";
    header("Location: guru_staff");
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

</style>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
  <div id="sidebar-wrapper" class="p-3">
    <div class="sidebar-heading text-white fw-bold mb-4">Dashboard Admin</div>
    <div class="list-group list-group-flush">
      <a href="index" class="list-group-item list-group-item-action"> <i class="fas fa-home me-2"></i> Home</a>
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
      <a href="berita" class="list-group-item list-group-item-action"> <i class="fas fa-newspaper me-2"></i> Berita</a>
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
    <h2>Data Guru & Staff</h2>

    <?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-secondary alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>


    <!-- FORM TAMBAH / EDIT -->
    <div class="card mb-4">
        <div class="card-header"><?= $editData ? 'Edit Guru / Staff' : 'Tambah Guru / Staff' ?></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                <input type="hidden" name="image_lama" value="<?= $editData['image'] ?? '' ?>">

                <div class="mb-2">
                    <label>ID (Optional)</label>
                    <input type="number" name="custom_id" class="form-control" value="<?= $editData['id'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" id="nama" name="nama" class="form-control" required value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" class="form-control" required value="<?= htmlspecialchars($editData['jabatan'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Foto</label>
                    <input type="file" id="image" name="image" class="form-control">
                    <?php if (!empty($editData['image'])): ?>
                        <div class="mt-2">
                            <small>Foto saat ini:</small><br>
                            <img src="<?= htmlspecialchars($editData['image']) ?>" alt="Foto Guru" style="max-height: 80px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="<?= $editData ? 'update' : 'tambah' ?>" class="btn btn-primary">
                    <?= $editData ? 'Update' : 'Tambah' ?>
                </button>
                <?php if ($editData): ?>
                    <a href="guru_staff" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- TABEL DATA -->
    <h5>Daftar Guru & Staff</h5>
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['jabatan']) ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Foto Guru" style="max-height: 60px;">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                      <a href="guru_staff?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                      </a>
                      <a href="guru_staff?hapus=<?= $row['id'] ?>" 
                        class="btn btn-sm btn-danger" 
                        onclick="return confirm('Yakin ingin hapus data ini?')">
                        <i class="fas fa-trash-alt me-1"></i> Hapus
                      </a>
                    </td>

                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center">Belum ada data guru & staff.</td></tr>
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

