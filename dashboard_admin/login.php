<?php
session_start();
include('../db.php');

// Ambil error dan input lama dari session jika ada
$error = $_SESSION['error'] ?? '';
$old = $_SESSION['old'] ?? ['email' => ''];

// Hapus session agar tidak muncul lagi saat reload
unset($_SESSION['error']);
unset($_SESSION['old']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $error = '';

    // Simpan input lama
    $old = ['email' => $email];

    // Validasi input kosong
    if (empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } else {
        // Cek email
        $query = "SELECT * FROM admin WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);

            // Verifikasi password
            if (password_verify($password, $row['password'])) {
                // Simpan session dan arahkan ke dashboard
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_nama'] = $row['nama'];
                $_SESSION['admin_email'] = $row['email'];

                header("Location: index");
                exit;
            } else {
                $error = "Kata sandi salah.";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    }

    // Jika ada error, simpan ke session dan reload
    if ($error) {
        $_SESSION['error'] = $error;
        $_SESSION['old'] = $old;
        header("Location: index");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Login Akun</title>
<style>
* {
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body, html {
  height: 100%;
  margin: 0;
  background: #003366;
  display: flex;
  justify-content: center;
  align-items: center;
}
.card {
  background: #fff;
  padding: 40px 60px;
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  width: 460px;
  text-align: center;
}
h2 {
  margin-bottom: 25px;
  color: #333;
}
.form-control {
  width: 100%;
  padding: 12px 15px;
  margin-bottom: 20px;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-size: 16px;
  transition: border-color 0.3s;
}
.form-control:focus {
  border-color: #003366;
  outline: none;
}
.btn {
  background: #003366;
  border: none;
  padding: 12px;
  width: 100%;
  color: white;
  font-weight: 600;
  font-size: 16px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}
.btn:hover { background: #001f4d; }
.error {
  color: #d93025;
  margin-bottom: 15px;
  font-weight: 600;
  font-size: 14px;
  text-align: center;
}
</style>
</head>
<body>
<div class="card">
    <h2>Login Akun</h2>

    <!-- Pesan error di atas form, merah, di tengah -->
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login.php" method="post" novalidate>
        <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>

        <button type="submit" class="btn">Login</button>
    </form>

</div>
</body>
</html>