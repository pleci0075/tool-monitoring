<?php 
// File: history.php (Versi Baru)
require_once 'includes/header.php'; 
require_once 'config/database.php';

$sql = "SELECT 
            t.id AS transaction_id, -- Kita butuh ID transaksi untuk proses pengembalian
            t.borrow_date,
            tl.tool_name, 
            tl.tool_code,
            t.supervisor_borrow,
            t.proof_borrow,
            t.return_date,
            t.supervisor_return,
            t.proof_return,
            (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') 
             FROM transaction_mechanics tm
             JOIN mechanics m ON tm.mechanic_id = m.id
             WHERE tm.transaction_id = t.id
            ) AS borrowers
        FROM transactions t
        JOIN tools tl ON t.tool_id = tl.id
        ORDER BY t.borrow_date DESC";

$stmt = $pdo->query($sql);
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

<h3>Riwayat Peminjaman Tool</h3>

<?php if (isset($_GET['status'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php
        if ($_GET['status'] == 'borrow_success') echo 'Data peminjaman baru berhasil ditambahkan!';
        if ($_GET['status'] == 'return_success') echo 'Tool berhasil dikembalikan!';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="table-responsive">
    <table id="historyTable" class="table table-hover" style="width:100%">
        <thead>
            <tr>
                <th>Tool & Peminjam</th>
                <th>Tanggal Pinjam</th>
                <th>Status</th>
                <th>Bukti</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($row['tool_name']) ?></strong>
                    <small class="d-block text-muted">Oleh: <?= htmlspecialchars($row['borrowers']) ?></small>
                </td>
                <td>
                    <?= date('d M Y, H:i', strtotime($row['borrow_date'])) ?>
                </td>
                <td>
                    <?php if($row['return_date']): ?>
                        <span class="badge bg-light text-dark border">Sudah Kembali</span>
                        <small class="d-block text-muted"><?= date('d M Y', strtotime($row['return_date'])) ?></small>
                    <?php else: ?>
                        <span class="badge bg-primary">Dipinjam</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="uploads/<?= htmlspecialchars($row['proof_borrow']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat Bukti Pinjam">
                        <i class="bi bi-camera"></i>
                    </a>
                    <?php if($row['proof_return']): ?>
                    <a href="uploads/<?= htmlspecialchars($row['proof_return']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat Bukti Kembali">
                        <i class="bi bi-camera-reels"></i>
                    </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!$row['return_date']): ?>
                        <button type="button" class="btn btn-success btn-sm btn-kembalikan" data-bs-toggle="modal" data-bs-target="#kembalikanModal" data-bs-transaction-id="<?= $row['transaction_id'] ?>">
                            Kembalikan
                        </button>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="kembalikanModal" tabindex="-1" aria-labelledby="kembalikanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kembalikanModalLabel">Form Pengembalian Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/process_return.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="modal_transaction_id">
                    <p>Konfirmasi pengembalian tool.</p>
                    
                    <div class="mb-3">
                        <label for="supervisor_return" class="form-label">Pengawas Lapangan (Saat Kembali)</label>
                        <select class="form-select" id="supervisor_return" name="supervisor_return" required style="width: 100%;">
                            <option value="" disabled selected>-- Pilih Pengawas --</option>
                            <?php
                                $supervisor_stmt = $pdo->query("SELECT name FROM supervisors ORDER BY name");
                                while ($supervisor = $supervisor_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$supervisor['name']}'>{$supervisor['name']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="proof_return" class="form-label">Bukti Foto Kembali</label>
                        <input class="form-control" type="file" id="proof_return" name="proof_return" accept="image/jpeg, image/png" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>