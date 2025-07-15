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
$damaged_tools = $pdo->query("SELECT COUNT(*) FROM tools WHERE status = 'Rusak'")->fetchColumn();

// Data untuk Grafik Peminjaman Harian (7 Hari Terakhir)
$chart_data = $pdo->query("
    SELECT DATE_FORMAT(borrow_date, '%d %b %Y') AS day, COUNT(*) AS count 
    FROM transactions 
    WHERE borrow_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(borrow_date)
    ORDER BY DATE(borrow_date) ASC
")->fetchAll();

$chart_labels = json_encode(array_column($chart_data, 'day'));
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
    <div class="col-lg-3 col-md-6 mb-4"><div class="stat-card"><h3><?= $total_tools ?></h3><p>Total Tools</p></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="stat-card"><h3><?= $available_tools ?></h3><p>Tools Tersedia</p></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="stat-card"><h3><?= $borrowed_tools ?></h3><p>Tools Dipinjam</p></div></div>
    <div class="col-lg-3 col-md-6 mb-4"><div class="stat-card border-danger"><h3 class="text-danger" id="stat-damaged-tools"><?= $damaged_tools ?></h3><p>Tools Rusak</p></div></div>
</div>

<div class="row mt-2">
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Aktivitas Peminjaman</h5>
                <div id="borrowingsChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Aktivitas Terkini</h5>
                <ul class="list-group list-group-flush" id="live-activity-feed">
                    <li class="list-group-item">Memuat data...</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('borrowingsChart')) {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [{
                name: 'Jumlah Peminjaman',
                data: <?= $chart_values ?>
            }],
            xaxis: {
                type: 'category', // Tipe sumbu X adalah kategori/teks
                categories: <?= $chart_labels ?> // Label dari query PHP
            },
            yaxis: {
                title: {
                    text: 'Jumlah Peminjaman'
                },
                min: 0, // Mulai dari 0
                forceNiceScale: true // Membuat skala angka menjadi lebih 'bulat'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#212529'], // Tema monokrom hitam
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0.1,
                }
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy' // Format tooltip saat di-hover
                },
            }
        };
        var chart = new ApexCharts(document.querySelector("#borrowingsChart"), options);
        chart.render();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>