<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Absensi Pintar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .table-container { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.1); }
        th { background: rgba(0,0,0,0.05); }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge.in { background: var(--success); color: white; }
        .badge.out { background: var(--error); color: white; }
        
        .admin-nav {
            display: flex; gap: 15px; margin-bottom: 20px;
        }
        .admin-btn {
            padding: 10px 20px; background: rgba(0,0,0,0.05); border-radius: 8px; color: var(--text-main); text-decoration: none; border: 1px solid rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .admin-btn:hover { background: var(--primary); color: white; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/logo-snt.png" alt="Logo" style="width:30px; height:30px; border-radius:4px;">
                <span>Absensi Pintar</span>
            </div>
            
            <div class="sidebar-nav">
                <a href="dashboard" class="active">Dashboard</a>
                
                <?php if($isAdmin): ?>
                    <a href="admin/rekap">Rekap Absen</a>
                    <a href="admin/users">Kelola User</a>
                    <a href="admin/locations">Kelola Lokasi</a>
                    <a href="admin/fingerprint">Sync Mesin</a>
                <?php endif; ?>
            </div>
            
            <div class="sidebar-footer sidebar-nav">
                <a href="auth/logout" style="color: #F87171;">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="dashboard-header">
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 5px;"><?php echo date('l, d F Y'); ?></p>
                    <h1>Halo, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                    <p>Selamat datang di dashboard <?php echo $isAdmin ? 'manajemen' : 'absensi Anda'; ?>.</p>
                </div>
        
        <?php if($isAdmin): ?>
            <div class="admin-nav">
                <a href="admin/rekap" class="admin-btn">Laporan Lanjutan</a>
                <a href="admin/users" class="admin-btn">Kelola Karyawan</a>
                <a href="admin/locations" class="admin-btn">Kelola Lokasi</a>
                <a href="admin/fingerprint" class="admin-btn" style="background: rgba(0, 200, 100, 0.2); border-color: rgba(0, 200, 100, 0.5);">Sync Mesin</a>
            </div>
        <?php endif; ?>

        <div class="glass" style="padding: 20px;">
            <h3><?php echo $isAdmin ? 'Rekap Absensi Keseluruhan (Bulan Ini)' : 'Histori Absensi Anda (Bulan Ini)'; ?></h3>
            
            <?php if(count($logs) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php if($isAdmin): ?>
                                <th>Nama</th>
                                <th>Role</th>
                            <?php endif; ?>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <?php if($isAdmin): ?>
                                <td><?php echo htmlspecialchars($log['user_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($log['role_name'] ?? ''); ?></td>
                            <?php endif; ?>
                            <td><?php echo $log['scan_date']; ?></td>
                            <td><?php echo $log['scan_time']; ?></td>
                            <td>
                                <span class="badge <?php echo strtolower($log['auth_type']); ?>">
                                    <?php echo $log['auth_type']; ?>
                                </span>
                            </td>
                            <td><?php echo $log['source']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="color: var(--text-muted); margin-top: 10px;">Belum ada data absensi di bulan ini.</p>
            <?php endif; ?>
        </div>
        </div>
    </main>
    </div>
</body>
</html>


