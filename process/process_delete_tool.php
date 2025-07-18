<?php
// File: process/process_delete_tool.php (Versi Final & Aman)

require_once '../config/database.php';

session_start();

// Validasi token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tool_id'])) {
    $tool_id = $_POST['tool_id'];

    // HANYA hapus tool jika statusnya 'tersedia' untuk mencegah menghapus tool yang sedang dipinjam
    try {
        $sql = "DELETE FROM tools WHERE id = ? AND status = 'tersedia'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tool_id]);

        if ($stmt->rowCount() > 0) {
            // Menggunakan notifikasi flash message
            $_SESSION['flash_message'] = "Tool berhasil dihapus dari daftar.";
            header("Location: ../index.php");
        } else {
            $_SESSION['flash_message'] = "Gagal menghapus: Tool tidak ditemukan atau sedang dipinjam.";
            header("Location: ../index.php");
        }
        exit();
        
    } catch (PDOException $e) {
        die("Error: Gagal menghapus tool. " . $e->getMessage());
    }
}
?>