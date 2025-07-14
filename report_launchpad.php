<?php
// File: report_launchpad.php (Final)
require_once 'includes/header_public.php';
require_once 'config/database.php';
?>

<div class="launchpad-container">
    <div class="container">
        <h3 class="mb-5 text-center">Pelaporan Kerusakan Tool</h3>

        <?php
        if (isset($_SESSION['flash_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" style="max-width: 800px; margin: 0 auto 2rem auto;">'.htmlspecialchars($_SESSION['flash_message']).'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            unset($_SESSION['flash_message']);
        }
        ?>

        <div class="row justify-content-center">
            <div class="col-lg-5 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">Lapor Kerusakan Baru</h4>
                        <p class="text-muted">Ditemukan masalah atau kerusakan pada tool? Laporkan di sini agar dapat segera ditindaklanjuti.</p>
                        <button type="button" class="btn btn-danger mt-auto" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="bi bi-exclamation-triangle-fill"></i> Buat Laporan
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">Riwayat Laporan</h4>
                        <p class="text-muted">Lihat status dan riwayat semua laporan kerusakan yang pernah dibuat.</p>
                        <a href="report_history.php" class="btn btn-outline-secondary mt-auto">
                            <i class="bi bi-clock-history"></i> Lihat Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Form Laporan Kerusakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/submit_report.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-3">
                        <label for="report_mechanic_id" class="form-label">Nama Pelapor</label>
                        <select class="form-select" id="report_mechanic_id" name="mechanic_id" required>
                            <option value="" disabled selected>-- Pilih Nama Anda --</option>
                            <?php 
                                $mechanic_stmt = $pdo->query("SELECT id, nrp, name FROM mechanics ORDER BY name");
                                while ($m = $mechanic_stmt->fetch()) { echo "<option value='{$m['id']}'>{$m['nrp']} - {$m['name']}</option>"; } 
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_tool_id" class="form-label">Nama Tool yang Rusak</label>
                        <select class="form-select" id="report_tool_id" name="tool_id" required style="width: 100%;">
                            <option value="" disabled selected>-- Cari & Pilih Nama Tool --</option>
                            <?php 
                                $tools_stmt = $pdo->query("SELECT id, tool_name, tool_code FROM tools WHERE status != 'Rusak' ORDER BY tool_name");
                                while ($tool = $tools_stmt->fetch()) { echo "<option value='{$tool['id']}'>{$tool['tool_name']} ({$tool['tool_code']})</option>"; } 
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Kerusakan</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Bukti Kerusakan</label>
                        <input class="form-control" type="file" name="photo" accept="image/jpeg, image/png" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>