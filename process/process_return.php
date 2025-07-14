<?php
// File: process/process_return.php (Versi Final)
require_once '../config/database.php';
session_start();

// ... (Validasi CSRF Token tetap di sini) ...
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form, termasuk data baru
    $transaction_id = $_POST['transaction_id'];
    $supervisor_return = htmlspecialchars(strip_tags($_POST['supervisor_return']));
    $returner_mechanic_id = $_POST['returner_mechanic_id']; // Data baru
    $return_date = date('Y-m-d H:i:s');

    // ... (Proses upload file tetap sama) ...
    $proof_filename = '';
    if (isset($_FILES['proof_return']) && $_FILES['proof_return']['error'] == 0) {
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
    }

    // 3. Transaksi Database dengan kolom baru
    try {
        $pdo->beginTransaction();
        $stmt_get_tool = $pdo->prepare("SELECT tool_id FROM transactions WHERE id = ?");
        $stmt_get_tool->execute([$transaction_id]);
        $tool_id = $stmt_get_tool->fetchColumn();

        // Query UPDATE baru
        $sql1 = "UPDATE transactions 
                 SET return_date = ?, supervisor_return = ?, proof_return = ?, returner_mechanic_id = ? 
                 WHERE id = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$return_date, $supervisor_return, $proof_filename, $returner_mechanic_id, $transaction_id]);

        $sql2 = "UPDATE tools SET status = 'tersedia' WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$tool_id]);

        $pdo->commit();
        $_SESSION['flash_message'] = "Tool berhasil dikembalikan!";
        header("Location: ../tool_list.php"); // Arahkan kembali ke portal publik
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: Transaksi pengembalian gagal. " . $e->getMessage());
    }
}
?>