# Prokerin

Prokerin adalah web app/PWA SaaS untuk organisasi mahasiswa Indonesia agar pengurus bisa mengelola program kerja dari awal sampai akhir dalam satu sistem: organisasi, anggota, role, proker, task, timeline, RAB, proposal, dokumen, rapat, absensi QR, sertifikat, LPJ, handover, microsite publik, surat menyurat, kalender, notifikasi, pencarian global, dan inventaris.

Target utama Prokerin adalah BEM, HIMA, UKM, komunitas kampus, dan kepanitiaan event. Masalah yang diselesaikan adalah 7 chaos klasik organisasi mahasiswa: proker telat, proposal lambat, task tidak terpantau, finance berantakan, dokumentasi tercecer, LPJ terlambat, dan handover pengurus buruk.

Dokumen ini adalah dokumentasi utama project. Root documentation sengaja dibatasi hanya:

- `README.md`: penjelasan produk, fitur, cara setup, cara pakai, flow pengguna, status, dan operasional.
- `AGENTS.md`: blueprint teknis, arsitektur, aturan pengembangan, guardrail keamanan, dan instruksi untuk AI/agent/developer.

Tidak ada dokumen roadmap/status terpisah lagi di root. Jika ada perubahan status fitur, alur, aturan, atau setup, update README dan/atau AGENTS langsung.

---

## Status Project

- Version: v0.1.0
- Status: Active development, MVP + Post-MVP Wave 1 feature-complete
- Verifikasi terakhir: 2026-05-17
- Full PHP test suite: 551 passed / 2981 assertions
- Frontend build: pass
- TypeScript check: pass
- PHP style: pass
- Sisa kerja utama: launch/device QA, deploy hardening, landing polish, dan perbaikan bug dari penggunaan nyata

Semua modul fitur aktif di roadmap saat ini sudah selesai secara code, route/UI/backend integration, dan automated test. Beberapa smoke test yang membutuhkan perangkat fisik atau environment production tetap masuk launch QA.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.4+, Laravel yang terpasang di project |
| Frontend | React + TypeScript via Inertia.js |
| Styling | Tailwind CSS, shadcn/ui, Radix UI primitives |
| App architecture | Laravel web routes + Inertia pages, bukan standalone SPA |
| Database | MySQL 8.x |
| ORM | Eloquent |
| Auth | Laravel Breeze + Google OAuth via Socialite |
| Authorization | Spatie Laravel Permission + policy/action-level guards |
| Queue/cache | Redis |
| Realtime | Laravel Reverb |
| Web push | laravel-notification-channels/webpush + service worker |
| Object storage | S3-compatible storage: Cloudflare R2, MinIO, AWS S3 |
| Admin panel | Filament PHP |
| PDF export | Browsershot preferred, DomPDF fallback |
| DOCX export | PHPWord |
| Build tool | Vite |
| Deployment target | VPS: Nginx + PHP-FPM + Supervisor |

---

## Product Principles

Prokerin bukan sekadar task manager umum. Prokerin adalah operational system untuk organisasi kampus Indonesia.

Prinsip produk:

- Proker adalah pusat data. Proposal, RAB, task, dokumen, absensi, LPJ, microsite, surat, dan sertifikat idealnya terhubung ke proker.
- Organisasi adalah tenant. Semua data organisasi wajib scoped by `organization_id`.
- User bisa punya banyak organisasi, dengan role berbeda di setiap organisasi.
- Inertia-first. Semua screen utama adalah Laravel web route yang render React page via Inertia.
- Tidak membuat REST API umum kecuali endpoint kecil yang memang AJAX-only, misalnya search, notification read, subscription.
- Admin operational memakai Filament, bukan membangun admin panel React duplikat.
- Heavy work masuk queue: export PDF/DOCX, email, push, reminder, dan job periodik.
- PWA-first, native mobile ditunda sampai ada signal kuat dari penggunaan nyata.

---

## Role dan Hak Akses

Role organisasi yang dipakai project:

| Role | Fungsi umum |
|---|---|
| `super_admin` | Admin platform/internal, akses Filament internal |
| `organization_owner` | Pemilik organisasi, akses penuh tenant |
| `organization_admin` | Admin organisasi, hampir setara owner untuk operasional |
| `project_lead` | PIC/ketua proker, mengelola proker dan task terkait |
| `secretary` | Proposal, LPJ, surat, dokumen, rapat, administrasi |
| `treasurer` | RAB, realisasi, finance approval |
| `division_coordinator` | Koordinasi divisi/task |
| `member` | Anggota/panitia biasa |
| `viewer` | Read-only limited |

Aturan penting:

- Role organisasi disimpan di `organization_members`.
- Role proker disimpan di `project_members`.
- Jangan percaya role atau `organization_id` dari client.
- Semua query tenant data harus derive organisasi dari session active organization atau membership user.
- Cross-tenant access harus 403 atau 404 sesuai konteks.

---

## Struktur Project

```text
prokerin/
├── app/
│   ├── Actions/                 # Business workflow single-responsibility
│   ├── Domain/                  # Enum/value object/domain helpers per domain
│   ├── DTOs/                    # Data transfer objects
│   ├── Filament/                # Internal admin resources/pages/widgets
│   ├── Http/
│   │   ├── Controllers/         # Thin controllers
│   │   ├── Middleware/
│   │   └── Requests/            # Form request validation + authorization
│   ├── Jobs/                    # Queue jobs
│   ├── Models/                  # Eloquent models
│   ├── Notifications/           # Laravel notifications
│   ├── Policies/                # Authorization policies
│   └── Support/                 # Shared helpers/gates
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── manifest.json
│   ├── service-worker.js
│   └── vendor/viho/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── hooks/
│   │   ├── Layouts/
│   │   ├── lib/
│   │   ├── Pages/
│   │   └── types/
│   └── views/app.blade.php
├── routes/
│   ├── web.php
│   ├── channels.php
│   └── console.php
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Setup Lokal

### Prasyarat

- PHP 8.4+
- Composer 2.x
- Node.js 20+ dan npm
- MySQL 8.x
- Redis
- Browser modern
- Optional: MinIO/R2/S3 untuk storage flow lengkap

Catatan macOS lokal: jika shell masih menunjuk PHP 8.3, prefix Composer/Artisan dengan:

```bash
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH
```

### Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Jalankan app:

```bash
php artisan serve
npm run dev
```

Queue dan realtime untuk flow lengkap:

```bash
php artisan queue:work
php artisan reverb:start
```

Service worker, web push, dan PWA berjalan dari file public:

- `public/manifest.json`
- `public/service-worker.js`
- `public/icons/icon-192.png`
- `public/icons/icon-512.png`

### Akun QA Seeded

Seeder menyediakan akun demo/QA. Akun yang paling sering dipakai:

```text
owner@prokerin.test / password
admin@prokerin.test / password
secretary@prokerin.test / password
treasurer@prokerin.test / password
lead@prokerin.test / password
member@prokerin.test / password
owner2@prokerin.test / password
superadmin@prokerin.test / password
```

Jika kredensial berubah, cek `database/seeders/DatabaseSeeder.php`.

---

## Environment Penting

Minimal local:

```env
APP_NAME=Prokerin
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=prokerin
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

Storage:

```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=prokerin
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Google OAuth:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Reverb:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Web Push:

```env
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:halo@prokerin.id
```

Jangan commit `.env`, secret, token, key, atau credential apa pun.

---

## Commands

```bash
# Development
php artisan serve
npm run dev
php artisan queue:work
php artisan reverb:start

# Build
npm run build

# Quality
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
npm run lint

# Test
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test --filter=Feature
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test --filter=Unit

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Storage/cache
php artisan storage:link
php artisan optimize:clear

# Admin panel
php artisan filament:make-resource Name
```

Larangan:

- Jangan pakai `npm` untuk package PHP. Gunakan Composer.
- Jangan jalankan `migrate:fresh` di staging/production.
- Jangan jalankan seeder di production tanpa rencana data yang jelas.
- Setelah pull di staging/production, jalankan `php artisan optimize:clear`.

---

## Fitur dan Flow Penggunaan

Bagian ini menjelaskan fitur Prokerin dari sudut pandang user.

### 1. Auth dan Akun

Fitur:

- Register/login email password.
- Login Google OAuth.
- Email verification.
- Password reset.
- Profile update.
- Calendar sync token di profile.

Flow:

1. User register.
2. User verify email jika verification aktif.
3. User membuat organisasi atau menerima invitation.
4. Setelah punya organisasi, user masuk dashboard sesuai role.
5. User bisa switch active organization jika tergabung di banyak organisasi.

Catatan:

- Platform admin/super admin diarahkan ke panel internal.
- Campus admin diarahkan ke dashboard kampus.
- User biasa masuk dashboard organisasi.

### 2. Organization Setup

Fitur:

- Membuat organisasi permanen.
- Membuat kepanitiaan event.
- Update profil organisasi.
- Upload logo organisasi.
- Periode aktif.
- Organization switcher.
- Onboarding wizard 5 step.

Flow organisasi permanen:

1. Owner membuat organisasi.
2. Owner mengisi nama, deskripsi, dan logo.
3. Owner membuat periode aktif.
4. Owner mengundang pengurus.
5. Owner membuat proker pertama.
6. Wizard onboarding selesai atau bisa di-skip.

Flow kepanitiaan:

1. User memilih mode kepanitiaan.
2. User mengisi nama event dan tanggal event.
3. Sistem set `mode = kepanitiaan`.
4. Sistem set `auto_archive_at = event_date + 90 days`.
5. Dashboard memakai variant kepanitiaan.
6. Menu yang tidak relevan seperti periode/handover disembunyikan.

### 3. Member, Role, dan Invitation

Fitur:

- Invite anggota via email/token.
- Accept/decline invitation.
- Update role anggota.
- Remove member.
- Role matrix.
- Guard agar owner terakhir tidak dihapus sembarangan.

Flow:

1. Owner/admin membuka halaman anggota.
2. Owner/admin mengirim invitation dengan role tertentu.
3. Calon anggota membuka link invitation.
4. Jika belum punya akun, user register/login dulu.
5. User accept invitation.
6. Membership dibuat di `organization_members`.
7. Role menentukan menu dan action yang tersedia.

### 4. Proker Management

Fitur:

- List proker tenant-scoped.
- Create/edit proker.
- Assign project lead.
- Status flow.
- Archive/delete soft flow.
- Detail proker.
- Project members.

Flow:

1. Owner/admin/role terkait membuat proker.
2. Pilih template type atau isi manual.
3. Set tanggal mulai dan selesai.
4. Assign project lead dari anggota organisasi.
5. Sistem membuat draft proker.
6. User menambahkan task, RAB, dokumen, proposal, microsite, dan LPJ.
7. Status bergerak dari draft ke planning/execution/completed sesuai flow.

### 5. Template Proker

Fitur:

- Template Seminar, Workshop, Kompetisi, Makrab, dan template sejenis.
- One-click generate:
  - Task awal.
  - Budget line awal.
  - Proposal draft.
  - LPJ checklist.

Flow:

1. User membuka template proker.
2. User memilih template sesuai jenis kegiatan.
3. User mengisi nama proker, tanggal, dan lead.
4. Sistem membuat proker beserta starter pack operasional.
5. User tinggal menyesuaikan detail.

### 6. Task dan Timeline

Fitur:

- Kanban task.
- Calendar task.
- Assignment PIC.
- Division field.
- Deadline.
- Status update.
- Overdue badge.
- Reminder deadline.

Flow:

1. Project lead membuat task.
2. Task diberi PIC dan deadline.
3. Anggota melihat task di dashboard/kanban.
4. PIC update status.
5. Jika deadline mendekat/terlewat, sistem bisa mengirim reminder.
6. Progress proker dihitung dari task completion.

### 7. RAB dan Finance

Fitur:

- Budget line per proker.
- Planned amount.
- Realized amount.
- Upload receipt.
- Approval budget/realisasi.
- Finance overview.
- RAB vs realisasi.

Flow:

1. Treasurer/project lead membuat budget line.
2. Budget masuk draft/review.
3. Role finance menyetujui atau menolak.
4. Saat pelaksanaan, user mengisi realisasi dan bukti.
5. Sistem menghitung selisih RAB vs realisasi.
6. Data finance dipakai untuk LPJ dan dashboard.

### 8. Proposal Generator

Fitur:

- Proposal draft per proker.
- Rich text editor Tiptap.
- Sanitizer rich text backend.
- Approval proposal.
- Export PDF/DOCX.
- AI suggestion tersedia tapi frozen/maintenance.

Flow:

1. Sistem membuat draft proposal dari template proker.
2. Secretary/project lead mengedit section.
3. User menyimpan draft.
4. User submit proposal untuk approval.
5. Approver memberi keputusan approve/revision.
6. Jika approved, proposal bisa diexport PDF/DOCX.

### 9. Document Management

Fitur:

- Upload dokumen.
- Folder.
- Project-related document.
- Visibility private/restricted/public-ish sesuai implementasi.
- Download via signed/controlled route.
- Export queue document.

Flow:

1. User membuka upload center.
2. User memilih file, folder, proker terkait, dan visibility.
3. Server validasi MIME dan size.
4. File disimpan ke storage private/S3-compatible.
5. Metadata dokumen tersimpan di database.
6. Download lewat route yang melakukan authorization.

### 10. LPJ Generator

Fitur:

- Checklist LPJ per proker.
- Required item.
- Completion status.
- Review/approval LPJ.
- Export LPJ.
- AI summary frozen/maintenance.

Flow:

1. LPJ checklist dibuat dari template/proker.
2. Panitia melengkapi item wajib.
3. Data finance, dokumen, task, dan proposal menjadi referensi.
4. User submit LPJ review.
5. Approver approve atau minta revisi.
6. LPJ export berjalan via job.

### 11. Dashboard

Fitur:

- Dashboard variant by role.
- Dashboard pimpinan.
- Dashboard sekretaris.
- Dashboard bendahara.
- Dashboard operasional/member.
- Dashboard kepanitiaan.
- Metrics dan focus widgets.

Flow:

1. User login.
2. Sistem resolve active organization.
3. Sistem resolve role/dashboard variant.
4. Dashboard payload dibuat tenant-scoped.
5. UI menampilkan metrik yang relevan dengan role.

### 12. Notifications

Fitur:

- Database notifications.
- Email notification.
- Web push.
- Real-time notification bell.
- Notification read/read all.
- Task deadline reminders.
- Approval notifications.

Flow:

1. Event domain terjadi, misalnya proposal approved atau task deadline.
2. Notification class dikirim ke user target.
3. Database notification tersimpan.
4. Listener broadcast ke Reverb.
5. Notification bell update tanpa refresh.
6. Jika user mengaktifkan web push dan VAPID configured, OS notification dikirim.

### 13. Filament Internal Admin

Fitur:

- Internal admin panel.
- User/organization operational resources.
- Feature flag management.
- System health/dashboard widgets.
- Impersonation with security constraints.

Flow:

1. Super admin login.
2. Super admin membuka `/internal-admin`.
3. Filament mengecek `super_admin`.
4. Admin dapat melihat resource internal dan melakukan operasi terbatas.

### 14. Rapat dan Notulen

Fitur:

- Meeting list.
- Agenda.
- Attendee.
- Meeting attendance status.
- Minutes.
- Decisions.
- Action items.
- Export minutes.
- WhatsApp alert preparation.

Flow:

1. Secretary/role terkait membuat meeting.
2. Tambahkan agenda dan attendee.
3. Saat meeting, status attendance dicatat.
4. Setelah meeting, notulen dipublish.
5. Action item bisa ditindaklanjuti sebagai task.
6. Minutes bisa diexport.

### 15. Absensi QR

Fitur:

- Attendance session.
- QR token issue/revoke.
- QR image.
- Camera scanner PWA via `html5-qrcode`.
- Manual fallback.
- Anti-duplicate check-in.
- Expired token guard.
- CSV export.

Flow panitia:

1. Panitia membuat sesi absensi.
2. Panitia generate QR token.
3. Sistem menampilkan QR image.
4. Peserta scan QR via kamera.
5. Sistem mencatat hadir jika valid.
6. Panitia export CSV jika diperlukan.

Flow scanner:

1. User buka halaman attendance.
2. Klik scan QR.
3. Browser meminta camera permission.
4. Scanner membaca token.
5. Jika token absensi, sistem post ke attendance check-in.
6. Jika token inventaris, scanner redirect ke detail inventory QR.

### 16. Sertifikat Digital

Status: maintenance/frozen untuk enhancement baru.

Fitur:

- Certificate template.
- Issue certificate.
- Public verification URL.
- PDF generation.
- Download route.

Flow:

1. Admin membuat template sertifikat.
2. Admin memilih recipient/proker/meeting.
3. Sistem issue certificate number dan verification token.
4. PDF dibuat.
5. Recipient/public bisa verifikasi via `/verify/{token}`.

### 17. Web Push Notifications

Fitur:

- Push subscription.
- Service worker push listener.
- Notification click handler.
- Enable banner di layout.
- VAPID config.

Flow:

1. User login.
2. Banner menawarkan aktivasi browser notification.
3. User approve permission.
4. Browser membuat push subscription.
5. Subscription disimpan server-side.
6. Notification penting dikirim ke OS/browser.

Launch QA yang masih perlu perangkat fisik:

- Permission prompt Chrome Android.
- Permission prompt Safari iOS.
- Delivery saat tab tidak aktif.

### 18. Real-Time Notifications

Fitur:

- Laravel Reverb.
- Broadcast database notification.
- Echo client.
- Notification bell dropdown.
- Recent endpoint fallback.

Flow:

1. Jalankan `php artisan reverb:start`.
2. User membuka app.
3. Frontend subscribe ke private user channel.
4. Event notification dibuat.
5. Bell badge bertambah tanpa refresh.

### 19. Onboarding Wizard

Fitur:

- Step 1 period/setup.
- Step 2 invite member.
- Step 3 create proker.
- Step 4 setup RAB.
- Step 5 preview.
- Auto-detect data existing.
- Complete/skip route.

Flow:

1. Owner baru login.
2. Sistem cek status onboarding.
3. Wizard tampil jika belum complete/skip.
4. User menyelesaikan step atau data existing otomatis dianggap complete.
5. Step terakhir set `onboarding_completed_at`.

### 20. Rich Text Editor

Fitur:

- Tiptap editor.
- Toolbar.
- Read-only renderer.
- Sanitizer server-side.
- Renderer HTML untuk export.
- Backward-compatible dengan body string legacy.

Flow:

1. User edit proposal/surat content.
2. Frontend menyimpan Tiptap JSON.
3. Backend sanitizes allowed nodes/marks.
4. Data disimpan.
5. Renderer mengubah JSON menjadi HTML aman untuk display/export.

### 21. Kepanitiaan Mode

Fitur:

- Organization mode `kepanitiaan`.
- Event date.
- Auto archive date.
- Simplified dashboard.
- Hide period/handover/role matrix yang tidak relevan.
- Daily auto archive job.

Flow:

1. User membuat workspace kepanitiaan.
2. Sistem set tanggal event dan archive window.
3. Dashboard memakai payload kepanitiaan.
4. Setelah `auto_archive_at`, scheduler mengarsipkan jika masih active.

### 22. Public Proker Microsite

Fitur:

- URL publik `/e/{orgSlug}/{prokerSlug}`.
- Publish/unpublish.
- Banner.
- Gallery.
- Description.
- Contact.
- Location/maps URL.
- OG/Twitter meta.
- Event registration CTA.
- Cache public payload.

Flow:

1. Admin membuka setting microsite dari detail proker.
2. Admin mengisi deskripsi, lokasi, kontak, banner, gallery.
3. Admin publish.
4. Public membuka URL microsite tanpa login.
5. Jika registration aktif, visitor klik CTA daftar event.

### 23. Surat Menyurat Generator

Fitur:

- Letter templates.
- Letter number sequence.
- Letter type enum.
- Draft letter.
- Submit for signing.
- Sign letter.
- Generate PDF.
- Mark sent.
- Bulk participation certificates/letters.

Flow:

1. Owner/admin membuat template surat.
2. Secretary membuat draft dari template.
3. Sistem generate nomor surat berdasarkan pattern.
4. Draft bisa terkait proker.
5. Secretary submit for signing.
6. Signatory sign.
7. Sistem generate PDF.
8. Surat ditandai sent atau didownload.

### 24. Global Search

Fitur:

- Cmd+K/Ctrl+K modal.
- Search projects, tasks, documents, meetings, members.
- Tenant scoped.
- Document visibility guard.
- Recent searches.

Flow:

1. User tekan Cmd+K atau klik search.
2. Frontend debounce query.
3. Endpoint `/search` menjalankan GlobalSearchAction.
4. Hasil dikelompokkan per kategori.
5. User navigasi pakai keyboard atau klik result.

### 25. Calendar Sync

Fitur:

- Generate/regenerate token.
- Public `.ics` feed by token.
- Feed meetings, project deadlines, task deadlines.
- Invalid token return empty VCALENDAR.

Flow:

1. User buka profile.
2. User generate calendar sync token.
3. User copy feed URL.
4. User subscribe di Google Calendar/Apple/Outlook.
5. Calendar provider fetch feed berkala.

### 26. Inventory & Asset Management

Fitur:

- Inventory items.
- Condition: excellent, good, needs repair, broken.
- Status: available, loaned, lost, archived.
- QR token per item.
- Loan request.
- Approve loan.
- Return loan with condition.
- Overdue loan notification job.
- QR lookup.
- Handover inventory snapshot.

Flow admin:

1. Secretary/admin/owner membuka Inventaris.
2. Tambah item, kategori, lokasi, kondisi, tanggal beli, nominal beli.
3. Sistem generate QR token unik.
4. QR bisa ditempel ke barang.

Flow peminjaman:

1. Member membuka detail item.
2. Member klik pinjam dan isi target kembali.
3. Loan dibuat status pending.
4. Owner/admin/secretary approve.
5. Item berubah status loaned.
6. Saat kembali, admin catat kondisi return.
7. Jika same, item available.
8. Jika damaged, condition menjadi needs repair.
9. Jika lost, status menjadi lost.

Flow QR:

1. User scan QR inventaris.
2. Scanner mendeteksi `/inventory/qr/{token}` atau `prokerin-inventory:{token}`.
3. User diarahkan ke detail item.
4. Route tetap require auth dan tenant guard.

Flow handover:

1. Owner initiate handover.
2. Snapshot menghitung total inventory, available, loaned, broken, lost.
3. Checklist handover otomatis menambahkan verifikasi inventaris.

### 27. Handover Kepengurusan

Status: maintenance/partial.

Fitur:

- Handover package.
- Snapshot proker/task/dokumen/finance/LPJ/inventory.
- Handover items.
- Submit/accept.
- Assign incoming owner/period.
- Export package.

Flow:

1. Owner membuat package handover.
2. Sistem mengambil snapshot kondisi organisasi.
3. Checklist handover dibuat.
4. Item diselesaikan satu per satu.
5. Package submitted.
6. Incoming owner menerima.

### 28. Payment & Ticketing

Status: beta/pro-tier opt-in only, jangan dipromosikan sebagai fitur utama.

Fitur:

- Payment/ticketing tables.
- Midtrans webhook controller.
- Event registration integration terbatas.

### 29. AI Assistant

Status: maintenance/frozen untuk ekspansi.

Fitur yang ada:

- Draft proposal with AI.
- Summarize LPJ with AI.
- Usage logs.

Aturan:

- Jangan tambah AI feature baru sebelum ada keputusan post-launch.
- Jangan hardcode key.
- Semua usage harus logged.

### 30. Campus Dashboard

Status: hibernate sampai ada paying campus customer.

Fitur:

- Campus dashboard tables.
- Campus admin routing.
- High-level organization monitoring.

---

## Flow Pengguna End-to-End

### Flow Owner Organisasi Baru

1. Register/login.
2. Buat organisasi.
3. Upload logo dan isi profil.
4. Buat periode aktif.
5. Undang admin, sekretaris, bendahara, project lead, dan anggota.
6. Jalankan onboarding wizard.
7. Buat proker dari template.
8. Review dashboard.
9. Monitor proposal/RAB/task sampai proker selesai.
10. Jalankan handover saat periode berakhir.

### Flow Sekretaris

1. Login.
2. Buka proposal editor.
3. Edit proposal dengan rich text.
4. Submit approval.
5. Kelola dokumen administrasi.
6. Buat rapat dan notulen.
7. Buat surat resmi.
8. Kelola LPJ checklist.
9. Export proposal/LPJ/surat saat approved.

### Flow Bendahara

1. Login.
2. Buka finance dashboard.
3. Review budget line.
4. Approve/reject RAB.
5. Cek realisasi dan bukti transaksi.
6. Pantau selisih RAB vs realisasi.
7. Pastikan data finance siap untuk LPJ.

### Flow Project Lead

1. Login.
2. Buka proker yang dipimpin.
3. Atur task dan PIC.
4. Pantau kanban dan deadline.
5. Koordinasi dokumen, proposal, finance, absensi.
6. Publish microsite jika proker publik.
7. Pastikan LPJ selesai.

### Flow Member/Panitia

1. Login atau accept invitation.
2. Lihat dashboard dan task.
3. Update task yang menjadi tanggung jawabnya.
4. Scan QR absensi saat rapat/event.
5. Upload dokumen jika diberi akses.
6. Pinjam inventory jika perlu.
7. Terima notifikasi deadline/approval.

### Flow Public Visitor

1. Buka microsite proker publik.
2. Membaca informasi event.
3. Klik daftar event jika registration aktif.
4. Mengisi form pendaftaran.
5. Menerima konfirmasi.
6. Bisa verifikasi sertifikat publik jika punya verification URL.

---

## Status Modul Ringkas

| Modul | Status |
|---|---|
| M01 Auth & Account | Complete |
| M02 Organization | Complete |
| M03 Member & Role | Complete |
| M04 Proker | Complete |
| M05 Template Proker | Complete |
| M06 Task & Timeline | Complete |
| M07 RAB & Finance | Complete |
| M08 Proposal Generator | Complete |
| M09 Document Management | Complete |
| M10 LPJ Generator | Complete |
| M11 Dashboard | Complete |
| M12 Notifications | Complete |
| M13 Admin Panel | Complete |
| M14 Rapat & Notulen | Complete |
| M15 Absensi QR | Complete |
| M16 Sertifikat Digital | Maintenance |
| M17 WhatsApp delivery infra | Complete as infra |
| M18 Multi-Level Approval | Maintenance |
| M19 Handover | Partial/Maintenance |
| M20 Sponsor & Vendor | Complete |
| M21 Event Registration | Complete |
| M22 Payment & Ticketing | Beta/Maintenance |
| M23 AI Assistant | Maintenance/Frozen |
| M24 Campus Dashboard | Hibernate |
| M25 Rich Text Editor | Complete |
| M26 Real-Time Notifications | Complete |
| M27 Mobile QR Scanner/PWA | Complete |
| M28 Onboarding Wizard | Complete |
| M29 Global Search | Complete |
| M30 Kepanitiaan Mode | Complete |
| M31 Public Proker Microsite | Complete |
| M39 Surat Menyurat Generator | Complete |
| M40 Inventory & Asset Management | Complete |
| M43 Calendar Sync | Complete |
| M44 Web Push Notifications | Complete |

Tier Momentum M41, M42, M45, M46, M47 dihapus/deferred dari roadmap aktif. Re-evaluate hanya setelah public launch dan ada feedback nyata.

---

## Frozen, Maintenance, dan Hibernate

Fitur berikut tidak boleh ditambah scope baru tanpa keputusan eksplisit:

- M16 Certificate: maintenance only.
- M18 Multi-Level Approval: maintenance only.
- M19 Handover: boleh bug fix, jangan scope creep.
- M22 Payment & Ticketing: beta/pro-tier opt-in only.
- M23 AI Assistant: frozen expansion.
- M24 Campus Dashboard: hibernate sampai ada customer kampus berbayar.

Bug critical boleh diperbaiki. Feature baru jangan masuk area ini tanpa konfirmasi.

---

## Launch QA Checklist

Automated test sudah hijau, tetapi beberapa hal harus QA manual:

- Chrome Android camera permission untuk attendance QR.
- Safari iOS camera permission untuk attendance QR.
- Printed inventory QR scan ke `/inventory/qr/{token}`.
- Web push permission dan OS delivery saat tab tidak aktif.
- Notification click membuka URL yang benar.
- Two-browser Reverb smoke: user A trigger notification, user B bell update tanpa refresh.
- Google Calendar/Apple/Outlook subscribe `.ics` feed.
- PDF/DOCX visual smoke untuk proposal, LPJ, surat, dan sertifikat.
- Public microsite OG preview di WhatsApp/social app.
- Lighthouse/performance landing page.
- Production storage signed download.
- Queue worker dan supervisor restart behavior.

---

## Testing dan Verification

Current verified gates per 2026-05-17:

| Gate | Result |
|---|---|
| Full PHP suite | 551 passed / 2981 assertions |
| PHP style | `./vendor/bin/pint --test` pass |
| Frontend build | `npm run build` pass |
| TypeScript | `npm run lint` pass |
| Vitest harness | ready |

Recommended before every commit:

```bash
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
npm run lint
npm run build
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
```

For focused development, run targeted tests first, then full suite.

---

## Deployment Notes

Typical VPS services:

- Nginx
- PHP-FPM
- MySQL
- Redis
- Supervisor
- Queue worker
- Reverb worker
- Scheduler cron

Required production steps:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Scheduler cron:

```cron
* * * * * cd /path/to/prokerin && php artisan schedule:run >> /dev/null 2>&1
```

Supervisor should manage:

- `php artisan queue:work --sleep=3 --tries=3`
- `php artisan reverb:start`

After deploy, smoke:

- Login.
- Dashboard.
- Create/update proker.
- Upload/download document.
- Queue export.
- Notification bell.
- Public microsite.
- Calendar feed.

---

## Git dan Contribution

Branch naming:

```text
feat/name
fix/name
hotfix/name
refactor/name
chore/name
```

Commit message:

```text
feat: add inventory asset management
fix: scope document download to active organization
refactor: extract budget calculation action
test: cover proposal approval tenant guard
docs: consolidate project documentation
db: add inventory loan tables
```

Rules:

- One logical change per commit.
- Do not commit `.env`, storage files, vendor, node_modules, generated secrets.
- Do not mix unrelated feature/refactor/docs work.
- Run verification before commit.
- Update README/AGENTS when changing feature status, setup, architecture, or rules.

---

## Documentation Policy

Root markdown files are intentionally limited to:

- `README.md`
- `AGENTS.md`

Do not add root `.md` files for roadmap, feature status, sprint plan, QA plan, or architecture notes unless the project owner explicitly changes this policy.

Where to put information:

- User/product/feature/flow/setup/status: README.
- Developer/AI/architecture/rules/guardrails: AGENTS.
- Code-level detail: comments only when necessary.
- Tests are the source of executable behavior truth.

---

## Contact

Product name: Prokerin  
Primary audience: organisasi kampus Indonesia  
Public brand direction: compact, professional, Viho-inspired admin SaaS, green/brown palette, practical and operational.
