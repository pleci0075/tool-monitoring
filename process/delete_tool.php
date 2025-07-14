<?php
// File: process/delete_tool.php (Versi Final)

session_start();
require_once '../config/database.php';

// Validasi CSRF dan Role Admin
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Path diperbaiki
    exit(); // Titik koma ditambahkan
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tool_id'])) {
    $tool_id = $_POST['tool_id'];

    try {
        $sql = "DELETE FROM tools WHERE id = ? AND status = 'tersedia'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tool_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_message'] = "Tool berhasil dihapus.";
        } else {
            $_SESSION['flash_message'] = "Gagal menghapus: Tool tidak ditemukan atau sedang dipinjam.";
        }
        header("Location: ../manage_tools.php");
        exit();
        
    } catch (PDOException $e) {
        die("Error: Gagal menghapus tool. " . $e->getMessage());
    }
}
?>