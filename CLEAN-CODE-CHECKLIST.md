# Prokerin Clean Code Checklist

Dokumen ini adalah daftar kerja clean-code Prokerin. Gunakan checklist ini untuk menandai progres refactor secara bertahap tanpa mengubah perilaku produk.

Status:

- `[ ]` Belum dikerjakan.
- `[~]` Sedang/parsial dikerjakan.
- `[x]` Selesai, sudah diverifikasi, dan sudah dicatat hasil test-nya.

Aturan pakai:

1. Kerjakan satu modul kecil per commit.
2. Jangan ubah behavior kecuali ada bug jelas dan test ikut diperbarui.
3. Jangan menghapus, memindah, atau rename file tanpa konfirmasi owner.
4. Setelah item selesai, ubah checkbox menjadi `[x]`, isi catatan singkat, jalankan gate relevan, lalu commit.
5. Untuk perubahan backend/authorization/tenant, minimal jalankan targeted test + `php artisan test`.
6. Untuk perubahan frontend, minimal jalankan `npm run lint` + `npm run build`.
7. Jika file masuk frozen/maintenance module, lakukan cleanup hanya untuk bug fix atau risiko kecil.

Gate standar sebelum checklist item dianggap selesai:

- [x] `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test`
- [x] `npm run lint`
- [x] `npm run build`
- [x] `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test`

---

## 1. Already Completed Clean-Code Items

- [x] `app/Http/Controllers/MidtransWebhookController.php`
  - Refactor: validasi payload Midtrans dipindah ke `ProcessMidtransWebhookRequest`.
  - Commit: `fc920fb refactor: tighten webhook validation and landing nav`
  - Verifikasi: payment targeted test, Pint, lint, build, full PHP suite.

- [x] `app/Http/Requests/ProcessMidtransWebhookRequest.php`
  - Refactor: FormRequest baru untuk kontrak payload webhook.
  - Commit: `fc920fb refactor: tighten webhook validation and landing nav`
  - Verifikasi: payment targeted test, Pint, lint, build, full PHP suite.

- [x] `resources/js/Components/Landing/Navbar.tsx`
  - Refactor: nav data dipindah ke shared data, cabang disabled item yang tidak terpakai dihapus.
  - Commit: `fc920fb refactor: tighten webhook validation and landing nav`
  - Verifikasi: lint dan build.

- [x] `resources/js/Components/Landing/MobileMenu.tsx`
  - Refactor: nav data dipindah ke shared data, cabang disabled item yang tidak terpakai dihapus.
  - Commit: `fc920fb refactor: tighten webhook validation and landing nav`
  - Verifikasi: lint dan build.

- [x] `resources/js/Data/landingNavigation.ts`
  - Refactor: shared navigation source untuk landing navbar dan mobile menu.
  - Commit: `fc920fb refactor: tighten webhook validation and landing nav`
  - Verifikasi: lint dan build.

---

## 2. P0 Guardrail Before Any Cleanup

- [x] Baca `AGENTS.md` section `Clean Code Operating Prompt`.
- [x] Baca `README.md` status fitur dan flow pengguna.
- [x] Jalankan `git status --short --branch`.
- [x] Pastikan tidak ada dirty work yang tidak berhubungan.
  - Catatan: `AGENTS 2.md` dan `vitest.config 2.ts` adalah untracked lama, tidak disentuh.
- [x] Tentukan satu scope kecil.
- [x] Catat targeted test yang akan dijalankan sebelum edit.
- [x] Pastikan root markdown policy dipatuhi: file checklist ini adalah pengecualian eksplisit dari owner.

---

## 3. P1 Backend Cleanup Candidates

Prioritas backend adalah menjaga controller tipis, query tenant aman, validasi eksplisit, action jelas, dan payload tidak terlalu besar.

### 3.1 Controllers

- [x] `app/Http/Controllers/WorkspacePageController.php`
  - Alasan: file besar, banyak route/page orchestration dalam satu controller.
  - Refactor: ekstrak helper `numericActiveOrganizationId()` dan `storedActiveOrganizationId()` untuk menghapus duplikasi session lookup tanpa mengubah behavior lama.
  - Verifikasi: Pint targeted pass; targeted workspace/page tests pass (`31 passed`, `194 assertions`).

- [x] `app/Http/Controllers/ProfileController.php`
  - Alasan: masih ada direct `$request->validate()` untuk update password/delete profile pattern bawaan Breeze.
  - Refactor: delete-account validation dipindah ke `DeleteProfileRequest`; `ProfileUpdateRequest` dibuat strict/final.
  - Verifikasi: profile/password targeted tests pass (`7 passed`, `29 assertions`).

- [x] `app/Http/Controllers/Auth/RegisteredUserController.php`
  - Alasan: direct validation bawaan auth.
  - Refactor: registration validation dipindah ke `Auth\RegisterUserRequest`; controller dibuat strict/final.
  - Verifikasi: registration/auth targeted tests pass (`12 passed`, `28 assertions`).

- [x] `app/Http/Controllers/Auth/PasswordController.php`
  - Alasan: direct validation bawaan auth.
  - Refactor: password update validation dipindah ke `Auth\UpdatePasswordRequest`; controller dibuat strict/final.
  - Verifikasi: password/profile targeted tests pass (`7 passed`, `29 assertions`).

- [x] `app/Http/Controllers/Auth/PasswordResetLinkController.php`
  - Alasan: direct validation bawaan auth.
  - Refactor: forgot-password email validation dipindah ke `Auth\SendPasswordResetLinkRequest`; controller dibuat strict/final.
  - Verifikasi: password reset targeted tests pass (`5 passed`, `11 assertions`).

- [x] `app/Http/Controllers/Auth/NewPasswordController.php`
  - Alasan: direct validation bawaan auth.
  - Refactor: reset-password validation dipindah ke `Auth\StoreNewPasswordRequest`; controller dibuat strict/final.
  - Verifikasi: password reset targeted tests pass (`5 passed`, `11 assertions`).

### 3.2 Large Actions And Payload Builders

- [ ] `app/Actions/DocumentExport/GenerateDocumentExportContentAction.php`
  - Alasan: sangat besar, banyak format/export branch.
  - Target cleanup: pecah renderer/section builder per export type tanpa mengubah output.
  - Test: export/LPJ/proposal/meeting/handover targeted tests + visual/manual export check bila perlu.

- [ ] `app/Actions/Workspace/GetMeetingMinutePayloadAction.php`
  - Alasan: payload query banyak dan transform data padat.
  - Target cleanup: ekstrak query helper kecil, pastikan eager/aggregate jelas.
  - Test: meeting minutes payload + meeting management tests.

- [ ] `app/Actions/Workspace/GetHandoverPayloadAction.php`
  - Alasan: payload handover cukup besar dan raw query banyak.
  - Target cleanup: pisahkan metrics, package list, transition payload.
  - Test: handover package tests.

- [ ] `app/Actions/Microsite/GetPublicMicrositePayloadAction.php`
  - Alasan: public payload sensitif untuk visibility dan published state.
  - Target cleanup: rapikan transformer, jaga public exposure.
  - Test: microsite tests.

- [ ] `app/Actions/Search/GlobalSearchAction.php`
  - Alasan: banyak query per domain.
  - Target cleanup: ekstrak search source methods dengan limit/scoping konsisten.
  - Test: global search feature/security tests.

- [ ] `app/Actions/Dashboard/GetDashboardOverviewAction.php`
  - Alasan: dashboard aggregate query padat.
  - Target cleanup: ekstrak metrics/query helpers dan pastikan tenant scoping.
  - Test: dashboard overview unit tests.

- [ ] `app/Actions/Dashboard/SidebarMenuAction.php`
  - Alasan: role/menu/count logic cukup besar.
  - Target cleanup: pisahkan badge count resolver dari menu definition.
  - Test: role menu/sidebar tests.

- [ ] `app/Actions/Campus/CampusDashboardPayloadAction.php`
  - Alasan: module hibernate, payload besar.
  - Target cleanup: maintenance-only; jangan ekspansi fitur.
  - Test: campus dashboard tests jika ada.

- [x] `app/Actions/Workspace/GetDocumentUploadCenterPayloadAction.php`
  - Alasan: document payload + filters + storage visibility.
  - Refactor: pecah payload builder menjadi `documents()`, `projects()`, dan visibility filter helper agar aturan public/private/restricted/committee lebih eksplisit.
  - Verifikasi: targeted document upload tests pass (`8 passed`, `48 assertions`).

- [ ] `app/Actions/Workspace/GetLpjChecklistPayloadAction.php`
  - Alasan: LPJ metrics dan checklist query banyak.
  - Target cleanup: ekstrak metrics builder.
  - Test: LPJ checklist/approval tests.

- [ ] `app/Actions/EventRegistration/RegisterPublicEventAction.php`
  - Alasan: public registration + payment order creation sensitif.
  - Target cleanup: pisahkan tier resolution/payment order creation; jaga transactional behavior.
  - Test: event registration + payment ticketing tests.

- [ ] `app/Actions/Handover/InitiateHandoverPackageAction.php`
  - Alasan: snapshot handover dan default item generation padat.
  - Target cleanup: ekstrak snapshot builder, jaga idempotency.
  - Test: handover package tests.

### 3.3 Dashboard Variant Actions

- [ ] `app/Actions/Dashboard/Variants/PimpinanDashboardPayloadAction.php`
  - Alasan: banyak inline metric query.
  - Target cleanup: ekstrak helper metric/query, jaga role-specific payload shape.
  - Test: dashboard variant tests.

- [ ] `app/Actions/Dashboard/Variants/BendaharaDashboardPayloadAction.php`
  - Alasan: finance metrics inline dan raw query berulang.
  - Target cleanup: reuse scoped finance query helper.
  - Test: finance dashboard/approval tests.

- [ ] `app/Actions/Dashboard/Variants/SekretarisDashboardPayloadAction.php`
  - Alasan: proposal/meeting/document metrics inline.
  - Target cleanup: ekstrak query methods.
  - Test: dashboard variant tests.

- [ ] `app/Actions/Dashboard/Variants/OperasionalDashboardPayloadAction.php`
  - Alasan: project/task metrics dan projectIds logic berulang.
  - Target cleanup: ekstrak project scope resolver.
  - Test: dashboard variant tests.

- [ ] `app/Actions/Dashboard/Variants/MemberDashboardPayloadAction.php`
  - Alasan: query panjang satu baris dan role-specific task list.
  - Target cleanup: pecah query line, ekstrak helper.
  - Test: dashboard role/member tests.

### 3.4 Support And Middleware

- [ ] `app/Http/Middleware/HandleInertiaRequests.php`
  - Alasan: shared Inertia data berukuran besar.
  - Target cleanup: ekstrak shared data builders per concern: auth, org, flash, feature flags.
  - Test: auth/session/organization switch tests.

- [ ] `app/Support/OrganizationModeGate.php`
  - Alasan: gate mode organisasi sensitif untuk multi-mode behavior.
  - Target cleanup: tambahkan nama method lebih jelas dan test boundary jika belum cukup.
  - Test: kepanitiaan mode tests.

- [ ] `app/Support/Ai/OpenAiProvider.php`
  - Alasan: external provider integration; frozen expansion.
  - Target cleanup: hanya hardening error handling/config boundary, jangan ekspansi AI.
  - Test: AI assistant tests.

---

## 4. P1 Frontend Cleanup Candidates

Prioritas frontend adalah memecah page besar, mengurangi helper lokal, menjaga Inertia-first, dan mempertahankan visual Viho/admin.

### 4.1 Very Large Pages

- [ ] `resources/js/Pages/Microsite/Settings.tsx`
  - Alasan: 600+ baris, banyak form/section dalam satu page.
  - Target cleanup: ekstrak form sections ke `resources/js/Components/Microsite/`.
  - Test: `npm run lint`, `npm run build`, browser smoke microsite settings.

- [ ] `resources/js/Pages/Certificates/Templates.tsx`
  - Alasan: 600+ baris, template UI dan form logic padat.
  - Target cleanup: ekstrak template editor, list, preview panel.
  - Test: certificate feature tests + build.

- [ ] `resources/js/Pages/Proker/Show.tsx`
  - Alasan: page detail besar dengan banyak panel.
  - Target cleanup: ekstrak overview, task summary, finance summary, document/status panels.
  - Test: proker detail smoke + build.

- [ ] `resources/js/Pages/Organization/Handover.tsx`
  - Alasan: handover flow kompleks dan module partial/maintenance.
  - Target cleanup: ekstrak package status, item list, transition controls.
  - Test: handover feature tests + browser smoke.

- [ ] `resources/js/Pages/Organization/SponsorsVendors.tsx`
  - Alasan: list/form/detail interaction cukup besar.
  - Target cleanup: ekstrak sponsor/vendor card/table/form.
  - Test: sponsor/vendor tests + build.

- [ ] `resources/js/Pages/Attendance/Index.tsx`
  - Alasan: attendance dashboard + QR/session UI padat.
  - Target cleanup: ekstrak session list, QR tools, record table.
  - Test: QR attendance tests + device QA later.

- [ ] `resources/js/Pages/Organization/Setup.tsx`
  - Alasan: setup/onboarding form panjang.
  - Target cleanup: ekstrak steps/sections; jaga first-run UX.
  - Test: onboarding wizard tests + build.

- [ ] `resources/js/Pages/Reports/ProposalEditor.tsx`
  - Alasan: editor page besar, export/review states.
  - Target cleanup: ekstrak editor toolbar, section list, approval panel.
  - Test: proposal approval/editor tests + build.

- [ ] `resources/js/Pages/Events/Registrations.tsx`
  - Alasan: registration management page cukup besar.
  - Target cleanup: ekstrak settings, table, export queue, stats.
  - Test: event registration tests + payment tests + build.

- [ ] `resources/js/Pages/Reports/LpjChecklist.tsx`
  - Alasan: checklist + metrics + status flow.
  - Target cleanup: ekstrak checklist item row dan summary cards.
  - Test: LPJ tests + build.

- [ ] `resources/js/Pages/Certificates/Issue.tsx`
  - Alasan: issuance form/list logic padat.
  - Target cleanup: ekstrak recipient picker dan issue summary.
  - Test: certificate tests + build.

### 4.2 Medium Pages Worth Cleaning

- [ ] `resources/js/Pages/Documents/UploadCenter.tsx`
  - Target cleanup: ekstrak drag-drop upload zone, filters, document table.
  - Test: document upload tests + build.

- [ ] `resources/js/Pages/Meetings/Index.tsx`
  - Target cleanup: ekstrak agenda/list/metrics sections.
  - Test: meeting management tests + build.

- [ ] `resources/js/Pages/Finance/Index.tsx`
  - Target cleanup: ekstrak budget summary/table/action panels.
  - Test: budget line CRUD tests + build.

- [ ] `resources/js/Pages/Organization/Periods.tsx`
  - Target cleanup: ekstrak period table/form/status controls.
  - Test: organization period tests + build.

- [ ] `resources/js/Pages/Organization/Calendar.tsx`
  - Target cleanup: ekstrak calendar list/filter/feed controls.
  - Test: calendar sync tests + build.

- [ ] `resources/js/Pages/Events/Register.tsx`
  - Target cleanup: ekstrak public registration form and ticket tier selector.
  - Test: event registration public tests + build.

- [ ] `resources/js/Pages/Microsite/Show.tsx`
  - Target cleanup: ekstrak public hero/schedule/gallery/sponsor sections.
  - Test: microsite tests + public browser smoke.

- [ ] `resources/js/Pages/Finance/Approval.tsx`
  - Target cleanup: approval queue/cards/timeline components.
  - Test: budget approval tests + build.

- [ ] `resources/js/Pages/Finance/Realization.tsx`
  - Target cleanup: receipt upload, realization table, summary.
  - Test: finance realization tests + build.

- [ ] `resources/js/Pages/Meetings/Partials/MeetingMinutesEditor.tsx`
  - Target cleanup: editor toolbar/content/status/export controls.
  - Test: meeting minutes tests + build.

- [ ] `resources/js/Pages/Reports/Index.tsx`
  - Target cleanup: report cards/export queue split.
  - Test: report overview tests + build.

- [ ] `resources/js/Pages/Certificates/Index.tsx`
  - Target cleanup: certificate dashboard cards/table.
  - Test: digital certificate tests + build.

- [ ] `resources/js/Pages/Members/Index.tsx`
  - Target cleanup: member table, role filters, action menu.
  - Test: member management tests + build.

- [ ] `resources/js/Pages/Finance/BudgetDraft.tsx`
  - Target cleanup: budget line grouping and row state.
  - Test: budget line CRUD tests + build.

- [ ] `resources/js/Pages/Proker/Index.tsx`
  - Target cleanup: project filters/cards/table components.
  - Test: proker index payload tests + build.

- [ ] `resources/js/Pages/Proker/Create.tsx`
  - Target cleanup: form sections and template picker.
  - Test: project create tests + build.

- [ ] `resources/js/Pages/Proker/Edit.tsx`
  - Target cleanup: shared form with create page if behavior identical.
  - Test: project update tests + build.

- [ ] `resources/js/Pages/KepanitiaanDashboard/Index.tsx`
  - Target cleanup: module dashboard panels.
  - Test: kepanitiaan mode tests + build.

- [ ] `resources/js/Pages/Campus/Dashboard.tsx`
  - Target cleanup: hibernated module; maintenance-only.
  - Test: campus tests if available + build.

### 4.3 Components And Shared Data

- [ ] `resources/js/Components/Search/GlobalSearchBar.tsx`
  - Alasan: keyboard navigation, debounced search, Inertia navigation dalam satu component.
  - Target cleanup: ekstrak keyboard hook atau search result list.
  - Test: search feature tests + build.

- [ ] `resources/js/Components/Attendance/QrCameraScanner.tsx`
  - Alasan: camera/device-sensitive component.
  - Target cleanup: isolate camera permission/state messages; jaga browser compatibility.
  - Test: build + Android/iOS device QA.

- [ ] `resources/js/Components/Prokerin/OnboardingWizard.tsx`
  - Alasan: multi-step state.
  - Target cleanup: extract step definitions and validation hints.
  - Test: onboarding wizard tests + build.

- [ ] `resources/js/Components/Viho/VihoSidebar.tsx`
  - Alasan: navigation shell penting.
  - Target cleanup: keep menu rendering declarative; avoid visual regressions.
  - Test: build + browser smoke desktop/mobile.

- [ ] `resources/js/Components/Prokerin/ModuleOverview.tsx`
  - Alasan: dashboard module display reusable.
  - Target cleanup: simplify props and repeated card rendering.
  - Test: build.

- [ ] `resources/js/Data/roleMenus.ts`
  - Alasan: menu definition besar.
  - Target cleanup: validate grouping, remove duplication, keep route names correct.
  - Test: lint/build + route smoke.

- [ ] `resources/js/types/prokerin.ts`
  - Alasan: type file besar.
  - Target cleanup: split only if ownership remains clear; avoid circular imports.
  - Test: `npm run lint`.

---

## 5. P2 Database And Seeder Cleanup Candidates

- [ ] `database/seeders/DatabaseSeeder.php`
  - Alasan: 1000+ baris, banyak demo/user/org/project setup dalam satu file.
  - Target cleanup: ekstrak private methods lebih kecil atau dedicated seeders bila aman.
  - Test: `php artisan migrate:fresh --seed` hanya di local/test DB, seeded QA tests.

- [ ] `database/seeders/DemoShowcaseSeeder.php`
  - Alasan: hampir 1000 baris demo data.
  - Target cleanup: ekstrak dataset arrays by domain, jaga deterministic seed.
  - Test: demo showcase seeder tests.

- [ ] `database/migrations/2026_05_16_000003_create_prokerin_workspace_tables.php`
  - Alasan: migration besar.
  - Target cleanup: jangan ubah migration historis kecuali belum rilis dan owner setuju.
  - Test: migration fresh test.

- [ ] Review migration index consistency for tenant-heavy tables.
  - Target cleanup: additive indexes only, no destructive schema changes.
  - Test: migration + targeted query tests.

---

## 6. P2 Routes And App Wiring

- [ ] `routes/web.php`
  - Alasan: 300+ baris, semua web/Inertia route terkumpul.
  - Target cleanup: rapikan route groups by domain/middleware/name prefix; jangan pindah ke API.
  - Test: `php artisan route:list --except-vendor`, route smoke, full suite.

- [ ] `resources/js/bootstrap.ts`
  - Alasan: axios global ada untuk AJAX-only use case.
  - Target cleanup: pastikan hanya dipakai untuk allowed AJAX flows, bukan REST layer umum.
  - Test: lint/build.

- [ ] `resources/js/types/global.d.ts`
  - Alasan: global axios/window route types.
  - Target cleanup: pastikan type global minimal dan eksplisit.
  - Test: lint.

---

## 7. P2 Test Cleanup Candidates

Test cleanup harus menjaga coverage, bukan sekadar memangkas baris.

- [ ] `tests/Feature/HandoverPackageTest.php`
  - Alasan: 400+ baris, banyak helper/setup bisa dipusatkan.
  - Target cleanup: extract helper methods/fixtures tanpa mengurangi assertion.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/ProposalApprovalTest.php`
  - Alasan: approval scenario besar.
  - Target cleanup: helper for approval setup and actor login.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/EventRegistrationTest.php`
  - Alasan: public registration + export scenarios banyak.
  - Target cleanup: helper for project/settings/registration setup.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/MultiLevelApprovalWorkflowTest.php`
  - Alasan: workflow approval kompleks.
  - Target cleanup: scenario builders.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/PaymentTicketingTest.php`
  - Alasan: payment setup helper bisa distandarkan.
  - Target cleanup: keep webhook/payment assertions strong.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/DigitalCertificateTest.php`
  - Alasan: certificate fixture setup panjang.
  - Target cleanup: helper for templates/recipients.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/InventoryManagementTest.php`
  - Alasan: inventory scenario cukup banyak.
  - Target cleanup: item/loan factory helper.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/MeetingManagementTest.php`
  - Alasan: meeting/minutes/export scenarios.
  - Target cleanup: setup helpers and named fixtures.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/OrganizationManagementTest.php`
  - Alasan: org/member/invite scenarios.
  - Target cleanup: shared organization helper.
  - Test: file test itu sendiri.

- [ ] `tests/Feature/BudgetLineCrudTest.php`
  - Alasan: finance setup duplicated with approval tests.
  - Target cleanup: shared budget helper if it does not obscure assertions.
  - Test: file test itu sendiri.

---

## 8. P3 Domain-by-Domain Cleanup Backlog

Gunakan daftar ini setelah P1/P2 selesai atau saat ada bug di domain terkait.

### Organization And Membership

- [ ] `app/Actions/Workspace/GetMembersOverviewPayloadAction.php`
- [ ] `app/Actions/Workspace/GetMemberInvitationsPayloadAction.php`
- [ ] `app/Actions/Workspace/GetOrganizationSwitcherPayloadAction.php`
- [ ] `app/Actions/Workspace/GetOrganizationCalendarPayloadAction.php`
- [ ] `resources/js/Pages/Organization/Switcher.tsx`
- [ ] `resources/js/Pages/Members/Invites.tsx`

### Project And Task

- [ ] `app/Actions/Workspace/GetProkerIndexPayloadAction.php`
- [ ] `app/Actions/Project/CreateProjectAction.php`
- [ ] `app/Actions/Project/UpdateProjectAction.php`
- [ ] `app/Actions/Task/CreateTaskAction.php`
- [ ] `resources/js/Pages/Task/Kanban.tsx`
- [ ] `resources/js/Components/Task/TaskQuickAdd.tsx`

### Finance

- [ ] `app/Actions/Finance/CreateBudgetLineAction.php`
- [ ] `app/Actions/Finance/UpdateBudgetLineAction.php`
- [ ] `app/Actions/Finance/DecideBudgetLineApprovalAction.php`
- [ ] `app/Actions/Workspace/GetFinanceApprovalPayloadAction.php`
- [ ] `resources/js/Components/Finance/BudgetLineRow.tsx` if present in current tree.
- [ ] `resources/js/Components/Finance/BudgetLineCreateForm.tsx` if present in current tree.

### Proposal And LPJ

- [ ] `app/Actions/Proposal/SubmitProposalDraftAction.php`
- [ ] `app/Actions/Proposal/UpdateProposalDraftSectionsAction.php`
- [ ] `app/Actions/Proposal/DecideProposalApprovalAction.php`
- [ ] `app/Actions/Lpj/SubmitLpjReviewAction.php`
- [ ] `app/Actions/Lpj/DecideLpjApprovalAction.php`

### Documents And Export

- [ ] `app/Actions/Document/StoreDocumentAction.php`
- [ ] `app/Actions/DocumentExport/QueueDocumentExportAction.php`
- [ ] `app/Jobs/GenerateDocumentExportJob.php`
- [ ] `resources/js/Pages/Documents/Folders.tsx`

### Attendance

- [ ] `app/Actions/Attendance/CheckInAttendanceQrAction.php`
- [ ] `app/Actions/Attendance/RecordManualAttendanceAction.php`
- [ ] `app/Actions/Attendance/GenerateAttendanceQrTokenAction.php`
- [ ] `resources/js/Pages/Attendance/QrLookup.tsx`

### Letters

- [ ] `app/Actions/Letter/DraftLetterAction.php`
- [ ] `app/Actions/Letter/SignLetterAction.php`
- [ ] `app/Actions/Letter/BulkIssueLettersAction.php`
- [ ] `resources/js/Pages/Letters/Templates.tsx`
- [ ] `resources/js/Pages/Letters/Index.tsx`

### Inventory

- [ ] `app/Actions/Inventory/StoreInventoryItemAction.php`
- [ ] `app/Actions/Inventory/UpdateInventoryItemAction.php`
- [ ] `app/Actions/Inventory/RequestLoanAction.php`
- [ ] `app/Actions/Inventory/ReturnLoanAction.php`
- [ ] `resources/js/Pages/Inventory/Index.tsx`
- [ ] `resources/js/Pages/Inventory/Show.tsx`

### Notifications And Realtime

- [ ] `app/Actions/Notification/StoreWebPushSubscriptionAction.php`
- [ ] `app/Actions/Notification/DeleteWebPushSubscriptionAction.php`
- [ ] `app/Actions/Workspace/GetNotificationRulePayloadAction.php`
- [ ] `resources/js/Components/Notifications/NotificationDropdown.tsx` if present in current tree.
- [ ] `resources/js/Pages/Notifications/Index.tsx`

### Admin And Super Admin

- [ ] `app/Filament/Resources/Users/Tables/UsersTable.php`
- [ ] `app/Filament/Resources/Users/UserResource.php`
- [ ] `app/Filament/Resources/Organizations/OrganizationResource.php`
- [ ] Super-admin V2 feature flag tooling files.

---

## 9. Checklist Update Log

- [x] 2026-05-17: Clean-code batch 2 selesai: WorkspacePageController active org helper, Profile delete FormRequest, Auth registration/password/reset FormRequests. Verifikasi full gate pass: Pint, lint, build, `551 passed`, `2981 assertions`.
- [x] 2026-05-17: Clean-code batch 3 selesai: Document upload center payload dipecah menjadi helper query/visibility. Verifikasi targeted document upload pass (`8 passed`, `48 assertions`).
- [x] 2026-05-17: Initial clean-code checklist dibuat berdasarkan audit file size, validation/controller patterns, payload/action density, dan frontend page complexity.
