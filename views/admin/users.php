<?php use Models\Auth; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <title>Kelola User - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .table-container { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.1); }
        th { background: rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border-radius: 4px; background: rgba(255, 255, 255, 0.9); color: var(--text-main); border: 1px solid rgba(0,0,0,0.15); margin-top: 5px; }
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
                <img src="assets/images/logo-snt.png" alt="Logo" style="width:30px; height:30px; border-radius:4px;">
                <span>Absensi Pintar</span>
            </div>
            
            <div class="sidebar-nav">
                <a href="dashboard">Dashboard</a>
                <a href="admin/rekap">Rekap Absen</a>
                <a href="admin/users" class="active">Kelola User</a>
                <a href="admin/locations">Kelola Lokasi</a>
                <a href="admin/fingerprint">Sync Mesin</a>
            </div>
            
            <div class="sidebar-footer sidebar-nav">
                <a href="auth/logout" style="color: #F87171;">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container grid-2">
        <!-- Form Tambah User -->
        <div class="glass" style="padding: 20px; height: fit-content;">
            <h3>Tambah User Baru</h3>
            <br>
            <?php if(!empty($message)) echo "<div class='alert' style='background:rgba(255,255,255,0.1)'>$message</div>"; ?>
            <form method="POST" action="admin/users">
                <input type="hidden" name="csrf_token" value="<?php echo Auth::getCSRFToken(); ?>">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label style="font-size: 13px;">Nama Lengkap</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Username (Untuk Login Web)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Password (Untuk Login Web)</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">PIN (ID di Mesin Fingerprint)</label>
                    <input type="text" name="pin" required>
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Role Akses</label>
                    <select name="role_id" required>
                        <option value="2">Guru</option>
                        <option value="3">Siswa</option>
                        <option value="1">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size: 13px;">Penempatan (Lokasi Absen GPS)</label>
                    <select name="location_id" required>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 10px;">Simpan User</button>
            </form>
        </div>

        <!-- Tabel User -->
        <div class="glass" style="padding: 20px;">
            <h3>Daftar Pengguna</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>PIN</th>
                            <th>Role</th>
                            <th>Lokasi GPS</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['pin']); ?></td>
                            <td><?php echo htmlspecialchars($u['role_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['location_name']); ?></td>
                            <td>
                                <a href="admin/edit_user?id=<?php echo $u['id']; ?>" class="btn-sm" style="background: var(--primary); margin-right: 5px;">Edit</a>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin/users?delete_id=<?php echo $u['id']; ?>&csrf_token=<?php echo Auth::getCSRFToken(); ?>" class="btn-sm" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                <?php endif; ?>
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

