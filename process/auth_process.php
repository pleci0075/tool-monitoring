<?php
// File: process/auth_process.php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nrp = $_POST['nrp'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nrp) || empty($password)) {
        $_SESSION['flash_message'] = "NRP dan Password tidak boleh kosong.";
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE nrp = ?");
    $stmt->execute([$nrp]);
    $user = $stmt->fetch();

    // Verifikasi jika user ditemukan DAN password cocok dengan hash di database
    if ($user && password_verify($password, $user['password'])) {
        // Jika sukses, simpan data penting ke session
        session_regenerate_id(); // Mencegah session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Arahkan ke dashboard admin
        if ($user['role'] == 'admin') {
            header("Location: ../admin_dashboard.php");
        } else {
            header("Location: ../mechanic_dashboard.php");
        }
        exit();
    } else {
        // Jika gagal, kirim pesan error kembali ke halaman login
        $_SESSION['flash_message'] = "NRP atau Password salah.";
        header("Location: ../login.php");
        exit();
    }
}
?>