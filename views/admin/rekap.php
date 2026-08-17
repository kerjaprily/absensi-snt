<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absen - Admin</title>
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
        .filter-form { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end; }
        .filter-form input { padding: 8px; border-radius: 4px; background: rgba(255, 255, 255, 0.9); color: var(--text-main); border: 1px solid rgba(0,0,0,0.15); }
        .btn-sm { padding: 9px 15px; background: var(--primary); color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/logo-snt.png" alt="Logo" style="width:30px; height:30px; background-color: #FFFFFF; padding: 2px; border-radius:6px;">
                <span>Absensi Pintar</span>
            </div>
            
            <div class="sidebar-nav">
                <a href="dashboard">Dashboard</a>
                <a href="admin/rekap" class="active">Rekap Absen</a>
                <a href="admin/users">Kelola User</a>
                <a href="admin/locations">Kelola Lokasi</a>
                <a href="admin/fingerprint">Sync Mesin</a>
            </div>
            
            <div class="sidebar-footer sidebar-nav">
                <a href="auth/logout" style="color: #F87171;">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
        <div class="glass" style="padding: 30px;">
            <h2>Rekap Seluruh Absensi</h2>
            <br>
            <form class="filter-form" method="GET" action="admin/rekap">
                <div>
                    <label style="font-size: 12px; color: var(--text-muted);">Pilih Karyawan:</label><br>
                    <select name="user_id" style="padding: 8px; border-radius: 4px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid rgba(255,255,255,0.2);">
                        <option value="">Semua Karyawan</option>
                        <?php foreach($allUsers as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($filter_user_id == $u['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--text-muted);">Dari Tanggal:</label><br>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--text-muted);">Sampai Tanggal:</label><br>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <button type="submit" class="btn-sm">Filter Laporan</button>
                <button type="submit" name="export" value="excel" class="btn-sm" style="background: var(--success);">⬇️ Export Excel</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Total Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($logs) > 0): ?>
                            <?php foreach($logs as $log): 
                                $jam_masuk = $log['jam_masuk'] ?: '-';
                                $jam_keluar = $log['jam_keluar'] ?: '-';
                                
                                $status_badge = 'in';
                                $status_text = 'Lengkap';
                                $total_jam = '-';

                                if ($jam_masuk !== '-' && $jam_keluar !== '-') {
                                    $t1 = strtotime($jam_masuk);
                                    $t2 = strtotime($jam_keluar);
                                    $diff = round(abs($t2 - $t1) / 3600, 1);
                                    $total_jam = $diff . ' Jam';
                                } elseif ($jam_masuk !== '-' && $jam_keluar === '-') {
                                    $status_badge = 'out';
                                    $status_text = 'Belum Pulang';
                                } elseif ($jam_masuk === '-' && $jam_keluar !== '-') {
                                    $status_badge = 'out';
                                    $status_text = 'Hanya Pulang';
                                } else {
                                    $status_badge = 'out';
                                    $status_text = 'Tidak Absen';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($log['role_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($log['scan_date'])); ?></td>
                                <td><span style="font-weight: 600; color: var(--success);"><?php echo $jam_masuk; ?></span></td>
                                <td><span style="font-weight: 600; color: var(--primary);"><?php echo $jam_keluar; ?></span></td>
                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                <td><span style="font-weight: bold;"><?php echo $total_jam; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center;">Tidak ada data pada periode ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </main>
    </div>
</body>
</html>


