<?php use Models\Auth; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Sync Mesin Fingerprint</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: var(--text-muted); font-size: 14px;}
        .form-control { width: 100%; padding: 10px; border-radius: 8px; background: rgba(255, 255, 255, 0.9); color: var(--text-main); border: 1px solid rgba(0,0,0,0.15); }
        .btn { padding: 10px 15px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;}
        .btn-sync { background: var(--success); padding: 8px 12px; font-size: 12px; font-weight: bold; border-radius: 6px; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;}
        .btn-sm { padding: 6px 10px; font-size: 12px; background: var(--error); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none;}
        .btn-edit { background: var(--primary); }
        .grid-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        @media(max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
        .table-container { overflow-x: auto; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(0,0,0,0.1); }
        th { background: rgba(0,0,0,0.05); font-size: 14px;}
        .loading-text { font-size: 12px; color: var(--success); display: none; margin-top: 5px; }
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
                <a href="admin/users">Kelola User</a>
                <a href="admin/locations">Kelola Lokasi</a>
                <a href="admin/fingerprint" class="active">Sync Mesin</a>
            </div>
            
            <div class="sidebar-footer sidebar-nav">
                <a href="auth/logout" style="color: #F87171;">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
        <div class="dashboard-header" style="margin-bottom: 20px;">
            <h2>Integrasi Multi-Mesin Fingerprint</h2>
            <p>Kelola konfigurasi banyak mesin absensi fisik sekaligus dan tarik datanya secara individual.</p>
        </div>

        <?php if(!empty($message)): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div id="sync-result" style="display: none;" class="alert"></div>

        <div class="grid-2">
            <!-- Kolom Form Tambah/Edit -->
            <div class="glass" style="padding: 20px; height: fit-content;">
                <h3 id="formTitle">Tambah Mesin Baru</h3>
                <br>
                <form method="POST" id="machineForm" action="admin/fingerprint">
                    <input type="hidden" name="csrf_token" value="<?php echo Auth::getCSRFToken(); ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="machine_id" id="machine_id" value="">
                    <div class="form-group">
                        <label>Nama Mesin</label>
                        <input type="text" name="machine_name" id="machine_name" class="form-control" required placeholder="Contoh: Mesin Lobby Depan">
                    </div>
                    <div class="form-group">
                        <label>Alamat IP (IPv4)</label>
                        <input type="text" name="ip_address" id="ip_address" class="form-control" required placeholder="Contoh: 192.168.1.201">
                    </div>
                    <div class="form-group">
                        <label>Port Komunikasi</label>
                        <input type="number" name="port" id="port" class="form-control" value="4370" required>
                    </div>
                    <div class="form-group">
                        <label>Comm Key</label>
                        <input type="text" name="comm_key" id="comm_key" class="form-control" value="0" required>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn" style="flex: 1;">Simpan</button>
                        <button type="button" class="btn" style="background: rgba(255,255,255,0.2);" onclick="resetForm()">Batal</button>
                    </div>
                </form>
            </div>

            <!-- Kolom Daftar Mesin -->
            <div class="glass" style="padding: 20px;">
                <h3>Daftar Mesin Fingerprint Terdaftar</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Mesin</th>
                                <th>IP & Port</th>
                                <th>Aksi Kelola</th>
                                <th>Sinkronisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($machines) == 0): ?>
                                <tr><td colspan="4" style="text-align: center;">Belum ada mesin terdaftar.</td></tr>
                            <?php else: ?>
                                <?php foreach($machines as $m): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['machine_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($m['ip_address'] . ':' . $m['port']); ?></td>
                                    <td>
                                        <button onclick="editMachine(<?php echo $m['id']; ?>, '<?php echo addslashes($m['machine_name']); ?>', '<?php echo addslashes($m['ip_address']); ?>', '<?php echo $m['port']; ?>', '<?php echo addslashes($m['comm_key']); ?>')" class="btn-sm btn-edit">Edit</button>
                                        <a href="admin/fingerprint?delete_id=<?php echo $m['id']; ?>&csrf_token=<?php echo Auth::getCSRFToken(); ?>" class="btn-sm" onclick="return confirm('Hapus mesin ini?');">Hapus</a>
                                    </td>
                                    <td>
                                        <button id="btn-sync-<?php echo $m['id']; ?>" class="btn-sync" onclick="tarikData(<?php echo $m['id']; ?>, '<?php echo addslashes($m['machine_name']); ?>')">
                                            ⬇️ Tarik Log
                                        </button>
                                        <div id="loading-<?php echo $m['id']; ?>" class="loading-text">Menghubungi IP...</div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editMachine(id, name, ip, port, key) {
            document.getElementById('formTitle').innerText = "Edit Mesin";
            document.getElementById('machine_id').value = id;
            document.getElementById('machine_name').value = name;
            document.getElementById('ip_address').value = ip;
            document.getElementById('port').value = port;
            document.getElementById('comm_key').value = key;
        }

        function resetForm() {
            document.getElementById('formTitle').innerText = "Tambah Mesin Baru";
            document.getElementById('machine_id').value = "";
            document.getElementById('machineForm').reset();
        }

        function tarikData(machineId, machineName) {
            let btn = document.getElementById('btn-sync-' + machineId);
            let loading = document.getElementById('loading-' + machineId);
            let resultDiv = document.getElementById('sync-result');
            
            btn.style.display = 'none';
            loading.style.display = 'block';
            resultDiv.style.display = 'none';
            
            fetch('api/pull_fingerprint.php?machine_id=' + machineId)
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    btn.style.display = 'inline-flex';
                    
                    resultDiv.style.display = 'block';
                    if (data.status === 'success') {
                        resultDiv.className = 'alert success';
                        resultDiv.innerHTML = `<strong>[${machineName}] Berhasil:</strong> ${data.message}`;
                    } else {
                        resultDiv.className = 'alert error';
                        resultDiv.innerHTML = `<strong>[${machineName}] Gagal:</strong> ${data.message}`;
                    }
                })
                .catch(err => {
                    loading.style.display = 'none';
                    btn.style.display = 'inline-flex';
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'alert error';
                    resultDiv.innerHTML = `[${machineName}] Terjadi kesalahan jaringan internal.`;
                });
        }
    </script>
    </div>
    </main>
    </div>
</body>
</html>

