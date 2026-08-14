let currentLat = null;
let currentLong = null;

// Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Radius of earth in meters
    const phi1 = lat1 * Math.PI / 180;
    const phi2 = lat2 * Math.PI / 180;
    const deltaPhi = (lat2 - lat1) * Math.PI / 180;
    const deltaLambda = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
              Math.cos(phi1) * Math.cos(phi2) *
              Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    return R * c; 
}

function initGeolocation() {
    const infoDiv = document.getElementById('location-info');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLat = position.coords.latitude;
                currentLong = position.coords.longitude;
                
                const distance = calculateDistance(currentLat, currentLong, assignedLat, assignedLong);
                
                infoDiv.innerHTML = `
                    Latitude: ${currentLat.toFixed(6)}<br>
                    Longitude: ${currentLong.toFixed(6)}<br>
                    Jarak ke pusat (${locationName}): <strong>${Math.round(distance)} meter</strong>
                `;
                
                if (distance <= maxRadius) {
                    infoDiv.innerHTML += '<br><span style="color:var(--success)">Anda berada di dalam radius!</span>';
                    document.getElementById('btn-in').disabled = false;
                    document.getElementById('btn-out').disabled = false;
                } else {
                    infoDiv.innerHTML += `<br><span style="color:var(--error)">Anda berada di luar radius! (Maks: ${maxRadius}m)</span>`;
                }
            },
            function(error) {
                infoDiv.innerHTML = `<span style="color:var(--error)">Error mengambil lokasi: ${error.message}</span>`;
            },
            { enableHighAccuracy: true }
        );
    } else {
        infoDiv.innerHTML = `<span style="color:var(--error)">Browser tidak mendukung Geolocation.</span>`;
    }
}

function submitAttendance(authType) {
    if (!currentLat || !currentLong) {
        alert("Lokasi belum didapatkan!");
        return;
    }
    
    const formData = new FormData();
    formData.append('auth_type', authType);
    formData.append('latitude', currentLat);
    formData.append('longitude', currentLong);

    fetch('api/web_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const statusBox = document.getElementById('status-message');
        if (data.trim() === 'OK') {
            statusBox.className = "status-box success";
            statusBox.innerHTML = `Absen ${authType} Berhasil!`;
            document.getElementById('btn-in').disabled = true;
            document.getElementById('btn-out').disabled = true;
        } else {
            statusBox.className = "status-box error";
            statusBox.innerHTML = `Error: ${data}`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Terjadi kesalahan sistem saat menghubungi server.");
    });
}

window.onload = initGeolocation;
