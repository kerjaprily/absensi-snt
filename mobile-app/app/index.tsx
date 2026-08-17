import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert, ActivityIndicator, Platform, Image } from 'react-native';
import { router } from 'expo-router';
import axios from 'axios';
import * as Application from 'expo-application';

// Alamat API sudah dikonfigurasi ke server produksi VPS
const API_URL = "https://pmp.kemendikdasmen.go.id/absensi-snt/api/v1";

export default function LoginScreen() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!username || !password) {
      Alert.alert('Error', 'Username dan Password wajib diisi');
      return;
    }

    setLoading(true);
    try {
      // Dapatkan Device ID unik
      let deviceId = 'unknown';
      if (Platform.OS === 'android') {
        deviceId = await Application.getAndroidId();
      } else if (Platform.OS === 'ios') {
        const iosId = await Application.getIosIdForVendorAsync();
        deviceId = iosId || 'unknown';
      }

      const response = await axios.post(`${API_URL}/login.php`, {
        username,
        password,
        device_id: deviceId
      });

      if (response.status === 200) {
        // Redirect ke dashboard dan mengirimkan ID user serta token JWT
        router.replace({
          pathname: '/dashboard',
          params: {
            userId: response.data.user_id,
            name: response.data.name,
            token: response.data.token
          }
        });
      }
    } catch (error: any) {
      console.error(error);
      if (error.response && error.response.data && error.response.data.message) {
        Alert.alert('Login Gagal', error.response.data.message);
      } else {
        Alert.alert('Login Gagal', 'Username atau password salah, atau periksa kembali konfigurasi IP API_URL kamu.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <View style={styles.card}>
        <Image 
          source={require('../assets/images/logo-snt.png')} 
          style={styles.logo} 
          resizeMode="contain" 
        />
        <Text style={styles.title}>Absensi Pintar</Text>
        <Text style={styles.subtitle}>Masuk melalui Aplikasi Mobile</Text>

        <TextInput
          style={styles.input}
          placeholder="Username"
          placeholderTextColor="#94A3B8"
          value={username}
          onChangeText={setUsername}
          autoCapitalize="none"
        />
        <TextInput
          style={styles.input}
          placeholder="Password"
          placeholderTextColor="#94A3B8"
          value={password}
          onChangeText={setPassword}
          secureTextEntry
        />

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleLogin}
          disabled={loading}
        >
          {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Masuk</Text>}
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
    backgroundColor: '#0F172A',
  },
  card: {
    backgroundColor: 'rgba(30, 41, 59, 0.9)',
    padding: 25,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  logo: {
    width: 120,
    height: 120,
    alignSelf: 'center',
    marginBottom: 20,
    backgroundColor: '#FFFFFF',
    borderRadius: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#F8FAFC',
    textAlign: 'center',
    marginBottom: 5,
  },
  subtitle: {
    color: '#94A3B8',
    textAlign: 'center',
    marginBottom: 30,
  },
  input: {
    backgroundColor: 'rgba(15, 23, 42, 0.5)',
    color: '#fff',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  button: {
    backgroundColor: '#4F46E5',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonDisabled: {
    backgroundColor: '#4338CA',
    opacity: 0.7,
  },
  buttonText: {
    color: 'white',
    fontWeight: 'bold',
    fontSize: 16,
  }
});
