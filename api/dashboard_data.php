<?php
// File: api/dashboard_data.php
// File ini HANYA akan mengembalikan data dalam format JSON

require_once '../config/database.php';

// Ambil data statistik
$total_tools = $pdo->query("SELECT COUNT(*) FROM tools")->fetchColumn();
$borrowed_tools = $pdo->query("SELECT COUNT(*) FROM tools WHERE status = 'dipinjam'")->fetchColumn();

// Ambil 5 aktivitas terakhir (pinjam, kembali, lapor rusak)
$latest_activities = $pdo->query("
    (SELECT tl.tool_name, 'dipinjam oleh' AS action, m.name AS person, tr.borrow_date AS event_date 
     FROM transactions tr 
     JOIN tools tl ON tr.tool_id = tl.id 
     JOIN transaction_mechanics tm ON tr.id = tm.transaction_id 
     JOIN mechanics m ON tm.mechanic_id = m.id 
     ORDER BY tr.borrow_date DESC LIMIT 5)
    UNION
    (SELECT tl.tool_name, 'dikembalikan oleh' AS action, m.name AS person, tr.return_date AS event_date 
     FROM transactions tr 
     JOIN tools tl ON tr.tool_id = tl.id 
     JOIN mechanics m ON tr.returner_mechanic_id = m.id 
     WHERE tr.return_date IS NOT NULL ORDER BY tr.return_date DESC LIMIT 5)
    UNION
    (SELECT tl.tool_name, 'dilaporkan rusak oleh' AS action, m.name AS person, dr.report_date AS event_date 
     FROM damage_reports dr 
     JOIN tools tl ON dr.tool_id = tl.id 
     JOIN mechanics m ON dr.mechanic_id = m.id 
     ORDER BY dr.report_date DESC LIMIT 5)
    ORDER BY event_date DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Siapkan data untuk dikirim
$data = [
    'total_tools' => $total_tools,
    'borrowed_tools' => $borrowed_tools,
    'available_tools' => $total_tools - $borrowed_tools,
    'latest_activities' => $latest_activities
];

// Set header sebagai JSON dan kirim datanya
header('Content-Type: application/json');
echo json_encode($data);
?>