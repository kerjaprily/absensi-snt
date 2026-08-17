import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Alert, ActivityIndicator, ScrollView, Image } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import * as Location from 'expo-location';
import axios from 'axios';

// PASTIKAN IP SAMA DENGAN DI INDEX.TSX
const API_URL = "https://pmp.kemendikdasmen.go.id/absensi-snt/api/v1";

export default function DashboardScreen() {
  const { userId, name, token } = useLocalSearchParams();
  const [locationStatus, setLocationStatus] = useState('Mencari sinyal GPS (Pastikan GPS Aktif)...');
  const [loading, setLoading] = useState(false);
  const [gpsData, setGpsData] = useState<Location.LocationObject | null>(null);
  const [addressName, setAddressName] = useState<string>('Memuat nama jalan...');

  const fetchLocation = async () => {
    setLocationStatus('Mencari sinyal GPS terkini...');
    let { status } = await Location.requestForegroundPermissionsAsync();
    if (status !== 'granted') {
      setLocationStatus('Izin akses lokasi ditolak oleh sistem!');
      return null;
    }

    try {
      let location = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.High });
      
      // Cek Keamanan: Deteksi Fake GPS
      if (location.mocked) {
          setLocationStatus('🚨 PERINGATAN: Fake GPS Terdeteksi! Matikan aplikasi mock location Anda.');
          return null;
      }

      setGpsData(location);
      setLocationStatus(`Sinyal GPS Terkunci (Akurasi: ${Math.round(location.coords.accuracy || 0)}m)`);
      
      try {
        let addressList = await Location.reverseGeocodeAsync({ 
          latitude: location.coords.latitude, 
          longitude: location.coords.longitude 
        });
        
        if (addressList.length > 0) {
          let addr = addressList[0];
          let formattedAddress = [addr.street, addr.subregion, addr.city, addr.region]
            .filter(Boolean)
            .join(', ');
          setAddressName(formattedAddress || 'Nama lokasi tidak ditemukan');
        } else {
          setAddressName('Nama lokasi tidak ditemukan');
        }
      } catch (geocodeError) {
        setAddressName('Gagal memuat nama jalan');
      }

      return location;
    } catch (e) {
      setLocationStatus('Gagal membaca GPS HP.');
      return null;
    }
  };

  useEffect(() => {
    fetchLocation();
  }, []);

  const handleAttendance = async (authType: 'IN' | 'OUT') => {
    setLoading(true);

    // Auto-refresh GPS di detik terakhir sebelum absen
    const freshLocation = await fetchLocation();
    
    if (!freshLocation) {
      Alert.alert('Gagal', 'Sistem tidak bisa mendapatkan lokasi terkini Anda. Pastikan GPS aktif!');
      setLoading(false);
      return;
    }

    try {
      const response = await axios.post(`${API_URL}/submit_attendance.php`, {
        user_id: userId,
        auth_type: authType,
        latitude: freshLocation.coords.latitude,
        longitude: freshLocation.coords.longitude
      }, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
      });

      if (response.status === 200) {
        Alert.alert('Sukses!', `Absen ${authType === 'IN' ? 'Masuk' : 'Pulang'} berhasil dicatat.`);
      }
    } catch (error: any) {
      if (error.response && error.response.data && error.response.data.message) {
        Alert.alert('Gagal', error.response.data.message);
      } else {
        Alert.alert('Error', 'Tidak dapat terhubung ke server.');
      }
    } finally {
      setLoading(false);
    }
  };

  const logout = () => {
    router.replace('/');
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Absensi Pintar</Text>
        <TouchableOpacity onPress={logout}>
          <Text style={styles.logoutText}>Logout</Text>
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.card}>
          <Image source={require('../assets/images/logo-snt.png')} style={{ width: 80, height: 80, alignSelf: 'center', marginBottom: 15, backgroundColor: '#FFFFFF', borderRadius: 16 }} resizeMode="contain" />
          <Text style={styles.welcome}>Halo, {name}!</Text>
          <Text style={styles.desc}>Siap untuk absen hari ini?</Text>

          <View style={styles.statusBox}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', width: '100%', alignItems: 'center', marginBottom: 5 }}>
                <Text style={styles.statusText}>{locationStatus}</Text>
                <TouchableOpacity onPress={fetchLocation} style={styles.refreshBtn}>
                    <Text style={styles.refreshBtnText}>🔄 Segarkan</Text>
                </TouchableOpacity>
            </View>
            {gpsData && (
              <View style={{ marginTop: 10, alignItems: 'center' }}>
                <Text style={{ color: '#fff', fontSize: 13, textAlign: 'center', fontWeight: 'bold' }}>
                  📍 {addressName}
                </Text>
                <Text style={styles.coordsText}>
                  Lat: {gpsData.coords.latitude.toFixed(6)} | Long: {gpsData.coords.longitude.toFixed(6)}
                </Text>
              </View>
            )}
          </View>

          <TouchableOpacity
            style={[styles.button, styles.btnIn, loading && styles.btnDisabled]}
            onPress={() => handleAttendance('IN')}
            disabled={loading}
          >
            {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>ABSEN MASUK</Text>}
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.button, styles.btnOut, loading && styles.btnDisabled]}
            onPress={() => handleAttendance('OUT')}
            disabled={loading}
          >
            {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>ABSEN PULANG</Text>}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0F172A',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    paddingHorizontal: 20,
    paddingBottom: 15,
    paddingTop: 50,
    backgroundColor: 'rgba(30, 41, 59, 1)',
    borderBottomWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  logoutText: {
    color: '#EF4444',
    fontWeight: 'bold',
  },
  content: {
    padding: 20,
  },
  card: {
    backgroundColor: 'rgba(30, 41, 59, 0.7)',
    padding: 25,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    alignItems: 'center',
  },
  welcome: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 5,
  },
  desc: {
    color: '#94A3B8',
    marginBottom: 25,
  },
  statusBox: {
    backgroundColor: 'rgba(15, 23, 42, 0.5)',
    padding: 15,
    borderRadius: 8,
    width: '100%',
    marginBottom: 25,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  statusText: {
    color: '#10B981',
    fontWeight: 'bold',
    flex: 1,
  },
  refreshBtn: {
    backgroundColor: 'rgba(255,255,255,0.1)',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
    marginLeft: 10,
  },
  refreshBtnText: {
    color: '#fff',
    fontSize: 12,
  },
  coordsText: {
    color: '#94A3B8',
    fontSize: 12,
    marginTop: 5,
  },
  button: {
    width: '100%',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 15,
  },
  btnIn: {
    backgroundColor: '#4F46E5',
  },
  btnOut: {
    backgroundColor: '#6366F1',
  },
  btnDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 16,
  }
});
