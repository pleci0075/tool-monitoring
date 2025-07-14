<?php
// File: tool_list.php (Versi Final & Lengkap)
require_once 'includes/header_public.php'; 
require_once 'config/database.php';

$categories = ['Map/Bantex', 'Tool PPM', 'Tool PPU', 'Tool Sampling PAP', 'Tool Elektrik', 'Tool Adjust Valve', 'Torque Wrench', 'Dial Gauge'];
?>

<h3 class="mb-4">Daftar Tools</h3>

<div class="mb-4">
    <input type="text" id="toolSearchInput" class="form-control" placeholder="Cari nama atau kode tool...">
</div>

<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i> '.htmlspecialchars($_SESSION['flash_message']).'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="table-responsive">
    <table class="table table-hover main-tool-table">
        <thead>
            <tr>
                <th>Nama Tool</th>
                <th>Kode Tool</th>
                <th>Status</th>
                <th>Aksi</th> </tr>
        </thead>
        <tbody>
            <?php
            foreach ($categories as $category):
                $stmt = $pdo->prepare("SELECT t.id, t.tool_name, t.tool_code, t.status, trans.id AS active_transaction_id, (SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('id', m.id, 'name', m.name)), ']') FROM transaction_mechanics tm JOIN mechanics m ON tm.mechanic_id = m.id WHERE tm.transaction_id = trans.id) AS borrowers FROM tools t LEFT JOIN ( SELECT id, tool_id FROM transactions WHERE return_date IS NULL) AS trans ON t.id = trans.tool_id WHERE t.category = ? ORDER BY t.tool_name ASC");
                $stmt->execute([$category]);
                $tools = $stmt->fetchAll();

                if ($stmt->rowCount() > 0):
            ?>
                    <tr class="category-header-row">
                        <td colspan="4" class="category-header"><?= htmlspecialchars($category) ?></td>
                    </tr>
                
                    <?php foreach ($tools as $tool): ?>
                    <tr>
                        <td><?= htmlspecialchars($tool['tool_name']) ?></td>
                        <td><?= htmlspecialchars($tool['tool_code']) ?></td>
                        <td>
                            <span class="badge <?= $tool['status'] == 'tersedia' ? 'bg-success' : ($tool['status'] == 'dipinjam' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= htmlspecialchars(ucfirst($tool['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($tool['status'] == 'tersedia'): ?>
                                <button type="button" class="btn btn-primary btn-sm btn-pinjam" data-bs-toggle="modal" data-bs-target="#pinjamModal" data-bs-tool-id="<?= $tool['id'] ?>" data-bs-tool-name="<?= htmlspecialchars($tool['tool_name']) ?>">Pinjam</button>
                            <?php elseif ($tool['status'] == 'dipinjam'): ?>
                                <button type="button" class="btn btn-success btn-sm btn-kembalikan" data-bs-toggle="modal" data-bs-target="#kembalikanModal" data-bs-transaction-id="<?= $tool['active_transaction_id'] ?>" data-borrowers='<?= htmlspecialchars($tool['borrowers'], ENT_QUOTES, 'UTF-8') ?>'>Kembalikan</button>
                            <?php else: // Status 'Rusak' (else yang benar) ?>
                                <a href="report_history.php" class="btn btn-danger btn-sm disabled">Rusak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
            <?php 
                endif;
            endforeach; 
            ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="pinjamModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Form Peminjaman Tool</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="process/process_borrow.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><div class="modal-body"><input type="hidden" name="tool_id" id="modal_tool_id"><p>Anda akan meminjam: <strong id="modal_tool_name"></strong></p><div class="mb-3"><label class="form-label">Nama Peminjam</label><select class="form-select" id="mechanics_public" name="mechanics[]" multiple="multiple" required style="width: 100%;"><?php $mechanic_stmt = $pdo->query("SELECT id, name FROM mechanics ORDER BY name"); while ($mechanic = $mechanic_stmt->fetch(PDO::FETCH_ASSOC)) { echo "<option value='{$mechanic['id']}'>{$mechanic['name']}</option>"; } ?></select></div><div class="mb-3"><label class="form-label">Pengawas Lapangan</label><select class="form-select" id="supervisor_borrow_public" name="supervisor_borrow" required style="width: 100%;"><option value="" disabled selected>-- Pilih Pengawas --</option><?php $supervisor_stmt = $pdo->query("SELECT name FROM supervisors ORDER BY name"); while ($supervisor = $supervisor_stmt->fetch(PDO::FETCH_ASSOC)) { echo "<option value='{$supervisor['name']}'>{$supervisor['name']}</option>"; } ?></select></div><div class="mb-3"><label class="form-label">Bukti Foto Pinjam</label><input class="form-control" type="file" name="proof_borrow" accept="image/jpeg, image/png" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Catat Peminjaman</button></div></form></div></div></div>
<div class="modal fade" id="kembalikanModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Form Pengembalian Tool</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="process/process_return.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><div class="modal-body"><input type="hidden" name="transaction_id" id="modal_transaction_id"><p>Konfirmasi pengembalian tool.</p><div class="mb-3"><label class="form-label">Nama Pengembali</label><select class="form-select" id="returner_mechanic_id" name="returner_mechanic_id" required style="width: 100%;"></select></div><div class="mb-3"><label class="form-label">Pengawas Lapangan</label><select class="form-select" id="supervisor_return_public" name="supervisor_return" required style="width: 100%;"><option value="" disabled selected>-- Pilih Pengawas --</option><?php $supervisor_stmt_return = $pdo->query("SELECT name FROM supervisors ORDER BY name"); while ($supervisor = $supervisor_stmt_return->fetch(PDO::FETCH_ASSOC)) { echo "<option value='{$supervisor['name']}'>{$supervisor['name']}</option>"; } ?></select></div><div class="mb-3"><label class="form-label">Bukti Foto Kembali</label><input class="form-control" type="file" name="proof_return" accept="image/jpeg, image/png" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>