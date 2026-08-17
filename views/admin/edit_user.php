<?php use Models\Auth; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: var(--text-muted); font-size: 14px;}
        .form-control { width: 100%; padding: 10px; border-radius: 8px; background: rgba(255, 255, 255, 0.9); color: var(--text-main); border: 1px solid rgba(0,0,0,0.15); }
        .btn { padding: 10px 15px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        .alert { padding: 10px; background: var(--success); color: white; border-radius: 8px; margin-bottom: 15px; }
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
            <div class="container">
                <div class="glass" style="padding: 30px; max-width: 500px; margin: 0 auto;">
                    <h2>Edit Data Pengguna</h2>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">Ubah informasi profil, role, atau lokasi penugasan.</p>
                    
                    <?php if(!empty($message)): ?>
                        <div class="alert"><?php echo $message; ?></div>
                    <?php endif; ?>
            <form action="admin/edit_user?id=<?php echo $edit_user['id']; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Auth::getCSRFToken(); ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Password (Kosongkan jika tidak ingin diubah)</label>
                    <input type="password" name="password" class="form-control" placeholder="Tulis password baru di sini...">
                </div>
                <div class="form-group">
                    <label>PIN Mesin Fingerprint (Opsional)</label>
                    <input type="number" name="pin" class="form-control" value="<?php echo htmlspecialchars($edit_user['pin']); ?>">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id" class="form-control" required>
                        <option value="1" <?php echo ($edit_user['role_id'] == 1) ? 'selected' : ''; ?>>Admin</option>
                        <option value="2" <?php echo ($edit_user['role_id'] == 2) ? 'selected' : ''; ?>>Guru / Karyawan</option>
                        <option value="3" <?php echo ($edit_user['role_id'] == 3) ? 'selected' : ''; ?>>Siswa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lokasi Kerja / Penugasan</label>
                    <select name="location_id" class="form-control">
                        <option value="">-- Tidak Ada / Default --</option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo $loc['id']; ?>" <?php echo ($edit_user['location_id'] == $loc['id']) ? 'selected' : ''; ?>><?php echo $loc['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="background: rgba(255, 60, 60, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(255, 60, 60, 0.3);">
                    <label style="color: #ff6b6b; font-weight: bold; margin-bottom: 8px;">Keamanan Perangkat (Device Binding)</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="reset_device" value="1" id="reset_device" style="width: 20px; height: 20px;">
                        <label for="reset_device" style="margin: 0; color: white; cursor: pointer;">
                            Reset Kuncian Perangkat HP<br>
                            <small style="color: var(--text-muted);">Centang ini jika user ganti HP agar mereka bisa login di HP baru.</small>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>
        </div>
    </div>
    </div>
    </main>
    </div>
</body>
</html>



