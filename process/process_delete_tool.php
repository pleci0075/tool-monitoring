<?php
// File: process/process_delete_tool.php
session_start()

require_once '../config/database.php';

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
            session_start();
            $_SESSION['flash_message'] = "Tool berhasil dikembalikan!";
            header("Location: ../history.php");
        } else {
            // Jika rowCount() = 0, berarti tool tidak ditemukan atau sedang dipinjam
            header("Location: ../index.php?status=delete_failed");
        }
        exit();
    } catch (PDOException $e) {
        die("Error: Gagal menghapus tool. " . $e->getMessage());
    }
}
?>