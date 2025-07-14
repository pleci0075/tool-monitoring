<?php
// File: manage_tools.php (Versi Final dengan Semua Modal Lengkap)

require_once 'includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Query untuk mengambil semua data yang diperlukan
$stmt = $pdo->query("
    SELECT 
        t.*, 
        dr.id as report_id, dr.report_code, dr.description, dr.photo_path, dr.report_date, 
        m.name as mechanic_name, m.nrp as mechanic_nrp
    FROM tools t
    LEFT JOIN damage_reports dr ON t.id = dr.tool_id AND dr.status IN ('Dilaporkan', 'Diproses')
    LEFT JOIN mechanics m ON dr.mechanic_id = m.id
    ORDER BY t.category, t.tool_name ASC
");
$tools = $stmt->fetchAll();
$categories = ['Map/Bantex', 'Tool PPM', 'Tool PPU', 'Tool Sampling PAP', 'Tool Elektrik', 'Tool Adjust Valve', 'Torque Wrench', 'Dial Gauge'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Manajemen Tools</h3>
    <button type="button" class="btn btn-primary" id="btn-add-tool">
        <i class="bi bi-plus-circle"></i> Tambah Tool Baru
    </button>
</div>

<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.htmlspecialchars($_SESSION['flash_message']).'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="toolsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Tool</th>
                        <th>Kode Tool</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th style="min-width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tools as $tool): ?>
                    <tr>
                        <td><a href="detail_history.php?tool_id=<?= $tool['id'] ?>"><?= htmlspecialchars($tool['tool_name']) ?></a></td>
                        <td><?= htmlspecialchars($tool['tool_code']) ?></td>
                        <td><?= htmlspecialchars($tool['category']) ?></td>
                        <td><span class="badge <?= $tool['status'] == 'tersedia' ? 'bg-success' : ($tool['status'] == 'dipinjam' ? 'bg-warning text-dark' : 'bg-danger') ?>"><?= htmlspecialchars(ucfirst($tool['status'])) ?></span></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm btn-edit-tool" data-id="<?= $tool['id'] ?>" data-name="<?= htmlspecialchars($tool['tool_name']) ?>" data-code="<?= htmlspecialchars($tool['tool_code']) ?>" data-category="<?= htmlspecialchars($tool['category']) ?>"><i class="bi bi-pencil-square"></i></button>
                            <?php if (!empty($tool['report_id'])): ?>
                            <button type="button" class="btn btn-info btn-sm btn-review-report" data-report-id="<?= $tool['report_id'] ?>" data-report-code="<?= htmlspecialchars($tool['report_code']) ?>" data-tool-code="<?= htmlspecialchars($tool['tool_code']) ?>" data-report-date="<?= date('d M Y, H:i', strtotime($tool['report_date'])) ?>" data-reporter-name="<?= htmlspecialchars($tool['mechanic_name'] . ' (' . $tool['mechanic_nrp'] . ')') ?>" data-description="<?= htmlspecialchars($tool['description']) ?>" data-photo-path="uploads/damage_reports/<?= htmlspecialchars($tool['photo_path']) ?>"><i class="bi bi-wrench"></i></button>
                            <?php endif; ?>
                            <form action="process/delete_tool.php" method="POST" class="delete-form d-inline"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="tool_id" value="<?= $tool['id'] ?>"><button type="submit" class="btn btn-danger btn-sm" title="Hapus Tool"><i class="bi bi-trash3-fill"></i></button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addToolModal" tabindex="-1" aria-labelledby="addToolModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="addToolModalLabel">Tambah Tool Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="process/add_tool.php" method="POST"><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><div class="mb-3"><label class="form-label">Nama Tool</label><input type="text" class="form-control" name="tool_name" required></div><div class="mb-3"><label class="form-label">Kode Tool</label><input type="text" class="form-control" name="tool_code" required></div><div class="mb-3"><label class="form-label">Kategori</label><select class="form-select select-category" name="category" required style="width:100%"><option value="" disabled selected>-- Pilih Kategori --</option><?php foreach ($categories as $cat): ?><option value="<?= $cat ?>"><?= $cat ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div>
</div>

<div class="modal fade" id="editToolModal" tabindex="-1" aria-labelledby="editToolModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="editToolModalLabel">Edit Tool</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="process/edit_tool.php" method="POST"><div class="modal-body"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="tool_id" id="edit_tool_id"><div class="mb-3"><label class="form-label">Nama Tool</label><input type="text" class="form-control" id="edit_tool_name" name="tool_name" required></div><div class="mb-3"><label class="form-label">Kode Tool</label><input type="text" class="form-control" id="edit_tool_code" name="tool_code" required></div><div class="mb-3"><label class="form-label">Kategori</label><select class="form-select select-category" id="edit_category" name="category" required style="width:100%"><?php foreach ($categories as $cat): ?><option value="<?= $cat ?>"><?= $cat ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div></form></div></div>
</div>

<div class="modal fade" id="reviewReportModal" tabindex="-1" aria-labelledby="reviewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="reviewReportModalLabel">Review Laporan Kerusakan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><h6 class="text-muted">Kode Laporan: <span id="modal_report_code" class="fw-normal text-body"></span></h6><p class="mb-1"><strong>Tool:</strong> <span id="modal_tool_code" class="fw-normal"></span></p><p class="mb-1"><strong>Tanggal Laporan:</strong> <span id="modal_report_date" class="fw-normal"></span></p><p class="mb-3"><strong>Pelapor:</strong> <span id="modal_reporter_name" class="fw-normal"></span></p><p class="mt-3 mb-1"><strong>Deskripsi Kerusakan:</strong></p><p id="modal_description" class="bg-body-secondary p-2 rounded"></p><p class="mt-3 mb-1"><strong>Bukti Foto:</strong></p><a id="modal_photo_link" href="#" target="_blank"><img id="modal_photo_img" src="" style="max-width: 100%; border-radius: 8px;"></a></div><div class="modal-footer justify-content-between"><form action="process/process_report_action.php" method="POST" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="report_id" class="modal_report_id_input"><button type="submit" name="action" value="reject" class="btn btn-danger"><i class="bi bi-x-circle-fill"></i> Tolak</button><button type="submit" name="action" value="approve" class="btn btn-warning"><i class="bi bi-check-circle-fill"></i> Proses</button><button type="submit" name="action" value="finish" class="btn btn-success"><i class="bi bi-bookmark-check-fill"></i> Selesai</button></form><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div></div></div>
</div>

<?php require_once 'includes/footer.php'; ?>