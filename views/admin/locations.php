<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <title>Kelola Lokasi - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .table-container { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.1); }
        th { background: rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 8px; border-radius: 4px; background: rgba(255, 255, 255, 0.9); color: var(--text-main); border: 1px solid rgba(0,0,0,0.15); margin-top: 5px; }
        .btn-sm { padding: 6px 10px; font-size: 12px; background: var(--error); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none;}
        .grid-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        
        @media(max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
        }
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
                <a href="admin/rekap">Rekap Absen</a>
                <a href="admin/users">Kelola User</a>
                <a href="admin/locations" class="active">Kelola Lokasi</a>
                <a href="admin/fingerprint">Sync Mesin</a>
            </div>
            
            <div class="sidebar-footer sidebar-nav">
                <a href="auth/logout" style="color: #F87171;">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container grid-2">
        <!-- Form Tambah -->
        <div class="glass" style="padding: 20px; height: fit-content;">
            <h3>Tambah Titik Lokasi</h3>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">Kamu bisa menambah hingga 17 lokasi atau lebih.</p>
            <?php if($message) echo "<div class='alert' style='background:rgba(255,255,255,0.1)'>$message</div>"; ?>
            <form method="POST" action="admin/locations">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label style="font-size: 13px;">Nama Gedung / Area</label>
                    <input type="text" name="name" required placeholder="Contoh: Gedung B">
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Latitude</label>
                    <input type="text" name="latitude" required placeholder="Contoh: -6.123456">
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Longitude</label>
                    <input type="text" name="longitude" required placeholder="Contoh: 106.123456">
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Batas Radius (Meter)</label>
                    <input type="number" name="radius_meters" value="100" required>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 10px;">Simpan Lokasi</button>
            </form>
        </div>

        <!-- Tabel -->
        <div class="glass" style="padding: 20px;">
            <h3>Daftar Lokasi Tersimpan</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Gedung</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Radius</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($locations as $loc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loc['name']); ?></td>
                            <td><?php echo $loc['latitude']; ?></td>
                            <td><?php echo $loc['longitude']; ?></td>
                            <td><?php echo $loc['radius_meters']; ?>m</td>
                            <td>
                                <a href="admin/locations?delete_id=<?php echo $loc['id']; ?>" class="btn-sm" onclick="return confirm('Yakin hapus lokasi ini? Pastikan tidak ada user yang di-assign ke lokasi ini.')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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


