<?php 
// File: index.php (Versi Baru)
require_once 'includes/header.php'; 
require_once 'config/database.php';

// Ambil semua data tool untuk ditampilkan di tabel
$tools_stmt = $pdo->query("SELECT id, tool_name, tool_code, status FROM tools ORDER BY tool_name ASC");
?>

<?php
// Cek dan tampilkan flash message jika ada
if (isset($_SESSION['flash_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> '.htmlspecialchars($_SESSION['flash_message']).'
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    // Hapus pesan setelah ditampilkan
    unset($_SESSION['flash_message']);
}
?>

<?php if (isset($_GET['status'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php
        if ($_GET['status'] == 'borrow_success') echo 'Tool berhasil dipinjam!';
        if ($_GET['status'] == 'delete_success') echo 'Tool berhasil dihapus dari daftar.';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>


<h3>Daftar Tools</h3>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Tool</th>
                <th>Kode Tool</th>
                <th>Status</th>
                <th style="width: 20%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($tool = $tools_stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($tool['tool_name']) ?></td>
                <td><?= htmlspecialchars($tool['tool_code']) ?></td>
                <td>
                    <?php if ($tool['status'] == 'tersedia'): ?>
                        <span class="badge bg-success">Tersedia</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Dipinjam</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($tool['status'] == 'tersedia'): ?>
                        <button type="button" class="btn btn-primary btn-sm btn-pinjam" 
                                data-bs-toggle="modal" 
                                data-bs-target="#pinjamModal"
                                data-bs-tool-id="<?= $tool['id'] ?>"
                                data-bs-tool-name="<?= htmlspecialchars($tool['tool_name']) ?>">
                            Pinjam
                        </button>
                    <?php else: ?>
                        <a href="history.php" class="btn btn-secondary btn-sm">Lihat Riwayat</a>
                    <?php endif; ?>
                    
                    <form action="process/process_delete_tool.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus tool ini secara permanen?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <input type="hidden" name="tool_id" value="<?= $tool['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Tool">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="pinjamModal" tabindex="-1" aria-labelledby="pinjamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pinjamModalLabel">Form Peminjaman Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/process_borrow.php" method="POST" enctype="multipart/form-data">
                <input type="hidden", name="csrf_token" value="<?= $csrf_token ?>">

                <div class="modal-body">
                    <input type="hidden" name="tool_id" id="modal_tool_id">
                    
                    <p>Anda akan meminjam: <strong id="modal_tool_name"></strong></p>
                    
                    <div class="mb-3">
                        <label for="mechanics" class="form-label">Nama Peminjam</label>
                        <select class="form-select" id="mechanics" name="mechanics[]" multiple="multiple" required style="width: 100%;">
                            <?php
                                // Ambil semua data mekanik
                                $mechanic_stmt = $pdo->query("SELECT id, name FROM mechanics ORDER BY name");
                                while ($mechanic = $mechanic_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$mechanic['id']}'>{$mechanic['name']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="supervisor_borrow" class="form-label">Pengawas Lapangan</label>
                        <select class="form-select" id="supervisor_borrow" name="supervisor_borrow" required style="width: 100%;">
                        <option value="" disabled selected>-- Pilih Pengawas --</option>
                            <?php
                                // Ambil semua data supervisor
                                $supervisor_stmt = $pdo->query("SELECT name FROM supervisors ORDER BY name");
                                while ($supervisor = $supervisor_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$supervisor['name']}'>{$supervisor['name']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="proof_borrow" class="form-label">Bukti Foto Pinjam</label>
                        <input class="form-control" type="file" id="proof_borrow" name="proof_borrow" accept="image/jpeg, image/png" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Catat Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>