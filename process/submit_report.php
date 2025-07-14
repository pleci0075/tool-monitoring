<?php
// File: process/submit_report.php (Versi Final)
session_start();
require_once '../config/database.php';

// Validasi Keamanan (CSRF & Role) bisa ditambahkan di sini jika diperlukan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $mechanic_id = $_POST['mechanic_id'];
    $tool_id = $_POST['tool_id']; // <-- PERBAIKAN: Langsung ambil tool_id
    $description = $_POST['description'];

    // Cek status tool langsung menggunakan tool_id
    $stmt = $pdo->prepare("SELECT status FROM tools WHERE id = ?");
    $stmt->execute([$tool_id]);
    $tool_status = $stmt->fetchColumn();

    if ($tool_status === false) {
        $_SESSION['flash_message'] = "Error: Tool tidak ditemukan.";
        header("Location: ../report_launchpad.php"); exit();
    }
    if ($tool_status == 'Rusak') {
        $_SESSION['flash_message'] = "Error: Tool tersebut sudah dalam status rusak.";
        header("Location: ../report_launchpad.php"); exit();
    }

    // Proses upload foto (lengkapi dengan validasi MIME-type)
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = '../uploads/damage_reports/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        $file_tmp_path = $_FILES['photo']['tmp_name'];
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $file_mime_type = mime_content_type($file_tmp_path);
        if (!in_array($file_mime_type, $allowed_mime_types)) { die("Error: Tipe file bukti tidak valid."); }
        $photo_path = time() . '_' . basename($_FILES['photo']['name']);
        if (!move_uploaded_file($file_tmp_path, $upload_dir . $photo_path)) { die("Error: Gagal mengupload foto."); }
    } else { die("Error: Foto bukti wajib diupload."); }

    // Generate kode laporan unik
    $report_code = 'REP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    try {
        $pdo->beginTransaction();
        // 1. Simpan laporan
        $sql1 = "INSERT INTO damage_reports (report_code, tool_id, mechanic_id, description, photo_path, report_date) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$report_code, $tool_id, $mechanic_id, $description, $photo_path]);

        // 2. Update status tool
        $sql2 = "UPDATE tools SET status = 'Rusak' WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$tool_id]);

        $pdo->commit();
        $_SESSION['flash_message'] = "Laporan kerusakan #$report_code berhasil dikirim.";
        header("Location: ../report_launchpad.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: Gagal mengirim laporan. " . $e->getMessage());
    }
}
?>