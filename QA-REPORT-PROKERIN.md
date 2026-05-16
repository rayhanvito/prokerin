# QA Report Prokerin

Tanggal update: 2026-05-17  
Scope: hasil QA sementara dari automated test, browser smoke, dan checklist manual di `QA-MASTER-PROKERIN.md`.

> Catatan penting: mulai sekarang laporan QA ditulis di file ini. `features.md` tidak dipakai lagi untuk catatan hasil QA, bug list, atau temuan UI.

---

## 1. Ringkasan Terakhir

Status automated regression terakhir:

| Check | Status | Hasil |
|---|---|---|
| PHP feature/unit test | Pass | `452 passed, 2541 assertions` |
| Targeted auth/security | Pass | `35 passed, 99 assertions` |
| Targeted expanded guest-route security | Pass | `3 passed, 133 assertions` |
| Targeted org/member/proker | Pass | `36 passed, 139 assertions` |
| Targeted member role promotion/demotion | Pass | `6 passed, 13 assertions` |
| Targeted dashboard/workspace | Pass | `48 passed, 415 assertions` |
| Targeted dashboard KPI accuracy | Pass | `4 passed, 153 assertions` |
| Targeted workspace payload | Pass | `9 passed, 158 assertions` |
| Targeted workspace/org/member/proker smoke | Pass | `20 passed, 226 assertions` |
| Targeted proker/template/task | Pass | `22 passed, 140 assertions` |
| Targeted template generation conflict | Pass | `4 passed, 23 assertions` |
| Targeted task/finance/proposal/lpj/document | Pass | `49 passed, 322 assertions` |
| Targeted finance/proposal/lpj/document refresh | Pass | `45 passed, 279 assertions` |
| Targeted proposal/document guard | Pass | `21 passed, 57 assertions` |
| Targeted multi-tenant finance/security | Pass | `3 passed, 30 assertions` |
| Targeted security input probes | Pass | `3 passed, 6 assertions` |
| Targeted event/meeting/attendance/certificate/notification | Pass | `59 passed, 355 assertions` |
| Targeted handover/sponsor/approval | Pass | `26 passed, 146 assertions` |
| Targeted sponsor/vendor workspace context | Pass | `15 passed, 190 assertions` |
| Targeted organization phase 1 | Pass | `14 passed, 101 assertions` |
| Targeted organization/workspace/security smoke | Pass | `27 passed, 430 assertions` |
| Targeted member invitation/overview phase 2 | Pass | `9 passed, 75 assertions` |
| Targeted organization+member workspace/security smoke | Pass | `35 passed, 502 assertions` |
| Targeted proker lifecycle phase 3 | Pass | `21 passed, 200 assertions` |
| Targeted proker/workspace/security smoke | Pass | `34 passed, 529 assertions` |
| Targeted task operasional phase 4 | Pass | `15 passed, 125 assertions` |
| Targeted task/workspace/security smoke | Pass | `28 passed, 454 assertions` |
| Targeted finance GET guard phase 5 partial | Pass | `19 passed, 255 assertions` |
| Targeted finance overview phase 5 partial | Pass | `17 passed, 130 assertions` |
| Targeted landing/campus/admin payload | Pass | `7 passed, 88 assertions` |
| Targeted attendance/certificate/notification | Pass | `31 passed, 171 assertions` |
| Targeted certificate verification | Pass | `10 passed, 88 assertions` |
| Targeted cross-module tenant/security refresh | Pass | `53 passed, 319 assertions` |
| Targeted meeting/event/payment | Pass | `28 passed, 184 assertions` |
| Pint targeted | Pass | Tidak ada formatting violation |
| npm lint | Pass | `tsc --noEmit` pass |
| npm build | Pass | `tsc && vite build` pass |

Dev handoff status:

- QA closure pass selesai: `QA-MASTER-PROKERIN.md` tidak lagi punya item checklist kosong selain legend.
- Automated gates hijau: PHP test, lint, build, dan Pint pass.
- Dev bisa mulai dari open findings `QA-OPEN-001` sampai `QA-OPEN-020`; prioritas tertinggi adalah route/form yang masih dummy/static dan guard finance/proker tenant scope.
- Item `[S]` di master adalah validasi manual/device/infra seperti Lighthouse, real mobile, raw S3 behavior, CSRF browser-level, dan worker/Supervisor, bukan blocker untuk mulai fix backlog aplikasi.

Area yang sudah cukup aman dari automated QA:

- Auth dasar: register, login, logout, remember me, password reset, email verification prompt.
- Security guest redirect: `/dashboard`, `/proker/create`, `/finance`, `/internal-admin`.
- Expanded guest redirect sweep: main workspace GET pages across Proker, Organization, Task, Finance, Reports, Documents, Members, Meetings, Events, Attendance, Certificates, Notifications, and Admin.
- Expanded guest mutation sweep: main workspace POST/PATCH/PUT/DELETE action routes redirect guests to `/login`.
- Dashboard role variant dan sidebar filtering.
- Super Admin access, asset loading, icon sizing, impersonation audit, destructive action hardening.
- Organization logo upload dan MIME rejection.
- Role matrix, role update guard, dan last-owner protection.
- Proker create, detail, edit, archive, duplicate slug, member create denial.
- Proker index tenant-scoped dari database, status transition guarded, project member assign/remove, dan progress recompute sampai 100%.
- Template generation: proker + tasks + RAB + proposal + LPJ checklist dibuat atomik.
- Task Kanban data, task status update guard, task calendar data.
- Task overview data-backed, quick-add task, assign PIC guarded, overdue badge payload, dan active organization scope untuk Kanban/Calendar.
- Finance receipt realization, MIME rejection, budget approval/rejection, workflow approval.
- Proposal edit/submit/approve/revision, LPJ review/decision, signed document/export download.
- Meeting create/attendance/minutes/export, QR attendance issue/revoke/check-in/export, event registration/ticketing/payment webhook, certificate template/issue/verify/download, and notification reminder jobs.
- Handover kepengurusan: create package, generated checklist, item status, transition assignment, submit/accept, PDF export queue/job, and member guards.
- Sponsor/vendor: filterable list payload, search/type filter, create, edit, detail history, member guard, and cross-tenant detail guard.
- Multi-level approval: ordered approval chain, rejection, revision request, delegation, timeline payload, cross-tenant guard, and next-step notification.
- Landing public pages render for `/`, `/features`, and `/pricing`.

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
| QA-OPEN-001 | High | Organization Switcher | Tombol `Buat Organisasi` kini membuka form, submit ke route `organization.store`, membuat owner membership, dan default active period. | Fixed |
| QA-OPEN-002 | High | Organization Switcher | Switcher kini memakai membership payload dari database dan POST switch menyimpan `active_organization_id` session. | Fixed |
| QA-OPEN-003 | High | Organization Periods | Periods kini data-backed dengan create/update/set-active flow dan role guard owner/admin. | Fixed |
| QA-OPEN-005 | Medium | Organization Calendar | Calendar kini data-backed dari proker, meeting, dan attendance session pada organisasi aktif. | Fixed |
| QA-OPEN-004 | High | Member Invites | Invitation kini punya form, route store, duplicate/member guard, token preview, accept/decline, dan expired-token rejection. | Fixed |
| QA-OPEN-014 | Medium | Members Overview | `/members` kini memakai payload database tenant-scoped dengan metrics, role breakdown, filter, dan owner-only remove member. | Fixed |
| QA-OPEN-013 | Medium | Proker Overview | `/proker` kini memakai payload database tenant-scoped, filter/search, empty state, dan link ke slug project sebenarnya. | Fixed |
| QA-OPEN-017 | High | Proker Cross-Tenant Index | Proker index kini dibatasi organisasi aktif sehingga project organisasi lain tidak bocor ke list. | Fixed |
| QA-OPEN-018 | High | Project Status & Progress | Status transition kini punya route/action guarded, dan perubahan task status menghitung ulang `projects.progress` sampai 100%. | Fixed |
| QA-OPEN-006 | Medium | Task Overview | `/tasks` kini memakai payload database tenant-scoped dengan metrics, urgent task, quick-add, dan empty state. | Fixed |
| QA-OPEN-007 | High | Task Assignment | `/tasks/assignments` kini data-backed dengan dropdown PIC dan route `tasks.pic.update` dengan guard role/member. | Fixed |
| QA-OPEN-019 | High | Task Assignment UX | Kanban dan overview kini punya quick-add task, payload overdue, dan badge overdue di kartu task. | Fixed |
| QA-OPEN-016 | High | Finance Access Control | Route GET/mutasi finance kini memakai middleware `finance`; member biasa 403 dan treasurer/owner/admin tetap bisa akses. | Fixed |
| QA-OPEN-008 | Medium | Finance Overview | `/finance` kini memakai payload database tenant-scoped dengan metrics, RAB vs realisasi, realisasi bulanan, dan queue approval ringkas. | Fixed |
| QA-OPEN-009 | High | Budget Draft | `/finance/budget-draft` kini data-backed dengan inline form create + edit + delete, summary plan/realized/remaining/lineCount, indikator over-budget per row, dan role guard `FINANCE_MANAGERS`. RAB vs Realisasi chart di Finance Overview menampilkan planned/realized overlay dengan legend. | Fixed |
| QA-OPEN-010 | High | Document Upload | Upload Center kini punya form upload nyata ke `documents.store`, validasi file/ukuran, penyimpanan S3, project tenant guard, dan progress upload. | Fixed |
| QA-OPEN-011 | Medium | Document Folders | `/documents/folders` kini memakai folder tree dari tabel `documents`, dengan daftar file dan link download per folder. | Fixed |
| S4.1 / S4.3 | High | File Upload Security | Upload `.php`/executable dan SVG payload ditolak oleh validasi Form Request/MIME sebelum dokumen disimpan. | Fixed |
| TECH-03 | High | Rate Limiting | Login (5/min/IP+email), forgot password (3/15min/IP), invitation dispatch (20/jam/org), WhatsApp (100/jam/org), filament-login (5/min/IP+email), certificate verify (20/min/IP) kini punya throttle named limiter. | Fixed |
| TECH-05 | High | Soft Deletes | `organizations`, `projects`, `documents`, `meetings`, `attendance_sessions`, `sponsors_vendors`, `certificate_recipients` punya `deleted_at` + indeks. Models `Organization` dan `Project` pakai `SoftDeletes` trait, default scope exclude trashed, restore tersedia. | Fixed |
| TECH-08 | Medium | Query Indexes | Indexes baru: `notifications(notifiable_id, read_at)`, `attendance_qr_tokens(token_hash, expires_at)`, `projects(organization_id, starts_at)`, `budget_lines(status, updated_at)`, `documents(organization_id, visibility)`. Migration idempotent (cek `indexExists`). | Fixed |
| Impersonation Expiry | High | Super Admin | Middleware `EnsureImpersonationFresh` di-mount di web stack; sesi impersonate yang lebih lama dari `IMPERSONATE_MAX_DURATION_HOURS` (default 2 jam) auto-leave + redirect ke `/internal-admin/users` + audit log `impersonate.expired`. | Fixed |
| QA-OPEN-015 | Medium | Reports Overview | `/reports` kini data-backed dengan metrics proposal/LPJ/export queue, status breakdown, export queue terbaru, dan proker terkait dokumen dari organisasi aktif. | Fixed |
| QA-OPEN-020 | High | LPJ Checklist & Export | LPJ checklist kini bisa toggle item via PATCH, menampilkan data eksekusi task/budget/attendance, dan memiliki trigger export LPJ PDF yang queue job setelah project completed. | Fixed |

Masih ada open findings untuk modul lain. Organization Phase 1, Member Phase 2, dan Proker Phase 3 sudah dipindahkan ke fixed dan regression terbaru hijau.

---

## 3. Open Findings Untuk Dev

Temuan di bawah bukan crash, tapi fitur/tombol belum benar-benar berfungsi end-to-end atau guard akses belum sesuai ekspektasi. Halaman render, tetapi beberapa data masih static, action belum tersambung ke route mutasi, atau direct URL masih terlalu longgar.

| ID | Severity | Area | Temuan | Bukti Teknis | Dampak |
|---|---|---|---|---|---|

Catatan verifikasi tambahan:

- `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/OrganizationLogoUploadTest.php tests/Feature/OrganizationMemberRoleUpdateTest.php tests/Feature/ProjectTemplateGenerationTest.php` -> `20 passed, 226 assertions`.
- `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskInteractionTest.php tests/Feature/BudgetReceiptRealizationTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/LpjApprovalTest.php tests/Feature/DocumentDownloadTest.php tests/Feature/WorkspacePayloadTest.php` -> `49 passed, 322 assertions`.
- `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MeetingManagementTest.php tests/Feature/MeetingMinutesPayloadTest.php tests/Feature/AttendanceQrManagementTest.php tests/Feature/QrAttendanceTest.php tests/Feature/DigitalCertificateTest.php tests/Feature/EventRegistrationTest.php tests/Feature/PaymentTicketingTest.php tests/Feature/TaskDeadlineReminderNotificationTest.php` -> `59 passed, 355 assertions`.
- Full regression `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `352 passed, 1740 assertions`.
- Expanded guest-route security `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/AuthenticationBypassTest.php` -> `2 passed, 67 assertions`.
- Latest full regression after expanded security assertions `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `352 passed, 1800 assertions`.
- Expanded guest mutation security `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/AuthenticationBypassTest.php` -> `3 passed, 133 assertions`.
- Targeted multi-tenant finance/security `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/MultiTenantFinanceAccessTest.php` -> `3 passed, 30 assertions`.
- Latest full regression after QA closure pass `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `367 passed, 1965 assertions`.
- Latest full regression after Phase 0 foundation helpers `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `368 passed, 1968 assertions`.
- Phase 0 foundation targeted refactor `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php tests/Feature/WorkspacePayloadTest.php --stop-on-failure` -> `15 passed, 190 assertions`.
- Latest full regression after Phase 1 organization management `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `382 passed, 2069 assertions`.
- Phase 1 organization targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php --stop-on-failure` -> `14 passed, 101 assertions`.
- Phase 1 organization/workspace/security smoke `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `27 passed, 430 assertions`.
- Latest full regression after Phase 2 member/invitation `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `391 passed, 2144 assertions`.
- Phase 2 member/invitation targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationInvitationFlowTest.php tests/Feature/MembersOverviewPayloadTest.php --stop-on-failure` -> `9 passed, 75 assertions`.
- Phase 2 organization+member workspace/security smoke `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php tests/Feature/OrganizationInvitationFlowTest.php tests/Feature/MembersOverviewPayloadTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `35 passed, 502 assertions`.
- Latest full regression after Phase 3 proker lifecycle `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `404 passed, 2246 assertions`.
- Phase 3 proker lifecycle targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ProkerIndexPayloadTest.php tests/Feature/ProkerStatusTransitionTest.php tests/Feature/ProjectMembersManagementTest.php tests/Feature/ProjectDetailTest.php tests/Feature/TaskInteractionTest.php --stop-on-failure` -> `21 passed, 200 assertions`.
- Phase 3 proker/workspace/security smoke `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ProkerIndexPayloadTest.php tests/Feature/ProkerStatusTransitionTest.php tests/Feature/ProjectMembersManagementTest.php tests/Feature/ProjectDetailTest.php tests/Feature/TaskInteractionTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `34 passed, 529 assertions`.
- Latest full regression after Phase 4 task operasional `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `415 passed, 2328 assertions`.
- Phase 4 task targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskOverviewPayloadTest.php tests/Feature/AssignTaskPicTest.php tests/Feature/CreateTaskTest.php tests/Feature/TaskOverdueBadgePayloadTest.php tests/Feature/TaskInteractionTest.php --stop-on-failure` -> `15 passed, 125 assertions`.
- Phase 4 task/workspace/security smoke `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskOverviewPayloadTest.php tests/Feature/AssignTaskPicTest.php tests/Feature/CreateTaskTest.php tests/Feature/TaskOverdueBadgePayloadTest.php tests/Feature/TaskInteractionTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `28 passed, 454 assertions`.
- Latest full regression after Phase 5 finance GET guard `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `417 passed, 2336 assertions`.
- Phase 5 finance GET guard targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/MultiTenantFinanceAccessTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/BudgetReceiptRealizationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `19 passed, 255 assertions`.
- Latest full regression after Phase 5 finance overview `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `419 passed, 2382 assertions`.
- Phase 5 finance overview targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/FinanceOverviewPayloadTest.php tests/Feature/Security/MultiTenantFinanceAccessTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/BudgetReceiptRealizationTest.php --stop-on-failure` -> `17 passed, 130 assertions`.
- Phase 5 budget line CRUD targeted suite `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/BudgetLineCrudTest.php` -> `9 passed, 39 assertions`.
- Latest full regression after Phase 5 completion `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `435 passed, 2453 assertions`.
- Phase 11 targeted security suite (rate limit + soft delete + impersonation freshness) `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/ImpersonationFreshnessTest.php tests/Feature/Security/SoftDeleteWorkspaceTest.php tests/Feature/Security/RateLimitTest.php` -> `10 passed, 23 assertions`.
- Latest full regression after Phase 11 hardening `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `452 passed, 2541 assertions`.
- Frontend gate `npm run lint` -> pass.
- Frontend production build `npm run build` -> pass.
- PHP formatter gate `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` -> pass.
- Smoke test membuktikan route/page utama render; browser/device pass tetap diperlukan untuk interaksi UI, loading state, dan toast.

---

## 4. Fitur Belum Terverifikasi

Bagian ini penting untuk dev karena item di bawah belum boleh dianggap aman untuk rilis beta walaupun full test suite hijau.

### Organization

Organization Phase 1 sudah terverifikasi otomatis untuk create organization, duplicate slug, switcher, periods, calendar data-backed, dan edit profile name/description. Manual browser/device smoke tetap bisa dilakukan sebelum beta.

### Member & Role Management

Member Phase 2 sudah terverifikasi otomatis untuk invite, duplicate/member guard, accept/decline, expired token, remove member, dan members overview data-backed. Manual browser/device smoke tetap bisa dilakukan sebelum beta.

### Proker & Template

Proker Phase 3 sudah terverifikasi otomatis untuk index data-backed tenant-scoped, status transition guarded, progress recompute sampai 100% saat semua task selesai, dan project member assign/remove. Manual browser/device smoke tetap perlu untuk state loading, confirm UX, dan redirect setelah submit.

| ID QA | Area | Yang Belum Terverifikasi |
|---|---|---|
| 8.1 | Template library | Halaman `/templates` menampilkan template seeded. |
| 8.4 | Customize generated project | Generated project bisa diedit setelah dibuat dari template. |
| 8.5 | Generate same template twice | Generate template yang sama dua kali tanpa konflik. |

### Task, Finance, Proposal, Document, LPJ

Area ini masih butuh QA lanjutan paling banyak:

- Task board: Kanban load, status advance, member status guard, calendar data, progress recompute 100%, overview real, assign PIC, quick-add, dan overdue state sudah automated pass. Browser/manual state loading tetap perlu dicek.
- Finance: Receipt upload, receipt MIME rejection, approval approve/reject, member mutation guard, GET finance page guard, finance overview data-backed, budget line CRUD (create/edit/delete dengan role guard `FINANCE_MANAGERS` + cross-tenant block + transaction-protected delete), serta RAB vs Realisasi chart sudah automated pass. Browser/manual state loading tetap perlu dicek.
- Proposal: Edit section, submit, approve, request revision, lock submitted proposal, export PDF/DOCX job coverage, dan member guard sudah automated pass. Browser/manual state loading tetap perlu dicek.
- Reports overview kini data-backed untuk status proposal/LPJ, export queue, dan proker terkait dokumen; browser/manual state loading tetap perlu dicek lintas role.
- Document: Signed private download, restricted finance receipt guard, committee URL, export download guard, upload dokumen umum, oversized rejection, folder tree, recent documents, dan cross-tenant document download sudah automated pass.
- LPJ: Submit review, approve/request changes, readiness guard, AI summary, checklist toggle, execution summary, dan export PDF queue sudah automated pass.

### Event, Meeting, Attendance, Certificate, Notification

Automated coverage untuk area ini relatif paling matang:

- Meeting: create meeting, member guard, record attendance, publish minutes, export guard, export content, tenant payload isolation.
- Attendance: QR token issue/revoke, SVG image, CSV export, QR check-in, duplicate scan idempotent, expired token rejection, cross-tenant rejection, manual fallback guard.
- Event registration: public form, duplicate email, capacity, registration window, internal tenant list, settings update, CSV export, PDF export job.
- Payment ticketing: free tier, paid order, tier capacity, Midtrans paid/expire webhook, invalid signature rejection.
- Certificate: tenant-scoped list, template create/edit/deactivate, issue batch, PDF generation, public verify, cross-tenant download guard.
- Notifications: task deadline reminders, meeting WhatsApp alerts, delivery success/fail logs.

Remaining UX/manual checks:

- Certificate template preview is text-only, not visual certificate render. Lihat QA-OPEN-012.
- Attendance camera scan needs real-browser/device verification, not only backend QR token tests.
- Event PDF export and certificate PDF output should still be visually inspected after queue job runs.

---

## 5. Tombol Dan Action Yang Perlu Dicek Manual

Tombol/action ini perlu dicek di browser karena automated tests belum cukup membuktikan interaksi UI, state loading, disabled state, toast, dan redirect-nya.

| Page | Button/Action | Risiko |
|---|---|---|
| `/members` | Promote/demote role | Perlu cek control role tidak muncul untuk role yang tidak berhak. |
| `/proker` | Overview list | Automated pass untuk payload real dan link detail; browser smoke tetap perlu untuk filter/search/loading di viewport nyata. |
| `/proker` | Status transition | Automated pass untuk guard dan transisi; browser smoke tetap perlu untuk confirm UX/toast/redirect. |
| `/templates` | Generate template | Perlu cek list template, empty/loading state, redirect detail. |
| `/tasks` atau detail proker | Quick-add task | Automated pass untuk create task; browser smoke tetap perlu cek loading, toast, dan layout shift. |
| `/tasks` | Advance status | Perlu cek progress update di UI setelah klik. |
| `/tasks/assignments` | Assign PIC | Automated pass untuk route dan guard; browser smoke tetap perlu cek dropdown, disabled/loading, dan toast. |
| `/finance/budget-draft` | Add/Edit/Delete budget line | Saat ini tombol `Tambah Item` dummy dan data mock; perlu CRUD budget line. |
| `/finance` | Upload receipt | Perlu cek file picker, size/type error, preview/download link. |
| `/finance/approval` | Approve/Reject | Perlu cek status update, flash, audit/notification. |
| `/reports/proposal-editor` | Save section | Perlu cek autosave/manual save dan lock saat submitted. |
| `/reports` | Overview metrics/list | Saat ini hardcoded; perlu payload proposal/LPJ/export queue real. |
| `/reports/proposal-editor` | Export PDF/DOCX | Perlu cek job queued, queue worker, file muncul. |
| `/reports/lpj-checklist` | Checklist item toggle | Perlu cek progress bar dan readiness guard. |
| `/documents/upload-center` | Upload document | Saat ini dropzone visual; perlu file input/form submit dokumen. |
| `/documents/folders` | Folder tree | Saat ini static array; perlu data-backed hierarchy. |
| `/certificates/templates/*/edit` | Preview template | Saat ini rekomendasi QA: butuh visual preview agar user non-teknis tidak menulis HTML buta. |
| Header notification bell | Open notification preview | Saat ini bell menuju page penuh, belum ada dropdown preview. |

---

## 6. Security Dan Multi-Tenancy Yang Belum Aman Untuk Dianggap Selesai

| Area | Status QA | Catatan |
|---|---|---|
| Main workspace GET routes unauthenticated | Pass | Expanded automated sweep redirects guest access to `/login` across main workspace pages. |
| Main workspace mutation routes unauthenticated | Pass | Expanded automated sweep redirects guest POST/PATCH/PUT/DELETE action routes to `/login`. |
| Cross-tenant dashboard isolation | Pass | Dashboard org lain tidak bocor dari automated tests. |
| Cross-tenant proker/finance/document | Partial | Proker index tenant-scope dan finance GET role guard sudah pass; document detail/download sebagian sudah pass dan masih perlu audit lanjutan. |
| Finance GET role guard | Pass | Member biasa menerima 403 untuk `/finance`, `/finance/budget-draft`, `/finance/realization`, dan `/finance/approval`; treasurer tetap 200. |
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
- Search/filter dasar perlu ada di `/documents` dan `/certificates`; `/proker` dan `/members` sudah punya filter dasar tetapi masih perlu browser smoke.
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

1. Lanjut Proposal + LPJ export dengan queue worker aktif.
2. Baru Document visibility dan cross-tenant download.
4. Setelah itu browser sweep desain: mobile 375px, desktop 1280px, console error, empty state, tombol disabled/loading.

File ini boleh dipakai dev sebagai "peta kerja QA". Kalau nanti ada bug baru, tambahkan ke section `2` kalau sudah fixed, atau buat section `Open Bugs` di atas section `3`.
