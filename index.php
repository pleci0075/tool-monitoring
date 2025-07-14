<?php
// File: index.php (Versi Desain Final dengan Layout Dua Kolom)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="logo-container">
        <img src="assets/img/logo_perusahaan.png" alt="Logo Perusahaan" class="logo">
    </div>

    <div class="launchpad-container">
        <div class="container">
            <div class="row min-vh-100 align-items-center">
                
                <div class="col-lg-6">
                    <div class="hero-text">
                        Tool<br>
                        Mekanik<br>
                        Plant Engineer<br>
                        PPA - BIB
                    </div>
                </div>

                <div class="col-lg-5 offset-lg-1">
                    <div class="action-panel">
                        <div class="action-item">
                            <div class="action-text">
                                <h3>Pinjam & Kembalikan</h3>
                                <p>Akses portal untuk melihat, meminjam, atau mengembalikan tool.</p>
                            </div>
                            <a href="tool_list.php" class="btn btn-primary">Masuk Portal</a>
                        </div>
                        <hr class="action-divider">
                        <div class="action-item">
                            <div class="action-text">
                                <h3>Lapor Kerusakan</h3>
                                <p>Laporkan masalah atau kerusakan yang ditemukan pada alat.</p>
                            </div>
                            <a href="report_launchpad.php" class="btn btn-warning mt-auto">Lapor Kerusakan</a>
                        </div>
                        <hr class="action-divider">
                        <div class="action-item">
                            <div class="action-text">
                                <h3>Portal Pengawas</h3>
                                <p>Area khusus untuk manajemen, riwayat, dan administrasi.</p>
                            </div>
                            <a href="login.php" class="btn btn-dark">Login Admin</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>

<?php require_once 'includes/footer.php'; ?>