<?php
// File: process/process_borrow.php

session_start()

// Memanggil file koneksi database
require_once '../config/database.php';

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Error: Token CSRF tidak valid.');
}

// Cek apakah data dikirim dari form (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil data dari form dan sanitasi dasar
    $tool_id = $_POST['tool_id'];
    $mechanics_ids = $_POST['mechanics']; // Ini adalah array
    $supervisor_borrow = htmlspecialchars(strip_tags($_POST['supervisor_borrow']));
    $borrow_date = date('Y-m-d H:i:s'); // Tanggal dan waktu saat ini

    // 2. Proses Upload File Bukti Peminjaman
$proof_filename = '';
if (isset($_FILES['proof_borrow']) && $_FILES['proof_borrow']['error'] == 0) {
    $upload_dir = '../uploads/';
    $file_tmp_path = $_FILES['proof_borrow']['tmp_name'];
    
    // VALIDASI TIPE MIME (Lebih Aman)
    $allowed_mime_types = ['image/jpeg', 'image/png'];
    $file_mime_type = mime_content_type($file_tmp_path);
    if (!in_array($file_mime_type, $allowed_mime_types)) {
        die("Error: Tipe file tidak valid. Hanya JPG dan PNG yang diizinkan.");
    }

    $proof_filename = time() . '_' . basename($_FILES['proof_borrow']['name']);
    $target_file = $upload_dir . $proof_filename;

    if (!move_uploaded_file($file_tmp_path, $target_file)) {
        die("Error: Gagal mengupload file bukti.");
    }
} else {
    die("Error: File bukti peminjaman wajib diupload.");
}

    // 3. Transaksi Database (PENTING!)
    // Menggunakan transaction untuk memastikan semua query berhasil atau tidak sama sekali.
    try {
        $pdo->beginTransaction();

        // Query 1: Masukkan data ke tabel 'transactions'
        $sql1 = "INSERT INTO transactions (tool_id, borrow_date, proof_borrow, supervisor_borrow) VALUES (?, ?, ?, ?)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$tool_id, $borrow_date, $proof_filename, $supervisor_borrow]);
        
        // Ambil ID dari transaksi yang baru saja dimasukkan
        $transaction_id = $pdo->lastInsertId();

        // Query 2: Masukkan data ke tabel 'transaction_mechanics' (bisa lebih dari satu)
        $sql2 = "INSERT INTO transaction_mechanics (transaction_id, mechanic_id) VALUES (?, ?)";
        $stmt2 = $pdo->prepare($sql2);
        // Loop sebanyak mekanik yang dipilih
        foreach ($mechanics_ids as $mechanic_id) {
            $stmt2->execute([$transaction_id, $mechanic_id]);
        }

        // Query 3: Update status tool menjadi 'dipinjam'
        $sql3 = "UPDATE tools SET status = 'dipinjam' WHERE id = ?";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute([$tool_id]);

        // Jika semua query berhasil, commit transaksinya
        $pdo->commit();
        
        // Redirect ke halaman riwayat dengan pesan sukses
        session_start();
        $_SESSION['flash_message'] = "Tool berhasil dikembalikan!";
        header("Location: ../history.php");
        exit();

    } catch (Exception $e) {
        // Jika ada satu saja query yang gagal, batalkan semua perubahan
        $pdo->rollBack();
        // Hentikan script dan tampilkan error
        die("Error: Transaksi database gagal. " . $e->getMessage());
    }
} else {
    // Jika file diakses langsung tanpa lewat form, redirect ke halaman utama
    header("Location: ../index.php");
    exit();
}
?>