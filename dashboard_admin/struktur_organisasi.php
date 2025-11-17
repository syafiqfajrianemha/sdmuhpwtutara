<?php
session_start();
include '../db.php';

$currentPage = basename($_SERVER['PHP_SELF']);


// === Proses simpan data (Tambah / Edit) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $urutan = $_POST['urutan'] ?? 0;

    // Cek apakah nama sudah ada di tabel (tapi bukan dirinya sendiri)
    $cek = $conn->prepare("SELECT id FROM guru_staff WHERE nama = ? AND id != ?");
    $cek->bind_param("si", $nama, $id);
    $cek->execute();
    $cek->bind_result($existing_id);
    $nama_sudah_ada = $cek->fetch();
    $cek->close();

    if ($id) {
        // === Mode EDIT ===
        if ($nama_sudah_ada) {
            // Jika nama sudah ada di data lain, update data lama (replace)
            $stmt = $conn->prepare("UPDATE guru_staff SET jabatan=?, urutan=? WHERE id=?");
            $stmt->bind_param("sii", $jabatan, $urutan, $existing_id);
            $stmt->execute();
            $stmt->close();

            // Hapus data yang sedang diedit (biar gak duplikat)
            $hapus = $conn->prepare("DELETE FROM guru_staff WHERE id=?");
            $hapus->bind_param("i", $id);
            $hapus->execute();
            $hapus->close();

            header("Location: struktur_organisasi.php?sukses=replaced");
        } else {
            // Update normal kalau nama unik
            $stmt = $conn->prepare("UPDATE guru_staff SET nama=?, jabatan=?, urutan=? WHERE id=?");
            $stmt->bind_param("ssii", $nama, $jabatan, $urutan, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: struktur_organisasi.php?sukses=updated");
        }
    } else {
        // === Mode TAMBAH ===
        $cek2 = $conn->prepare("SELECT id FROM guru_staff WHERE nama = ?");
        $cek2->bind_param("s", $nama);
        $cek2->execute();
        $cek2->bind_result($existing_id2);
        $sudah_ada = $cek2->fetch();
        $cek2->close();

        if ($sudah_ada) {
            // Replace data lama (update jabatan & urutan)
            $stmt = $conn->prepare("UPDATE guru_staff SET jabatan=?, urutan=? WHERE id=?");
            $stmt->bind_param("sii", $jabatan, $urutan, $existing_id2);
            $stmt->execute();
            $stmt->close();
            header("Location: struktur_organisasi.php?sukses=replaced");
        } else {
            // Tambah data baru
            $stmt = $conn->prepare("INSERT INTO guru_staff (nama, jabatan, urutan) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nama, $jabatan, $urutan);
            $stmt->execute();
            $stmt->close();
            header("Location: struktur_organisasi.php?sukses=inserted");
        }
    }
    exit;
}

// === Ambil data untuk Edit ===
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM guru_staff WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// === Proses hapus ===
if (isset($_GET['hapus_id'])) {
    $hapus_id = $_GET['hapus_id'];
    $stmt = $conn->prepare("DELETE FROM guru_staff WHERE id=?");
    $stmt->bind_param("i", $hapus_id);
    $stmt->execute();
    $stmt->close();
    header("Location: struktur_organisasi.php?sukses=deleted");
    exit;
}

// === Ambil semua data untuk ditampilkan ===
$data = $conn->query("SELECT * FROM guru_staff ORDER BY urutan ASC");
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
  /* Bikin form tambah data kecil */
        #formTambah {
            max-width: 500px;
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }
        /* Bikin textarea tambah data kecil */
        #formTambah textarea {
            height: 80px;
        }
        /* Form struktur organisasi khusus */
form#strukturForm {
  max-width: 500px;
  margin: 0 auto 2rem auto;
  background: #fff;
  padding: 25px 30px;
  border-radius: 10px;
  box-shadow: 0 3px 12px rgba(0,0,0,0.12);
  font-family: Arial, sans-serif;
}

form#strukturForm .form-group {
  margin-bottom: 20px;
  background-color: #f5f7fa;
  padding: 12px 15px;
  border-radius: 8px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
}

form#strukturForm label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #222;
}

form#strukturForm input[type="text"],
form#strukturForm input[type="number"] {
  width: 100%;
  padding: 10px 12px;
  border: 1.8px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
  transition: border-color 0.25s ease;
}

form#strukturForm input[type="text"]:focus,
form#strukturForm input[type="number"]:focus {
  border-color: #0056b3;
  outline: none;
}

form#strukturForm button.btn {
  width: 100%;
  background-color: #003366;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 700;
  color: white;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

form#strukturForm button.btn:hover {
  background-color: #002244;
}

form#strukturForm a.btn-cancel {
  display: inline-block;
  margin-top: 15px;
  background-color:rgb(14, 136, 207);
  color: white;
  padding: 10px 18px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

form#strukturForm a.btn-cancel:hover {
  background-color:rgb(14, 136, 207);
}
.content-wrapper {
    padding: 20px 50px 30px 10px; /* Atas, kanan, bawah, kiri */
    margin-left: 80px; /* Kalau sidebar kamu 250px + 10px jarak */
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

      <a href="berita" class="list-group-item list-group-item-action"><i class="fas fa-newspaper me-2"></i>Berita</a>
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

  <!-- Form dan konten lainnya disimpan di sini -->
  <div class="content-wrapper">
  <h3 class="fw-bold text-dark">Struktur Organisasi</h3>
  <?php if (isset($_GET['sukses'])): ?>
  <?php if ($_GET['sukses'] === 'replaced'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>Info:</strong> Nama sudah ada, data lama berhasil diganti (diupdate).
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['sukses'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Berhasil!</strong> Data berhasil diperbarui.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['sukses'] === 'inserted'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Berhasil!</strong> Data baru berhasil ditambahkan.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($_GET['sukses'] === 'deleted'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Hapus!</strong> Data berhasil dihapus.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <script>
    if (history.replaceState) {
        const url = window.location.href.split('?')[0];
        history.replaceState(null, null, url);
    }
  </script>
<?php endif; ?>


<!-- Form Tambah/Edit -->
<form id="strukturForm" action="struktur_organisasi.php" method="post" autocomplete="off">
  
     <!-- ✅ Tambahkan baris ini di paling atas form -->
  <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">

  <div class="form-group">
    <label for="nama">Nama:</label>
    <input type="text" name="nama" id="nama" required value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label for="jabatan">Jabatan:</label>
    <input type="text" name="jabatan" id="jabatan" required value="<?= htmlspecialchars($edit_data['jabatan'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label for="urutan">Urutan:</label>
    <select name="urutan" id="urutan" required>
      <option value="">-- Pilih Urutan --</option>
      <option value="1" <?= (isset($edit_data['urutan']) && $edit_data['urutan'] == 1) ? 'selected' : '' ?>>1 (Kepala Sekolah)</option>
      <option value="2" <?= (isset($edit_data['urutan']) && $edit_data['urutan'] == 2) ? 'selected' : '' ?>>2 (Wakil Kepala Sekolah)</option>
      <option value="3" <?= (isset($edit_data['urutan']) && $edit_data['urutan'] == 3) ? 'selected' : '' ?>>3 (Guru)</option>
      <option value="4" <?= (isset($edit_data['urutan']) && $edit_data['urutan'] == 4) ? 'selected' : '' ?>>4 (Staff)</option>
    </select>
  </div>
  <button type="submit" class="btn"><?= isset($edit_data) ? 'Simpan Perubahan' : 'Tambah' ?></button>
  <?php if (isset($edit_data)): ?>
    <a href="struktur_organisasi.php" class="btn-cancel">Batal</a>
  <?php endif; ?>
</form>



<!-- Tabel Data -->
<h5>Daftar Struktur Organisasi</h5>
<table class="table table-bordered table-striped table-hover">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Urutan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($data->num_rows > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['jabatan']) ?></td>
                <td><?= $row['urutan'] ?></td>
                <td>
                  <a href="struktur_organisasi.php?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  <a href="struktur_organisasi.php?hapus_id=<?= $row['id'] ?>" 
                    class="btn btn-sm btn-danger" 
                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                    <i class="fas fa-trash-alt me-1"></i>Hapus
                  </a>
                </td>

            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Belum ada data.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById("sidebarToggle").addEventListener("click", function (event) {
  event.stopPropagation(); // ✅ cegah event nyebar ke dropdown Bootstrap
  document.getElementById("wrapper").classList.toggle("toggled");
});
</script>


</body>
</html>
