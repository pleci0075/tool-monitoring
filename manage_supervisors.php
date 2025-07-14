<?php
// File: manage_mechanics.php

require_once 'includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$stmt = $pdo->query("SELECT * FROM supervisors ORDER BY name ASC");
$supervisors = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Manajemen Pengawas</h3>
    <button type="button" class="btn btn-primary" id="btn-add-mechanic">
        <i class="bi bi-plus-circle"></i> Tambah Data Pengawas Baru
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
            <table id="supervisorsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengawas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($supervisors as $supervisor): ?>
                    <tr>
                        <td><?= $supervisor['id'] ?></td>
                        <td><?= htmlspecialchars($supervisor['name']) ?></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm btn-edit-supervisor" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal"
                                    data-id="<?= $mechanic['id'] ?>"
                                    data-name="<?= htmlspecialchars($supervisor['name']) ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="process/delete_supervisor.php" method="POST" class="delete-form d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="id" value="<?= $supervisor['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Supervisor"><i class="bi bi-trash3-fill"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Pengawas Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form action="process/add_supervisor.php" method="POST"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><div class="modal-body">
    <div class="mb-3"><label for="add_name" class="form-label">Nama Pengawas</label><input type="text" class="form-control" id="add_name" name="name" required></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Pengawas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form action="process/edit_pengawas.php" method="POST"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="id" id="edit_id"><div class="modal-body">
    <div class="mb-3"><label for="edit_name" class="form-label">Nama Pengawas</label><input type="text" class="form-control" id="edit_name" name="name" required></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div></form></div></div></div>

<?php require_once 'includes/footer.php'; ?>