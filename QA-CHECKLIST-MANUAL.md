# QA Checklist Manual — Prokerin (Comprehensive Edition)

> Senior QA test plan untuk verifikasi end-to-end **semua fitur, halaman, tombol, form, link, file output (PDF/DOCX/CSV), dan flow lintas role** Prokerin.
>
> **Tujuan**:
> 1. Setiap halaman ter-render dengan benar (tidak putih, tidak 500, layout utuh).
> 2. Setiap tombol/link mengarahkan ke halaman yang tepat dan menjalankan proses sampai selesai.
> 3. Setiap form submit menghasilkan side-effect yang benar di database, file storage, queue, dan UI feedback.
> 4. Setiap fitur yang melibatkan file (export PDF/DOCX/CSV, upload, download, QR image, sertifikat) menghasilkan output yang valid dan bisa dibuka.
> 5. Setiap fitur diuji dari **>1 role**, sehingga authorization & multi-tenant scoping terbukti benar.
>
> **Cara pakai**: ikuti urutan section. Tiap baris satu test case. Tandai dengan:
> - `[x]` PASS
> - `[!]` FAIL (catat issue di kolom Notes / di bug tracker)
> - `[-]` Not applicable (catat alasan)
> - `[?]` Blocked (tunggu data/dependency)
>
> **Legend role kolom Test Matrix**: O=Owner, A=Admin, S=Secretary, T=Treasurer, L=Lead, C=Coordinator, M=Member, V=Viewer, CA=CampusAdmin, SA=SuperAdmin, G=Guest (tanpa login).

---

## Daftar Isi

- [0. Persiapan Lingkungan](#0-persiapan-lingkungan-test)
- [1. Public / Guest Pages](#1-public--guest-pages)
- [2. Auth Flow](#2-auth-flow)
- [3. Onboarding & Organization Setup](#3-onboarding--organization-setup)
- [4. Member Management](#4-member-management)
- [5. Proker (Project) Management](#5-proker-project-management)
- [6. Task Management](#6-task-management)
- [7. Finance / RAB](#7-finance--rab)
- [8. Reports — Proposal & LPJ](#8-reports--proposal--lpj)
- [9. Documents](#9-documents)
- [10. Meetings](#10-meetings)
- [11. Attendance (QR + Manual)](#11-attendance-qr--manual)
- [12. Certificates](#12-certificates)
- [13. Events / Public Registration](#13-events--public-registration)
- [14. Approval Workflows](#14-approval-workflows-multi-level)
- [15. Notifications & WhatsApp](#15-notifications--whatsapp)
- [16. Profile](#16-profile)
- [17. Campus Dashboard](#17-campus-dashboard-campus-admin)
- [18. Admin Workspace (Org Admin)](#18-admin-workspace-org-level-admin)
- [19. Filament Super Admin Panel](#19-filament-super-admin-panel)
- [20. Cross-Cutting Concerns](#20-cross-cutting-concerns)
- [21. Background Jobs / Queue](#21-background-jobs--queue)
- [22. File Output Verification](#22-file-output-verification)
- [23. Role-Based Access Matrix](#23-role-based-access-matrix-by-page)
- [24. Smoke Test 15 Menit](#24-smoke-test-15-menit)
- [25. Pre-QA Automated Gate](#25-pre-qa-automated-gate)
- [26. Sign-Off](#26-sign-off)

---

## 0. Persiapan Lingkungan Test

### 0.1 Reset Database & Seed
- [ ] `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate:fresh --seed` selesai tanpa error
- [ ] Tabel `users`, `organizations`, `projects`, `proposal_drafts`, `lpj_checklist_items`, `meetings`, `documents`, `budget_lines` ter-isi seed data (cek di MySQL Workbench / phpMyAdmin)
- [ ] `php artisan storage:link` sukses → folder symlink `public/storage` exist
- [ ] Redis: `redis-cli ping` → `PONG` **atau** `.env`: `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array` untuk dev cepat
- [ ] Queue worker terminal 1: `php artisan queue:listen --queue=default,exports,notifications` — running tanpa exception
- [ ] Vite terminal 2: `npm run dev` — Vite HMR ready, port 5173
- [ ] App terminal 3: `php artisan serve` — running di `http://localhost:8000`
- [ ] Buka `http://localhost:8000` di Chrome/Edge dengan DevTools terbuka
- [ ] Tidak ada error merah di Console saat halaman landing load
- [ ] Tab Network: tidak ada request 500/404 saat halaman idle

### 0.2 Mailtrap / Mail Capture
- [ ] `MAIL_MAILER=log` di `.env` lokal **atau** Mailtrap credential terisi
- [ ] Setelah trigger email (forgot password, invitation, notification): cek `storage/logs/laravel.log` atau Mailtrap inbox — email tampil dengan link yang benar
- [ ] Link di email tidak ber-format aneh (tidak ada `localhost%2F` encoding ganda)

### 0.3 S3 / Object Storage
- [ ] Local disk atau MinIO/R2 credential terisi di `.env`
- [ ] Test upload dummy file via tinker: `Storage::disk('s3')->put('test.txt', 'hi')` — sukses
- [ ] `Storage::disk('s3')->url('test.txt')` mengembalikan URL valid

### 0.4 Browsershot / DomPDF
- [ ] `node --version` ≥ 18 (Browsershot butuh)
- [ ] Puppeteer terpasang (`npm ls puppeteer` di vendor) atau fallback DomPDF tersedia
- [ ] Test export sample: queue job sukses tanpa error chrome-not-found

### 0.5 Akun Test (semua password: `password`)

| Email | Role Spatie | Role Org BEM FT | Role Org HIMA | Role Org UKM Kreatif | Catatan |
|---|---|---|---|---|---|
| `superadmin@prokerin.internal` | super_admin | — | — | — | Akses Filament |
| `superadmin@prokerin.test` | super_admin | — | — | — | Legacy alias |
| `campus@prokerin.test` | campus_admin | — | — | — | Akses /campus/dashboard |
| `owner@prokerin.test` | — | organization_owner | — | — | Owner BEM FT |
| `owner2@prokerin.test` | — | — | — | organization_owner | Owner UKM |
| `admin@prokerin.test` | — | organization_admin | organization_admin | — | Admin 2 org |
| `secretary@prokerin.test` | — | secretary | — | — | Sekretaris |
| `sekretaris@prokerin.test` | — | secretary | — | — | Alias sekretaris |
| `treasurer@prokerin.test` | — | treasurer | — | — | Bendahara |
| `bendahara@prokerin.test` | — | treasurer | — | — | Alias bendahara |
| `lead@prokerin.test` | — | project_lead | — | — | Lead seminar |
| `coordinator@prokerin.test` | — | division_coordinator | — | — | Coord |
| `koordinator@prokerin.test` | — | division_coordinator | — | — | Alias coord |
| `member@prokerin.test` | — | member | — | — | Anggota |
| `viewer@prokerin.test` | — | viewer | — | viewer | Read-only |
| `test@example.com` | — | — | — | — | Tanpa org (smoke) |

- [ ] Login berhasil untuk **semua** akun di atas
- [ ] User tanpa org (`test@example.com`) login → diarahkan ke onboarding/setup org

### 0.6 Health Endpoint Smoke
- [ ] GET `/` → 200, hero landing render
- [ ] GET `/features` → 200
- [ ] GET `/pricing` → 200
- [ ] GET `/login` → 200, form tampil
- [ ] GET `/register` → 200, form tampil
- [ ] GET `/dashboard` (no auth) → 302 ke `/login`
- [ ] GET `/proker` (no auth) → 302 ke `/login`
- [ ] GET `/admin` (login owner non-super-admin) → 403/redirect
- [ ] GET `/admin` (login super_admin) → Filament dashboard render
- [ ] GET `/non-existent-route` → 404 page render dengan layout custom

### 0.7 Browser Coverage
- [ ] Chrome (latest): semua test berikut diulang
- [ ] Firefox (latest): smoke test minimal — login, dashboard, 1 export, 1 upload
- [ ] Safari macOS: smoke test sama
- [ ] Mobile Chrome (DevTools 375px): responsive section terverifikasi



---

## 1. Public / Guest Pages

> Roles diuji: G (guest), M (member), O (owner). Guest WAJIB bisa akses tanpa login. Member/Owner login WAJIB tetap bisa akses.

### 1.1 Landing Home (`GET /`)
**Halaman render**
- [ ] Hero render dengan headline + sub-text + tombol CTA besar
- [ ] Section "Fitur Utama" tampil 3-6 card dengan icon
- [ ] Section "Cara Kerja" / "How it works" render
- [ ] Section "Testimoni" / "Statistik" render (jika ada)
- [ ] Section "Pricing teaser" render dengan tombol "Lihat Harga"
- [ ] Footer lengkap: nama brand, copyright tahun = 2026, link sosial media

**Tombol & link**
- [ ] Klik logo "Prokerin" di header → tetap di `/` (atau scroll ke top)
- [ ] Klik "Masuk" / "Login" di header → buka `/login` (full reload OK, Inertia OK)
- [ ] Klik "Daftar Gratis" / "Register" di header → buka `/register`
- [ ] Klik tombol CTA hero "Mulai Gratis" → buka `/register`
- [ ] Klik link "Fitur" di header / footer → `/features`
- [ ] Klik link "Harga" di header / footer → `/pricing`
- [ ] Klik link Privacy / Terms / Kontak di footer → halaman valid (tidak 404). Bila masih TODO, label `(coming soon)` jelas
- [ ] Klik anchor "Cara Kerja" → smooth scroll ke section

**Edge case**
- [ ] Reload halaman 5x → tidak ada flicker / FOUC
- [ ] Akses `/` saat sudah login → tetap render landing (tidak auto redirect dashboard) atau sesuai design
- [ ] Akses dengan locale `?lang=en` (jika ada) → translate jalan, tidak fallback ke Indonesia parsial

### 1.2 Landing Features (`GET /features`)
- [ ] Halaman render dengan list fitur per modul (Proker, Task, Finance, Reports, Documents, Meetings, Attendance, Certificates, Events)
- [ ] Setiap card punya icon + judul + deskripsi singkat (≥30 karakter)
- [ ] Tombol "Coba Gratis" pada card → `/register`
- [ ] Breadcrumb / tombol "Kembali ke Beranda" → `/`
- [ ] Scroll spy / table of contents (jika ada) jalan

### 1.3 Landing Pricing (`GET /pricing`)
- [ ] 3 tier card render: Free / Pro / Campus dengan harga jelas (Rp/bulan atau "Hubungi Sales")
- [ ] Tier "Pro" ditandai "Recommended" / highlighted
- [ ] Tabel comparison fitur per tier (jika ada) lengkap
- [ ] Tombol "Pilih Free" → `/register?plan=free` atau `/register`
- [ ] Tombol "Pilih Pro" → `/register?plan=pro` atau ke checkout/contact
- [ ] Tombol "Hubungi Sales" → buka `mailto:` atau form kontak
- [ ] FAQ accordion bisa expand/collapse, tidak ada animasi patah
- [ ] Toggle bulanan/tahunan (jika ada) update harga

### 1.4 Public Event Registration (`GET /events/{project}/register`)
**Setup data**: project `seminar-karier-digital` dengan registrasi public diaktifkan.

- [ ] Buka URL valid → halaman render dengan banner event + nama, tanggal, tempat
- [ ] Form field: nama lengkap, email, no.HP, tier ticket, instansi/kampus
- [ ] Submit form kosong → semua field required highlighted error
- [ ] Submit email format invalid → field email error
- [ ] Submit no.HP format invalid (bukan E.164 atau 08…) → error
- [ ] Pilih tier gratis + submit valid → halaman success: "Terima kasih, registrasi berhasil"
- [ ] Pilih tier berbayar + submit valid → redirect ke Midtrans Snap / payment page
- [ ] Setelah pembayaran sukses (sandbox) → redirect kembali ke success page dengan order ID
- [ ] Project belum publish / tidak terbuka → 404 atau pesan "Pendaftaran belum dibuka"
- [ ] Project sudah lewat tanggal → pesan "Pendaftaran sudah ditutup"
- [ ] Capacity penuh → pesan "Kuota habis"
- [ ] Refresh setelah submit → tidak duplikat (idempotent / token CSRF)

### 1.5 Certificate Verification (`GET /verify/{token}`)
**Setup**: 1 sertifikat ter-issue dengan `certificate_number`.

- [ ] Buka URL dengan token valid → halaman tampil:
  - [ ] Logo Prokerin / org
  - [ ] Nama penerima
  - [ ] Nama event / project
  - [ ] Tanggal terbit
  - [ ] Nomor sertifikat
  - [ ] Status "Valid" hijau
- [ ] Tombol "Download Sertifikat PDF" → file PDF terdownload, bisa dibuka, isi sesuai nama
- [ ] Buka token yang tidak ada di DB → halaman "Sertifikat tidak ditemukan" 404
- [ ] Buka token sertifikat yang dicabut/revoked → status "Tidak valid" merah
- [ ] Refresh ≥21 kali dalam 1 menit → 429 (`throttle:certificate-verify`)
- [ ] Halaman responsive di mobile

### 1.6 Organization Invitation (`GET /invitations/{token}`)
**Setup**: invitation `pending` di seed.

- [ ] Buka URL token valid (status pending) → halaman tampil:
  - [ ] Nama org pengundang
  - [ ] Logo org
  - [ ] Role yang ditawarkan
  - [ ] Tombol "Terima" + "Tolak"
- [ ] Buka tanpa login → redirect `/login?redirect=/invitations/{token}`, setelah login balik ke halaman invitation
- [ ] Tombol "Terima" → POST `/invitations/{token}/accept` → user join org, redirect `/dashboard` org tsb dengan flash sukses
- [ ] Tombol "Tolak" → POST `/invitations/{token}/decline` → status decline, redirect `/dashboard` user
- [ ] Buka token status `accepted` → pesan "Undangan sudah diterima"
- [ ] Buka token status `expired` → pesan "Undangan sudah kadaluarsa"
- [ ] Buka token tidak ada → 404
- [ ] User yang menerima sudah anggota org → error "Sudah menjadi anggota"

### 1.7 Midtrans Webhook (POST `/payments/midtrans/webhook`)
> Test ini via Postman / Midtrans simulator, bukan UI.

- [ ] POST dengan payload + signature valid (status `settlement`) → DB `payment_orders.status = paid`, `event_registrations.payment_status = paid`
- [ ] POST dengan signature invalid → 401/403
- [ ] POST status `pending` → DB tetap pending
- [ ] POST status `expire` → DB `expired`
- [ ] POST duplicate (idempotency) → tidak double-process



---

## 2. Auth Flow

### 2.1 Login (`GET /login` → POST `/login`)
**Halaman render**
- [ ] Form: email, password, remember me, tombol Submit, link "Lupa password", link "Belum punya akun? Daftar", tombol "Login dengan Google"
- [ ] Logo Prokerin di top
- [ ] Layout center, responsive

**Validasi & flow**
- [ ] Submit form kosong → error "Email wajib diisi", "Password wajib diisi"
- [ ] Submit email format invalid → error format
- [ ] Submit password salah → error "These credentials do not match our records"
- [ ] Submit email tidak terdaftar → error generic (jangan bocor info)
- [ ] Submit valid → redirect `/dashboard`, session set, header avatar tampil
- [ ] Centang "Ingat saya" + login → tutup browser, buka lagi dalam 30 hari → masih login
- [ ] Tombol Submit disabled saat processing (tampil spinner)

**Tombol & link**
- [ ] Klik "Lupa password" → `/forgot-password`
- [ ] Klik "Daftar" → `/register`
- [ ] Klik "Login dengan Google" → redirect ke `accounts.google.com/o/oauth2/...`
- [ ] Klik logo → `/`

**Rate limit & security**
- [ ] Submit password salah 5x dalam 1 menit dari IP+email yang sama → 429 "Too many attempts"
- [ ] Tunggu 1 menit → bisa login lagi
- [ ] Login dengan akun di-disable / soft-deleted → error
- [ ] CSRF token: form punya `<input name="_token">` yang valid (cek di view source)

### 2.2 Register (`GET /register` → POST `/register`)
**Halaman render**
- [ ] Form: nama, email, password, konfirmasi password, persetujuan term (checkbox), tombol Daftar, tombol "Daftar dengan Google"
- [ ] Link "Sudah punya akun? Login" → `/login`

**Validasi**
- [ ] Email format invalid → error
- [ ] Email duplikat → error "Email sudah digunakan"
- [ ] Password < 8 karakter → error
- [ ] Password tanpa angka/huruf besar (jika rule ada) → error
- [ ] Konfirmasi password tidak match → error
- [ ] Belum centang term → tombol disabled atau error
- [ ] Submit valid → user terbuat, auto-login, redirect `/dashboard` atau `/verify-email`

**Side-effect**
- [ ] Email verifikasi terkirim (cek mailtrap)
- [ ] DB row `users` baru dengan `email_verified_at = null`

### 2.3 Forgot Password (`GET /forgot-password` → POST `/forgot-password`)
- [ ] Form email + tombol Submit
- [ ] Submit email valid → flash sukses "Link reset password sudah dikirim ke email Anda"
- [ ] Cek email di mailtrap → email berisi link `/reset-password/{token}?email=...`
- [ ] Klik link di email → halaman reset password ter-render
- [ ] Submit email yang tidak ada → flash sukses (tidak bocor info ke attacker)
- [ ] Submit ≥3x dalam 15 menit → 429 (`throttle:password.email`)
- [ ] Link kembali ke login jalan

### 2.4 Reset Password (`GET /reset-password/{token}`)
- [ ] Token valid → form password baru + konfirmasi + email pre-filled (read-only)
- [ ] Token invalid / expired → error "Link tidak valid atau sudah kadaluarsa"
- [ ] Password baru < 8 karakter → error
- [ ] Konfirmasi tidak match → error
- [ ] Submit valid → flash "Password berhasil direset", redirect `/login`
- [ ] Login dengan password lama → gagal
- [ ] Login dengan password baru → sukses
- [ ] Reuse token setelah berhasil → error (token sekali pakai)

### 2.5 Email Verification (`GET /verify-email`)
- [ ] User belum verified login → redirect ke `/verify-email`
- [ ] Halaman tampil pesan "Cek email Anda untuk verifikasi"
- [ ] Tombol "Kirim ulang email" → flash "Email verifikasi sudah dikirim ulang"
- [ ] Tombol "Logout" → POST `/logout` → ke `/`
- [ ] Klik link verifikasi di email → `email_verified_at` terisi, redirect `/dashboard`
- [ ] Klik link expired (>60 menit) → error
- [ ] Submit "Kirim ulang" ≥6x/menit → 429

### 2.6 Confirm Password (`GET /confirm-password`)
- [ ] Akses fitur sensitif (mis. hapus akun, ubah email) → redirect ke confirm-password
- [ ] Submit password salah → error
- [ ] Submit password benar → redirect kembali ke halaman tujuan
- [ ] Konfirmasi cached 3 jam (default Laravel)

### 2.7 Logout (POST `/logout`)
- [ ] Klik avatar → "Logout" di dropdown → POST `/logout` → redirect `/`
- [ ] Session dihapus → akses `/dashboard` redirect `/login`
- [ ] Cookie `XSRF-TOKEN` & `laravel_session` terhapus
- [ ] Klik back browser setelah logout → tetap di `/login` (tidak bisa balik)

### 2.8 Google OAuth (`GET /auth/google` → callback)
- [ ] Klik tombol Google di login → redirect ke Google OAuth consent screen
- [ ] Pilih akun Google → callback ke `/auth/google/callback` → akun terbuat (jika baru) atau login (jika exist)
- [ ] User baru via Google: nama + avatar dari Google, `email_verified_at` terisi otomatis
- [ ] Tolak permission Google → redirect kembali ke `/login` dengan flash error
- [ ] `GOOGLE_CLIENT_ID` belum diset di config → tombol disabled atau pesan "Login Google belum dikonfigurasi" (tidak crash)
- [ ] User existing dengan email sama (sudah ada via register manual) → akun di-link, tidak duplikat

### 2.9 Update Password (PUT `/password`) — saat sudah login
- [ ] Form di Profile: current password, new password, konfirmasi
- [ ] Current salah → error
- [ ] New password = current → error
- [ ] Konfirmasi tidak match → error
- [ ] Submit valid → flash "Password updated", session tetap aktif
- [ ] Login ulang dengan password baru → sukses



---

## 3. Onboarding & Organization Setup

### 3.1 Onboarding Flow (User Baru tanpa Org)
**Setup**: login dengan `test@example.com` (tanpa org).

- [ ] Setelah login → redirect / show banner onboarding di dashboard: "Lengkapi profil organisasi"
- [ ] Banner punya tombol "Mulai Onboarding" → `/organization`
- [ ] Banner punya tombol "Lewati untuk sekarang" → POST `/onboarding/complete` → banner hilang, dashboard tetap render dengan empty state

### 3.2 Organization Setup (`GET /organization`)
**Halaman render**
- [ ] Form: nama org, slug (auto-generate dari nama), deskripsi, logo upload, alamat, kampus afiliasi
- [ ] Preview logo placeholder

**Validasi & flow**
- [ ] Nama < 3 karakter → error
- [ ] Slug duplikat dengan org lain → error
- [ ] Logo > 2MB → error
- [ ] Logo non-image (.pdf, .exe) → error
- [ ] Submit valid → org terbuat, user dijadikan `organization_owner`, redirect `/dashboard` dengan flash sukses
- [ ] Buka `/organization` setelah org sudah ada → form di-mode "Edit" pre-filled
- [ ] PATCH `/organization` update field → flash sukses, data di DB ter-update

**Tombol & link**
- [ ] Tombol "Simpan & Lanjutkan" submit form
- [ ] Tombol "Batal" → balik ke dashboard
- [ ] Tombol "Upload Logo" buka file picker
- [ ] Tombol "Hapus Logo" → POST/DELETE → logo path null

### 3.3 Organization Logo Upload (POST `/organization/logo`)
- [ ] Pilih file PNG/JPG/SVG ≤2MB → upload → preview update
- [ ] Pilih file >2MB → error
- [ ] Pilih file MIME bukan image → error
- [ ] Logo tersimpan di S3 path `organizations/{slug}/logo.png`
- [ ] Logo render di sidebar header setelah refresh

### 3.4 Organization Switcher (`GET /organization/switcher`)
**Setup**: user `admin@prokerin.test` punya 2 org (BEM FT, HIMA Informatika).

- [ ] Halaman render list semua org user dengan card: logo, nama, role, badge "Aktif" pada org current
- [ ] Klik card org berbeda → POST `/organization/switch` → flash "Beralih ke {nama org}", redirect `/dashboard` org tsb
- [ ] Sidebar update: logo + nama org berubah
- [ ] Data dashboard berubah sesuai org (proker, members, dll)
- [ ] Cek tidak bisa lihat data org lain via URL (mis. `/proker/{id-dari-org-lain}` → 403/404)

### 3.5 Organization Periods (`GET /organization/periods`)
**Setup**: BEM FT punya 1 periode "2026" aktif.

- [ ] List periode tampil dengan kolom: nama, tanggal mulai, tanggal selesai, status aktif, jumlah proker
- [ ] Tombol "Tambah Periode" buka form modal/inline
- [ ] Form: nama (mis. "2027"), starts_at, ends_at, is_active toggle
- [ ] Submit periode baru valid → row baru muncul di list
- [ ] Submit periode dengan tanggal overlap → warning (tidak block, sesuai design)
- [ ] Set periode baru aktif → periode lama otomatis non-aktif (atau sesuai rule)
- [ ] Tombol "Edit" pada row → form pre-filled, PATCH jalan
- [ ] Tombol "Hapus" pada periode tanpa proker → konfirmasi → DELETE jalan
- [ ] Tombol "Hapus" pada periode dengan proker → disabled atau error "Tidak bisa hapus, ada N proker terkait"

**Test role**
- [ ] Owner: full akses
- [ ] Admin: bisa CRUD
- [ ] Secretary/Member/Viewer: hanya read, tombol CRUD tidak tampil / 403

### 3.6 Organization Calendar (`GET /organization/calendar`)
- [ ] Calendar bulan ini render dengan event: proker (warna khusus), meeting (warna lain), deadline task
- [ ] Tombol "< Bulan Lalu" / "Bulan Depan >" jalan
- [ ] Tombol "Hari Ini" → kembali ke bulan sekarang
- [ ] Klik event → modal detail dengan link ke resource (`/proker/{slug}`, `/meetings`, `/tasks`)
- [ ] Filter "Tampilkan: Proker / Meeting / Task" toggle jalan
- [ ] Tombol "Export iCal" (jika ada) → download .ics yang valid
- [ ] Empty state bila tidak ada event di bulan ini

### 3.7 Organization Handover (`GET /organization/handover`)
**Halaman render**
- [ ] List paket handover per periode tampil dengan status: draft, in_progress, completed, archived
- [ ] Empty state bila belum ada paket

**Tombol & flow**
- [ ] Tombol "Buat Paket Handover" buka form
- [ ] Form: nama paket, periode (dropdown), deskripsi, items dynamic (tambah/hapus row)
- [ ] Setiap item: title, deskripsi, dokumen attach (dropdown dari existing documents)
- [ ] Tombol "Tambah Item" → row baru muncul
- [ ] Tombol "Hapus Item" pada row → row hilang
- [ ] Submit paket valid → row baru di list, status `draft`
- [ ] Tombol "Export PDF" → POST `/organization/handover/packages/{id}/export` → flash "Export di-queue", row export muncul di queue
- [ ] Tombol "Update Status Item" (todo→in_progress→done) → PATCH jalan, badge update
- [ ] Tombol "Transition Paket" (draft→handover→archived) → PATCH jalan, status berubah
- [ ] Tombol "Hapus Paket" konfirmasi → DELETE jalan

**Test role**
- [ ] Owner/Admin: full akses
- [ ] Member: read-only atau 403

### 3.8 Sponsors & Vendors (`GET /organization/sponsors-vendors`)
**Halaman render**
- [ ] List kontak tampil dengan kolom: nama, type (sponsor/vendor), category, contact_person, status (active/inactive), jumlah proker terkait
- [ ] Filter type, status, search nama jalan
- [ ] Tab "Sponsor" / "Vendor" / "Semua" toggle jalan

**Tombol & flow**
- [ ] Tombol "Tambah Kontak" buka form
- [ ] Form: type, nama, category, contact_person, phone, email, alamat, status, notes
- [ ] Submit valid → kontak baru muncul
- [ ] Phone validasi format → error jika invalid
- [ ] Email validasi → error jika invalid
- [ ] Klik nama kontak → buka detail `/organization/sponsors-vendors/{id}`

### 3.9 Sponsor/Vendor Detail (`GET /organization/sponsors-vendors/{id}`)
- [ ] Header: nama, type badge, status badge
- [ ] Section "Info Kontak": phone, email, alamat (klik phone → tel:, klik email → mailto:)
- [ ] Section "Project Terkait": list project yang link ke kontak ini dengan kolom nama, peran, amount
- [ ] Section "Dokumen Terkait": list dokumen attached
- [ ] Tombol "Edit Kontak" buka form pre-filled
- [ ] Tombol "Hapus Kontak" konfirmasi → DELETE
- [ ] Tombol "Tambah Project Link" buka modal: pilih project, role, amount



---

## 4. Member Management

### 4.1 Members Index (`GET /members`)
**Halaman render**
- [ ] List anggota org aktif tampil dengan kolom: avatar, nama, email, role badge, divisi, joined_at, status WA opt-in
- [ ] Total anggota di header
- [ ] Search by nama/email jalan real-time
- [ ] Filter role dropdown jalan
- [ ] Pagination (jika >25) jalan

**Tombol & flow**
- [ ] Tombol "Undang Anggota" buka modal
- [ ] Form: email, role (dropdown), divisi (opsional)
- [ ] Submit valid → invitation token terbuat, flash "Undangan dikirim ke {email}"
- [ ] Cek email mailtrap → email berisi link `/invitations/{token}`
- [ ] Submit email duplikat (sudah anggota) → error
- [ ] Submit invitation >20x dalam 1 jam → 429
- [ ] Tombol "Hapus Anggota" pada baris non-self → konfirmasi modal "Yakin hapus {nama} dari org?"
- [ ] Konfirmasi → DELETE jalan, baris hilang, flash "Anggota dihapus"
- [ ] Tombol "Hapus" pada self → disabled / hidden
- [ ] Tombol "Hapus" pada owner terakhir → disabled
- [ ] Tombol "Ubah Role" buka dropdown → pilih role baru → PATCH `/members/{id}/role` → role badge update

**Test role**
- [ ] Owner: full CRUD + assign owner
- [ ] Admin: CRUD kecuali tidak bisa hapus owner / set role owner
- [ ] Secretary/Treasurer/Lead: read + invite (jika permission), tidak bisa hapus
- [ ] Member/Viewer: read-only, tombol invite/hapus tidak tampil

### 4.2 Member Invites (`GET /members/invites`)
- [ ] List undangan tampil dengan kolom: email, role, status (pending/accepted/expired/revoked), tanggal kirim, expired_at
- [ ] Tombol "Resend" pada pending → POST → flash "Undangan dikirim ulang"
- [ ] Tombol "Cancel" pada pending → konfirmasi → status revoked
- [ ] Tombol "Copy Link" pada pending → URL ter-copy ke clipboard
- [ ] Filter status jalan

### 4.3 Member Roles (`GET /members/roles`)
- [ ] Matrix render: baris = role, kolom = permission (tampil ✓ / ✗)
- [ ] Hover/klik permission → tooltip deskripsi
- [ ] List anggota dengan tombol "Ubah Role" inline
- [ ] Klik "Ubah Role" pada anggota → dropdown pilih role → PATCH jalan, flash sukses
- [ ] Coba ubah role diri sendiri → disabled
- [ ] Coba ubah role owner ke role bawah (jika cuma 1 owner) → disabled / error

---

## 5. Proker (Project) Management

### 5.1 Proker Index (`GET /proker`)
**Halaman render**
- [ ] List proker org aktif tampil dengan kolom/card: nama, status badge warna, progress bar %, lead avatar, tanggal mulai/selesai, jumlah task done/total
- [ ] Tab/filter status: All / Draft / Proposal Review / RAB Approval / Ready to Execute / Running / LPJ Review / Completed / Archived
- [ ] Filter periode dropdown jalan
- [ ] Filter template type jalan (Seminar/Workshop/Competition/Makrab)
- [ ] Search by nama proker real-time
- [ ] View toggle: Card grid / Table list

**Tombol & flow**
- [ ] Tombol "Buat Proker" → `/proker/create`
- [ ] Tombol "Templates" → `/proker/templates`
- [ ] Tombol "Status Flow" → `/proker/status-flow`
- [ ] Klik card/baris proker → `/proker/{slug}` detail
- [ ] Tombol "Edit" pada baris → `/proker/{slug}/edit`
- [ ] Tombol "Hapus" → konfirmasi typed-name → DELETE jalan, baris hilang
- [ ] Empty state "Belum ada proker. Buat pertama kali sekarang." dengan CTA

**Test role**
- [ ] Owner/Admin: lihat semua proker, full CRUD
- [ ] Lead: lihat semua proker org, edit/delete hanya proker yang dia lead
- [ ] Coordinator/Secretary/Treasurer: lihat semua, tidak bisa delete
- [ ] Member/Viewer: lihat semua, tidak ada tombol create/edit/delete

### 5.2 Proker Create (`GET /proker/create`)
**Halaman render**
- [ ] Form sticky-header dengan tombol "Simpan" dan "Batal"
- [ ] Field: nama, slug (auto), deskripsi, periode (dropdown), template type (dropdown), lead user (dropdown member), starts_at (date), ends_at (date), target audience, division (multi-select)

**Validasi**
- [ ] Nama < 5 karakter → error
- [ ] Nama duplikat di periode sama → error
- [ ] ends_at < starts_at → error
- [ ] Lead bukan member org → error
- [ ] Submit valid → POST `/proker` → redirect `/proker/{slug}` dengan flash "Proker berhasil dibuat"

**Side-effect**
- [ ] Project ter-insert dengan `status=draft`, `progress=0`
- [ ] Pilih template → setelah create, otomatis ter-generate proposal_draft, lpj_checklist_items, project_tasks dasar, budget_lines awal sesuai template
- [ ] User pembuat otomatis ter-assign sebagai project_member dengan role `project_lead`

### 5.3 Proker Detail (`GET /proker/{project}` atau `/proker/sample`)
**Halaman render**
- [ ] Header: nama proker, status badge, progress bar, lead avatar+nama, tombol aksi (Edit, Hapus, Transition Status)
- [ ] Tabs: Overview / Timeline / Task / Budget / Proposal / LPJ / Documents / Members / Sponsors

**Tab Overview**
- [ ] Card: deskripsi, target audience, divisi, tanggal mulai/selesai, lead, periode
- [ ] KPI: jumlah task (todo/in-progress/done), budget (planned/realized/sisa), dokumen
- [ ] Mini timeline event terdekat

**Tab Timeline**
- [ ] List event/milestone urut tanggal
- [ ] Klik event → detail modal

**Tab Task**
- [ ] Mini kanban / list task project
- [ ] Tombol "Buka Kanban Lengkap" → `/tasks/kanban?project={id}`

**Tab Budget**
- [ ] Ringkasan RAB: total planned, realized, sisa, persentase realisasi
- [ ] Tombol "Buka Finance" → `/finance?project={id}`

**Tab Proposal**
- [ ] Status proposal_draft (draft/submitted/approved/revision_requested)
- [ ] Tombol "Buka Editor Proposal" → `/reports/proposal-editor?project={id}`

**Tab LPJ**
- [ ] Progress checklist LPJ (% complete)
- [ ] Tombol "Buka Checklist LPJ" → `/reports/lpj-checklist?project={id}`

**Tab Documents**
- [ ] List dokumen attached ke project
- [ ] Tombol "Upload Dokumen" buka form upload (project_id pre-fill)
- [ ] Tombol "Download" pada dokumen jalan

**Tab Members**
- [ ] List project_member dengan role (Project Lead / Division Coordinator / Committee Member / Viewer)
- [ ] Tombol "Tambah Member" buka form pilih user + role → POST `/proker/{id}/members` → row baru
- [ ] Tombol "Hapus Member" → konfirmasi → DELETE `/proker/{id}/members/{member}` jalan
- [ ] Tidak bisa hapus diri sendiri (jika lead) → disabled

**Tab Sponsors**
- [ ] List sponsor/vendor link ke project
- [ ] Tombol "Link Sponsor/Vendor" buka modal pilih kontak + role + amount

**Status Transition**
- [ ] Tombol "Pindah ke Proposal Review" muncul di status `Draft` (untuk Lead)
- [ ] PATCH `/proker/{id}/status` dengan status target → flash sukses, badge update
- [ ] Transisi tidak valid (mis. Draft → Completed langsung) → error
- [ ] Member/Viewer tidak lihat tombol transition

### 5.4 Proker Edit (`GET /proker/{project}/edit`)
- [ ] Form pre-filled
- [ ] Submit PATCH → flash sukses, redirect ke detail
- [ ] User non-lead/admin → 403

### 5.5 Proker Templates (`GET /proker/templates`)
**Halaman render**
- [ ] List template tampil: Seminar / Workshop / Competition / Makrab dengan icon + deskripsi singkat
- [ ] Klik template → preview struktur (proposal outline sections, list task, list budget line, list lpj checklist)

**Tombol & flow**
- [ ] Tombol "Generate Proker dari Template" buka form input nama+periode+lead
- [ ] Submit → POST `/proker/templates/{template}/generate` → proker baru terbuat dengan semua relasi pre-filled, redirect ke detail
- [ ] Cek di DB: project_tasks, budget_lines, proposal_drafts, lpj_checklist_items terisi sesuai template

### 5.6 Proker Status Flow (`GET /proker/status-flow`)
- [ ] Diagram flow render: Draft → ProposalReview → RabApproval → ReadyToExecute → Running → LpjReview → Completed → Archived
- [ ] Hover node → tooltip role yang bisa transisi
- [ ] Legend warna jelas
- [ ] Tombol "Kembali" → `/proker`



---

## 6. Task Management

### 6.1 Tasks Index (`GET /tasks`)
- [ ] List task user (assigned to me + tasks dari project tempat user member) tampil
- [ ] Kolom: title, project, division, PIC, status, due_at, priority
- [ ] Badge "Overdue" merah untuk due_at < today & status ≠ done
- [ ] Filter project, status, divisi, PIC, due range jalan
- [ ] Sort by due_at asc/desc
- [ ] Tombol "Buat Task" buka form modal
- [ ] Form: project (dropdown), title, divisi, PIC (dropdown member project), due_at, status awal (default backlog)
- [ ] Submit valid → row baru, flash sukses
- [ ] Klik task → modal detail (title, deskripsi, comment, history)
- [ ] Quick links di header ke `/tasks/kanban`, `/tasks/calendar`, `/tasks/assignments`

### 6.2 Task Kanban (`GET /tasks/kanban`)
**Halaman render**
- [ ] 4 kolom utama: Backlog / In Progress / Review / Done (Todo dan Blocked optional sesuai TaskStatus enum)
- [ ] Card task tampil: title, project tag, PIC avatar, due_at badge (kuning H-3, merah overdue), divisi tag
- [ ] Counter task per kolom di header

**Drag & drop**
- [ ] Drag card dari Backlog ke In Progress → PATCH `/tasks/{id}/status` → status update di DB, kolom update tanpa refresh
- [ ] Drag ke Done → status=done, completed_at terisi
- [ ] Drag ke kolom yang sama → tidak ada PATCH (no-op)
- [ ] Drag gagal (network error) → status balik ke kolom asal, flash error
- [ ] Drag oleh non-PIC/non-coordinator → 403, balik ke kolom asal

**Filter**
- [ ] Filter project di top → kanban tampil hanya task project tsb
- [ ] Filter PIC, divisi jalan
- [ ] Tombol "+" pada kolom buka quick-add modal

### 6.3 Task Calendar (`GET /tasks/calendar`)
- [ ] Calendar bulan render task berdasarkan due_at
- [ ] Klik task di calendar → modal detail
- [ ] Tombol < / > / Today navigasi
- [ ] Drag task ke tanggal lain (jika feature ada) → PATCH due_at update
- [ ] Filter project / PIC / status di sidebar

### 6.4 Task Assignments (`GET /tasks/assignments`)
- [ ] Group by PIC tampil dengan jumlah task per PIC
- [ ] Klik PIC → expand list task
- [ ] Tombol "Reassign" pada task buka dropdown member → PATCH `/tasks/{id}/pic` → flash sukses
- [ ] PIC baru bukan member project → 422

### 6.5 Task Status / PIC Update Endpoints
- [ ] PATCH `/tasks/{task}/status` valid status → 302 + flash sukses
- [ ] PATCH dengan status invalid (bukan enum) → 422
- [ ] PATCH `/tasks/{task}/pic` valid user_id → 302 + flash sukses
- [ ] Member non-coordinator tidak bisa reassign → 403

### 6.6 Deadline Reminder (POST `/notifications/task-deadline-reminders`)
- [ ] Tombol "Kirim Reminder Deadline" di task index/admin → flash "Reminder masuk antrean"
- [ ] Cek `whatsapp_delivery_logs` table → row baru status `queued`
- [ ] Setelah queue jalan → status `sent`
- [ ] User opt-out WA → tidak ada row baru
- [ ] Submit ≥100x dalam 1 jam → 429

**Test role**
- [ ] Owner/Admin/Lead/Coordinator: full akses kanban + reassign + status update
- [ ] Secretary/Treasurer/Member: hanya update status task yang assigned ke mereka
- [ ] Viewer: read-only, tidak bisa drag

---

## 7. Finance / RAB

### 7.1 Finance Index (`GET /finance`)
**Middleware check**
- [ ] User dengan role finance (treasurer/owner/admin) → halaman accessible
- [ ] Member non-finance (secretary/lead/coordinator/member/viewer) → 403 dengan halaman error custom
- [ ] Test: login member → akses `/finance` → harus 403

**Halaman render**
- [ ] Filter project (dropdown) di top — wajib pilih project
- [ ] KPI card 4: total planned, total realized, sisa (planned - realized), persentase realisasi
- [ ] Chart "RAB vs Realisasi" bar chart per category dengan:
  - [ ] Bar planned (warna biru)
  - [ ] Bar realized (warna hijau)
  - [ ] Bar over-budget (warna merah) — muncul jika realized > planned
  - [ ] Legend 3 warna jelas, tidak overlap
- [ ] Pie chart distribusi anggaran per kategori
- [ ] Tabel realisasi terbaru (10 transaksi terakhir)

**Tombol & link**
- [ ] Tombol "Budget Draft" → `/finance/budget-draft`
- [ ] Tombol "Realisasi" → `/finance/realization`
- [ ] Tombol "Approval" → `/finance/approval`
- [ ] Tombol "Export Laporan" (jika ada) → queue export

### 7.2 Budget Draft (`GET /finance/budget-draft`)
**Halaman render**
- [ ] List budget line per project tampil dengan kolom: name, category, planned_amount, status badge
- [ ] Footer: total planned, "Over budget" indicator jika ada line over

**CRUD inline**
- [ ] Tombol "Tambah Budget Line" buka form inline di row baru
- [ ] Form: name, category, planned_amount, deskripsi
- [ ] Submit valid → POST `/finance/budget-lines` → row baru muncul di list
- [ ] planned_amount non-numeric atau <0 → error inline
- [ ] Tombol "Edit" pada row → field jadi editable inline
- [ ] Tombol "Save" → PATCH `/finance/budget-lines/{id}` → flash sukses, row update
- [ ] Tombol "Cancel" edit → field kembali ke value lama
- [ ] Tombol "Delete" → konfirmasi modal → DELETE `/finance/budget-lines/{id}` → row hilang
- [ ] Total planned di footer auto-update tanpa refresh setelah CRUD

**Test role**
- [ ] Treasurer/Owner: full CRUD
- [ ] Admin: full CRUD
- [ ] Lead/Coordinator/Secretary/Member/Viewer: 403

### 7.3 Realization (`GET /finance/realization`)
- [ ] List budget line tampil dengan kolom: name, planned, realized, sisa, status, jumlah receipt
- [ ] Tombol "Upload Receipt" pada row buka modal
- [ ] Form: file (PDF/JPG/PNG ≤5MB), amount, name transaksi, deskripsi, tanggal
- [ ] Submit valid → POST `/finance/budget-lines/{id}/realizations` → transaksi tercatat di `budget_transactions`, file ter-upload ke S3
- [ ] Realized di kolom auto-update
- [ ] List receipt history bisa dilihat di expand row
- [ ] Tombol "Download Receipt" pada history jalan → file terdownload (signed URL)
- [ ] File MIME bukan PDF/image → error
- [ ] File >5MB → error

### 7.4 Approval (`GET /finance/approval`)
- [ ] List budget line status `review` tampil
- [ ] Tombol "Approve" pada line → modal konfirmasi opsional + alasan
- [ ] PATCH `/finance/budget-lines/{id}/approval` decision=approve → status=`approved`, flash sukses
- [ ] Tombol "Reject" / "Request Revision" → modal alasan wajib → PATCH decision=reject → status=`draft`/`rejected`
- [ ] Setelah approval → notifikasi ke treasurer (in-app + email + WA jika opt-in)
- [ ] List `approved` & `rejected` filter tab terpisah

**Test role**
- [ ] Owner: full approve/reject
- [ ] Treasurer: bisa submit untuk approval, tidak bisa approve sendiri
- [ ] Admin: full approve
- [ ] Member non-finance: 403



---

## 8. Reports — Proposal & LPJ

### 8.1 Reports Index (`GET /reports`)
- [ ] List proker dengan summary status proposal & LPJ tampil
- [ ] Kolom: nama proker, status proposal (draft/submitted/approved/revision), status LPJ (incomplete/in_review/approved), tombol aksi
- [ ] Tombol "Editor Proposal" → `/reports/proposal-editor?project={id}`
- [ ] Tombol "Checklist LPJ" → `/reports/lpj-checklist?project={id}`
- [ ] Tombol "Export Queue" → `/reports/export-queue`

### 8.2 Proposal Editor (`GET /reports/proposal-editor`)
**Halaman render**
- [ ] Dropdown pilih proker (filter draft existing)
- [ ] Setelah pilih → editor sections render (Latar Belakang, Tujuan, Sasaran, Susunan Acara, RAB, Kepanitiaan, dll sesuai template)
- [ ] Setiap section editable textarea / rich-text dengan label

**Save Draft**
- [ ] Edit field → tombol "Simpan Draft" enabled (atau auto-save indicator)
- [ ] Klik "Simpan Draft" → PATCH `/reports/proposal-drafts/{id}` → flash "Draft tersimpan", sections persisted
- [ ] Reload halaman → data terisi seperti yang disimpan
- [ ] Submit dengan section kosong → validasi sesuai design (boleh empty for draft)

**AI Suggestion**
- [ ] Tombol "Saran AI" pada section → POST `/reports/proposal-drafts/{id}/ai-suggestions` dengan body section & context
- [ ] Loading indicator tampil
- [ ] Response sukses → modal/inline tampil suggestion + tombol "Apply" / "Dismiss"
- [ ] Apply → text masuk ke section, tombol Save aktif
- [ ] Feature flag AI off / quota habis → tombol disabled atau pesan "AI feature belum aktif"
- [ ] AI usage logged di `ai_usage_logs` table

**Submit Approval**
- [ ] Tombol "Submit untuk Approval" tampil saat status `draft` atau `revision_requested`
- [ ] Klik → konfirmasi modal "Yakin submit?"
- [ ] POST `/reports/proposal-drafts/{id}/submit` → flash "Proposal dikirim ke approval", proposal_draft.status=`submitted`, project.status=`ProposalReview`
- [ ] Side-effect: queue export PDF, queue WA reminder ke approver
- [ ] Cek `document_exports` table: row baru status=queued, document_type=proposal, format=pdf
- [ ] Status changed: lihat row di `approval_instances` jika multi-level

**Approve / Request Revision**
- [ ] Login sebagai owner → buka proposal status `submitted`
- [ ] Tombol "Approve" tampil → klik → PATCH `/reports/proposal-drafts/{id}/decision` decision=approve → flash "Proposal disetujui", project.status=`RabApproval`
- [ ] Tombol "Request Revision" → modal alasan wajib → PATCH decision=request_changes → flash "Dikembalikan untuk revisi", project.status=`Draft`, proposal_draft.status=`revision_requested`
- [ ] Notifikasi ke submitter (cek bell + email + WA jika opt-in)

**Test role**
- [ ] Secretary (author): bisa edit + submit, tidak bisa approve
- [ ] Owner/Admin: bisa approve/revision, lihat semua draft
- [ ] Lead: bisa lihat draft project-nya
- [ ] Member/Viewer: read-only atau 403
- [ ] Submitted proposal tidak bisa di-edit (tombol disabled, error 422 jika force PATCH)

### 8.3 LPJ Checklist (`GET /reports/lpj-checklist`)
**Halaman render**
- [ ] Dropdown pilih proker (filter project status `Running` atau `LpjReview`)
- [ ] Progress bar % completeness di top
- [ ] List checklist items per project tampil sebagai checkbox (dari seed: 5 item default)

**Toggle item**
- [ ] Klik checkbox → PATCH `/reports/lpj-checklist/items/{id}` → state tersimpan, progress bar update real-time
- [ ] Reload → state tetap ✓

**AI Summary**
- [ ] Tombol "Generate AI Summary" → POST `/reports/lpj/{project}/ai-summary`
- [ ] Loading → response → modal/section tampil ringkasan AI dari project data
- [ ] Tombol "Apply Summary" → masuk ke section laporan
- [ ] Quota habis / feature off → pesan jelas

**Export LPJ**
- [ ] Tombol "Export PDF" → POST `/reports/lpj-checklist/{project}/export?format=pdf` → flash "Export di-queue", row di `document_exports`
- [ ] Tombol "Export DOCX" → format=docx → row baru
- [ ] Buka `/reports/export-queue` → export muncul dengan status queued/processing/completed
- [ ] Saat completed → tombol Download di queue jalan, file PDF/DOCX bisa dibuka

**Submit untuk Review**
- [ ] Checklist 100% complete + tombol "Submit untuk Review" enabled
- [ ] Checklist <100% + tombol disabled atau klik → error "Lengkapi checklist dulu"
- [ ] Klik → POST `/reports/lpj/{project}/review` → project.status=`LpjReview`, flash sukses
- [ ] Notifikasi ke owner

**Approve / Request Revision LPJ**
- [ ] Owner buka LPJ status `LpjReview`
- [ ] Tombol "Approve LPJ" → PATCH `/reports/lpj/{project}/decision` decision=approve → project.status=`Completed`, flash "LPJ disetujui dan proker selesai"
- [ ] Tombol "Request Revision" → PATCH decision=request_changes → project.status=`Running`, flash "Dikembalikan untuk revisi"
- [ ] Notifikasi ke project_lead

**Test role**
- [ ] Secretary/Lead: edit checklist + submit, tidak approve
- [ ] Owner/Admin: approve/revision
- [ ] Member: read-only

### 8.4 Export Queue (`GET /reports/export-queue`)
- [ ] List document_exports user/org tampil dengan kolom: title, type (proposal/lpj/handover/event/meeting), format (pdf/docx), status (queued/processing/completed/failed), requested_at, completed_at, file_size
- [ ] Filter status, type jalan
- [ ] Sort by requested_at desc
- [ ] Status `completed` → tombol "Download" enabled → klik → file terdownload via signed URL S3, nama file masuk akal (`proposal-seminar-karier-2026-06-12.pdf`)
- [ ] Status `failed` → tombol "Retry" → POST `/document-exports/{id}/retry` → status balik queued
- [ ] Status `failed` → tampil error message jika ada
- [ ] Auto-refresh polling tiap 5 detik (jika ada) update status tanpa reload manual
- [ ] User tidak bisa download export org lain (URL manipulation test → 403)
- [ ] Row > 30 hari → archived/hidden (jika ada cleanup)



---

## 9. Documents

### 9.1 Documents Index (`GET /documents`)
**Halaman render**
- [ ] List dokumen org tampil dengan kolom: name, folder, owner avatar, visibility badge (Private/Restricted/Committee/Public), size_kb, mime icon, project link, status (uploaded/review/ready)
- [ ] View toggle: Grid / List
- [ ] Empty state bila belum ada dokumen

**Filter & search**
- [ ] Filter folder dropdown jalan
- [ ] Filter visibility jalan
- [ ] Filter project jalan
- [ ] Filter MIME type (PDF/Image/Doc/Other) jalan
- [ ] Search by name jalan

**Tombol & flow**
- [ ] Tombol "Upload Dokumen" buka form/modal
- [ ] Form: file (drag-drop atau pilih), folder (autocomplete + create new), visibility (radio), project (dropdown), deskripsi
- [ ] Multiple file upload jalan dengan progress bar per file
- [ ] Submit valid → POST `/documents` → flash "N dokumen berhasil diupload"
- [ ] File >10MB → error "Maksimal 10MB"
- [ ] File MIME terlarang (.exe, .svg, .php, .bat, .sh) → error
- [ ] File MIME diizinkan (PDF, JPG, PNG, DOCX, XLSX, ZIP) → upload sukses
- [ ] File tersimpan di S3 path `documents/{project-slug-or-org-slug}/{filename}`
- [ ] Tombol "Download" pada row → file terdownload via signed URL
- [ ] Tombol "Hapus" → konfirmasi → DELETE jalan, file di S3 ikut terhapus (sesuai design soft/hard delete)
- [ ] Tombol "Edit Metadata" buka form: name, folder, visibility, deskripsi (file tidak diganti)

**Visibility test**
- [ ] Private: hanya owner_user_id yang bisa lihat & download
- [ ] Restricted: owner + role spesifik (treasurer untuk receipt)
- [ ] Committee: semua project_member
- [ ] Public: semua organization_member

**Test role**
- [ ] Owner/Admin: full akses semua dokumen
- [ ] Member: lihat sesuai visibility, upload allowed
- [ ] Viewer: read-only sesuai visibility, tidak bisa upload/delete

### 9.2 Document Folders (`GET /documents/folders`)
- [ ] Tree folder render dengan jumlah file per folder
- [ ] Klik folder → halaman list dokumen folder tsb
- [ ] Tombol "Buat Folder" buka form (jika ada feature)

### 9.3 Upload Center (`GET /documents/upload-center`)
- [ ] Drag-drop area besar render
- [ ] Drag file ke area → file masuk antrian upload
- [ ] Multi-file: progress bar per file individual
- [ ] Upload sukses → file masuk list di halaman documents
- [ ] Tombol "Hapus dari antrian" sebelum upload jalan
- [ ] Setelah selesai → tampil ringkasan "N berhasil, N gagal"

### 9.4 Document Download (`GET /documents/{document}/download`)
- [ ] Klik tombol Download → redirect ke signed URL S3 → browser download file
- [ ] File yang terdownload bisa dibuka tanpa corrupt
- [ ] PDF: render OK di Preview/Acrobat
- [ ] Image: thumbnail tampil
- [ ] DOCX: bisa dibuka di Word/LibreOffice
- [ ] User tanpa visibility access klik download → 403
- [ ] Signed URL expired (>15 menit default) → 403, harus klik download lagi

---

## 10. Meetings

### 10.1 Meetings Index (`GET /meetings`)
**Halaman render**
- [ ] List meeting tampil dengan kolom: title, agenda preview, location, starts_at, ends_at, status badge (planned/in_progress/completed/cancelled), jumlah attendee
- [ ] Tab status atau filter
- [ ] Sort by starts_at desc by default
- [ ] Empty state

**Tombol & flow**
- [ ] Tombol "Buat Meeting" buka form/modal
- [ ] Form: title, agenda (textarea), location, starts_at (datetime), ends_at (datetime), attendees (multi-select member), creator auto = current user
- [ ] starts_at >= now (atau bebas, sesuai design)
- [ ] ends_at >= starts_at → validasi
- [ ] Submit valid → POST `/meetings` → row baru, flash sukses, attendee status default `invited`
- [ ] Notifikasi ke attendees (jika feature aktif) — cek bell

**Klik meeting → modal/halaman detail**
- [ ] Detail tampil: info dasar, list attendee dengan status hadir, agenda, minutes (jika ada)
- [ ] Tombol "Edit Meeting" → form pre-filled, PATCH `/meetings/{id}` jalan
- [ ] Tombol "Cancel Meeting" → status=cancelled, flash sukses
- [ ] Tombol "Mulai Meeting" → status=in_progress
- [ ] Tombol "Selesai Meeting" → status=completed

### 10.2 Meeting Attendance
- [ ] List attendee tampil dengan kolom: nama, status (invited/present/absent/excused), jam check-in
- [ ] Tombol toggle status pada attendee (Invited→Present/Absent/Excused) → PATCH `/meetings/attendees/{attendee}` → status update, badge update
- [ ] User non-creator/admin tidak bisa update status attendee orang lain → 403
- [ ] User self bisa update status sendiri (RSVP)

### 10.3 Meeting Minutes
- [ ] Tab/section "Notulen" buka rich-text editor
- [ ] Tombol "Save Draft" → PATCH `/meetings/{id}/minutes` dengan body (markdown/HTML) → tersimpan
- [ ] Tombol "Publikasi" → minutes status `published`, attendee notif via WA jika opt-in
- [ ] Riwayat versi minutes (jika ada) tampil

### 10.4 Meeting Minutes Export
- [ ] Tombol "Export PDF" → POST `/meetings/{id}/exports?format=pdf` → flash "Export di-queue"
- [ ] Tombol "Export DOCX" → format=docx
- [ ] Cek `document_exports`: row baru dengan document_type=meeting_minutes
- [ ] Buka `/reports/export-queue` → meeting export muncul
- [ ] File jadi → bisa download dan dibuka

### 10.5 Meeting WhatsApp Reminder
- [ ] Tombol "Kirim Reminder WA" pada meeting → POST `/notifications/meeting-alerts` → flash "Reminder masuk antrean"
- [ ] Cek `whatsapp_delivery_logs` → row baru per attendee (yang opt-in)
- [ ] Submit ≥100x dalam 1 jam → 429

**Test role**
- [ ] Owner/Admin/Lead/Coordinator/Secretary: full CRUD meeting + minutes
- [ ] Member: lihat meeting yang dia diundang, RSVP sendiri
- [ ] Viewer: read-only

---

## 11. Attendance (QR + Manual)

### 11.1 Attendance Index (`GET /attendance`)
**Halaman render**
- [ ] List sesi attendance per project / meeting tampil
- [ ] Kolom: title, project/meeting reference, jumlah peserta hadir, total quota, status sesi, expired_at token
- [ ] Filter project, status jalan

### 11.2 Issue QR Token (POST `/attendance/sessions/{session}/qr-tokens`)
- [ ] Tombol "Generate QR" pada sesi → POST → token + expired_at terbuat
- [ ] QR image tampil inline (`<img src="/attendance/qr-image?token=xxx">`)
- [ ] QR image render dengan resolusi cukup (tidak blur), bisa di-scan dengan kamera
- [ ] Tombol "Copy URL Check-in" → URL `/attendance/check-in?token=xxx` tersalin
- [ ] Tombol "Download QR PNG/SVG" → file image terdownload, bisa dibuka

### 11.3 Revoke QR Token (DELETE `/attendance/qr-tokens/{token}`)
- [ ] Tombol "Revoke" pada token aktif → konfirmasi → DELETE jalan, status revoked
- [ ] Coba check-in dengan token yang sudah revoked → error "Token tidak valid"

### 11.4 Manual Check-in (POST `/attendance/sessions/{session}/manual-check-in`)
- [ ] Operator (admin/lead/coordinator) buka sesi → tombol "Manual Check-in"
- [ ] Form: pilih user dari list member (search by name/email/NIM)
- [ ] Submit → POST → user tercatat hadir, list update
- [ ] User sudah check-in di sesi → error "Sudah check-in"
- [ ] User bukan member project → error

### 11.5 QR Check-in Public (POST `/attendance/check-in`)
**User flow (mobile)**
- [ ] Scan QR / buka link `/attendance/check-in?token=xxx` di mobile browser
- [ ] User belum login → redirect login dengan return URL
- [ ] Setelah login → halaman konfirmasi: "Anda akan check-in untuk sesi {nama}"
- [ ] Tombol "Konfirmasi Hadir" → POST → flash "Anda berhasil check-in pada {jam}"
- [ ] Token expired (>10 menit atau setting) → error
- [ ] Token revoked → error
- [ ] User sudah check-in di sesi sama → error "Sudah check-in"
- [ ] Lokasi GPS validation (jika feature aktif) → error jika di luar radius

### 11.6 Attendance Export CSV (`GET /attendance/sessions/{session}/export.csv`)
- [ ] Klik "Export CSV" → file `.csv` terdownload
- [ ] Buka file di Excel/Numbers → header: Nama, Email, Jam Check-in, Status, Metode (qr/manual)
- [ ] Data sesuai dengan list di UI
- [ ] Encoding UTF-8 BOM (karakter Indonesia tidak rusak)

**Test role**
- [ ] Owner/Admin/Lead/Coordinator: bisa generate QR, manual check-in, export
- [ ] Member: hanya check-in via QR
- [ ] Viewer: read-only attendance list

---

## 12. Certificates

### 12.1 Certificates Index (`GET /certificates`)
- [ ] List sertifikat issued tampil dengan kolom: nomor, penerima, project, template, issued_at, status (issued/revoked)
- [ ] Filter project, template, status jalan
- [ ] Search by nomor / nama penerima jalan
- [ ] Tombol "Templates" → `/certificates/templates`
- [ ] Tombol "Issue Sertifikat" → `/certificates/issue`
- [ ] Tombol "Download" pada row → file PDF terdownload
- [ ] Tombol "Verify" → buka `/verify/{token}` di tab baru
- [ ] Tombol "Revoke" → konfirmasi → status revoked, flash sukses

### 12.2 Certificate Templates (`GET /certificates/templates`)
- [ ] List template tampil dengan thumbnail preview
- [ ] Tombol "Tambah Template" → form: nama, background image upload, posisi placeholder text (nama, event, tanggal, signature, QR), font style
- [ ] Submit valid → template baru muncul, thumbnail render
- [ ] Tombol "Edit" pada template → `/certificates/templates/{id}/edit` → form pre-filled
- [ ] Tombol "Hapus" pada template tanpa sertifikat issued → konfirmasi → delete

### 12.3 Issue Certificate Batch (`GET /certificates/issue`)
**Halaman render**
- [ ] Form: project (dropdown), template (dropdown), penerima (multi)
- [ ] Sumber penerima: dari attendance present / manual entry / upload CSV

**Upload CSV**
- [ ] Tombol "Upload CSV" → form file `.csv`
- [ ] CSV format: nama, email, NIM, role (peserta/panitia/narasumber)
- [ ] Submit → preview data → tombol "Konfirmasi Issue"
- [ ] CSV tidak valid → error per baris dengan row number

**Issue batch**
- [ ] Tombol "Issue Batch" → POST `/certificates/issue` → batch terbuat, queue PDF generation per penerima
- [ ] Flash "Batch sertifikat dibuat, X penerima, generation di-queue"
- [ ] Buka queue / index → row per sertifikat dengan nomor + status
- [ ] Setelah generation selesai → tombol Download per sertifikat aktif

### 12.4 Certificate Download (`GET /certificates/{certificateNumber}/download`)
- [ ] Klik download → file PDF terdownload
- [ ] Buka PDF: nama penerima sesuai, tanggal sesuai, nomor sertifikat, QR code mengarah ke `/verify/{token}` valid
- [ ] PDF tidak corrupt, image background render OK
- [ ] User non-org tidak bisa download (403 jika visibility internal)

### 12.5 Public Verify (`/verify/{token}`)
> Sudah dicover di section 1.5

**Test role**
- [ ] Owner/Admin/Secretary: full CRUD template + issue + revoke
- [ ] Lead: bisa issue untuk project sendiri
- [ ] Member: read sertifikat sendiri
- [ ] Viewer: 403



---

## 13. Events / Public Registration

### 13.1 Manage Registrations (`GET /events/registrations`)
**Halaman render**
- [ ] List project event yang punya registrasi public tampil
- [ ] Kolom: nama event, tanggal, jumlah registran, status (open/closed/full), revenue total
- [ ] Filter status, periode jalan

**Klik project → detail registrasi**
- [ ] List registran tampil dengan kolom: nama, email, no.HP, tier, payment status (paid/pending/expired), registered_at, source (organic/referral)
- [ ] Filter payment status jalan
- [ ] Search by nama/email jalan
- [ ] Tombol "Tandai Hadir" pada registran → status attended
- [ ] Tombol "Refund" (jika ada) buka modal alasan → POST refund

### 13.2 Event Registration Settings (`PATCH /events/registrations/{project}/settings`)
- [ ] Tombol "Settings" buka form: enable_public_registration toggle, deadline date, capacity total, list tier (nama, harga, kuota)
- [ ] Submit valid → PATCH jalan, flash sukses
- [ ] Tier dengan harga 0 → tidak butuh payment
- [ ] Capacity = 0 → unlimited
- [ ] Toggle off → public URL `/events/{slug}/register` jadi tidak accessible

### 13.3 Export CSV Registran (`GET /events/registrations/export`)
- [ ] Klik "Export CSV" → file `.csv` terdownload
- [ ] Buka di Excel: header, data sesuai filter aktif
- [ ] Encoding UTF-8 BOM
- [ ] Format tanggal Indonesia (dd/mm/yyyy)

### 13.4 Export PDF Registran (POST `/events/registrations/{project}/export-pdf`)
- [ ] Klik "Export PDF" → flash "Export di-queue", row di document_exports type=event_registration
- [ ] Setelah selesai → download PDF, isi: header event, list registran lengkap, total

### 13.5 Midtrans Webhook
> Lihat 1.7

**Test role**
- [ ] Owner/Admin/Treasurer: full akses settings + refund + export
- [ ] Secretary: lihat list, export CSV
- [ ] Member/Viewer: 403

---

## 14. Approval Workflows (Multi-level)

### 14.1 Workflow Definition (Setup)
> Setup via seeder atau Filament. Cek di section 19.

- [ ] Org bisa punya workflow definition untuk subject_type: proposal, rab, lpj
- [ ] Setiap definition punya N step ordered dengan approver_id
- [ ] is_active toggle

### 14.2 Workflow Decision (`PATCH /approval-workflows/{instance}/decision`)
**Setup**: 1 proposal_draft sudah submitted, instance pending step 1.

- [ ] Login sebagai approver step 1 → buka halaman terkait (proposal-editor) → tombol "Approve" / "Request Changes" tampil
- [ ] Klik "Approve" → PATCH dengan decision=approve → step 1 record decision=approved, instance.current_step=2
- [ ] Login sebagai approver step 2 → tombol Approve tampil
- [ ] Approve di step terakhir → instance.status=approved, subject (proposal/lpj) di-sync (status berubah)
- [ ] Reject di salah satu step → instance.status=rejected, subject revert ke draft/revision
- [ ] Login sebagai non-approver step aktif → tombol tidak tampil / 403

### 14.3 Workflow Delegation (`PATCH /approval-workflows/{instance}/delegate`)
- [ ] Approver step aktif tombol "Delegasikan" → modal pilih user
- [ ] Submit → PATCH → step record approver_id berubah ke delegate target
- [ ] Delegate ke user bukan member org → 422
- [ ] Delegate ke diri sendiri → 422
- [ ] Notifikasi ke delegate target

### 14.4 Approval Timeline / Audit
- [ ] Halaman terkait (proposal/LPJ) tampil timeline audit: siapa approve/reject/delegate kapan
- [ ] Setiap entry tampil avatar + nama + decision + timestamp + alasan (jika ada)

### 14.5 Notifikasi Approval Step
- [ ] Submit subject → approver step 1 dapat notifikasi (in-app + email + WA jika opt-in)
- [ ] Step naik → approver step berikutnya dapat notifikasi
- [ ] Approve final → submitter dapat notifikasi "Disetujui"
- [ ] Reject → submitter dapat notifikasi "Perlu revisi" + alasan

---

## 15. Notifications & WhatsApp

### 15.1 Notification Bell (Header — Semua Halaman Authenticated)
- [ ] Bell icon di header tampil
- [ ] Badge unread count tampil sebagai angka merah (atau dot bila ada)
- [ ] Klik bell → dropdown 5 notifikasi terbaru tampil
- [ ] Tiap notif: judul, body singkat, timestamp relative ("5 menit lalu"), avatar/icon, indicator unread (bold/dot)
- [ ] Klik notif → buka URL `resourceUrl` + auto mark as read (badge -1)
- [ ] Tombol "Tandai Semua Dibaca" → PATCH `/notifications/read-all` → semua read, badge hilang
- [ ] Tombol "Lihat Semua" → `/notifications`
- [ ] Klik di luar dropdown → dropdown tertutup
- [ ] Polling auto-refresh tiap 30-60 detik (cek Network tab)

### 15.2 Notifications Index (`GET /notifications`)
- [ ] Full list semua notifikasi user paginated
- [ ] Filter: All / Unread
- [ ] Filter type/event jalan
- [ ] Klik notif → mark read + arah ke resourceUrl
- [ ] Tombol "Tandai Semua Dibaca" jalan
- [ ] Empty state bila kosong

### 15.3 Notification Recent API (`GET /notifications/recent`)
- [ ] Endpoint return JSON: `{notifications: [...], unreadCount: n}`
- [ ] Format `{id, title, body, url, readAt, createdAt}`
- [ ] Auth required → 401 untuk guest

### 15.4 Mark Read / Mark All Read
- [ ] PATCH `/notifications/{id}/read` user notif sendiri → success, readAt terisi
- [ ] PATCH user notif orang lain → 403/404
- [ ] PATCH `/notifications/read-all` → semua user-self ter-update

### 15.5 WhatsApp Channel
**Setup**: provider WA HTTP terkonfigurasi atau di-mock.

- [ ] Notif yang punya `toWhatsApp()` method → dispatch ke `whatsapp_delivery_logs`
- [ ] Status `queued` → worker pickup → status `sent` setelah HTTP success
- [ ] HTTP fail → status `failed`, retry 3x dengan backoff
- [ ] User `whatsapp_opt_in=false` → channel skip, tidak ada row baru
- [ ] User `whatsapp_number=null` → channel skip
- [ ] User `whatsapp_number` format invalid → channel skip atau error log

### 15.6 In-App + Email + WA Integration
**Test scenario**: submit proposal → owner approve

- [ ] Submitter dapat notif in-app (bell badge +1) dengan judul "Proposal disetujui"
- [ ] Submitter dapat email (cek mailtrap) dengan subject sesuai
- [ ] Submitter dapat WA (cek `whatsapp_delivery_logs`) jika opt-in
- [ ] Submitter opt-out WA → email + bell saja, tidak ada WA log

### 15.7 Notification Rules (Filament)
> Lihat section 19.6

- [ ] Default rules ter-seed
- [ ] Custom rule via Filament → applied saat event tertentu

---

## 16. Profile

### 16.1 Profile Edit (`GET /profile`)
**Halaman render**
- [ ] 3 section card: Update Profile / Update Password / Delete Account
- [ ] Form pre-filled: nama, email, whatsapp_number, whatsapp_opt_in toggle, avatar (jika ada)

**Update Profile**
- [ ] Edit nama → PATCH `/profile` → flash "Profile updated"
- [ ] Edit email → kirim ulang verifikasi → email_verified_at di-reset
- [ ] Edit whatsapp_number format invalid → error
- [ ] Toggle whatsapp_opt_in off → disimpan, notif WA berikut skip
- [ ] Upload avatar (jika feature) → preview update, S3 path tersimpan

**Update Password**
- [ ] Form: current, new, confirm
- [ ] Current salah → error
- [ ] New < 8 char → error
- [ ] Konfirmasi tidak match → error
- [ ] Submit valid → flash "Password updated", session tetap aktif

**Delete Account**
- [ ] Tombol "Hapus Akun" buka modal konfirmasi: input password + checkbox "Saya paham akun akan dihapus"
- [ ] Submit valid → DELETE `/profile` → akun ter-soft-delete, redirect `/`
- [ ] User owner terakhir di org → error "Tidak bisa hapus akun, Anda owner terakhir di org X. Hapus org dulu atau alihkan ownership."



---

## 17. Campus Dashboard (Campus Admin)

### 17.1 Campus Dashboard (`GET /campus/dashboard`)
**Akses**
- [ ] Login `campus@prokerin.test` → halaman accessible
- [ ] Login non-campus_admin (owner/admin/member) → 403
- [ ] Login super_admin → bisa akses (jika design memberikan)

**Halaman render**
- [ ] Header: nama kampus (Universitas Nusantara), tombol switcher kampus (jika campus admin punya >1 kampus)
- [ ] KPI: total org terdaftar, total proker aktif, total mahasiswa terlibat, total dana yang dikelola
- [ ] List organisasi terdaftar di kampus dengan kolom: nama org, jumlah anggota, jumlah proker aktif, plan tier
- [ ] Filter periode jalan
- [ ] Chart distribusi proker per status / per fakultas
- [ ] Klik nama org → arah ke detail org (read-only)

**Tombol & link**
- [ ] Tombol "Export Laporan Kampus" (jika ada) → queue export
- [ ] Tombol "Tambah Organisasi" → buka form (jika campus admin punya privilege)

---

## 18. Admin Workspace (Org-level Admin)

### 18.1 Admin Index (`GET /admin`)
> Catatan: di Inertia route ini berbeda dari Filament `/admin`. Cek `WorkspacePageController::adminIndex`.

**Akses**
- [ ] Login `admin@prokerin.test` (organization_admin) atau `owner@prokerin.test` → halaman accessible
- [ ] Login member/viewer → 403
- [ ] Login super_admin (Spatie) → akses Filament panel saja, atau bisa ke sini juga sesuai design

**Halaman render**
- [ ] KPI org: total members, total proker, total dokumen, total budget
- [ ] Quick actions: undang anggota, buat proker, buat periode
- [ ] Recent activity feed
- [ ] Pending approvals (proposal/RAB/LPJ menunggu keputusan)
- [ ] Integration status (WA, Mail, S3)

**Tombol & link**
- [ ] Setiap shortcut quick action arah ke halaman terkait
- [ ] Tombol "Buka Filament Panel" (jika user juga super_admin) muncul

---

## 19. Filament Super Admin Panel

> URL Filament panel: cek `AdminPanelProvider::path`. Biasanya `/admin` atau `/super-admin`.

### 19.1 Login Filament
- [ ] User non-super_admin (Spatie role check) → 403/redirect
- [ ] User dengan role Spatie `super_admin` → masuk dashboard Filament
- [ ] Throttle login: 5 percobaan/menit per IP+email (`filament-login`)
- [ ] Halaman login Filament render dengan logo

### 19.2 Filament Dashboard Widgets
- [ ] **PlatformStatsOverview**: card 4 — total users, total orgs, total active projects, growth %
- [ ] **PlatformHealthCard**: status DB, Redis, Queue, Storage (hijau/merah)
- [ ] **OrganizationGrowthChart**: line chart 30 hari org baru per hari
- [ ] **UserGrowthChart**: line chart 30 hari user baru
- [ ] **PlanDistributionChart**: pie/donut chart Free/Starter/Pro/Campus
- [ ] **ActiveProkerByPhase**: bar chart proker per ProjectStatus
- [ ] **EngagedOrganizationsTable**: top org berdasarkan aktivitas
- [ ] **FailedJobsCounter**: badge merah dengan jumlah, link ke FailedJobs resource
- [ ] **RecentOrganizationsTable**: 10 org terakhir
- [ ] **RecentUsersTable**: 10 user terakhir + tombol Impersonate inline

### 19.3 Users Resource (`/admin/users`)
**List**
- [ ] Tabel render dengan kolom: email, name, roles (Spatie + org), last_login_at, created_at, status (active/banned)
- [ ] Filter by Spatie role (super_admin/campus_admin/none) jalan
- [ ] Filter by org membership jalan
- [ ] Search nama/email jalan
- [ ] Pagination jalan

**Tombol & flow**
- [ ] Tombol "Create User" buka form
- [ ] Form: name, email, password, role assignment, send invitation
- [ ] Submit valid → user terbuat, ActivityLog tercatat
- [ ] Tombol "Edit" buka form
- [ ] Tombol "Impersonate" pada row → set session `impersonate_started_at` → redirect ke `/dashboard` user tsb
- [ ] Banner impersonation tampil di top semua halaman user yang di-impersonate
- [ ] Tombol "Stop Impersonate" di banner → kembali ke admin Filament
- [ ] Session impersonate ≥ TTL (cek `EnsureImpersonationFresh` middleware) → auto stop
- [ ] Tombol "Delete" → konfirmasi → DELETE jalan
  - [ ] Tidak bisa hapus diri sendiri → disabled/error
  - [ ] Tidak bisa hapus super_admin terakhir → error
- [ ] Tombol "Assign Role" bulk action → assign Spatie role

### 19.4 Organizations Resource (`/admin/organizations`)
**List**
- [ ] Tabel: name, slug, plan_tier badge (Free/Starter/Pro/Campus), status, created_at, jumlah anggota, jumlah proker
- [ ] Filter plan_tier, status jalan
- [ ] Search nama/slug jalan

**Tombol & flow**
- [ ] Tombol "Create Org" → form lengkap → submit → org terbuat
- [ ] Tombol "Edit" buka form pre-filled
- [ ] Tombol "Change Plan" → modal: pilih tier baru + alasan → konfirmasi
  - [ ] Submit → plan_tier update + audit log row di `activity_logs` dengan action=`organization.plan_changed`, payload before/after
- [ ] Tombol "View Members" → arah ke list members org
- [ ] Tombol "Soft Delete" → status=archived, data tidak hilang
- [ ] Tombol "Force Delete" → typed confirm "DELETE {slug}" wajib match → DELETE cascade (members, projects, dll)
  - [ ] Audit log tercatat
- [ ] Tombol "Restore" pada soft-deleted org

### 19.5 Projects Resource (read-only)
- [ ] Tabel project lintas org tampil
- [ ] Filter org & status jalan
- [ ] Klik row → lihat detail (read-only, tidak ada Edit/Delete by spec)

### 19.6 NotificationRules Resource
- [ ] List rule default + custom tampil
- [ ] Tombol "Create Rule" → form: event (dropdown NotificationEvent enum), channels (multi: in_app/email/whatsapp), recipients_role, is_active
- [ ] Submit valid → rule baru
- [ ] Tombol "Edit/Delete" jalan
- [ ] Toggle is_active jalan
- [ ] Test: ubah rule TaskDeadlineReminder hanya in_app → trigger → tidak ada email/WA

### 19.7 ActivityLogs Resource (read-only)
- [ ] List log paginated dengan kolom: timestamp, actor (user), action, subject_type, subject_id, payload (JSON preview)
- [ ] Filter actor, action, subject_type jalan
- [ ] Search action/payload jalan
- [ ] Klik row → view full payload JSON

### 19.8 AiUsageLogs Resource
- [ ] List AI calls dengan kolom: user, action_type (proposal_suggestion/lpj_summary), tokens used, cost, status, created_at
- [ ] Filter by user, action jalan
- [ ] Read-only

### 19.9 Campuses Resource
- [ ] List kampus tampil dengan kolom: nama, domain, admin_user, jumlah org linked
- [ ] CRUD kampus jalan
- [ ] Tombol "Link Org" → pilih org → org tertaut ke kampus

### 19.10 CertificateRecipients Resource
- [ ] List sertifikat issued lintas org tampil
- [ ] Filter org, project, status jalan
- [ ] Tombol "Revoke" jalan
- [ ] Tombol "Regenerate PDF" jalan (queue job)

### 19.11 DocumentExports Resource
- [ ] List export jobs dengan status: queued, processing, completed, failed
- [ ] Filter status, type, format jalan
- [ ] Tombol "Retry" pada failed jalan
- [ ] Tombol "Delete" pada completed (cleanup) jalan

### 19.12 EventRegistrations Resource
- [ ] List registran lintas event tampil
- [ ] Filter project, payment_status jalan
- [ ] Tombol "Mark Paid" / "Refund" jalan
- [ ] Bulk export CSV jalan

### 19.13 FailedJobs Resource
- [ ] List failed jobs tampil dengan kolom: id, queue, exception preview, failed_at
- [ ] Klik row → detail full exception trace
- [ ] Tombol "Retry" → job kembali ke queue
- [ ] Tombol "Delete" → row hilang
- [ ] Bulk action retry/delete jalan

### 19.14 FeatureFlags Resource
- [ ] List flags tampil dengan kolom: key, label, is_enabled toggle, scope (global/org)
- [ ] Toggle is_enabled → langsung tersimpan
- [ ] Tombol "Create Flag" → form: key, label, default value
- [ ] Test: toggle flag `ai_proposal_suggestion` off → tombol AI di proposal editor disabled

### 19.15 OrganizationInvitations Resource
- [ ] List semua undangan lintas org tampil
- [ ] Filter status (pending/accepted/expired/revoked) jalan
- [ ] Tombol "Resend" / "Revoke" jalan

### 19.16 PaymentOrders Resource
- [ ] List payment dari Midtrans tampil dengan kolom: order_id, amount, status, midtrans_response
- [ ] Filter status (paid/pending/expired/failed) jalan
- [ ] Tombol "Resync from Midtrans" (jika ada) jalan

### 19.17 WhatsAppDeliveryLogs Resource
- [ ] List dispatch WA tampil dengan kolom: org, user, message_type, recipient, status (queued/sent/failed), sent_at
- [ ] Filter status, message_type jalan
- [ ] Klik row → detail provider_response
- [ ] Tombol "Retry" pada failed jalan

### 19.18 Custom Pages
**OnboardingChecklistPage** (`/admin/onboarding-checklist`)
- [ ] List checklist setup awal Prokerin (mail config, S3, queue, AI key, Midtrans)
- [ ] Status per item: ✓ done / × pending / ⚠ warning
- [ ] Klik item → instruksi setup

**PrkAdminDashboard** (`/admin/prk-dashboard`)
- [ ] Custom dashboard render dengan widget tertentu (jika ada)

**SystemHealthPage** (`/admin/system-health`)
- [ ] Status DB ping (latency ms)
- [ ] Status Redis ping
- [ ] Queue depth per queue (default/exports/notifications)
- [ ] Storage usage (S3 bucket size)
- [ ] Last failed job timestamp
- [ ] Tombol "Refresh" → re-fetch metrics



---

## 20. Cross-Cutting Concerns

### 20.1 Sidebar Navigation
- [ ] Sidebar render menu sesuai role (lihat section 23 untuk matrix)
- [ ] Item menu disorot saat halaman aktif (active state, biasanya bg primary)
- [ ] Submenu collapsible (klik panah → expand/collapse)
- [ ] State expand/collapse persisted di localStorage (refresh tetap)
- [ ] Logo org switcher di top sidebar buka dropdown daftar org
- [ ] Klik org lain di dropdown → switch org
- [ ] Logout button di footer sidebar jalan
- [ ] Sidebar collapse mode: tombol toggle → sidebar narrow icon-only mode

### 20.2 Header
- [ ] Logo org + nama tampil di top
- [ ] Search global (jika ada) → typeahead hasil per project/task/dokumen jalan
- [ ] Notification bell (section 15.1)
- [ ] Avatar dropdown: profile, settings, logout — semua link jalan
- [ ] Dark mode toggle: klik → tema berubah tanpa flicker, persisted di localStorage
- [ ] Breadcrumbs render sesuai halaman (Dashboard > Proker > Detail)

### 20.3 Footer
- [ ] Copyright tahun dinamis (2026)
- [ ] Link kebijakan & syarat ke halaman valid
- [ ] Versi app tampil (dari `config('app.version')`)

### 20.4 Empty States (semua list)
- [ ] List proker kosong → empty state dengan CTA "Buat Proker Pertama"
- [ ] List task kosong → empty state
- [ ] List dokumen kosong → empty state
- [ ] List notifikasi kosong → empty state
- [ ] List members kosong (mustahil tapi) → empty state
- [ ] Search tanpa hasil → "Tidak ada hasil untuk '{query}'" + saran

### 20.5 Error States
- [ ] Akses URL tidak ada → halaman 404 custom dengan logo + tombol "Kembali ke Dashboard"
- [ ] Error 403 → halaman 403 custom dengan pesan "Anda tidak memiliki akses"
- [ ] Error 419 (CSRF expired) → instruksi "Sesi habis, refresh halaman"
- [ ] Error 500 → halaman 500 custom (production)
- [ ] Validasi server (422) → tampil di field (Inertia form errors)
- [ ] Toast/flash error muncul untuk action yang gagal
- [ ] Network error (offline) → toast "Koneksi terputus, coba lagi"

### 20.6 Responsive
**Test viewport 375px (mobile), 768px (tablet), 1280px (desktop), 1920px (FHD)**

- [ ] Sidebar collapse jadi drawer di mobile (≤768px), tombol hamburger di header
- [ ] Header tetap sticky di mobile
- [ ] Tabel scroll horizontal di mobile (tidak break layout)
- [ ] Form 2-kolom jadi 1-kolom di mobile
- [ ] Modal full-screen di mobile, max-w-md di desktop
- [ ] Card grid: 1 col mobile, 2 col tablet, 3-4 col desktop
- [ ] Font size readable di semua viewport (min 14px body)
- [ ] Tombol touch-friendly di mobile (min 44x44 px)

### 20.7 Dark Mode
- [ ] Setiap halaman utama render benar di dark mode:
  - [ ] Dashboard
  - [ ] Proker (index, detail, create, edit)
  - [ ] Task (kanban, calendar, assignments)
  - [ ] Finance (index, draft, realization, approval)
  - [ ] Reports (proposal-editor, lpj-checklist, export-queue)
  - [ ] Documents
  - [ ] Meetings
  - [ ] Attendance
  - [ ] Certificates
  - [ ] Events
  - [ ] Members, Profile, Organization pages
  - [ ] Notifications
  - [ ] Auth pages
  - [ ] Landing pages
- [ ] Tidak ada teks hitam di background hitam / putih di putih
- [ ] Chart pakai tema yang readable di dark mode (bukan white-on-white)
- [ ] Code blocks (jika ada) syntax highlight readable

### 20.8 Accessibility (a11y)
- [ ] Form field semua punya `<label>` linked dengan `for=`
- [ ] Tombol icon-only punya `aria-label`
- [ ] Modal dapat di-close dengan Escape key
- [ ] Modal trap focus di dalam modal saat terbuka
- [ ] Focus-visible ring ada di tombol/input
- [ ] Tab order logis di setiap form (top-down, left-right)
- [ ] Skip-to-content link untuk screen reader
- [ ] Heading order benar (H1 → H2 → H3, tidak loncat)
- [ ] Alt text untuk semua image
- [ ] Color contrast WCAG AA (min 4.5:1 untuk teks normal)
- [ ] Form error diumumkan oleh screen reader (`aria-live="polite"`)

### 20.9 Performance
- [ ] Initial page load < 3 detik di local
- [ ] Tidak ada query N+1 (cek dengan Laravel Debugbar / Telescope)
- [ ] Asset Vite ter-bundle (production: `npm run build` sukses, dist size reasonable)
- [ ] Image lazy-load di list panjang
- [ ] Tidak ada console.warn / console.error di prod build
- [ ] Time to Interactive < 3.5 detik (Lighthouse)
- [ ] Largest Contentful Paint < 2.5 detik
- [ ] Cumulative Layout Shift < 0.1

### 20.10 Security
- [ ] CSRF token aktif di semua form POST/PATCH/DELETE (cek `<meta name="csrf-token">`)
- [ ] Tenant scope: user org A coba akses URL `/proker/{id-org-B}` → 403/404
- [ ] Tenant scope: user org A coba akses `/finance?project={id-org-B}` → 403
- [ ] Upload file `.svg` → ditolak (XSS via SVG)
- [ ] Upload file `.php`, `.exe`, `.bat`, `.sh` → ditolak
- [ ] Upload file dengan polyglot (rename .php → .pdf) → cek MIME real, ditolak
- [ ] Signed URL S3 expire setelah 15 menit default
- [ ] Headers HTTP di response (cek di DevTools Network):
  - [ ] X-Frame-Options: DENY atau SAMEORIGIN
  - [ ] X-Content-Type-Options: nosniff
  - [ ] Strict-Transport-Security (di staging/prod HTTPS)
  - [ ] Referrer-Policy: same-origin
- [ ] Cookie `XSRF-TOKEN` & `laravel_session`: httpOnly + secure (di prod) + SameSite=Lax
- [ ] SQL injection test: input `'; DROP TABLE users; --` di search → di-escape, tidak crash
- [ ] XSS test: input `<script>alert('xss')</script>` di nama proker → di-escape, tampil sebagai teks
- [ ] No secrets di response HTML (cek view source: tidak ada AWS_KEY, dll)
- [ ] Environment `production` tidak tampilkan stack trace ke user
- [ ] Login bruteforce: 5 percobaan gagal → 429 (lihat 2.1)



---

## 21. Background Jobs / Queue

### 21.1 Document Export Job (`GenerateDocumentExportJob`)
**Trigger**: submit proposal/LPJ/handover/event-registration → row di `document_exports` queued.

- [ ] Queue worker pickup job → status `processing`
- [ ] Browsershot dipakai untuk PDF → file render dari Blade view, tersimpan di S3
- [ ] PHPWord dipakai untuk DOCX → file tersimpan di S3
- [ ] Setelah selesai → status `completed`, `output_path` terisi
- [ ] Failure (mis. Browsershot tidak bisa launch chrome): status `failed`, exception_message terisi
- [ ] Tombol Retry di UI (Reports/Export Queue + Filament FailedJobs) jalan
- [ ] User dapat notifikasi "Export selesai" / "Export gagal"

### 21.2 WhatsApp Dispatch Job (`SendWhatsAppReminderJob`)
- [ ] Trigger reminder → row di `whatsapp_delivery_logs` status `queued`
- [ ] Worker dispatch ke provider HTTP → status `sent` + `provider_response`, `sent_at` terisi
- [ ] Provider return error → status `failed`, retry sesuai backoff (60s, 300s, 900s)
- [ ] User opt-out → tidak ada row baru saat trigger
- [ ] User WA number invalid → log skip
- [ ] Job retry exhausted (3x fail) → `QueueJobFailedNotification` ke user

### 21.3 Notification Job (Notification ShouldQueue)
- [ ] Trigger notify → entry di `notifications` table (in-app)
- [ ] Mail channel dispatch saat queue jalan
- [ ] WA channel dispatch terpisah ke `SendWhatsAppReminderJob`
- [ ] Test environment `MAIL_MAILER=array` → email tertangkap di `Mail::fake()` tidak crash

### 21.4 Task Deadline Reminder Job
- [ ] Schedule daily run (jika ada di Console Kernel) → cek task overdue dan kirim reminder
- [ ] Manual trigger via POST `/notifications/task-deadline-reminders` jalan

### 21.5 Meeting WhatsApp Alert Job
- [ ] Trigger H-1 / H-30 menit (sesuai schedule) → kirim WA ke attendee
- [ ] Manual trigger via POST `/notifications/meeting-alerts` jalan

### 21.6 Event Registration PDF Export Job
- [ ] Trigger via tombol UI → queue → file PDF di S3 → download tersedia

### 21.7 Handover Package Export Job
- [ ] Trigger via tombol UI → queue → file PDF di S3 → download tersedia

### 21.8 Failed Jobs Handling
- [ ] Job gagal masuk ke `failed_jobs` table
- [ ] Filament FailedJobs resource tampil semuanya
- [ ] Retry dari Filament jalan
- [ ] Email notif ke super_admin saat failed_jobs > threshold (jika ada feature)

---

## 22. File Output Verification

> Section khusus untuk verifikasi setiap file yang dihasilkan oleh sistem **bisa dibuka, isinya benar, dan tidak corrupt**.

### 22.1 Proposal Export PDF
**Trigger**: submit proposal → tunggu queue selesai → download dari `/reports/export-queue`.

- [ ] File `.pdf` terdownload dengan nama: `proposal-{slug}-{date}.pdf`
- [ ] Buka di Preview (macOS) / Adobe Reader → tidak corrupt
- [ ] Halaman 1: cover dengan nama proker, organisasi, periode
- [ ] Halaman berikutnya: section proposal sesuai data (Latar Belakang, Tujuan, Sasaran, dll)
- [ ] Format teks Indonesia tidak rusak (huruf é, ñ, simbol Rp)
- [ ] Ukuran A4
- [ ] Footer: nomor halaman + nama org
- [ ] Logo organisasi muncul di header

### 22.2 Proposal Export DOCX
- [ ] File `.docx` terdownload
- [ ] Buka di MS Word / LibreOffice / Google Docs → tidak error
- [ ] Section terformat dengan heading yang bisa di-edit
- [ ] Text Indonesia rendered correctly
- [ ] Editable (bukan image/snapshot)

### 22.3 LPJ Export PDF
- [ ] File `.pdf` terdownload dengan nama `lpj-{slug}-{date}.pdf`
- [ ] Section: ringkasan kegiatan, realisasi anggaran (tabel), dokumentasi (image embed), evaluasi, lampiran
- [ ] Grafik (jika ada) render OK
- [ ] Total realized di tabel sesuai dengan data DB

### 22.4 LPJ Export DOCX
- [ ] Tabel realisasi anggaran rapi
- [ ] Image embed dari S3 muncul (bukan placeholder)

### 22.5 Meeting Minutes PDF/DOCX
- [ ] File terdownload sesuai format
- [ ] Header: title meeting, tanggal, lokasi, attendee list
- [ ] Body: notulen sesuai yang di-publish
- [ ] Daftar hadir dengan status

### 22.6 Event Registration PDF
- [ ] File terdownload
- [ ] Header: nama event, organizer
- [ ] Tabel registran: no, nama, email, no.HP, tier, status pembayaran, registered_at
- [ ] Footer: total registran, total pendapatan

### 22.7 Handover Package PDF
- [ ] File terdownload dengan nama `handover-{org}-{period}.pdf`
- [ ] Section: info paket, list items dengan status, dokumen attached (link)
- [ ] Cover dengan logo + tanda tangan blok

### 22.8 Sertifikat PDF (per recipient)
- [ ] File terdownload dengan nama `certificate-{number}.pdf`
- [ ] Background image template render benar
- [ ] Nama penerima centered, font besar
- [ ] Tanggal terbit, nomor sertifikat
- [ ] QR code di sudut → scan via mobile → buka `/verify/{token}` valid
- [ ] Tanda tangan / signature (image atau ttd) muncul

### 22.9 Attendance Export CSV
- [ ] File `attendance-{session}-{date}.csv` terdownload
- [ ] Buka di Excel/Numbers → kolom: No, Nama, Email, NIM, Status, Jam Check-in, Metode
- [ ] Encoding UTF-8 BOM → karakter Indonesia tidak ‹÷
- [ ] Format tanggal Indonesia atau ISO 8601
- [ ] Total row sesuai dengan list di UI

### 22.10 Event Registration CSV
- [ ] File `registrations-{event}-{date}.csv` terdownload
- [ ] Kolom: nama, email, no HP, tier, payment_status, payment_amount, registered_at, attended

### 22.11 Members Export CSV (jika ada)
- [ ] Kolom: nama, email, role, divisi, joined_at, status
- [ ] Encoding UTF-8 BOM

### 22.12 Logo & Image Upload
- [ ] Org logo upload PNG → preview tampil di sidebar dan halaman setup
- [ ] Org logo SVG → ditolak (XSS)
- [ ] User avatar upload → preview di profile + header
- [ ] Image >2MB → ditolak

### 22.13 QR Image Render (`/attendance/qr-image?token=xxx`)
- [ ] Image SVG/PNG render dengan token encoded
- [ ] Scan via mobile camera → URL valid
- [ ] Resolusi cukup untuk print (≥200x200 px)

### 22.14 PDF Sertifikat dari Verify Page
- [ ] `/verify/{token}` → tombol Download PDF → file terdownload, sama dengan yang di certificates index

### 22.15 Receipt Document (Finance)
- [ ] Upload receipt JPG/PDF → tampil thumbnail di realisasi
- [ ] Klik → buka full size di tab baru (signed URL)
- [ ] Download → file terdownload, bisa dibuka



---

## 23. Role-Based Access Matrix (Per Page)

> Verifikasi aktual akses tiap halaman per role. Gunakan kolom: ✓ = bisa akses penuh, R = read-only, ✗ = 403/forbidden, — = tidak applicable.
>
> **Cara test**: login pakai akun role tsb → coba buka URL → catat hasil aktual. Bandingkan dengan ekspektasi.

### 23.1 Halaman Inertia (Auth Required)

| Halaman / URL | O | A | S | T | L | C | M | V | CA | SA |
|---|---|---|---|---|---|---|---|---|---|---|
| `/dashboard` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | ✓ | ✓ |
| `/proker` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/proker/create` | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | — | ✓ |
| `/proker/{id}` (own org) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/proker/{id}` (other org) | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | R(linked) | ✓ |
| `/proker/{id}/edit` | ✓ | ✓ | ✗ | ✗ | ✓(own) | ✗ | ✗ | ✗ | — | ✓ |
| `/proker/templates` | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ | R | R | — | ✓ |
| `/proker/status-flow` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/tasks`, `/tasks/kanban`, `/tasks/calendar`, `/tasks/assignments` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓(own) | R | — | ✓ |
| `/finance` | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/finance/budget-draft` | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/finance/realization` | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/finance/approval` | ✓ | ✓ | ✗ | R | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/reports` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/reports/proposal-editor` | ✓(approve) | ✓ | ✓(edit) | ✗ | ✓(submit) | ✗ | R | R | — | ✓ |
| `/reports/lpj-checklist` | ✓(approve) | ✓ | ✓(edit) | ✗ | ✓ | ✗ | R | R | — | ✓ |
| `/reports/export-queue` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓(own) | R | — | ✓ |
| `/documents` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓(visibility) | R(visibility) | — | ✓ |
| `/documents/folders` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/documents/upload-center` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | — | ✓ |
| `/meetings` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | R | — | ✓ |
| `/attendance` | ✓ | ✓ | ✓ | ✗ | ✓ | ✓ | ✓(check-in) | ✗ | — | ✓ |
| `/certificates` | ✓ | ✓ | ✓ | ✗ | ✓(own project) | ✗ | R(own) | ✗ | — | ✓ |
| `/certificates/templates` | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/certificates/issue` | ✓ | ✓ | ✓ | ✗ | ✓(own) | ✗ | ✗ | ✗ | — | ✓ |
| `/events/registrations` | ✓ | ✓ | ✓ | ✓ | ✓(own) | ✗ | ✗ | ✗ | — | ✓ |
| `/notifications`, bell | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `/profile` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `/organization` (setup/edit) | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/organization/switcher` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `/organization/periods` | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| `/organization/calendar` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | R | — | ✓ |
| `/organization/handover` | ✓ | ✓ | ✓ | ✓ | ✓(own) | ✗ | R | R | — | ✓ |
| `/organization/sponsors-vendors` | ✓ | ✓ | ✓ | ✓ | R | ✗ | R | R | — | ✓ |
| `/members`, `/members/invites`, `/members/roles` | ✓ | ✓ | R | R | R | R | R | ✗ | — | ✓ |
| `/campus/dashboard` | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ | ✓ |
| `/admin` (Inertia workspace) | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | — | ✓ |
| Filament `/admin` panel | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ |

### 23.2 Tindakan Mutasi Per Role

> Untuk endpoint mutasi (POST/PATCH/DELETE), uji 2 role: yang seharusnya bisa + yang seharusnya tidak.

| Action | Bisa (✓) | Tidak Bisa (✗ → 403) |
|---|---|---|
| Buat proker | O, A, S, L | T, C, M, V |
| Edit proker | O, A, L(own) | S, T, C, M, V |
| Hapus proker | O, A | semua lainnya |
| Transition status proker | O, A, L(own) | semua lainnya |
| Buat task | O, A, L, C, S | T, M, V |
| Update status task | O, A, L, C, PIC(own) | non-PIC, V |
| Reassign PIC task | O, A, L, C | S, T, M, V |
| Buat budget line | O, A, T | semua lainnya |
| Approve budget | O, A | T(submit), S, L, C, M, V |
| Edit proposal draft | S(author), O, A | semua lainnya |
| Submit proposal | S(author), O, A, L | T, C, M, V |
| Approve proposal | O, A | semua lainnya (juga submitter) |
| Toggle LPJ checklist item | S, L, O, A | T, C, M, V |
| Submit LPJ review | S, L, O, A | T, C, M, V |
| Approve LPJ | O, A | semua lainnya |
| Upload dokumen | O, A, S, T, L, C, M | V |
| Hapus dokumen | O, A, owner | non-owner |
| Buat meeting | O, A, S, L, C | T, M, V |
| Update kehadiran meeting | creator, O, A | non-creator (kecuali update self RSVP) |
| Issue QR token attendance | O, A, L, C | S, T, M, V |
| Manual check-in attendance | O, A, L, C | S, T, M, V |
| Issue sertifikat | O, A, S, L(own) | T, C, M, V |
| Revoke sertifikat | O, A | semua lainnya |
| Settings event registration | O, A, T, L(own) | S, C, M, V |
| Refund event registration | O, A, T | semua lainnya |
| Undang anggota org | O, A | semua lainnya |
| Hapus anggota org | O, A | semua lainnya |
| Ubah role anggota | O, A | semua lainnya |
| Tambah periode org | O, A | semua lainnya |
| Tambah sponsor/vendor | O, A, T, S | L, C, M, V |
| Buat handover package | O, A | S, T, L, C, M, V |
| Approve workflow step | approver step aktif | non-approver |
| Delegate workflow step | approver step aktif | non-approver |
| Impersonate user | SA | semua lainnya |
| Change plan tier org | SA | semua lainnya |
| Force delete org | SA | semua lainnya |
| Akses Filament | SA | semua lainnya |

### 23.3 Tenant Scope Test (Cross-Org Leak)

> Penting: setiap test berikut WAJIB lulus untuk security.

- [ ] Login `owner@prokerin.test` (org BEM FT) → akses URL `/proker/{slug-dari-HIMA}` → 403 atau 404
- [ ] Login `owner@prokerin.test` → API list proker → hanya proker BEM FT yang muncul
- [ ] Login `owner2@prokerin.test` (UKM Kreatif) → akses dokumen org BEM FT via direct URL signed → 403
- [ ] Login `treasurer@prokerin.test` → buka `/finance?project={id-proker-org-lain}` → 403
- [ ] Login user member 2 org → switch org → cek list proker, dokumen, members tampil sesuai org aktif saja
- [ ] Login member → coba PATCH `/proker/{id-org-lain}/status` via curl/Postman → 403
- [ ] User org A coba akses `/admin` Filament tanpa role super_admin → 403
- [ ] User org A coba GET `/api/...` (jika ada) tanpa scope → tidak bocorkan data org B



---

## 24. Smoke Test 15 Menit

> Set rapid smoke test untuk verifikasi cepat sebelum/sesudah deploy. Total ~15 menit, satu tester.

### Skenario A: Owner Lifecycle (5 menit)
1. [ ] Login `owner@prokerin.test` → dashboard render
2. [ ] Buka `/proker` → list muncul dengan 1 proker `Seminar Karier Digital`
3. [ ] Klik proker → detail render dengan tabs lengkap
4. [ ] Buka `/finance` → KPI + chart render
5. [ ] Buka `/reports/proposal-editor` → pilih proker → editor render
6. [ ] Buka `/reports/lpj-checklist` → pilih proker → checklist render
7. [ ] Buka `/documents` → list 3 dokumen muncul
8. [ ] Buka `/meetings` → list 2 meeting muncul
9. [ ] Buka `/notifications` → list muncul
10. [ ] Logout → redirect `/`

### Skenario B: Secretary Workflow (5 menit)
1. [ ] Login `sekretaris@prokerin.test`
2. [ ] Buka `/reports/proposal-editor` → pilih `Seminar Karier Digital`
3. [ ] Edit section "Latar Belakang" → klik "Simpan Draft" → flash sukses
4. [ ] Klik "Submit untuk Approval" → flash, status berubah submitted
5. [ ] Buka `/reports/export-queue` → row baru queued
6. [ ] Buka `/meetings` → buat meeting baru → submit → muncul di list
7. [ ] Logout

### Skenario C: Treasurer Finance (3 menit)
1. [ ] Login `bendahara@prokerin.test`
2. [ ] Buka `/finance` → akses OK
3. [ ] Buka `/finance/budget-draft` → list line tampil
4. [ ] Tambah budget line baru → row muncul
5. [ ] Buka `/finance/realization` → upload receipt → file ter-upload, realized update
6. [ ] Logout

### Skenario D: Member + Viewer (2 menit)
1. [ ] Login `member@prokerin.test` → akses `/finance` → 403
2. [ ] Akses `/proker` → bisa lihat list, tombol "Buat" tidak ada
3. [ ] Akses `/tasks` → list task assigned ke member tampil
4. [ ] Update status task own → sukses
5. [ ] Logout
6. [ ] Login `viewer@prokerin.test` → akses kebanyakan halaman read-only, tidak ada tombol mutasi
7. [ ] Logout

---

## 25. Pre-QA Automated Gate

> WAJIB lulus sebelum mulai manual QA.

- [ ] `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → semua test PASS, 0 failure
- [ ] `./vendor/bin/pint --test` → 0 issue
- [ ] `npm run build` → build sukses tanpa warning fatal
- [ ] `npm run lint` → 0 eslint error
- [ ] `php artisan route:list` → tidak ada error, semua route ter-resolve
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache` → sukses (uncache lagi setelah dev)
- [ ] `php artisan migrate:fresh --seed --env=testing` → sukses

---

## 26. Sign-Off

### 26.1 Tabel Hasil

| Section | Total Test | Pass | Fail | N/A | % Pass |
|---|---|---|---|---|---|
| 0. Persiapan | __ | __ | __ | __ | __% |
| 1. Public/Guest | __ | __ | __ | __ | __% |
| 2. Auth | __ | __ | __ | __ | __% |
| 3. Onboarding & Org | __ | __ | __ | __ | __% |
| 4. Members | __ | __ | __ | __ | __% |
| 5. Proker | __ | __ | __ | __ | __% |
| 6. Tasks | __ | __ | __ | __ | __% |
| 7. Finance | __ | __ | __ | __ | __% |
| 8. Reports | __ | __ | __ | __ | __% |
| 9. Documents | __ | __ | __ | __ | __% |
| 10. Meetings | __ | __ | __ | __ | __% |
| 11. Attendance | __ | __ | __ | __ | __% |
| 12. Certificates | __ | __ | __ | __ | __% |
| 13. Events | __ | __ | __ | __ | __% |
| 14. Approval Workflows | __ | __ | __ | __ | __% |
| 15. Notifications | __ | __ | __ | __ | __% |
| 16. Profile | __ | __ | __ | __ | __% |
| 17. Campus | __ | __ | __ | __ | __% |
| 18. Admin Workspace | __ | __ | __ | __ | __% |
| 19. Filament SA | __ | __ | __ | __ | __% |
| 20. Cross-Cutting | __ | __ | __ | __ | __% |
| 21. Background Jobs | __ | __ | __ | __ | __% |
| 22. File Output | __ | __ | __ | __ | __% |
| 23. Role Matrix | __ | __ | __ | __ | __% |
| **TOTAL** | __ | __ | __ | __ | __% |

### 26.2 Bug Tracker Summary

| ID | Section | Severity | Status | Assignee | Issue Link |
|---|---|---|---|---|---|
| QA-MANUAL-001 | _ | Critical/High/Medium/Low | Open/InProgress/Fixed | _ | _ |

> Severity:
> - **Critical**: blocker login/auth, data loss, security leak (cross-org), payment broken
> - **High**: fitur utama tidak jalan (tidak bisa buat proker, submit proposal, approve, dll)
> - **Medium**: fitur sekunder bermasalah (export, filter, dark mode patah)
> - **Low**: kosmetik, typo, minor UX

### 26.3 Kriteria Release

- [ ] **100%** test case section 25 (Pre-QA Gate) PASS
- [ ] **0** Critical bug
- [ ] **≤ 2** High bug (dengan workaround terdokumentasi)
- [ ] **≥ 95%** total test case PASS
- [ ] Tenant scope test (23.3) **100% PASS** (security non-negotiable)
- [ ] File output verification (section 22) **100% PASS** untuk format yang user-facing

### 26.4 Sign-Off Block

| Role | Nama | Tanggal | Build / Commit | Tanda Tangan |
|---|---|---|---|---|
| QA Lead | __________ | __/__/____ | `git rev-parse HEAD` | __________ |
| Tech Lead | __________ | __/__/____ | __________ | __________ |
| Product Owner | __________ | __/__/____ | __________ | __________ |

### 26.5 Catatan & Rekomendasi

```
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
__________________________________________________________________________
```

---
