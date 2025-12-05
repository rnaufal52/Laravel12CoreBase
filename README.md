<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
    </a>
</p>

<h1 align="center">Core API - Laravel 12</h1>

## 📖 Tentang Project

**Core API Laravel 12** adalah starter kit / boilerplate yang dibangun di atas framework Laravel versi 12 terbaru. Project ini dirancang khusus untuk mempercepat pengembangan Backend API yang robust, skalabel, dan modern.

Tujuan utama dari core ini adalah menyediakan fondasi yang kuat dengan konfigurasi standar yang sering digunakan dalam pengembangan RESTful API.

### 🚀 Fitur Utama

- **Laravel 12 Ready**: Menggunakan fitur terbaru dari Laravel 12.
- **API Response Standard**: Helper untuk format response JSON yang konsisten (Success/Error).
- **Authentication**: Setup dasar untuk autentikasi (JWT).
- **Role & Permission**: Struktur dasar manajemen hak akses (Spatie).

## 🛠️ Persyaratan Sistem

Pastikan server Anda memenuhi persyaratan berikut:

- PHP >= 8.2
- Composer
- Database (MySQL/PostgreSQL)

## 📦 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan project di local machine:

1. **Clone Repository**
   ```bash
   git clone https://github.com/rnaufal52/Laravel12CoreBase.git
   cd Laravel12CoreBase
2. **Install Dependencies**
   ```bash
   composer install
3. **Environment Setup Salin file .env.example menjadi .env:**
   ```bash
   cp .env.example .env
4. **Generate Key**
   ```bash
   php artisan key:generate
5. **Konfigurasi Database Sesuaikan kredensial database di file .env, lalu jalankan migrasi**
   ```bash
   php artisan migrate --seed
6. **Jalankan Server**
   ```bash
   php artisan serve
