<?php
session_start();
require_once '../config/database.php';
// Validasi CSRF dan Role Admin di sini

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id'];
    $new_status = '';
    $tool_status_update = false;

    switch ($action) {
        case 'approve': $new_status = 'Diproses'; break;
        case 'reject': $new_status = 'Ditolak'; $tool_status_update = 'tersedia'; break;
        case 'finish': $new_status = 'Selesai'; $tool_status_update = 'tersedia'; break;
        default: die('Aksi tidak valid.');
    }

    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare("UPDATE damage_reports SET status = ?, processed_by_user_id = ?, processed_date = NOW() WHERE id = ?");
        $stmt1->execute([$new_status, $admin_id, $report_id]);

        if ($tool_status_update) {
            $stmt_get_tool = $pdo->prepare("SELECT tool_id FROM damage_reports WHERE id = ?");
            $stmt_get_tool->execute([$report_id]);
            $tool_id = $stmt_get_tool->fetchColumn();
            
            $stmt2 = $pdo->prepare("UPDATE tools SET status = ? WHERE id = ?");
            $stmt2->execute([$tool_status_update, $tool_id]);
        }
        $pdo->commit();
        $_SESSION['flash_message'] = "Status laporan berhasil diperbarui menjadi '$new_status'.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = "Error: Gagal memperbarui status laporan.";
    }
    header("Location: ../manage_tools.php");
    exit();
}
?>