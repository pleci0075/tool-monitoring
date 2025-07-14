<?php
// File: detail_history.php

// Panggil header yang sesuai (admin atau publik)
// Untuk kesederhanaan, kita gunakan header admin karena ini adalah fitur detail
require_once 'includes/header.php'; 

// Skrip Penjaga - halaman ini hanya untuk admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$page_title = "Detail Riwayat";
$items = [];
$base_query = "
    SELECT 
        tr.borrow_date, tr.return_date,
        tl.tool_name, tl.tool_code,
        (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') FROM transaction_mechanics tm JOIN mechanics m ON tm.mechanic_id = m.id WHERE tm.transaction_id = tr.id) AS borrowers,
        (SELECT m.name FROM mechanics m WHERE m.id = tr.returner_mechanic_id) AS returner_name
    FROM transactions tr
    JOIN tools tl ON tr.tool_id = tl.id
";

// Cek apakah filter berdasarkan ID tool
if (isset($_GET['tool_id']) && is_numeric($_GET['tool_id'])) {
    $tool_id = $_GET['tool_id'];
    $stmt = $pdo->prepare("SELECT tool_name FROM tools WHERE id = ?");
    $stmt->execute([$tool_id]);
    $page_title = "Riwayat untuk Tool: " . htmlspecialchars($stmt->fetchColumn());
    
    $stmt = $pdo->prepare($base_query . " WHERE tr.tool_id = ? ORDER BY tr.borrow_date DESC");
    $stmt->execute([$tool_id]);
    $items = $stmt->fetchAll();
} 
// Cek apakah filter berdasarkan ID mekanik
elseif (isset($_GET['mechanic_id']) && is_numeric($_GET['mechanic_id'])) {
    $mechanic_id = $_GET['mechanic_id'];
    $stmt = $pdo->prepare("SELECT name FROM mechanics WHERE id = ?");
    $stmt->execute([$mechanic_id]);
    $page_title = "Riwayat Peminjaman oleh: " . htmlspecialchars($stmt->fetchColumn());

    $stmt = $pdo->prepare($base_query . " JOIN transaction_mechanics tm ON tr.id = tm.transaction_id WHERE tm.mechanic_id = ? ORDER BY tr.borrow_date DESC");
    $stmt->execute([$mechanic_id]);
    $items = $stmt->fetchAll();
} else {
    // Jika tidak ada parameter, kembali ke halaman riwayat utama
    header("Location: history.php");
    exit();
}
?>

<h3 class="mb-4"><?= $page_title ?></h3>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="detailHistoryTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Peminjam</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Dikembalikan Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['tool_name']) ?> (<?= htmlspecialchars($item['tool_code']) ?>)</td>
                        <td><?= htmlspecialchars($item['borrowers']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($item['borrow_date'])) ?></td>
                        <td><?= $item['return_date'] ? date('d M Y, H:i', strtotime($item['return_date'])) : 'Masih Dipinjam' ?></td>
                        <td><?= htmlspecialchars($item['returner_name'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>