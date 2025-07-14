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
    // Menambahkan try-catch untuk penanganan error yang lebih baik
    try {
        $stmt = $pdo->prepare("DELETE FROM supervisors WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $_SESSION['flash_message'] = "Data pengawas berhasil dihapus.";
    } catch (PDOException $e) {
        // Menangkap error jika mekanik tidak bisa dihapus (misal karena terkait data lain)
        $_SESSION['flash_message'] = "Gagal menghapus data. Kemungkinan pengawas ini terkait dengan data transaksi.";
    }
    header("Location: ../manage_pengawas.php");
    exit(); // Tambahkan exit()
}
?>