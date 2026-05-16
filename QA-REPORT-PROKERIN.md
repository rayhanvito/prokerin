# QA Report Prokerin

Tanggal update: 2026-05-17  
Scope: hasil QA sementara dari automated test, browser smoke, dan checklist manual di `QA-MASTER-PROKERIN.md`.

> Catatan penting: mulai sekarang laporan QA ditulis di file ini. `features.md` tidak dipakai lagi untuk catatan hasil QA, bug list, atau temuan UI.

---

## 1. Ringkasan Terakhir

Status automated regression terakhir:

| Check | Status | Hasil |
|---|---|---|
| PHP feature/unit test | Pass | `352 passed, 1740 assertions` |
| Targeted auth/security | Pass | `35 passed, 99 assertions` |
| Targeted org/member/proker | Pass | `36 passed, 139 assertions` |
| Targeted dashboard/workspace | Pass | `48 passed, 415 assertions` |
| Targeted workspace/org/member/proker smoke | Pass | `20 passed, 226 assertions` |
| Pint targeted | Pass | Tidak ada formatting violation |
| npm lint | Pass | `tsc --noEmit` baseline |
| npm build | Pass | Production build sukses |

Area yang sudah cukup aman dari automated QA:

- Auth dasar: register, login, logout, remember me, password reset, email verification prompt.
- Security guest redirect: `/dashboard`, `/proker/create`, `/finance`, `/internal-admin`.
- Dashboard role variant dan sidebar filtering.
- Super Admin access, asset loading, icon sizing, impersonation audit, destructive action hardening.
- Organization logo upload dan MIME rejection.
- Role matrix, role update guard, dan last-owner protection.
- Proker create, detail, edit, archive, duplicate slug, member create denial.
- Template generation: proker + tasks + RAB + proposal + LPJ checklist dibuat atomik.

---

## 2. Bug Yang Sudah Ditemukan Dan Fixed

| ID | Severity | Area | Masalah | Status |
|---|---|---|---|---|
| BUG-001 | Medium | QA Tooling | `npm run lint` belum tersedia sehingga gate lint gagal. | Fixed |
| BUG-002 | High | Seeder QA | Test user matrix tidak lengkap. | Fixed |
| BUG-003 | Low | Pricing Page | React duplicate key warning di tabel perbandingan pricing. | Fixed |
| BUG-004 | Low | Mobile App Shell | Tombol mobile sidebar icon-only tanpa accessible label. | Fixed |
| BUG-005 | High | Super Admin Impersonation | Stop impersonation bisa lewat package route tanpa audit Prokerin. | Fixed |
| BUG-006 | Medium | Super Admin Document Export | Resource export menampilkan raw `output_path` dan masih membuka create/edit route. | Fixed |
| BUG-007 | Medium | Super Admin User Delete | Delete user belum pakai typed confirmation. | Fixed |
| BUG-008 | High | Super Admin Assets | `/internal-admin` sempat load tanpa Filament CSS/JS sehingga icon membesar dan layout rusak. | Fixed |

Tidak ada bug baru berstatus `Open` dari pass terakhir. Yang masih banyak adalah area belum diuji manual/automated, bukan bukti rusak.

---

## 3. Open Findings Untuk Dev

Temuan di bawah bukan crash, tapi fitur/tombol belum benar-benar berfungsi end-to-end. Halaman render, tetapi beberapa data masih static atau action belum tersambung ke route mutasi.

| ID | Severity | Area | Temuan | Bukti Teknis | Dampak |
|---|---|---|---|---|---|
| QA-OPEN-001 | High | Organization Switcher | Tombol `Buat Organisasi` hanya button biasa dan belum ada form/route create organization. | `resources/js/Pages/Organization/Switcher.tsx:49`, `routes/web.php` hanya punya GET `/organization` dan GET `/organization/switcher`. | User tidak bisa membuat organisasi dari UI ini. |
| QA-OPEN-002 | High | Organization Switcher | Daftar organisasi masih static array, bukan payload dari membership user aktif. Klik organisasi juga belum punya action switch. | `resources/js/Pages/Organization/Switcher.tsx:8`, `app/Http/Controllers/WorkspacePageController.php:120`. | Multi-org user belum bisa benar-benar pindah workspace. |
| QA-OPEN-003 | High | Organization Periods | Tombol `Tambah Periode` belum membuka form atau submit backend; data periode masih static rows. | `resources/js/Pages/Organization/Periods.tsx:8`, `resources/js/Pages/Organization/Periods.tsx:52`, `app/Http/Controllers/WorkspacePageController.php:125`. | User belum bisa membuat periode kepengurusan dari halaman ini. |
| QA-OPEN-004 | High | Member Invites | Tombol `Invite` belum punya form/submit route; invitation queue masih static sample. | `resources/js/Pages/Members/Invites.tsx:8`, `resources/js/Pages/Members/Invites.tsx:52`, `app/Http/Controllers/WorkspacePageController.php:263`. | Invite member, duplicate invite, accept/decline belum dapat diuji end-to-end. |
| QA-OPEN-005 | Medium | Organization Calendar | Calendar masih overview/static navigation, belum calendar data-backed. | `resources/js/Pages/Organization/Calendar.tsx`, `app/Http/Controllers/WorkspacePageController.php:130`. | QA belum bisa validasi event/proker muncul di kalender organisasi. |

Catatan verifikasi tambahan:

- `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/OrganizationLogoUploadTest.php tests/Feature/OrganizationMemberRoleUpdateTest.php tests/Feature/ProjectTemplateGenerationTest.php` -> `20 passed, 226 assertions`.
- Smoke test membuktikan route/page utama render, bukan membuktikan tombol dummy di atas sudah berfungsi.

---

## 4. Fitur Belum Terverifikasi

Bagian ini penting untuk dev karena item di bawah belum boleh dianggap aman untuk rilis beta walaupun full test suite hijau.

### Organization

| ID QA | Area | Yang Belum Terverifikasi |
|---|---|---|
| 5.1 | Create organization | Belum ada route/form create organization end-to-end. Lihat QA-OPEN-001. |
| 5.2 | Duplicate slug | Belum ada route create organization, jadi validation duplicate slug belum bisa diuji. |
| 5.5 | Active period | Belum ada form/route create active organization period. Lihat QA-OPEN-003. |
| 5.6 | Org switcher | Data masih static dan belum ada action switch. Lihat QA-OPEN-002. |
| 5.7 | Calendar | Organization calendar belum data-backed. Lihat QA-OPEN-005. |
| 5.8 | Edit organization | Edit nama organisasi dan tampil di sidebar/pages. |

### Member & Role Management

| ID QA | Area | Yang Belum Terverifikasi |
|---|---|---|
| 6.1 | Invite member | Belum ada form/submit route invite member. Lihat QA-OPEN-004. |
| 6.2 | Duplicate invite | Belum ada submit route invite, jadi duplicate guard belum bisa diuji. |
| 6.3 | Accept invitation | Accept token/route belum terlihat dari QA pass ini. |
| 6.4 | Decline invitation | Decline token/route belum terlihat dari QA pass ini. |
| 6.5 | Role promotion | Promote member ke treasurer lalu akses finance muncul. |
| 6.6 | Role demotion | Demote admin ke member lalu approval access hilang. |
| 6.8 | Remove member | Member dihapus dan kehilangan akses org. |

### Proker & Template

| ID QA | Area | Yang Belum Terverifikasi |
|---|---|---|
| 7.5 | Status transition | Planning -> Active -> Completed. |
| 7.8 | Progress calculation | Semua task selesai membuat progress 100%. |
| 8.1 | Template library | Halaman `/templates` menampilkan template seeded. |
| 8.4 | Customize generated project | Generated project bisa diedit setelah dibuat dari template. |
| 8.5 | Generate same template twice | Generate template yang sama dua kali tanpa konflik. |

### Task, Finance, Proposal, Document, LPJ

Area ini masih butuh QA lanjutan paling banyak:

- Task board: kanban load, status advance, assign PIC, non-member guard, calendar, overdue state, quick-add.
- Finance: create/edit/delete budget line, receipt upload/download, MIME rejection, submit approval, approve/reject, RAB vs realization, remaining budget.
- Proposal: auto-fill, edit section, submit, approve, request revision, export PDF/DOCX, lock submitted proposal, member cannot approve.
- Document: upload, oversized rejection, signed private download, restricted download, visibility rules, folder tree, recent documents, cross-tenant document guard.
- LPJ: checklist load, mark done, readiness guard, submit complete LPJ, approve, request revision, export PDF, task data referenced.

---

## 5. Tombol Dan Action Yang Perlu Dicek Manual

Tombol/action ini perlu dicek di browser karena automated tests belum cukup membuktikan interaksi UI, state loading, disabled state, toast, dan redirect-nya.

| Page | Button/Action | Risiko |
|---|---|---|
| `/organization/switcher` | Buat Organisasi | Saat ini tombol dummy; perlu form + route create organization. |
| `/organization/switcher` | Pilih organisasi | Saat ini list static; perlu action switch org aktif. |
| `/organization/periods` | Tambah Periode | Saat ini tombol dummy; perlu form + route create period. |
| `/members/invites` | Invite | Saat ini tombol dummy; perlu modal/form, validation, duplicate invite, toast. |
| `/members` | Promote/demote role | Perlu cek control role tidak muncul untuk role yang tidak berhak. |
| `/members` | Remove member | Perlu typed/confirm flow dan akses member setelah dihapus. |
| `/proker` | Status transition | Tombol ubah status harus konsisten dengan policy dan progress. |
| `/templates` | Generate template | Perlu cek list template, empty/loading state, redirect detail. |
| `/tasks` atau detail proker | Quick-add task | Perlu cek task masuk kolom benar tanpa layout shift. |
| `/tasks` | Advance status | Perlu cek progress update di UI setelah klik. |
| `/finance` | Add/Edit/Delete budget line | Perlu cek total recalculation dan validation message. |
| `/finance` | Upload receipt | Perlu cek file picker, size/type error, preview/download link. |
| `/finance/approval` | Approve/Reject | Perlu cek status update, flash, audit/notification. |
| `/reports/proposal-editor` | Save section | Perlu cek autosave/manual save dan lock saat submitted. |
| `/reports/proposal-editor` | Export PDF/DOCX | Perlu cek job queued, queue worker, file muncul. |
| `/reports/lpj-checklist` | Checklist item toggle | Perlu cek progress bar dan readiness guard. |
| `/documents` | Upload/download document | Perlu cek S3 signed URL dan visibility rule. |
| `/certificates/templates/*/edit` | Preview template | Saat ini rekomendasi QA: butuh visual preview agar user non-teknis tidak menulis HTML buta. |
| Header notification bell | Open notification preview | Saat ini bell menuju page penuh, belum ada dropdown preview. |

---

## 6. Security Dan Multi-Tenancy Yang Belum Aman Untuk Dianggap Selesai

| Area | Status QA | Catatan |
|---|---|---|
| All organization routes unauthenticated | Partial | Baru spot-check `/dashboard`, `/proker/create`, `/finance`. |
| Cross-tenant dashboard isolation | Pass | Dashboard org lain tidak bocor dari automated tests. |
| Cross-tenant proker/finance/document | Belum lengkap | Perlu test semua route detail/download/action. |
| File upload MIME validation | Partial | Logo upload sudah, document/receipt/certificate asset belum semua. |
| S3 signed URL | Partial | Unit download plan ada, browser/download route perlu dicek. |
| CSRF protection | Belum audited penuh | Perlu spot-check semua POST/PUT/PATCH/DELETE penting. |
| Crafted/stolen session cookie | Belum diuji | Perlu security test manual/automated khusus. |

---

## 7. Design Dan UX Yang Perlu Improve

Prioritas tinggi:

- Empty state harus lebih membantu, terutama dashboard kosong, approval queue kosong, document folder kosong, dan finance tanpa data. Jangan blank white space.
- Onboarding first-run perlu jelas: user baru harus tahu urutan minimal membuat periode, invite anggota, buat proker, dan RAB awal.
- Mobile bottom action untuk task/attendance/proker perlu dievaluasi supaya action utama tidak tenggelam di sidebar.
- Approval queue owner sebaiknya punya preview panel dan approve/reject inline, bukan harus bolak-balik detail page.
- Certificate template editor perlu visual preview. Raw HTML terlalu berat untuk user sekretaris/BEM umum.

Prioritas medium:

- Finance RAB table perlu live total calculation dan indikator over-budget.
- Dashboard quick actions perlu disesuaikan per role, bukan hanya Pimpinan.
- Search/filter dasar perlu ada di `/proker`, `/members`, `/documents`, dan `/certificates`.
- Breadcrumb perlu ditambahkan di halaman bertingkat.
- Notification bell lebih baik punya dropdown 5 notifikasi terbaru.
- Long list perlu pagination atau virtualized list sebelum data membesar.

---

## 8. Technical Risk Untuk Dev

| Risk | Dampak | Rekomendasi |
|---|---|---|
| Authorization role campur local role dan Spatie | Guard bisa inkonsisten antar module. | Standarisasi service role/permission checker. |
| Error tracking belum jelas | Production error tidak kelihatan. | Tambahkan Sentry Laravel + React sebelum beta. |
| Rate limit route sensitif belum diaudit penuh | Risiko brute force/spam invite. | Audit login, forgot password, invite, WhatsApp, internal-admin. |
| PDF engine quality | DomPDF bisa buruk untuk layout kompleks. | Pertimbangkan Browsershot untuk proposal/LPJ rich content. |
| Soft delete core models belum diaudit | Risiko data loss dari hard delete. | Audit model penting dan tambah trash/recover flow. |
| Redis cache invalidation belum terdokumentasi | Badge/KPI bisa stale. | Dokumentasikan cache key dan invalidation trigger. |
| Failed job handling belum lengkap | Export/email gagal tanpa feedback jelas. | Tambah failed handler + retry UI. |
| Missing indexes belum diaudit | Risiko lambat saat data besar. | Audit index untuk projects, tasks, approvals, notifications, attendance. |

---

## 9. Rekomendasi QA Berikutnya

Urutan QA yang paling enak untuk dev:

1. Tutup flow Organization dan Member dulu: create org, period, invite, accept/decline, remove member.
2. Lanjut full Proker lifecycle: create, status transition, task progress 100%, template twice, archive.
3. Lanjut Finance end-to-end: budget line, receipt, approval, remaining budget.
4. Lanjut Proposal + LPJ export dengan queue worker aktif.
5. Baru Document visibility dan cross-tenant download.
6. Setelah itu browser sweep desain: mobile 375px, desktop 1280px, console error, empty state, tombol disabled/loading.

File ini boleh dipakai dev sebagai "peta kerja QA". Kalau nanti ada bug baru, tambahkan ke section `2` kalau sudah fixed, atau buat section `Open Bugs` di atas section `3`.
