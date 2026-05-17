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
[![Tests](https://img.shields.io/badge/Tests-526%20passed-22c55e?style=flat-square&logo=checkmarx&logoColor=white)](#testing)
[![License](https://img.shields.io/badge/License-Pre--Release-f59e0b?style=flat-square)](#lisensi)
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
# Jalankan semua test (PHP)
php artisan test

# Unit tests saja
php artisan test --filter=Unit

# Feature tests saja
php artisan test --filter=Feature

# Test spesifik module
php artisan test --filter=ProposalTest

# Frontend unit test (Vitest, untuk hooks & lib helpers)
npm run test:js          # one-shot
npm run test:js:watch    # watch mode
```

**Status test saat ini (2026-05-17):**

| Suite | Tests | Assertions | Catatan |
|---|---:|---:|---|
| Full PHP suite | **526 passed** | 2856 | `php artisan test` — durasi ~37s |
| Frontend type-check | — | — | `npm run lint` (`tsc --noEmit`) ✅ |
| PHP code style | — | — | `./vendor/bin/pint --test` ✅ |
| Frontend build | — | — | `npm run build` ✅ |
| Vitest harness | — | — | `npx vitest --run` ✅ ready (test files akan ditambah saat Sprint 3 landing-polish) |

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

# Real-time notifications
php artisan reverb:start       # WebSocket worker for notification bell updates

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

### Post-MVP Wave 1 — Tier IMMEDIATE (Complete)

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| M14 · Rapat & Notulen | Agenda, attendee, keputusan, action items | ✅ |
| M15 · Absensi QR | QR token, check-in, manual fallback, anti-duplikat | ✅ |
| M16 · Sertifikat Digital | Issue, verifikasi publik, PDF generation | 〜 Maintenance |
| M27 · Mobile QR Camera Scanner (PWA) | html5-qrcode, continuous mode, fallback manual | ✅ |
| M44 · Web Push Notifications | VAPID, service worker push handler, banner permission | ✅ |
| M28 · Onboarding Wizard | 5 step (period → invite → proker → RAB → preview), auto-detect | ✅ |
| M25 · Rich Text Editor (Tiptap) | Proposal & LPJ editor, sanitize + render Tiptap JSON | ✅ |
| M26 · Real-Time Notifications | Laravel Reverb broadcasting, bell dropdown | ✅ |

### Inisiatif Khusus (Active Specs)

| Inisiatif | Path | Status |
|-----------|------|--------|
| Landing Polish | `.kiro/specs/landing-polish/` + `LANDING PAGE PLAN.md` | 🟡 Sprint 1 in progress (4/61 leaf task) |
| Super Admin V2 | `SUPER-ADMIN-V2-PLAN.md` | 🟡 Planning |
| QA | `.kiro/specs/prokerin-qa/` | 🟡 Active |

### Tier GROWTH (Planned)

| Modul | Deskripsi | Status |
|-------|-----------|--------|
| M30 · Kepanitiaan Mode | Ad-hoc committee, lifecycle 3-6 bulan, auto-archive 90 hari | ✅ |
| M31 · Public Proker Microsite | Halaman publik per proker, OG meta, gallery | ✅ |
| M39 · Surat Menyurat Generator | Template, sequencing, sign, send, bulk | 🔲 |
| M29 · Global Search (Cmd+K) | Laravel Scout, 5 model searchable | 🔲 |
| M43 · Calendar Sync (.ics) | Token-based public calendar feed | 🔲 |
| M40 · Inventory & Asset | Loan, return, integrasi M19 + M27 | 🔲 |

> **Tier MOMENTUM dihapus dari roadmap aktif (2026-05-17).** Modul M45/M46/M47/M41/M42 ditunda untuk prioritas deploy MVP. Akan di-re-evaluate pasca-deploy berdasarkan feedback user nyata.

### Modul Existing — FROZEN / Maintenance

Per `POST-MVP-ROADMAP.md` §0.5: scope tidak boleh ditambah, hanya bug fix critical.

| Modul | Catatan |
|-------|---------|
| M16 · Certificate | QA-OPEN-012 visual preview di-skip |
| M18 · Multi-Level Approval | Pro tier perk, no further enhancement |
| M19 · Handover Kepengurusan | Sebagian flow jalan, partial |
| M22 · Payment & Ticketing | Beta / Pro-tier opt-in only |
| M23 · AI Assistant | Freeze 6 bulan post-launch |
| M24 · Campus Dashboard B2B | Hibernate sampai paying customer signed |

> Lihat [`features.md`](features.md) untuk single source of truth status modul, dan [`POST-MVP-ROADMAP.md`](POST-MVP-ROADMAP.md) untuk spec detail tiap Post-MVP module.

---

## Roadmap

```
2026 Q2  ▸  MVP Complete (M01–M16) ✅
             Post-MVP Wave 1 IMMEDIATE — M27, M44, M28, M25, M26 ✅
             Launch QA: native mobile camera, OS push, two-browser Reverb smoke
             Tier GROWTH started — M30, M31 ✅
             Active spec: Landing Polish (4/61 task) 🟡

2026 Q3  ▸  Tier GROWTH lanjutan: M39 Surat Menyurat (killer feature)
             M29 Global Search (Cmd+K)
             M43 Calendar Sync (.ics)
             M40 Inventory & Asset
             Pre-launch hardening: BUG-FIX security + observability
             Public deploy 🚀

2027+    ▸  Pasca-deploy:
             - Re-evaluate Tier MOMENTUM (M45/M46/M47/M41/M42 — dihapus 2026-05-17)
             - Re-evaluate FROZEN modules (M22 Payment, M23 AI, M24 Campus)
             - Native mobile (PWA-first sampai signal jelas)
```

> Status modul aktual & migration timeline ada di [`features.md`](features.md). Spec detail per modul ada di [`POST-MVP-ROADMAP.md`](POST-MVP-ROADMAP.md).

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
- Baca [`features.md`](features.md) untuk status modul terkini & migration timeline (single source of truth).
- Baca [`POST-MVP-ROADMAP.md`](POST-MVP-ROADMAP.md) untuk spec detail Tier IMMEDIATE / GROWTH.
- Cek `.kiro/specs/` untuk spec aktif (mis. `landing-polish`, `prokerin-qa`).
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

Prokerin masih dalam fase active development. Lisensi resmi akan diumumkan saat MVP go-public.

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
