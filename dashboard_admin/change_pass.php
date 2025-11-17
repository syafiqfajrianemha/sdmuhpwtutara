<?php
session_start();
include('../db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$error = $_SESSION['change_pass_error'] ?? '';
$success = $_SESSION['change_pass_success'] ?? '';
unset($_SESSION['change_pass_error'], $_SESSION['change_pass_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $msg_error = '';
    $msg_success = '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $msg_error = "Semua kolom harus diisi.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $hash = $row['password'];

            if (!password_verify($current, $hash)) {
                $msg_error = "Kata sandi saat ini tidak sesuai.";
            } elseif (password_verify($new, $hash)) {
                $msg_error = "Password baru tidak boleh sama dengan password lama.";
            } elseif ($new !== $confirm) {
                $msg_error = "Konfirmasi password tidak cocok dengan password baru.";
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $u = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $u->bind_param("si", $newHash, $admin_id);
                if ($u->execute()) {
                    $msg_success = "Kata sandi berhasil diperbarui.";
                } else {
                    $msg_error = "Gagal memperbarui kata sandi. Silakan coba lagi.";
                }
            }
        } else {
            $msg_error = "Data admin tidak ditemukan.";
        }
    }

    if ($msg_error) {
        $_SESSION['change_pass_error'] = $msg_error;
    }
    if ($msg_success) {
        $_SESSION['change_pass_success'] = $msg_success;
    }

    header("Location: change_pass");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Ganti Kata Sandi</title>
<style>
* { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body, html { height: 100%; margin:0; background:#003366; display:flex; justify-content:center; align-items:center; }
.card { background:#fff; padding:30px 40px; border-radius:10px; width:360px; text-align:center; }
h2 { margin-bottom:20px; color:#333; }
.form-control { width:100%; padding:12px 15px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; font-size:16px; transition:border-color 0.3s; }
.form-control:focus { border-color:#003366; outline:none; }
.btn { background:#003366; border:none; padding:12px; width:100%; color:white; font-weight:600; font-size:16px; border-radius:5px; cursor:pointer; transition: background 0.3s; }
.btn:hover { background:#001f4d; }
.error { color:#d93025; margin-bottom:15px; font-weight:600; font-size:14px; text-align:center; }
.success { color:#0f9d58; margin-bottom:15px; font-weight:600; font-size:14px; text-align:center; }
</style>
</head>
<body>
<div class="card">
  <h2>Ganti Kata Sandi</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="success" id="success-msg"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" action="change_pass.php" novalidate>
    <input type="password" name="current_password" class="form-control" placeholder="Kata Sandi Saat Ini" required>
    <input type="password" name="new_password" class="form-control" placeholder="Kata Sandi Baru" required>
    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Kata Sandi Baru" required>

    <button type="submit" class="btn">Simpan</button>
  </form>
</div>

<?php if (!empty($success)): ?>
<script>
setTimeout(() => {
    window.location.href = 'profil_admin.php';
}, 1000);
</script>
<?php endif; ?>
</body>
</html>
