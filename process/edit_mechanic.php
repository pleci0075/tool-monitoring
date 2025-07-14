<?php
session_start();
require_once '../config/database.php';

// Validasi Keamanan: CSRF dan Role Admin
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("UPDATE mechanics SET nrp = ?, name = ? WHERE id = ?");
    $stmt->execute([$_POST['nrp'], $_POST['name'], $_POST['id']]);
    $_SESSION['flash_message'] = "Data mekanik berhasil diperbarui.";
    header("Location: ../manage_mechanics.php");
    exit(); // Tambahkan exit()
}
?>

