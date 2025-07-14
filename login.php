<?php
// File: login.php
session_start();

// Jika pengguna sudah login, langsung arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    }
    // Nanti bisa ditambahkan redirect untuk role 'mechanic'
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitoring Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; border: none; border-radius: 12px; box-shadow: 0 4px M(0, 0, 0, 0.05); padding: 2.5rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card bg-white">
            <h3 class="text-center mb-4 fw-bold">Tool Monitoring</h3>
            <?php
            // Tampilkan pesan error jika login gagal
            if (isset($_SESSION['flash_message'])) {
                echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['flash_message']).'</div>';
                unset($_SESSION['flash_message']);
            }
            ?>
            <form action="process/auth_process.php" method="POST">
                <div class="mb-3">
                    <label for="nrp" class="form-label fw-bold">NRP</label>
                    <input type="text" class="form-control" id="nrp" name="nrp" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>