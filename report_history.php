<?php
// File: report_history.php
require_once 'includes/header_public.php'; 
require_once 'config/database.php';

// Query untuk mengambil semua riwayat laporan kerusakan
$stmt = $pdo->query("
    SELECT 
        dr.report_code,
        dr.report_date,
        dr.description,
        dr.photo_path,
        dr.status,
        t.tool_code,
        m.name as mechanic_name,
        m.nrp as mechanic_nrp
    FROM damage_reports dr
    JOIN tools t ON dr.tool_id = t.id
    JOIN mechanics m ON dr.mechanic_id = m.id
    ORDER BY dr.report_date DESC
");
$reports = $stmt->fetchAll();
?>

<h3 class="mb-4">Riwayat Laporan Kerusakan</h3>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="reportsHistoryTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Laporan</th>
                        <th>Tanggal</th>
                        <th>Tool</th>
                        <th>Pelapor</th>
                        <th>Deskripsi</th>
                        <th>Bukti</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($report['report_code']) ?></strong></td>
                        <td><?= date('d M Y, H:i', strtotime($report['report_date'])) ?></td>
                        <td><?= htmlspecialchars($report['tool_code']) ?></td>
                        <td><?= htmlspecialchars($report['mechanic_name']) ?></td>
                        <td><?= htmlspecialchars($report['description']) ?></td>
                        <td>
                            <a href="uploads/damage_reports/<?= htmlspecialchars($report['photo_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                        <td>
                            <?php
                                $status_class = '';
                                switch ($report['status']) {
                                    case 'Dilaporkan': $status_class = 'bg-primary'; break;
                                    case 'Diproses': $status_class = 'bg-warning text-dark'; break;
                                    case 'Ditolak': $status_class = 'bg-danger'; break;
                                    case 'Selesai': $status_class = 'bg-success'; break;
                                }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= htmlspecialchars($report['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div></div></div>

<script>
// Skrip ini akan berjalan setelah halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Cari link brand di navbar dan ganti tujuannya
    const brandLink = document.querySelector('.navbar-brand');
    if (brandLink) {
        brandLink.setAttribute('href', 'report_launchpad.php');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>