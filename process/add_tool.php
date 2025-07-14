<?php
// File: process/add_tool.php (Versi Final)

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO tools (tool_name, tool_code, category, status) VALUES (?, ?, ?, 'tersedia')";
    $stmt= $pdo->prepare($sql);
    $stmt->execute([$_POST['tool_name'], $_POST['tool_code'], $_POST['category']]);
    
    $_SESSION['flash_message'] = "Tool baru berhasil ditambahkan.";
    header("Location: ../manage_tools.php");
    exit();
}
?>