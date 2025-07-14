<?php

date_default_timezone_set('Asia/Singapore');
// File: config/database.php

// ##################################################################
// # PENTING: Untuk Lingkungan Produksi (saat aplikasi sudah online)
// # Hapus tanda komentar (//) pada dua baris di bawah ini untuk
// # menyembunyikan pesan error detail dari pengguna.
// ##################################################################
//
// ini_set('display_errors', 0);
// error_reporting(0);

$host = 'localhost';
$db_name = 'tool_monitoring';
$username = 'root';
$password = ''; // Default password XAMPP adalah kosong.

// Gunakan try-catch block untuk menangani error koneksi
try {
    // Membuat object PDO baru untuk koneksi
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    
    // Set error mode ke exception. Ini cara modern untuk menangani error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan script dan tampilkan pesan error.
    // Jangan pernah tampilkan error detail di production. Ini hanya untuk development.
    die("ERROR: Tidak dapat terhubung ke database. " . $e->getMessage());
}
?>