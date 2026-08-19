<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <title>Kelola Lokasi - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & Geocoder -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
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
                
                <p style="font-size: 13px; font-weight: 600; margin-bottom: 5px;">Pilih Titik di Peta</p>
                <div id="map" style="height: 250px; width: 100%; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); z-index: 1;"></div>

                <div class="form-group">
                    <label style="font-size: 13px;">Nama Gedung / Area</label>
                    <input type="text" name="name" required placeholder="Contoh: Gedung B">
                </div>
                
                <div class="grid-2" style="gap: 15px; grid-template-columns: 1fr 1fr; margin-bottom: 0;">
                    <div class="form-group">
                        <label style="font-size: 13px;">Latitude</label>
                        <input type="text" name="latitude" id="lat_input" required placeholder="Contoh: -6.123456">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 13px;">Longitude</label>
                        <input type="text" name="longitude" id="lng_input" required placeholder="Contoh: 106.123456">
                    </div>
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
<!-- Leaflet JS & Geocoder -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    // Default location (Jakarta)
    var defaultLat = -6.200000;
    var defaultLng = 106.816666;
    
    var map = L.map('map').setView([defaultLat, defaultLng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Geocoder Search Box
    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: "Cari nama tempat..."
    })
    .on('markgeocode', function(e) {
        var latlng = e.geocode.center;
        map.setView(latlng, 16);
        marker.setLatLng(latlng);
        updateInputs(latlng.lat, latlng.lng);
    })
    .addTo(map);

    var marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
    
    var latInput = document.getElementById('lat_input');
    var lngInput = document.getElementById('lng_input');

    // Function to update inputs
    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }
    
    // Set initial values
    updateInputs(defaultLat, defaultLng);

    // Update on marker drag
    marker.on('dragend', function (e) {
        var position = marker.getLatLng();
        updateInputs(position.lat, position.lng);
    });

    // Update on map click
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    // Allow manual input update
    latInput.addEventListener('change', function() {
        var newLat = parseFloat(this.value);
        if(!isNaN(newLat)) {
            var currentLng = marker.getLatLng().lng;
            marker.setLatLng([newLat, currentLng]);
            map.setView([newLat, currentLng]);
        }
    });

    lngInput.addEventListener('change', function() {
        var newLng = parseFloat(this.value);
        if(!isNaN(newLng)) {
            var currentLat = marker.getLatLng().lat;
            marker.setLatLng([currentLat, newLng]);
            map.setView([currentLat, newLng]);
        }
    });

    // Try to get user's current location if possible
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            updateInputs(lat, lng);
        });
    }
</script>
</html>
