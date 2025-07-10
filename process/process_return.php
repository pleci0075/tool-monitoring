<?php
// File: process/process_return.php
session_start()
require_once '../config/database.php';

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari form
    $transaction_id = $_POST['transaction_id'];
    $supervisor_return = htmlspecialchars(strip_tags($_POST['supervisor_return']));
    $return_date = date('Y-m-d H:i:s');

    // 2. Proses Upload File Bukti Pengembalian
$proof_filename = '';
if (isset($_FILES['proof_return']) && $_FILES['proof_return']['error'] == 0) {
    $upload_dir = '../uploads/';
    $file_tmp_path = $_FILES['proof_return']['tmp_name'];

    // VALIDASI TIPE MIME (Lebih Aman)
    $allowed_mime_types = ['image/jpeg', 'image/png'];
    $file_mime_type = mime_content_type($file_tmp_path);
    if (!in_array($file_mime_type, $allowed_mime_types)) {
        die("Error: Tipe file tidak valid. Hanya JPG dan PNG yang diizinkan.");
    }
    
    $proof_filename = 'return_' . time() . '_' . basename($_FILES['proof_return']['name']);
    $target_file = $upload_dir . $proof_filename;

    if (!move_uploaded_file($file_tmp_path, $target_file)) {
        die("Error: Gagal mengupload file bukti kembali.");
    }
} else {
    die("Error: File bukti pengembalian wajib diupload.");
}

    // 3. Transaksi Database
    try {
        $pdo->beginTransaction();

        // Ambil dulu tool_id dari transaksi ini sebelum diupdate
        $stmt_get_tool = $pdo->prepare("SELECT tool_id FROM transactions WHERE id = ?");
        $stmt_get_tool->execute([$transaction_id]);
        $transaction = $stmt_get_tool->fetch();
        $tool_id = $transaction['tool_id'];

        // Query 1: Update tabel 'transactions' dengan data pengembalian
        $sql1 = "UPDATE transactions 
                 SET return_date = ?, supervisor_return = ?, proof_return = ? 
                 WHERE id = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$return_date, $supervisor_return, $proof_filename, $transaction_id]);

        // Query 2: Update status tool menjadi 'tersedia' kembali
        $sql2 = "UPDATE tools SET status = 'tersedia' WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$tool_id]);

        $pdo->commit();
        
        session_start();
        $_SESSION['flash_message'] = "Tool berhasil dikembalikan!";
        header("Location: ../history.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: Transaksi pengembalian gagal. " . $e->getMessage());
    }
}
?>