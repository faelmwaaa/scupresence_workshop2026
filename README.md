# Panduan Instalasi dan Penggunaan SCUpresence (Localhost)

Dokumen ini berisi panduan singkat untuk menjalankan aplikasi SCUpresence di komputer lokal (localhost) untuk keperluan evaluasi dan pengujian oleh Dosen Penguji/Pembimbing.

Karena aplikasi ini menggunakan **Google OAuth 2.0** yang sudah dikonfigurasi menggunakan kredensial milik pengembang, Anda hanya perlu melakukan *setup* basis data dan menjalankan *server* lokal.

## Prasyarat (Requirements)
Sebelum menjalankan aplikasi, pastikan komputer Anda telah terinstal perangkat lunak berikut:
1. **XAMPP** (atau Laragon) dengan PHP minimal versi 8.1
2. **Composer** (untuk manajemen dependensi PHP)
3. **Node.js** dan **NPM** (untuk manajemen aset *frontend*)
4. **Git** (Opsional, jika ingin melakukan *clone* secara langsung)

---

## Langkah Instalasi

### 1. Unduh (Download) Kode Sumber
*   Unduh repositori proyek dari GitHub dalam format ZIP, lalu ekstrak ke dalam folder `htdocs` (jika menggunakan XAMPP) atau `www` (jika menggunakan Laragon).
*   *Atau* gunakan terminal/Command Prompt untuk melakukan *clone*:
    ```bash
    cd C:\xampp\htdocs
    git clone https://github.com/faelmwaaa/scupresence_workshop2026.git
    cd scupresence_workshop2026
    ```

### 2. Pengaturan Environment (PENTING)
Secara bawaan (*default*), file `.env` tidak ikut terunggah ke GitHub demi keamanan. Anda harus menyalin file konfigurasi ini:
1. Salin file konfigurasi `.env` yang **telah diberikan oleh Mahasiswa/Pengembang** ke dalam root folder proyek.
2. File `.env` ini sangat penting karena berisi *Client ID* dan *Client Secret* Google OAuth agar fitur *Login with Google* dapat berjalan.
3. Pastikan konfigurasi database di dalam `.env` sudah benar:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=scupresence
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 3. Instalasi Dependensi
Buka Terminal / Command Prompt di dalam folder proyek, lalu jalankan dua perintah berikut secara berurutan:
```bash
# Menginstal library backend (Laravel)
composer install

# Menginstal library frontend (Vue & Tailwind)
npm install
```

### 4. Setup Database
1. Buka aplikasi **XAMPP Control Panel**.
2. Jalankan modul **Apache** dan **MySQL** (Klik tombol *Start*).
3. Buka browser dan akses `http://localhost/phpmyadmin`.
4. Buat sebuah database baru dengan nama: `scupresence`.
5. Anda dapat mengimpor database dengan dua cara:
   *   **Cara A (Import File SQL):** Klik tab *Import* di phpMyAdmin, lalu unggah file `database_backup.sql` yang telah disediakan oleh pengembang di dalam folder proyek.
   *   **Cara B (Migrasi Laravel):** Jalankan perintah berikut di terminal untuk membuat struktur tabel dari awal beserta data *dummy* (*dummy users*):
       ```bash
       php artisan migrate:fresh --seed
       ```

### 5. Menjalankan Aplikasi
Setelah database siap, Anda membutuhkan dua jendela terminal yang berjalan secara bersamaan di dalam folder proyek:

**Terminal 1 (Backend):**
```bash
php artisan serve
```
*(Ini akan menjalankan server Laravel di `http://127.0.0.1:8000`)*

**Terminal 2 (Frontend):**
```bash
npm run dev
```
*(Ini akan mengompilasi aset Vue.js secara *real-time*)*

---

## Panduan Pengujian (Testing)

Buka browser Anda dan akses alamat: **http://localhost:8000**

### Skenario Login Mahasiswa / Pelatih (Google Auth)
1. Klik tombol **Login dengan Google**.
2. Gunakan akun Google mana saja untuk masuk.
3. Karena Google Auth *Client ID* sudah dikonfigurasi ke akun pengembang (dan `URL callback` disetel ke `http://localhost:8000`), fitur login akan otomatis berhasil.
4. Setelah masuk, Anda akan diminta mengisi halaman pendaftaran (*Onboarding*) untuk memilih unit dan jabatan.

### Skenario Login Super Admin (Secret Door)
Sistem ini memblokir halaman login *default* untuk alasan keamanan. Untuk masuk sebagai **Super Admin**, ikuti langkah berikut:
1. Akses secara manual URL Rahasia: **http://localhost:8000/admin/secret-door**
2. Masukkan kredensial berikut (jika Anda menggunakan `php artisan migrate:fresh --seed` di langkah 4):
   *   **Email:** `admin@scupresence.com`
   *   **Password:** `password`
3. Dari dashboard Admin, Anda dapat menyetujui (*Approve*) keanggotaan mahasiswa/pelatih baru, menambah daftar UKM/ORMAWA, dan melihat rekapitulasi data.

---
**Catatan:** Pastikan koneksi internet aktif saat melakukan pengujian presensi agar sistem dapat mengambil koordinat GPS (Geolocation) secara akurat.
