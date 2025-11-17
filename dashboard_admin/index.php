<?php
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

function getImageContent($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    return file_get_contents($file['tmp_name']);
}

/// === HANDLE SAAT TOMBOL SIMPAN DIKLIK ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- UPLOAD GAMBAR HEADER ---
    if (isset($_FILES['gambar_header']) && $_FILES['gambar_header']['error'] === 0) {
        // Hapus gambar lama dari folder dan database
        $old = $conn->query("SELECT gambar_header FROM header");
        while ($row = $old->fetch_assoc()) {
            $old_path = "../image/header/" . $row['gambar_header'];
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }
        $conn->query("DELETE FROM header");

        // Upload gambar baru
        $nama_asli = basename($_FILES['gambar_header']['name']);
        $nama_baru = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $nama_asli);
        $target_path = "../image/header/" . $nama_baru;

        if (move_uploaded_file($_FILES['gambar_header']['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO header (gambar_header) VALUES (?)");
            $stmt->bind_param("s", $nama_baru);
            $stmt->execute();
            $stmt->close();
        }
    }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update nama sekolah
    if (isset($_POST['nama_sekolah'])) {
        $nama_sekolah = $conn->real_escape_string($_POST['nama_sekolah']);
        $conn->query("UPDATE kontak SET nama_sekolah='$nama_sekolah' WHERE id=1");
    }

    // Update kontak
    if (isset($_POST['alamat'], $_POST['email'], $_POST['no_whatsapp'], $_POST['instagram'], $_POST['facebook'], $_POST['link_gmaps'])) {
        $alamat = $conn->real_escape_string($_POST['alamat']);
        $email = $conn->real_escape_string($_POST['email']);
        $no_whatsapp = $conn->real_escape_string($_POST['no_whatsapp']);
        $instagram = $conn->real_escape_string($_POST['instagram']);
        $facebook = $conn->real_escape_string($_POST['facebook']);
        $youtube = $conn->real_escape_string($_POST['youtube']);
        $link_gmaps = $conn->real_escape_string($_POST['link_gmaps']);

        $conn->query("UPDATE kontak SET 
            alamat='$alamat', email='$email', no_whatsapp='$no_whatsapp',
            instagram='$instagram', facebook='$facebook', youtube='$youtube', link_gmaps='$link_gmaps'
            WHERE id=1");
    }

// Keunggulan Sekolah
if (isset($_POST['id_keunggulan'], $_POST['judul_keunggulan'], $_POST['deskripsi_keunggulan'])) {
    $id_keunggulan = $_POST['id_keunggulan'];
    $judul_keunggulan = $_POST['judul_keunggulan'];
    $deskripsi_keunggulan = $_POST['deskripsi_keunggulan'];
    $hapus_keunggulan = isset($_POST['hapus_keunggulan']) ? $_POST['hapus_keunggulan'] : [];

    for ($i = 0; $i < count($id_keunggulan); $i++) {
        $id = intval($id_keunggulan[$i]);
        $judul = trim($judul_keunggulan[$i]);
        $deskripsi = trim($deskripsi_keunggulan[$i]);

        if ($id > 0 && in_array((string)$id_keunggulan[$i], $hapus_keunggulan)) {
            $stmt = $conn->prepare("DELETE FROM keunggulan_sekolah WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            continue;
        }

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE keunggulan_sekolah SET judul = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("ssi", $judul, $deskripsi, $id);
            $stmt->execute();
            $stmt->close();
        }

        if ($id == 0 && (!empty($judul) || !empty($deskripsi))) {
            $stmt = $conn->prepare("INSERT INTO keunggulan_sekolah (judul, deskripsi) VALUES (?, ?)");
            $stmt->bind_param("ss", $judul, $deskripsi);
            $stmt->execute();
            $stmt->close();
        }
    }
}


    // Program Unggulan
    $id_programs = $_POST['id_program'];
$judul_programs = $_POST['judul_program'];
$deskripsi_programs = $_POST['deskripsi_program'];

foreach ($id_programs as $index => $id) {
    $judul = mysqli_real_escape_string($conn, $judul_programs[$index]);
    $deskripsi = mysqli_real_escape_string($conn, $deskripsi_programs[$index]);

    // Cek jika data baru (id = 0)
    if ($id == 0 && $judul != '' && $deskripsi != '') {
        $gambar = null;
        $input_name = "image_program_new_0";
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $gambar = basename($_FILES[$input_name]['name']);
            move_uploaded_file($_FILES[$input_name]['tmp_name'], "uploads/" . $gambar);
        }

        $conn->query("INSERT INTO program_unggulan (judul, deskripsi, image) VALUES ('$judul', '$deskripsi', '$gambar')");
    } elseif ($id != 0) {
        $gambar = null;
        $input_name = "image_program_" . intval($id);
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $gambar = basename($_FILES[$input_name]['name']);
            move_uploaded_file($_FILES[$input_name]['tmp_name'], "uploads/" . $gambar);
        }

        if ($gambar) {
            $conn->query("UPDATE program_unggulan SET judul='$judul', deskripsi='$deskripsi', image='$gambar' WHERE id=$id");
        } else {
            $conn->query("UPDATE program_unggulan SET judul='$judul', deskripsi='$deskripsi' WHERE id=$id");
        }
    }
}

    // Update data_demografi
    if (isset($_POST['id_demografi'], $_POST['jumlah_siswa'], $_POST['jumlah_siswi'], $_POST['jumlah_guru_staff'], $_POST['kuota_murid_baru'])) {
        foreach ($_POST['id_demografi'] as $index => $id_demografi) {
            $jumlah_siswa = $conn->real_escape_string($_POST['jumlah_siswa'][$index]);
            $jumlah_siswi = $conn->real_escape_string($_POST['jumlah_siswi'][$index]);
            $jumlah_guru_staff = $conn->real_escape_string($_POST['jumlah_guru_staff'][$index]);
            $kuota_murid_baru = $conn->real_escape_string($_POST['kuota_murid_baru'][$index]);

            $conn->query("UPDATE data_demografi SET 
                jumlah_siswa='$jumlah_siswa', 
                jumlah_siswi='$jumlah_siswi', 
                jumlah_guru_staff='$jumlah_guru_staff', 
                kuota_murid_baru='$kuota_murid_baru' 
                WHERE id=" . intval($id_demografi));
        }
    }

     // Redirect agar alert sukses bisa muncul (tambahan penting)
    header("Location: ".$_SERVER['PHP_SELF']."?sukses=1");
   
}
 // Redirect agar alert sukses bisa muncul
header("Location: ".$_SERVER['PHP_SELF']."?sukses=1");

}

$headerImage = null;
$headerResult = $conn->query("SELECT gambar_header FROM header ORDER BY id DESC LIMIT 1");
if ($headerResult && $headerResult->num_rows > 0) {
    $rowHeader = $headerResult->fetch_assoc();
    $headerImage = $rowHeader['gambar_header'];
}

// Ambil data dari database
$sql_demografi = "SELECT * FROM data_demografi ORDER BY id LIMIT 3";
$result_demografi = $conn->query($sql_demografi);

$sql_kontak = "SELECT * FROM kontak WHERE id=1 LIMIT 1";
$result_kontak = $conn->query($sql_kontak);
$kontak = $result_kontak->fetch_assoc();

$sql_keunggulan = "SELECT * FROM keunggulan_sekolah ORDER BY id LIMIT 3";
$result_keunggulan = $conn->query($sql_keunggulan);

// Ambil data untuk ditampilkan di form
$result_program = mysqli_query($conn, "SELECT * FROM program_unggulan ORDER BY id DESC");
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
  .program-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: flex-start;
}

.program-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  padding: 20px;
  width: 300px;
  box-sizing: border-box;
}



</style>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
  <div id="sidebar-wrapper" class="p-3">
    <div class="sidebar-heading text-white fw-bold mb-4">Dashboard Admin</div>
    <div class="list-group list-group-flush">
     <a href="index" class="list-group-item list-group-item-action <?= ($currentPage == 'home.php') ? 'active-page' : '' ?>">
      <i class="fas fa-home me-2"></i> Home
    </a>
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
      <a href="kelola_admin" class="list-group-item list-group-item-action <?= ($currentPage == 'kelola-admin') ? 'active-page' : '' ?>">
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

    <!-- SEMUA INPUT: nama sekolah, fitur, program unggulan, demografi, kontak -->
    <!-- (seperti kode sebelumnya, tinggal lanjut dari sini...) -->
  <form action="index.php" method="post" enctype="multipart/form-data">

   <?php if (isset($_GET['sukses'])): ?>
  <div class="alert alert-secondary alert-dismissible fade show" role="alert">
      <strong>Info:</strong> Perubahan berhasil disimpan.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <script>
    const url = new URL(window.location.href);
    url.searchParams.delete('sukses');
    window.history.replaceState({}, document.title, url.toString());
  </script>
<?php endif; ?>


<div class="container my-5">
  <h4 class="mb-3">Gambar Header</h4>

  <?php if ($headerImage): ?>
    <div class="mb-4 text-center">
<div class="mb-4 text-start"> <!-- text-end biar ke kanan -->
  <img src="../image/header/<?= htmlspecialchars($headerImage) ?>" 
       alt="Header"
       style="width: 100%; max-width: 500px; height: 150px; object-fit: cover; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
</div>
</div>

  <?php else: ?>
    <p>Belum ada gambar header yang diunggah.</p>
  <?php endif; ?>

  <input type="file" name="gambar_header" class="form-control mb-3" accept="image/*">
  <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar header.</small>
</div>




  <div class="container my-5">
    <h4 class="mb-3">Nama Sekolah</h4>
    <input type="text" class="form-control mb-4" name="nama_sekolah" value="<?= htmlspecialchars($kontak['nama_sekolah'] ?? '') ?>" />

  

   
  <!-- KEUNGGULAN SEKOLAH -->
  <h4 class="mb-3">Keunggulan Sekolah</h4>
  <div class="row">
    <?php while ($keunggulan = $result_keunggulan->fetch_assoc()) : ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <input type="hidden" name="id_keunggulan[]" value="<?= intval($keunggulan['id']) ?>" />
            <input type="text" class="form-control mb-2" name="judul_keunggulan[]" value="<?= htmlspecialchars($keunggulan['judul']) ?>" placeholder="Judul Keunggulan" />
            <textarea class="form-control mb-2" name="deskripsi_keunggulan[]" rows="2" placeholder="Deskripsi Keunggulan"><?= htmlspecialchars($keunggulan['deskripsi']) ?></textarea>

            <!-- Checkbox hapus -->
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="hapus_keunggulan[]" value="<?= intval($keunggulan['id']) ?>" id="hapus<?= $keunggulan['id'] ?>">
              <label class="form-check-label text-danger" for="hapus<?= $keunggulan['id'] ?>">
                Hapus keunggulan ini
              </label>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>

    <!-- Tambah keunggulan baru -->
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <input type="hidden" name="id_keunggulan[]" value="0" />
          <input type="text" class="form-control mb-2" name="judul_keunggulan[]" placeholder="Judul Keunggulan Baru" />
          <textarea class="form-control" name="deskripsi_keunggulan[]" rows="2" placeholder="Deskripsi Keunggulan Baru"></textarea>
        </div>
      </div>
    </div>
  </div>





<h4 class="mb-4 text-xl font-semibold">Program Unggulan</h4>

<div class="program-wrapper">

  <?php
  include '../db.php';
  $result_program = $conn->query("SELECT * FROM program_unggulan");
  while ($program = $result_program->fetch_assoc()):
  ?>
    <div class="program-card">
      <h6>Edit Program</h6>
      <input type="hidden" name="id_program[]" value="<?= intval($program['id']) ?>">

      <div style="display: flex; flex-direction: column; gap: 10px;">
        <input type="text" name="judul_program[]" value="<?= htmlspecialchars($program['judul']) ?>"
               placeholder="Judul Program" required>

        <textarea name="deskripsi_program[]" rows="3" required><?= htmlspecialchars($program['deskripsi']) ?></textarea>

        <?php if (!empty($program['image'])): ?>
          <img src="<?= htmlspecialchars('../image/program/' . $program['image']) ?>" alt="Gambar Program">
        <?php endif; ?>

        <label>Ganti Gambar:</label>
        <input type="file" name="image_program_<?= intval($program['id']) ?>" accept="image/*">
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Form Tambah Baru -->
  <div class="program-card">
    <h6>Tambah Program Baru</h6>
    <input type="hidden" name="id_program[]" value="0">

    <div style="display: flex; flex-direction: column; gap: 10px;">
      <input type="text" name="judul_program[]" placeholder="Judul Program Baru">
      <textarea name="deskripsi_program[]" rows="3" placeholder="Deskripsi Program Baru"></textarea>
      <label>Upload Gambar:</label>
      <input type="file" name="image_program_new_0" accept="image/*">
    </div>
  </div>

</div>







    <h4 class="mb-3">Data Demografi</h4>
<div class="row g-2">
  <?php while ($demografi = $result_demografi->fetch_assoc()) : ?>
    <div class="col-12">
      <div class="row g-2 align-items-stretch">
        <input type="hidden" name="id_demografi[]" value="<?= intval($demografi['id']) ?>" />

        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Jumlah Siswa</label>
          <input type="number" class="form-control form-control-sm" name="jumlah_siswa[]" value="<?= intval($demografi['jumlah_siswa']) ?>" placeholder="Siswa" />
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Jumlah Guru dan Staff</label>
          <input type="number" class="form-control form-control-sm" name="jumlah_guru_staff[]" value="<?= intval($demografi['jumlah_guru_staff']) ?>" placeholder="Guru/Staff" />
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label small mb-1">Kuota Murid Baru</label>
          <input type="number" class="form-control form-control-sm" name="kuota_murid_baru[]" value="<?= intval($demografi['kuota_murid_baru']) ?>" placeholder="Kuota Baru" />
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<div class="mt-5"></div>

<h4 class="mb-3">Kontak</h4>
<div class="mb-3">
  <label class="form-label">Alamat</label>
  <input type="text" class="form-control" name="alamat" placeholder="Alamat" value="<?= htmlspecialchars($kontak['alamat'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">Email</label>
  <input type="email" class="form-control" name="email" placeholder="Email" value="<?= htmlspecialchars($kontak['email'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">No WhatsApp</label>
  <input type="text" class="form-control" name="no_whatsapp" placeholder="No WhatsApp" value="<?= htmlspecialchars($kontak['no_whatsapp'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">Instagram</label>
  <input type="text" class="form-control" name="instagram" placeholder="Instagram" value="<?= htmlspecialchars($kontak['instagram'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">Facebook</label>
  <input type="text" class="form-control" name="facebook" placeholder="Facebook" value="<?= htmlspecialchars($kontak['facebook'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">YouTube</label>
  <input type="text" class="form-control" name="youtube" placeholder="Link YouTube" value="<?= htmlspecialchars($kontak['youtube'] ?? '') ?>" />
</div>
<div class="mb-3">
  <label class="form-label">Link Google Maps</label>
  <input type="text" class="form-control" name="link_gmaps" placeholder="Google Maps Link" value="<?= htmlspecialchars($kontak['link_gmaps'] ?? '') ?>" />
</div>

    <div class="text-end mt-4">
      <button type="submit" class="btn btn-primary px-4 shadow-sm">
        <i class="fa-solid fa-save me-2"></i>Simpan Perubahan
      </button>
    </div>
  </form>
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
