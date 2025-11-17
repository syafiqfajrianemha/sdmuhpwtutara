<?php 
session_start();
include('../db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Ambil data admin
$stmt = $conn->prepare("SELECT id, nama, email FROM admin WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "Data admin tidak ditemukan.";
    exit;
}

$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>
<style>
* { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body, html {
    margin: 0; padding: 0; height: 100%;
    background-color: #003366;
    display: flex; justify-content: center; align-items: center;
}
.profile-card {
    background-color: #ffffff;
    width: 850px;
    max-width: 95%;
    border-radius: 15px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.3);
    overflow: hidden;
}
.header-section {
    display: flex;
    align-items: center;
    padding: 30px 40px;
    background-color: #e9ecef;
}
.profile-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #6c757d;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 30px;
}
.profile-icon svg {
    width: 50px;
    height: 50px;
    fill: #ffffff;
}
.header-text h2 {
    margin: 0 0 5px 0;
    font-size: 28px;
    color: #003366;
}
.header-text p {
    margin: 0;
    font-size: 18px;
    color: #555;
}
.detail-section {
    padding: 25px 40px;
    border-top: 1px solid #ccc; /* garis pemisah halus */
}
.detail-section p {
    font-size: 18px;
    margin-bottom: 15px;
    color: #333;
}
.button-group {
    text-align: center;
    padding: 20px 0 30px 0;
}
.btn {
    display: inline-block;
    padding: 14px 30px;
    margin: 0 10px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.3s;
}
.btn-primary { background-color: #003366; color: white; }
.btn-primary:hover { background-color: #001f4d; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover { background-color: #5a6268; }
</style>
</head>
<body>
<div class="profile-card">
    <!-- Header Section -->
    <div class="header-section">
        <!-- Icon Profil dengan siluet orang -->
        <div class="profile-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
            </svg>
        </div>
        <div class="header-text">
            <h2><?= htmlspecialchars($admin['nama']); ?></h2>
            <p><?= htmlspecialchars($admin['email']); ?></p>
        </div>
    </div>

    <!-- Detail Section -->
    <div class="detail-section">
        <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($admin['nama']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']); ?></p>
        <p><strong>Kata Sandi:</strong> ********</p>
    </div>

    <!-- Buttons -->
    <div class="button-group">
        <a href="change_pass" class="btn btn-primary">Ganti Kata Sandi</a>
        <a href="index" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
