<?php
// File: admin_dashboard.php (Versi Final Gabungan Analytics & Manajemen Tools)

require_once 'includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// =======================================================
// BAGIAN 1: PENGAMBILAN DATA UNTUK ANALYTICS
// =======================================================
$total_tools = $pdo->query("SELECT COUNT(*) FROM tools")->fetchColumn();
$borrowed_tools = $pdo->query("SELECT COUNT(*) FROM tools WHERE status = 'dipinjam'")->fetchColumn();
$available_tools = $total_tools - $borrowed_tools;

$last_transactions = $pdo->query("
    SELECT tl.tool_name, tr.borrow_date, 
    (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') FROM transaction_mechanics tm JOIN mechanics m ON tm.mechanic_id = m.id WHERE tm.transaction_id = tr.id) AS borrowers
    FROM transactions tr JOIN tools tl ON tr.tool_id = tl.id ORDER BY tr.borrow_date DESC LIMIT 5
")->fetchAll();

$chart_data = $pdo->query("
    SELECT DATE_FORMAT(borrow_date, '%b %Y') AS month, COUNT(*) AS count 
    FROM transactions 
    WHERE borrow_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month ORDER BY borrow_date ASC
")->fetchAll();

$chart_labels = json_encode(array_column($chart_data, 'month'));
$chart_values = json_encode(array_column($chart_data, 'count'));

$pending_reports = $pdo->query("
    SELECT dr.report_code, t.tool_name 
    FROM damage_reports dr 
    JOIN tools t ON dr.tool_id = t.id 
    WHERE dr.status = 'Dilaporkan' 
    ORDER BY dr.report_date ASC
")->fetchAll();


// =======================================================
// BAGIAN 2: PENGAMBILAN DATA UNTUK DAFTAR TOOLS
// =======================================================
$categories = ['Map/Bantex', 'Tool PPM', 'Tool PPU', 'Tool Sampling PAP', 'Tool Elektrik', 'Tool Adjust Valve', 'Torque Wrench', 'Dial Gauge'];
?>

<h3 class="mb-4">Dashboard Analytics</h3>

<?php if (count($pending_reports) > 0): ?>
<div class="alert alert-warning border-0 border-start border-5 border-warning d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-tools fs-2 me-3"></i>
    <div>
        <h5 class="alert-heading fw-bold">Ada Laporan Kerusakan Baru!</h5>
        Terdapat <strong><?= count($pending_reports) ?> laporan</strong> yang perlu ditinjau. 
        <?php foreach($pending_reports as $index => $report): ?>
            <?= ($index > 0) ? ', ' : '' ?><?= htmlspecialchars($report['tool_name']) ?>
        <?php endforeach; ?>
        . Silakan periksa di <a href="manage_tools.php" class="alert-link">Manajemen Tools</a>.
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4"><div class="stat-card"><h3><?= $total_tools ?></h3><p>Total Tools</p></div></div>
    <div class="col-lg-4 mb-4"><div class="stat-card"><h3><?= $available_tools ?></h3><p>Tools Tersedia</p></div></div>
    <div class="col-lg-4 mb-4"><div class="stat-card"><h3><?= $borrowed_tools ?></h3><p>Tools Dipinjam</p></div></div>
</div>

<div class="row mt-2">
    <div class="col-lg-7 mb-4"><div class="card"><div class="card-body"><h5 class="card-title">Aktivitas Peminjaman</h5><div id="borrowingsChart"></div></div></div></div>
    <div class="col-lg-5 mb-4"><div class="card"><div class="card-body"><h5 class="card-title">Peminjaman Terakhir</h5><div class="table-responsive"><table class="table table-borderless table-sm"><tbody>
        <?php foreach ($last_transactions as $trans): ?>
        <tr><td><strong><?= htmlspecialchars($trans['tool_name']) ?></strong></td><td><span class="text-muted">Oleh:</span> <?= htmlspecialchars($trans['borrowers'] ?? 'N/A') ?></td><td class="text-end text-muted"><?= date('d M Y', strtotime($trans['borrow_date'])) ?></td></tr>
        <?php endforeach; ?>
    </tbody></table></div></div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('borrowingsChart')) {
        var options = {
            chart: {
                type: 'area', // Tipe grafik
                height: 350,
                toolbar: { show: false }, // Sembunyikan toolbar default
                foreColor: '#adb5bd', // Warna teks untuk sumbu (sesuai dark mode)
                zoom: { enabled: false }
            },
            series: [{
                name: 'Jumlah Peminjaman',
                data: <?= $chart_values ?> // Mengambil data jumlah dari PHP
            }],
            xaxis: {
                categories: <?= $chart_labels ?> // Mengambil data label bulan dari PHP
            },
            dataLabels: { 
                enabled: false // Sembunyikan label angka di setiap titik
            },
            stroke: { 
                curve: 'smooth', // Membuat garis menjadi melengkung
                width: 2 
            },
            grid: {
                borderColor: '#495057', // Warna garis grid
                strokeDashArray: 5      // Membuat garis grid menjadi putus-putus
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                }
            },
            tooltip: { 
                theme: 'dark' // Menggunakan tooltip tema gelap
            }
        };
        
        var chart = new ApexCharts(document.querySelector("#borrowingsChart"), options);
        chart.render();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>