<?php
require_once 'includes/header_public.php';
require_once 'config/database.php';

$tool_to_return = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tool_code'])) {
    $tool_code = $_POST['tool_code'];
    $stmt = $pdo->prepare("SELECT * FROM tools WHERE tool_code = ?");
    $stmt->execute([$tool_code]);
    $tool_to_return = $stmt->fetch();

    if (!$tool_to_return) {
        $error_message = "Tool dengan kode '$tool_code' tidak ditemukan.";
    } elseif ($tool_to_return['status'] == 'tersedia') {
        $error_message = "Tool dengan kode '$tool_code' tidak sedang dipinjam.";
        $tool_to_return = null; // Sembunyikan form jika tidak bisa dikembalikan
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Form Pengembalian Tool</h3>

                <form method="POST" action="return_page.php">
                    <div class="mb-3">
                        <label for="tool_code" class="form-label">Masukkan Kode Tool</label>
                        <input type="text" class="form-control" name="tool_code" id="tool_code" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cari Tool</button>
                </form>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <?php if ($tool_to_return):
                    // Ambil transaction_id berdasarkan tool_id yang belum kembali
                    $trans_stmt = $pdo->prepare("SELECT id FROM transactions WHERE tool_id = ? AND return_date IS NULL");
                    $trans_stmt->execute([$tool_to_return['id']]);
                    $transaction = $trans_stmt->fetch();
                    $transaction_id = $transaction ? $transaction['id'] : null;
                ?>
                    <hr>
                    <h4>Detail Tool</h4>
                    <p><strong>Nama Tool:</strong> <?= htmlspecialchars($tool_to_return['tool_name']) ?></p>
                    <p><strong>Kode Tool:</strong> <?= htmlspecialchars($tool_to_return['tool_code']) ?></p>

                    <form action="process/process_return.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        <input type="hidden" name="transaction_id" value="<?= $transaction_id ?>">

                        <button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>