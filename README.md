<div align="center">

<img src="public/vendor/viho/assets/images/logo/logo.png" alt="Prokerin Logo" width="180" />

# Prokerin

**Platform manajemen organisasi mahasiswa Indonesia — dari proker sampai LPJ, semua dalam satu tempat.**

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-18-61DAFB?style=flat-square&logo=react&logoColor=black)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat-square&logo=typescript&logoColor=white)](https://typescriptlang.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/Tests-190%20passed-22c55e?style=flat-square&logo=checkmarx&logoColor=white)](#testing)
[![License](https://img.shields.io/badge/License-MIT-f59e0b?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active%20Development-24695c?style=flat-square)](#roadmap)

<br />

[**🚀 Coba Sekarang**](https://prokerin.id) · [**📖 Dokumentasi**](https://docs.prokerin.id) · [**🐛 Laporkan Bug**](https://github.com/yourusername/prokerin/issues) · [**💡 Request Fitur**](https://github.com/yourusername/prokerin/discussions)

<br />

> *"Eliminating the 7 chaos areas of student organizations — one module at a time."*

</div>

---

## Apa itu Prokerin?

Prokerin adalah platform SaaS berbasis web & PWA yang dirancang khusus untuk kebutuhan organisasi mahasiswa Indonesia — BEM, HIMA, UKM, dan kepanitiaan event.

Kebanyakan ormawa masih mengandalkan kombinasi Google Drive, WhatsApp Group, dan spreadsheet Excel yang tersebar di mana-mana. Hasilnya: proposal bolak-balik revisi, RAB yang tidak sinkron, LPJ dikerjain H-1 deadline, dan saat pergantian pengurus semua konteks hilang.

Prokerin menyelesaikan ini dengan satu platform terintegrasi — dari perencanaan proker, manajemen task, keuangan, proposal otomatis, absensi QR, sampai LPJ dan serah terima kepengurusan.

---

## Fitur Utama

<table>
<tr>
<td width="50%">

### 📋 Manajemen Proker
Buat, pantau, dan arsip program kerja. Template satu klik yang otomatis generate task, RAB, draft proposal, dan checklist LPJ sekaligus.

### 📄 Proposal Generator
Isi data proker sekali, proposal terisi otomatis. Edit per section, submit untuk approval, export ke PDF atau DOCX.

### 💰 RAB & Keuangan
Budget planning, upload bukti realisasi, approval queue, dan perbandingan RAB vs realisasi — semua terlacak dengan rapi.

### ✅ Timeline & Task
Kanban board, calendar view, dan PIC assignment. Setiap task ada deadline, status, dan penanggung jawab yang jelas.

### 📁 Manajemen Dokumen
Upload, categorize, dan download dokumen dengan access control — private, restricted, atau committee-only. Storage di S3-compatible cloud.

</td>
<td width="50%">

### 📋 LPJ Generator
LPJ terbentuk dari data eksekusi nyata, bukan dokumen baru dari nol. Checklist readiness sebelum submit ke approval.

### 🤝 Rapat & Notulen
Catat agenda, keputusan, dan action items setiap rapat. Notulen tersimpan dan bisa diakses kapan saja.

### 📱 Absensi QR
Buat sesi absensi, generate QR code, anggota scan dari HP. Anti-duplikat, anti-expired, dan fallback manual untuk kondisi darurat.

### 🏅 Sertifikat Digital
Terbitkan sertifikat partisipasi yang bisa diverifikasi via URL publik. Langsung bisa dibagikan ke LinkedIn.

### 📊 Dashboard Monitoring
Overview metrik organisasi, proker prioritas, fokus mingguan, dan ringkasan anggota — semua database-backed dan tenant-scoped.

</td>
</tr>
</table>

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.4+, Laravel 13.x |
| **Frontend** | React 18 + TypeScript 5 via Inertia.js |
| **Styling** | Tailwind CSS + shadcn/ui (Radix UI) |
| **Database** | MySQL 8.x |
| **Auth** | Laravel Breeze + Google OAuth (Socialite) |
| **Authorization** | Spatie Laravel Permission |
| **Queue & Cache** | Redis |
| **Object Storage** | S3-compatible (Cloudflare R2 / AWS S3 / MinIO) |
| **Admin Panel** | Filament PHP |
| **PDF Export** | Browsershot / DomPDF fallback |
| **DOCX Export** | PHPWord |
| **Build Tool** | Vite |
| **Deployment** | VPS — Nginx + PHP-FPM + Supervisor |

---

## Arsitektur

Prokerin dibangun dengan **Modular Monolith** — semua domain business logic dalam satu Laravel app, tidak microservices.

```
prokerin/
├── app/
│   ├── Domain/          # Business logic per domain (Organization, Project, Finance, ...)
│   ├── Actions/         # Single-responsibility action classes
│   ├── Http/
│   │   ├── Controllers/ # Thin controllers — hanya delegate ke Actions
│   │   └── Requests/    # Form Request validation
│   ├── Models/          # Eloquent models
│   ├── Policies/        # Authorization policies
│   ├── Jobs/            # Queue jobs (export, notification, reminder)
│   ├── Notifications/   # Laravel notification classes
│   └── DTOs/            # Data Transfer Objects
├── resources/js/
│   ├── Pages/           # Inertia page components (one per route)
│   ├── Components/      # Shared React components
│   │   ├── ui/          # shadcn/ui base components
│   │   └── Viho/        # App shell components (sidebar, header, cards)
│   ├── hooks/           # Custom React hooks
│   ├── lib/             # Utilities, cn() helper
│   └── types/           # TypeScript types & interfaces
├── database/
│   ├── migrations/      # Additive, non-destructive migrations
│   └── seeders/         # Idempotent seeders for demo data
└── tests/
    ├── Unit/            # Business logic unit tests
    └── Feature/         # HTTP & integration feature tests
```

**Prinsip utama:**
- Controller tipis — validasi input → panggil Action → return Inertia response
- Business logic hanya di Actions atau Domain services, tidak pernah di Controller atau Model
- Semua data di-scope ke `organization_id` — multi-tenancy dijaga di setiap query
- Tidak ada REST API layer — semua data flow lewat Inertia web routes

---

## Memulai

### Prasyarat

- PHP 8.4+
- Composer 2.x
- Node.js 20+ & npm
- MySQL 8.x
- Redis
- (Opsional) Cloudflare R2 / AWS S3 untuk object storage

> **Catatan untuk macOS:** Jika menggunakan Homebrew, pastikan PHP 8.4 aktif.
> Tambahkan prefix ini ke semua perintah Composer/Artisan jika shell masih menunjuk PHP 8.3:
> ```bash
> PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH
> ```

### Instalasi

```bash
# 1. Clone repository
git clone https://github.com/yourusername/prokerin.git
cd prokerin

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Edit .env — set database, Redis, dan storage credentials
# Minimal yang harus diisi:
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# REDIS_HOST, REDIS_PORT

# 6. Jalankan migration + seeder
php artisan migrate
php artisan db:seed

# 7. Buat symlink storage
php artisan storage:link

# 8. Jalankan dev server (2 terminal terpisah)
php artisan serve      # Terminal 1
npm run dev            # Terminal 2
```

Buka `http://localhost:8000` dan login dengan:
```
Email    : owner@prokerin.test
Password : password
```

### Environment Variables Penting

```env
# Application
APP_NAME=Prokerin
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prokerin

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Object Storage (S3-compatible)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=prokerin
AWS_ENDPOINT=               # Contoh: https://[account].r2.cloudflarestorage.com

# Google OAuth (opsional untuk dev)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=hello@prokerin.id
```

> **Security:** Jangan pernah commit `.env`. Semua credentials hanya via environment variables.

---

## Testing

```bash
# Jalankan semua test
php artisan test

# Unit tests saja
php artisan test --filter=Unit

# Feature tests saja
php artisan test --filter=Feature

# Test spesifik module
php artisan test --filter=ProposalTest
```

**Status test saat ini:**

| Checkpoint | Tests | Assertions |
|-----------|-------|-----------|
| MVP baseline (M01–M13) | 181 ✅ | 712 |
| Setelah M14 (Rapat) | 183 ✅ | 755 |
| Setelah M15 (Absensi QR) | 190 ✅ | 804 |

Coverage target: minimum 70% untuk Actions dan Policies.
Prioritas: Authorization > Business Logic > API endpoints.

---

## Development Commands

```bash
# Development
php artisan serve              # Laravel dev server
npm run dev                    # Vite asset bundler (jalankan bersamaan)

# Production build
npm run build                  # Build frontend untuk production

# Code quality
./vendor/bin/pint              # Format PHP (Laravel Pint)
npm run lint                   # ESLint untuk JS/TS/JSX

# Database
php artisan migrate            # Jalankan pending migrations
php artisan migrate:fresh --seed  # ⚠️  DEV ONLY — reset + seed
php artisan db:seed            # Seed tanpa drop table

# Queue
php artisan queue:work         # Start queue worker
php artisan queue:listen       # Auto-reload on code change (dev)

# Cache
php artisan optimize:clear     # Clear setelah pull di staging/production

# Admin Panel
php artisan filament:make-resource [Name]   # Scaffold Filament resource
```

> ⚠️ Jangan jalankan `migrate:fresh` atau `db:seed` di production/staging.

---

## Status Modul

### MVP (Selesai ✅)

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| M01 · Auth & Account | Login, register, Google OAuth, email verification | ✅ |
| M02 · Organization | Setup organisasi, periode, logo, calendar overview | ✅ |
| M03 · Member & Role | Undang anggota, role matrix, permission guard | ✅ |
| M04 · Proker | Buat, edit, status flow, archive program kerja | ✅ |
| M05 · Template Proker | One-click generate proker dari template | ✅ |
| M06 · Task & Timeline | Kanban, calendar, PIC assignment, status update | ✅ |
| M07 · RAB & Finance | Budget, realisasi + bukti, approval queue | ✅ |
| M08 · Proposal Generator | Auto-fill, edit, approval flow, PDF/DOCX export | ✅ |
| M09 · Document Management | Upload, folder, download dengan access control | ✅ |
| M10 · LPJ Generator | Checklist, readiness guard, approval, export | ✅ |
| M11 · Dashboard | Aggregate metrics, proker prioritas, focus mingguan | ✅ |
| M12 · Notifikasi | Email + database channel, deadline reminder | ✅ |
| M13 · Admin Panel | Filament internal admin (Organization, User, Export) | ✅ |

### Post-MVP Wave 1 (Selesai ✅)

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| M14 · Rapat & Notulen | Agenda, attendee, keputusan, action items | ✅ |
| M15 · Absensi QR | QR token, check-in, manual fallback, anti-duplikat | ✅ |
| M16 · Sertifikat Digital | Issue, verifikasi publik, PDF generation | 〜 Partial |

### Post-MVP Aktif & Planned

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| M17 · WhatsApp Reminder | Notifikasi via WhatsApp channel | 🔲 |
| M18 · Multi-Level Approval | Workflow approval bertahap | 🔲 |
| M19 · Handover Kepengurusan | Serah terima antar periode | 〜 Partial |
| M20 · Sponsor & Vendor DB | Direktori sponsor dan vendor per org | 🔲 |
| M21 · Event Registration | Pendaftaran publik per event | 🔲 |
| M22 · Payment & Ticketing | Tiket berbayar via Midtrans | 🔲 |
| M23 · AI Assistant | Draft proposal, ringkasan LPJ berbasis AI | 🔲 |
| M24 · Campus Dashboard B2B | Agregat lintas organisasi untuk rektorat | 🔲 |

> Lihat [`features.md`](features.md) dan [`features-extended.md`](features-extended.md) untuk spec lengkap setiap modul.

---

## Roadmap

```
2026 Q2  ▸  MVP Complete (M01–M13) ✅
             Post-MVP Wave 1: M14 Rapat, M15 Absensi QR ✅
             M16 Sertifikat Digital 🔲

2026 Q3  ▸  UX Critical: M25 Rich Text Editor
             M26 Real-Time Notifications
             M27 QR Camera Scanner (PWA)
             M28 Onboarding Wizard
             Landing Page (prokerin.id)

2026 Q4  ▸  Growth: M29 Global Search
             M30 Kepanitiaan Mode
             M31 Public Proker Microsite
             M32 Template Marketplace
             M17 WhatsApp Reminder
             M18 Multi-Level Approval

2027 Q1  ▸  Enterprise: M19 Handover (complete)
             M21 Event Registration
             M22 Payment & Ticketing
             M33 Prokerin Academy
             M24 Campus Dashboard B2B

2027 Q2  ▸  Platform: M23 AI Assistant
             M34 Smart Deadline Prediction
             M37 Org Health Analytics
             M38 Prokerin Pay
```

---

## Kontribusi

Prokerin masih dalam fase active development. Kontribusi sangat disambut!

### Setup untuk Kontributor

```bash
# Fork repo, lalu clone fork kamu
git clone https://github.com/YOUR_USERNAME/prokerin.git
cd prokerin

# Ikuti langkah instalasi di atas
# Buat branch untuk fitur/fix kamu
git checkout -b feat/nama-fitur
```

### Konvensi Commit

```
feat     : fitur baru
fix      : perbaikan bug
refactor : refactor tanpa perubahan fungsional
style    : perubahan styling/formatting
docs     : perubahan dokumentasi
test     : penambahan atau perubahan test
chore    : perubahan config atau tooling
db       : migration atau seeder
```

Contoh:
```
feat: add digital certificate module (M16)
fix: scope organization query to authenticated user — prevent data leak
refactor: extract budget calculation into CalculateBudgetTotalAction
db: add attendance_sessions table with qr token support
```

### Sebelum Submit Pull Request

```bash
# Pastikan semua test hijau
php artisan test

# Format PHP code
./vendor/bin/pint

# Lint frontend
npm run lint

# Build production berhasil
npm run build
```

### Panduan Pengembangan

- Baca [`AGENTS.md`](AGENTS.md) untuk arsitektur, konvensi, dan aturan yang wajib diikuti.
- Baca [`features.md`](features.md) untuk status dan spec modul M01–M24.
- Baca [`features-extended.md`](features-extended.md) untuk roadmap M25–M38 dan landing page.
- **Jangan mulai modul baru kalau test suite sedang merah.**
- Satu PR untuk satu perubahan logis — jangan mix fitur yang tidak berkaitan.
- Setiap fitur baru wajib disertai feature test. Coverage target: 70% untuk Actions & Policies.

---

## Multi-Tenancy & Keamanan

Prokerin mengimplementasikan multi-tenancy berbasis `organization_id`:

- Setiap query data organisasi **wajib** di-scope ke `organization_id` authenticated user.
- Authorization menggunakan kombinasi **Laravel Policies** (model-level) + **Spatie Laravel Permission** (role-level).
- File disimpan di private S3 bucket dan diakses via **signed URL** yang di-generate server-side — bukan URL publik langsung.
- `organization_id` dan role **tidak pernah dipercaya dari client** — selalu di-derive dari session/auth.
- Setiap controller method **wajib** memanggil `$this->authorize()`.

---

## Lisensi

Prokerin dilisensikan di bawah [MIT License](LICENSE).

---

## Kontak & Support

- **Website:** [prokerin.id](https://prokerin.id)
- **Email:** hello@prokerin.id
- **Instagram:** [@prokerin.id](https://instagram.com/prokerin.id)
- **Issues & Bug Report:** [GitHub Issues](https://github.com/yourusername/prokerin/issues)
- **Feature Request & Diskusi:** [GitHub Discussions](https://github.com/yourusername/prokerin/discussions)

---

<div align="center">

Dibuat dengan ❤️ untuk organisasi mahasiswa Indonesia

**[prokerin.id](https://prokerin.id)**

</div>
