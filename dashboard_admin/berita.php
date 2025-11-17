<?php
// koneksi database
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Fungsi membuat slug dari judul
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

// Fungsi upload foto
function handleUploadFoto($uploadFieldName, $oldFoto = null) {
    $foto = $oldFoto;

    if (isset($_FILES[$uploadFieldName]) && $_FILES[$uploadFieldName]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$uploadFieldName]['tmp_name'];
        $originalName = basename($_FILES[$uploadFieldName]['name']);

        // Pastikan hanya nama asli, tanpa dobel ekstensi
        $pathInfo = pathinfo($originalName);
        $baseName = $pathInfo['filename'];
        $ext = strtolower($pathInfo['extension']);
        $fileName = $baseName . '.' . $ext;

        $uploadDir = '../image/berita/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetFile = $uploadDir . $fileName;

        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowedExtensions)) {
            // Timpa file lama jika sudah ada (tidak pakai timestamp)
            if (move_uploaded_file($tmpName, $targetFile)) {
                $foto = $fileName; // Simpan hanya nama file asli tanpa path
            }
        }
    }

    // Kalau tidak upload dan tidak ada foto lama, pakai default
    if (!$foto) {
        $foto = '../image/berita/default.jpg';
    }

    return $foto;
}


// Proses tambah berita
if (isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $slug = slugify($judul);
    $tanggal = $_POST['tanggal'];
    $deskripsi = $_POST['deskripsi'];
    $foto = handleUploadFoto('foto');

    $stmt = mysqli_prepare($conn, "INSERT INTO berita (judul, slug, tanggal, deskripsi, foto, views, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
    mysqli_stmt_bind_param($stmt, "sssss", $judul, $slug, $tanggal, $deskripsi, $foto);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['success'] = 'Berita berhasil ditambah.';
    header("Location: berita?sukses=inserted");
    exit;
}

// Proses update berita
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $slug = slugify($judul);
    $tanggal = $_POST['tanggal'];
    $deskripsi = $_POST['deskripsi'];
    $fotoLama = $_POST['foto_lama'] ?? null;
    $foto = handleUploadFoto('foto', $fotoLama);

    $stmt = mysqli_prepare($conn, "UPDATE berita SET judul=?, slug=?, tanggal=?, deskripsi=?, foto=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssi", $judul, $slug, $tanggal, $deskripsi, $foto, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['success'] = 'Berita berhasil diupdate.';
    header("Location: berita?sukses=updated");
    exit;
}

// Ambil data berita untuk ditampilkan
$result = mysqli_query($conn, "SELECT * FROM berita ORDER BY created_at DESC");
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Jika ada parameter edit
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM berita WHERE id=$editId");
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
        data-bs-toggle="collapse" data-bs-target="#profilMenu" role="button" aria-expanded="false">
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
      <a href="berita" class="list-group-item list-group-item-action <?= ($currentPage == 'berita.php') ? 'active-page' : '' ?>"><i class="fas fa-newspaper me-2"></i>Berita</a>

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
    <h2>Berita dan Pengumuman</h2>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-secondary alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

    <!-- FORM -->
      <div class="card mb-4">
          <div class="card-header"><?= $editData ? 'Edit Berita' : 'Tambah Berita dan Pengumuman' ?></div>
          <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                  <input type="hidden" name="foto_lama" value="<?= $editData['foto'] ?? '' ?>">

                  <div class="mb-2">
                      <label>Judul</label>
                      <input type="text" name="judul" class="form-control" value="<?= $editData['judul'] ?? '' ?>" required>
                  </div>

                  <div class="mb-2">
                      <label>Tanggal</label>
                      <input type="date" name="tanggal" class="form-control" value="<?= $editData['tanggal'] ?? '' ?>" required>
                  </div>

                  <div class="mb-2">
                      <label>Deskripsi</label>
                      <textarea name="deskripsi" class="form-control" rows="5" required><?= $editData['deskripsi'] ?? '' ?></textarea>
                  </div>

                  <div class="mb-2">
                      <label>Foto</label>
                      <input type="file" name="foto" class="form-control">
                      <?php if (!empty($editData['foto']) && file_exists('../image/berita/' . $editData['foto'])): ?>
                          <small>Foto sekarang:
                              <img src="../image/berita/<?= $editData['foto'] ?>" style="max-height: 60px;">
                          </small>
                      <?php endif; ?>
                  </div>

                  <?php if ($editData): ?>
                      <div class="mb-2">
                          <label>Slug (otomatis)</label>
                          <input type="text" class="form-control" value="<?= htmlspecialchars($editData['slug']) ?>" readonly>
                      </div>

                      <div class="mb-2">
                          <label>Total Views</label>
                          <input type="text" class="form-control" value="<?= $editData['views'] ?>" readonly>
                      </div>
                  <?php endif; ?>

                  <button type="submit" name="<?= $editData ? 'update' : 'tambah' ?>" class="btn btn-primary">
                      <?= $editData ? 'Update' : 'Tambah' ?>
                  </button>

                  <?php if ($editData): ?>
                      <a href="berita" class="btn btn-secondary">Batal</a>
                  <?php endif; ?>
              </form>
          </div>
      </div>


    <!-- TABEL -->
    <!-- TABEL -->
<h5>Daftar Berita dan Pengumuman</h5>
<table class="table table-bordered table-striped">
<thead>
    <tr>
        <th>No</th>
        <th>Judul</th>
        <th>Tanggal</th>
        <th>Views</th>
        <th>Deskripsi</th>
        <th>Foto</th>
        <th>Created At</th>
        <th>Aksi</th>
    </tr>
</thead>
<tbody>
    <?php if (!empty($data)): $no = 1; foreach ($data as $row): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= $row['tanggal'] ?></td>
            <td><?= $row['views'] ?></td>
            <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
            <td>
                <?php if (!empty($row['foto'])): ?>
                    <img src="../image/berita/<?= $row['foto'] ?>" style="max-height: 60px;">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="berita?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
    <?php endforeach; else: ?>
        <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
    <?php endif; ?>
</tbody>

</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("sidebarToggle").addEventListener("click", function () {
    document.getElementById("wrapper").classList.toggle("toggled");
  });

</script>

</body>
</html>

