# Attendance Management - Backend (Laravel)

Tugas backend untuk sistem absensi berdasarkan spesifikasi dan flowchart yang diberikan.

## 📌 Spesifikasi

- Bahasa: PHP (Laravel)
- Database: MySQL
- Berdasarkan skema ERD dan flowchart yang diberikan
- API-only (tanpa blade)

## 🎯 Endpoint yang Disediakan

### 🔹 Departemen
| Method | Endpoint              | Keterangan             |
|--------|-----------------------|------------------------|
| GET    | /api/departments      | List semua departemen |
| POST   | /api/departments      | Tambah departemen     |
| PATCH  | /api/departments/{id} | Update departemen     |
| DELETE | /api/departments/{id} | Hapus departemen      |

### 🔹 Karyawan
| Method | Endpoint              | Keterangan             |
|--------|-----------------------|------------------------|
| GET    | /api/employees        | List semua karyawan   |
| POST   | /api/employees        | Tambah karyawan       |
| PATCH  | /api/employees/{id}   | Update karyawan       |
| DELETE | /api/employees/{id}   | Hapus karyawan        |

### 🔹 Absensi
| Method | Endpoint                 | Keterangan                  |
|--------|--------------------------|-----------------------------|
| POST   | /api/attendances         | Clock-in (absen masuk)     |
| PATCH  | /api/attendances/{id}    | Clock-out (absen keluar)   |
| GET    | /api/attendances         | List log absensi karyawan  |

- Log absensi mendukung filter `search`, `per_page`, dan berdasarkan `tanggal` serta `departemen`.

## ✅ Validasi

- Tidak bisa absen masuk lebih dari 1 kali per hari.
- Clock-in lewat dari `max_clock_in_time` wajib isi deskripsi.
- Clock-out sebelum `max_clock_out_time` wajib isi deskripsi.
- Jika sudah telat clock-in, tidak bisa dianggap early leave.
- Status absensi:
  - `Late`, `Early Leave`, atau kosong jika normal.

## 📦 Instalasi

- git clone [https://github.com/username/backend-attendance.git](https://github.com/dnshaniff/attendances_backend.git)
- cd attendances_backend
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan serve
