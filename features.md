# Prokerin Feature Tracker

Dokumen ini adalah sumber utama status fitur Prokerin. `AGENTS.md` hanya menyimpan aturan kerja dan harus mengarahkan pembaca ke file ini untuk status fitur.

## Cara Membaca

- `[x]` berarti selesai, terhubung ke kode, dan sudah masuk verifikasi yang tertulis di bagian `Verifikasi Terakhir`.
- `[~]` berarti sebagian sudah ada, tetapi belum boleh dianggap selesai.
- `[ ]` berarti belum dikerjakan.
- Setiap perubahan status modul wajib memperbarui bagian modul terkait, `Verifikasi Terakhir`, dan `Next Action`.
- Jangan mulai modul Post-MVP berikutnya kalau full test suite sedang merah.

## Ringkasan Status

- MVP M01-M13: selesai dan tervalidasi.
- Post-MVP M14-M15: selesai dan tervalidasi.
- Post-MVP berikutnya: M16 Sertifikat Digital.
- Risiko operasional saat ini: shell default masih menunjuk PHP 8.3, sedangkan proyek membutuhkan PHP 8.4+.

## Verifikasi Terakhir

- [x] 2026-05-16 · `composer validate --strict` berhasil.
- [x] 2026-05-16 · PHP platform check berhasil dengan PHP 8.4.10:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH composer check-platform-reqs`
- [x] 2026-05-16 · MVP baseline test berhasil:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test`
  Hasil: `181 passed`, `712 assertions`.
- [x] 2026-05-16 · Setelah M14, full test suite berhasil:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test`
  Hasil: `183 passed`, `755 assertions`.
- [x] 2026-05-16 · Setelah M15, full test suite berhasil:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test`
  Hasil: `190 passed`, `804 assertions`.
- [x] 2026-05-16 · M15 migration dan seeder berhasil di MySQL lokal:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate`
  dan `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan db:seed`.
- [x] 2026-05-16 · Frontend production build berhasil:
  `npm run build`
- [x] 2026-05-16 · Browser smoke test untuk `/meetings` berhasil setelah login seeded user `owner@prokerin.test`.
- [x] 2026-05-16 · Browser smoke test untuk `/attendance` berhasil setelah login seeded user `owner@prokerin.test`.

## Environment Notes

- Proyek sekarang butuh PHP 8.4+.
- PHP 8.4 tersedia di `/opt/homebrew/bin/php`.
- Shell default masih menunjuk `/opt/homebrew/opt/php@8.3/bin/php`.
- Untuk Composer/Artisan, gunakan prefix ini sampai Homebrew PHP direlink:
  `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH`
- Database lokal menggunakan MySQL di `.env` port `8889`.
- Migration additive M14 sudah dijalankan di database lokal pada 2026-05-16.
- Migration additive M15 sudah dijalankan di database lokal pada 2026-05-16.
- Seeder idempotent sudah dijalankan ulang setelah M15.

## Fondasi Selesai

- [x] Laravel + Breeze React/Inertia TypeScript scaffold.
- [x] Inertia app shell dengan Viho-inspired fixed sidebar, white header, compact SaaS admin layout, dan copied Viho assets di `public/vendor/viho/`.
- [x] Shared frontend modules: `VihoSidebar`, `VihoHeader`, `VihoCard`, `VihoDataTable`, `VihoStatusBadge`, `ModuleOverview`, `FlashBanner`, dan `vihoMenu`.
- [x] Breeze auth/profile/account recovery pages sudah disejajarkan dengan Viho tokens.
- [x] Inertia shared props untuk auth, active organization, app context, dan flash messages.
- [x] Public/workspace routes dipindah dari closure ke controller tipis.
- [x] Domain enums/value objects untuk organization, membership, project, task, finance, proposal, report, notification, document, export, `Money`, dan `Progress`.
- [x] DTO dan Action pattern dipakai untuk payload dan business logic utama.
- [x] MySQL workspace persistence migration untuk MVP modules.
- [x] Seeder idempotent untuk demo roles, organizations, periods, projects, tasks, finance, documents, proposals, LPJ, notifications, exports, meetings, dan role matrix.
- [x] Route smoke test untuk semua workspace pages yang aktif.
- [x] Dashboard dan workspace payload penting sudah database-backed dan tenant-scoped.

## MVP Modules

### M01 · Auth & Account

Status: `[x]` Selesai.

Sudah ada:
- Breeze login, register, forgot password, reset password, confirm password, verify email, dan profile edit.
- UI auth sudah Viho-style.
- Google OAuth package, config keys, readiness Action, redirect URL builder, code exchange, callback user sync, web routes, dan Login/Register entry points.
- Email verification prompt Action.
- Feature tests untuk authentication, registration, password reset/update/confirmation, email verification, profile, dan Google OAuth.

Belum/next:
- Tidak ada blocker fitur MVP.
- Production Google OAuth credentials tetap harus diisi via `.env`, bukan kode.

### M02 · Organization Management

Status: `[x]` Selesai.

Sudah ada:
- Organization setup, switcher, periods, calendar, dan logo upload UI.
- Active organization/period context di Inertia shared props.
- Organization period resolver Action.
- Organization logo upload planner dan persistence flow.
- Server-derived organization scope untuk upload logo.
- Tables: `organizations`, `organization_periods`, `organization_members`, `organization_invitations`.
- Seed organizations dan active periods.
- Feature test upload logo.

Belum/next:
- Calendar organisasi masih berfungsi sebagai overview/scaffold, bukan full drag-drop scheduler.
- Advanced period closing/handover masuk area Post-MVP/M19.

### M03 · Member & Role Management

Status: `[x]` Selesai.

Sudah ada:
- Members overview, invite queue, dan role matrix UI.
- Role permission matrix Action dan database-backed payload.
- Invitation decision Action.
- Organization member role mutation backend.
- Owner/admin guardrails dan last-owner protection.
- Seed multi-role users.
- Feature tests untuk role update dan workspace payload.

Belum/next:
- Invitation email delivery aktual belum dijadikan flow utama.
- Spatie permission formalization masih dapat diperluas saat authorization makin detail.

### M04 · Proker / Event Management

Status: `[x]` Selesai.

Sudah ada:
- Proker index, create, detail, edit, status flow, dan archive.
- Create backend dengan server-derived organization scope, active period/template lookup, unique slug, dan project lead membership guard.
- Detail payload database-backed dengan tenant-scoped slug lookup, metrics, dan nearby tasks.
- Update backend dengan tenant-scoped slug lookup dan slug regeneration.
- Archive/delete backend non-destructive memakai status `archived`.
- Project status transition Action.
- Project progress calculation Action.
- Template-based project draft Action.
- Inertia create/edit forms memakai `useForm`.
- Feature tests create, detail, update, archive.

Belum/next:
- Full project member assignment UI masih bisa diperdalam.
- Project-level policy formalization perlu diperluas bila role matrix mulai enforce semua mutation.

### M05 · Template Proker

Status: `[x]` Selesai.

Sudah ada:
- Template Proker UI.
- Default template plan generator untuk tasks, budget lines, proposal outline, dan LPJ checklist.
- `project_templates` persistence.
- Database-backed template payload.
- One-click generate flow yang membuat project/task/RAB/proposal/LPJ scaffold.
- Feature test template generation.

Belum/next:
- Custom template builder belum ada.
- Versioning template belum ada.

### M06 · Timeline & Task Management

Status: `[x]` Selesai.

Sudah ada:
- Timeline/task overview, kanban, calendar, dan PIC assignment scaffold.
- Database-backed kanban/calendar payload.
- Quick task status update backend.
- Completion timestamp handling.
- Task board summary Action.
- Task PIC assignment Action guardrails.
- Feature tests untuk task interaction.

Belum/next:
- Full create/edit task form belum menjadi fokus.
- Drag-drop kanban belum ada.
- Reminder lebih lanjut terkait M12/M17.

### M07 · RAB & Finance

Status: `[x]` Selesai.

Sudah ada:
- Finance overview, budget draft, realization, dan approval queue.
- Budget summary DTO/Action.
- Budget approval decision Action.
- Receipt-backed realization Action.
- Receipt upload backend dengan private receipt document storage.
- Budget transaction persistence.
- DB-backed realization payload.
- Review-stage approve/reject route dan Inertia controls.
- Feature tests receipt realization dan budget approval decision.

Belum/next:
- Multi-level approval masuk M18.
- Export finance-specific report bisa diperluas setelah LPJ/export stabil.

### M08 · Proposal Generator

Status: `[x]` Selesai.

Sudah ada:
- Proposal editor UI dan export queue UI.
- Proposal draft builder auto-fill dari project/template data.
- Draft edit persistence untuk section body.
- Submit flow ke approval.
- Revision-to-draft save flow.
- Locked state untuk submitted/approved draft.
- Owner/admin approve atau request revision.
- Project status transitions.
- Queued PDF/DOCX export generation dari database content.
- Export queue download route dengan tenant-scoped signed URL.
- Feature tests proposal approval/edit/export queue.

Belum/next:
- Rich text editor belum ada.
- Proposal template customization belum ada.

### M09 · Document Management

Status: `[x]` Selesai.

Sudah ada:
- Documents overview, folder structure, dan upload center UI.
- Document upload validation Action untuk MIME, size, visibility, signed URL rule.
- Document download planner.
- Documents table dan seed data.
- Upload center recent documents database-backed.
- Tenant-scoped signed download route.
- Private/restricted/committee download handling.
- Feature tests untuk document download/storage permission.

Belum/next:
- Real upload center form untuk semua jenis dokumen belum penuh.
- S3/R2 production credentials tetap environment-only.

### M10 · LPJ Generator

Status: `[x]` Selesai.

Sudah ada:
- LPJ checklist UI.
- LPJ readiness Action.
- Checklist persistence.
- Database-backed tenant-scoped checklist payload.
- Review submission flow dengan readiness guard.
- Owner/admin approval atau revision request.
- Project status transitions.
- Export queue planner terhubung M08/M10.
- Feature tests LPJ approval.

Belum/next:
- Full LPJ document editor belum ada.
- LPJ export layout bisa dipoles lebih lanjut.

### M11 · Dashboard Monitoring

Status: `[x]` Selesai.

Sudah ada:
- Viho dashboard UI.
- DTO payload dan aggregate metrics Action.
- Database-backed overview metrics, priority projects, weekly focus, member summary.
- Tenant scoping untuk authenticated user's organizations.
- Anti-leak unit coverage untuk metrics, priority projects, tasks, budget, LPJ, dan member summary.

Belum/next:
- Drilldown analytics masih per modul, belum dashboard advanced.

### M12 · Notification Basic

Status: `[x]` Selesai.

Sudah ada:
- Notification rules/channel UI.
- Default rule Action.
- `notification_rules` table dan Laravel notification table.
- Database-backed rules payload.
- Queued task deadline reminder notification.
- Email/database channels.
- Simulate reminder route/button.
- Tenant-scoped reminder dispatch Action.
- Feature tests notification reminder.

Belum/next:
- WhatsApp reminder masuk M17.
- User notification preference belum detail.

### M13 · Admin Panel Internal

Status: `[x]` Selesai.

Sudah ada:
- Internal admin planning UI scaffold.
- Backend readiness payload.
- Resource plan dan system health counters.
- Filament package install.
- `/internal-admin` panel provider.
- Filament Organization, User, dan DocumentExport resources.
- Destructive delete row/bulk actions dihapus dari MVP resources.
- Feature tests admin panel payload.

Belum/next:
- Admin policies belum formal penuh.
- Resource coverage bisa diperluas setelah policy matang.

## Post-MVP Modules

### M14 · Rapat & Notulen

Status: `[x]` Selesai untuk scope awal Post-MVP.

Sudah ada:
- Additive migration `2026_05_16_000006_create_meeting_minute_tables.php`.
- Tables: `meetings`, `meeting_attendees`, `meeting_minutes`.
- Seed demo rapat, attendees, dan published minutes.
- Tenant-scoped `GetMeetingMinutePayloadAction`.
- Route `meetings.index` di `/meetings`.
- Sidebar menu `Rapat & Notulen` dengan badge `M14`.
- Inertia page `resources/js/Pages/Meetings/Index.tsx`.
- UI menampilkan metrics, agenda rapat, attendee count, present count, status notulen, latest decisions, dan action items.
- Route smoke test ditambahkan.
- Feature test payload dan anti-leak tenant scope.
- Browser smoke test `/meetings` berhasil.

Belum/next:
- Create/edit meeting belum ada.
- Publish/edit notulen belum ada.
- Attendance taking belum interaktif.
- Export notulen belum ada.
- Jika modul ini dilanjutkan, tambah Form Request, controller mutation, policies, dan tests sebelum memperkaya UI.

### M15 · Absensi QR

Status: `[x]` Selesai untuk scope awal Post-MVP.

Sudah ada:
- Additive migration `2026_05_16_000007_create_attendance_tables.php`.
- Tables: `attendance_sessions`, `attendance_qr_tokens`, `attendance_records`.
- Attendance session bisa scoped ke organization, project, dan meeting.
- QR token disimpan sebagai SHA-256 hash dengan expiry, revoked timestamp, dan last-used timestamp.
- `CheckInAttendanceQrAction` untuk valid QR check-in.
- `RecordManualAttendanceAction` untuk fallback absensi manual oleh owner/admin/secretary/project_lead.
- Tenant membership guard untuk QR check-in.
- Anti-duplikat untuk scan QR berulang pada user/session yang sama.
- Expired token guard.
- Cross-tenant rejection untuk user yang bukan anggota organisasi sesi absensi.
- Manual fallback route dengan role guard.
- Route `attendance.index` di `/attendance`.
- Route `attendance.check-in.store` untuk QR check-in.
- Route `attendance.manual.store` untuk manual fallback.
- Sidebar menu `Absensi QR` dengan badge `M15`.
- Inertia page `resources/js/Pages/Attendance/Index.tsx`.
- UI menampilkan metrics, session list, QR/manual counts, expiry, dan check-in terbaru.
- Seeder demo session absensi untuk M14 meeting.
- Feature tests untuk payload, valid check-in, duplicate scan, expired token, cross-tenant guard, manual fallback, dan manual role guard.
- Route smoke test ditambahkan.
- Browser smoke test `/attendance` berhasil.

Belum/next:
- QR image generation belum ada; saat ini form menerima token hasil scan/paste.
- Camera scanner PWA belum ada.
- Regenerate/revoke QR token UI belum ada.
- Create/edit attendance session belum ada.
- Attendance export belum ada.
- Migration M15 perlu dijalankan ke database lokal persistent sebelum browser smoke test manual.

### M16 · Sertifikat Digital

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Certificate template table.
- Recipient table.
- Certificate number generator.
- PDF export job.
- Signed verification URL.
- Inertia page untuk template/recipient/issue queue.
- Tests untuk unique certificate number dan tenant-scoped verification.

### M17 · WhatsApp Reminder

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Environment variables untuk provider WhatsApp di `.env.example`.
- Notification channel abstraction.
- Queue job untuk send WhatsApp reminder.
- Delivery log table.
- Retry/failure status.
- Tenant/user opt-in guard.
- Tests dengan fake provider.

Catatan:
- Jangan hardcode token/provider URL.
- Jangan kirim pesan real dari test/local tanpa explicit user approval.

### M18 · Approval Workflow Advanced

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Approval workflow definitions.
- Approval steps dan approvers.
- Multi-level approval instance per proposal/RAB/LPJ.
- Delegate/reassign flow.
- Audit trail.
- Tests untuk role order, reject, revision, and cross-tenant access.

### M19 · Handover Kepengurusan

Status: `[~]` Partial scaffold only.

Sudah ada:
- Route/page `organization.handover`.
- Viho-style planning/readiness UI scaffold.

Belum/next:
- Handover package persistence belum ada.
- Data snapshot antar periode belum ada.
- Handover checklist/action owner belum ada.
- Archive/export handover belum ada.
- Access policy belum formal.

### M20 · Sponsor & Vendor Database

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Sponsor/vendor tables scoped by organization.
- Contact person, category, status, documents, and historical project links.
- Inertia list/detail page.
- Tests tenant scoping and CRUD authorization.

### M21 · Event Registration

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Public registration form per project/event.
- Participant table.
- Capacity and registration period guard.
- Confirmation email/notification.
- Export participant list.
- Tests for capacity, duplicate email, and tenant/project scope.

### M22 · Payment / Ticketing

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Payment provider config in env.
- Ticket tiers.
- Order/payment tables.
- Webhook handling.
- Paid/free registration integration with M21.
- Tests with fake webhook signatures.

Catatan:
- Jangan mulai sebelum M21 cukup stabil.
- Jangan hardcode payment credentials.

### M23 · AI Assistant

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Use-case definition first: proposal drafting, LPJ summary, task suggestions, or dashboard insight.
- Provider config through env.
- Prompt/action classes with auditability.
- Tenant data minimization.
- Tests for prompt payload construction and permission guard.

Catatan:
- Jangan mulai tanpa scope eksplisit.
- Jangan kirim data sensitif ke provider tanpa desain permission dan env config.

### M24 · Campus Dashboard B2B

Status: `[ ]` Belum dikerjakan.

Target scope yang disarankan:
- Campus/super-admin aggregate dashboard.
- Cross-organization metrics with strict authorization.
- Read-only analytics first.
- Tests for super admin access and org data isolation.

## Next Action

1. Relink shell PHP ke PHP 8.4 atau terus gunakan PATH prefix untuk semua Composer/Artisan commands.
2. Mulai M16 Sertifikat Digital dengan desain migration additive.
3. Tambahkan tests untuk unique certificate number, signed verification URL, dan tenant-scoped verification sebelum M16 ditandai selesai.
4. Setelah M16 selesai, update bagian `Verifikasi Terakhir`, `Post-MVP Modules`, dan commit dengan pesan `feat: add digital certificate module`.

## Riwayat Commit Penting

- `f2c148a` · `docs: record MVP verification handoff`
- `4f37fb5` · `docs: mark MVP validation complete`
- `af3de2a` · `feat: add meeting minutes module`
