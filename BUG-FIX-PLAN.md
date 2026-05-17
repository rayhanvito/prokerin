# BUG-FIX-PLAN.md — Prokerin
## Rencana Perbaikan Bug, Tombol Dummy, dan Fitur Belum Wired (Untuk AI Dev / Codex)

> **Wajib baca dulu sebelum mulai koding:** `AGENTS.md`, `features.md`, `QA-MASTER-PROKERIN.md`, dan `QA-REPORT-PROKERIN.md`.
> Dokumen ini menerjemahkan temuan QA `QA-OPEN-001` sampai `QA-OPEN-020` plus rekomendasi UX/security tambahan menjadi rencana eksekusi konkret per fase. Setiap fase punya tujuan, file yang disentuh, action class baru, route baru, validasi, test, dan checklist.
> Patuhi semua aturan di `AGENTS.md` (PHP 8.4 strict_types, controller tipis → Action, Form Request untuk validasi, Inertia (bukan REST), Tailwind + cn(), tenant scoping wajib, tidak boleh `env()` di luar config, tidak boleh business logic di Controller/Model, tidak boleh `any` di TS, dst).

---

## 0. Aturan Kerja Eksekusi

### 0.1 Sebelum Setiap Fase
1. Tarik branch baru per fase dengan format `fix/qa-phase-<n>-<slug>` (misal `fix/qa-phase-1-org-mgmt`).
2. Jalankan baseline:
   ```bash
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
   npm run build
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
   ```
   Test harus hijau, build harus bersih sebelum mulai.
3. Buka checklist fase di dokumen ini, kerjakan top-down.

### 0.2 Selama Eksekusi
- Tulis kode mengikuti urutan: migration → enum/value object → Action → Form Request → Controller → Route → React Page/Component → Tests.
- Setiap Action class **wajib** punya:
  - `declare(strict_types=1);`
  - Tipe parameter dan return.
  - Tenant scoping eksplisit (`organization_id` derived from `auth()->user()`, **bukan** dari request body).
  - Authorization check via Policy atau role gate sebelum mutasi (gunakan helper di Section 0.5 jika belum ada).
- Setiap Form Request:
  - `authorize()` mengembalikan boolean berdasarkan role/membership, bukan `true` mentah.
  - `rules()` lengkap, gunakan `Rule::enum()` untuk enum domain.
- React component:
  - Tidak boleh `any`.
  - Pakai `useForm` dari `@inertiajs/react` untuk semua mutasi.
  - Pakai `cn()` dari `lib/utils.ts`.
  - Empty state wajib (illustration + title + helper copy + CTA), bukan blank space.
  - Komponen besar pecah ke `Partials/` co-located atau `Components/<domain>/`.

### 0.3 Sebelum Commit
- `./vendor/bin/pint` (auto-fix) → harus zero diff sesudah dijalankan ulang.
- `npm run build` (TypeScript strict) → harus pass.
- `php artisan test` → harus pass dan jumlah test naik (regression suite + feature baru).
- Update bagian "Verification" di fase yang dikerjakan: ceklist + hasil test count.
- Commit format AGENTS.md §12, contoh:
  - `feat(qa-001): wire create organization form and route`
  - `feat(qa-016): role-gate finance GET routes`
  - `fix(qa-017): replace workspaceMock with tenant-scoped proker payload`
  - `test(qa-006): add task overview database-backed payload tests`

### 0.4 Definition of Done Per Item Bug
1. Code merged ke fase branch.
2. Test feature/unit yang relevan pass dan jumlah assertion bertambah dibanding baseline.
3. Browser smoke (manual) sudah dicatat di Section "Verification" fase tersebut.
4. `QA-REPORT-PROKERIN.md` diupdate: pindahkan item dari Section 3 (Open Findings) ke Section 2 (Bug Fixed) dengan status `Fixed` dan referensi commit.
5. Checklist `[ ]` di dokumen ini ditandai `[x]`.

### 0.5 Helper Yang Boleh Dipakai (Sudah Ada Atau Bisa Dibuat Sekali)
- `App\Actions\Workspace\GetActiveOrganizationContextAction` (buat baru jika belum ada): mengembalikan `{ organizationId, role, periodId }` dari user aktif. Reuse oleh semua Action mutasi agar tenant scoping konsisten dan ada satu tempat update.
- `App\Support\Roles::CAN_MANAGE_ORG = ['organization_owner', 'organization_admin']` dan `Roles::CAN_MANAGE_FINANCE = [...]` — kumpulan konstanta role agar tidak hard-code string array di banyak tempat.
- React: bikin `resources/js/Components/ui/EmptyState.tsx`, `resources/js/Components/ui/ConfirmDialog.tsx`, `resources/js/Components/ui/FormField.tsx` di Phase 0 supaya fase berikutnya tinggal pakai.

### 0.6 Larangan Mutlak (AGENTS.md §15)
- Jangan tambah top-level folder baru tanpa konfirmasi user.
- Jangan ubah file di `resources/js/Components/ui/` (shadcn primitives) — extend lewat wrapper.
- Jangan kembalikan model Eloquent mentah ke Inertia. Selalu pakai array atau DTO.
- Jangan pakai `env()` di kode app (bukan config). Tambah konfigurasi ke `config/prokerin.php` atau buat config baru sesuai modul.
- Jangan pakai `migrate:fresh` atau `db:seed` di staging/production.
- Jangan inline style. Jangan `!important`. Jangan string concatenation untuk class — pakai `cn()`.
- Jangan REST API baru. Semua data flow via Inertia web routes.

---

## 1. Ringkasan Fase

| Fase | Nama | QA Items | Severity | Estimasi |
|------|------|----------|----------|----------|
| 0 | Foundation Helpers (EmptyState, ConfirmDialog, FormField, Role helpers) | — | Enabler | Kecil |
| 1 | Organization Management (create, switcher data, periods, calendar, edit) | QA-OPEN-001, 002, 003, 005, plus 5.8 | HIGH | Sedang |
| 2 | Member & Invite (invite form, accept/decline, remove member, members overview data-backed) | QA-OPEN-004, 014, plus 6.3, 6.4, 6.8, S2.3 | HIGH | Sedang |
| 3 | Proker Lifecycle (overview data-backed, status transition, progress recompute, project member management) | QA-OPEN-013, 017, 018, plus 7.5, 7.8, F1 flow, F6.2 | HIGH | Besar |
| 4 | Task Operasional (overview data, assign PIC form, quick-add, overdue, kanban polish) | QA-OPEN-006, 007, 019, plus 9.3, 9.4, 9.6, 9.7 | HIGH | Sedang |
| 5 | Finance End-to-End (overview data, budget draft CRUD, role-gate finance, remaining budget) | QA-OPEN-008, 009, 016, plus 10.1, 10.2, 10.3, 10.10, 10.12 | HIGH | Besar |
| 6 | Documents (upload form, folders data-backed, oversized rejection, visibility) | QA-OPEN-010, 011, plus 12.1, 12.2, 12.5, 12.7, 12.8, S4.1, S4.3 | HIGH | Sedang |
| 7 | Reports & LPJ (overview data, checklist toggle, LPJ export trigger, completed task data binding) | QA-OPEN-015, 020, plus 13.2, 13.7, 13.8 | MEDIUM | Sedang |
| 8 | Certificate Visual Preview | QA-OPEN-012, 18 review | LOW | Kecil |
| 9 | UX Improvements Wide (empty state sweep, toast, breadcrumbs, list filters, confirmation dialogs) | UX-01, UX-04, UX-06, UX-12, UX-13 | MEDIUM | Sedang |
| 10 | Notification Bell Dropdown + Per-user Preferences | UX-14, plus 15.6, 15.7 | MEDIUM | Sedang |
| 11 | Security Hardening (rate-limit, soft delete audit, missing indexes, file upload SVG/PHP guard) | TECH-03, TECH-05, TECH-08, S4.1, S4.3 | HIGH | Sedang |
| 12 | Observability (Sentry, failed job handler, retry UI) | TECH-02, TECH-07 | MEDIUM | Sedang |

**Urutan eksekusi yang direkomendasikan:** 0 → 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → 9 → 10 → 11 → 12.
Fase 1–7 menutup HIGH severity dummy-button yang membuat Flow 1 (Full Proker Lifecycle) dan F6 (cross-tenant) gagal di QA-MASTER. Fase 8–12 adalah polish dan hardening pra-launch.

---

## 2. Phase 0 — Foundation Helpers

### Tujuan
Bikin sekali, dipakai berulang. Fase berikutnya **tidak boleh** menulis ulang empty state, confirm dialog, dan helper role gate.

### 2.1 EmptyState Component
- File baru: `resources/js/Components/ui/EmptyState.tsx`.
- Props (TypeScript explicit, no `any`):
  ```ts
  interface EmptyStateProps {
      icon: LucideIcon;
      title: string;
      description: string;
      action?: { label: string; onClick?: () => void; href?: string };
  }
  ```
- Style mengikuti Viho tokens (`#24695c` aksen, border `#e6edef`, padding 32px, ilustrasi icon 48px di lingkaran ringan).
- `action.href` → render Inertia `Link`. `action.onClick` → render `<button type="button">`. Salah satu boleh kosong.

### 2.2 ConfirmDialog Component
- File baru: `resources/js/Components/ui/ConfirmDialog.tsx`.
- Bungkus shadcn `AlertDialog` (sudah ada di `Components/ui/`).
- Props:
  ```ts
  interface ConfirmDialogProps {
      open: boolean;
      onOpenChange: (open: boolean) => void;
      title: string;
      description: string;
      confirmLabel: string;
      confirmTone?: 'default' | 'danger';
      requireTypedPhrase?: string; // jika diisi, user harus ketik persis untuk enable Confirm
      onConfirm: () => void;
  }
  ```
- Validasi typed phrase pakai state internal; tombol Confirm disable sampai input cocok persis.

### 2.3 FormField Wrapper
- File baru: `resources/js/Components/ui/FormField.tsx`.
- Props:
  ```ts
  interface FormFieldProps {
      label: string;
      htmlFor: string;
      required?: boolean;
      error?: string;
      hint?: string;
      children: ReactNode;
  }
  ```
- Render label, slot input children, error state (`text-[#d22d3d]`), hint, dan border merah pada wrapper bila `error` ada.

### 2.4 Role Constants
- File baru: `app/Support/Roles.php`.
  ```php
  final class Roles
  {
      public const ORGANIZATION_MANAGERS = ['organization_owner', 'organization_admin'];
      public const ORGANIZATION_FULL_VIEWERS = ['organization_owner', 'organization_admin', 'secretary', 'treasurer', 'project_lead', 'division_coordinator', 'member', 'viewer'];
      public const FINANCE_VIEWERS = ['organization_owner', 'organization_admin', 'treasurer'];
      public const FINANCE_MANAGERS = ['organization_owner', 'organization_admin', 'treasurer'];
      public const SECRETARY_AND_UP = ['organization_owner', 'organization_admin', 'secretary'];
      public const PROJECT_LEADERSHIP = ['organization_owner', 'organization_admin', 'project_lead', 'division_coordinator'];
  }
  ```
- Pakai konstanta ini di setiap Action/Policy/Form Request menggantikan array literal.

### 2.5 GetActiveOrganizationContextAction
- File baru: `app/Actions/Workspace/GetActiveOrganizationContextAction.php`.
- Return DTO: `App\DTOs\Workspace\ActiveOrganizationContextData` dengan `organizationId: int`, `role: string`, `activePeriodId: ?int`.
- Resolve dari `organization_members` join dengan `organization_periods`.
- Throw `\Symfony\Component\HttpKernel\Exception\HttpException(409, 'No active organization for user')` kalau user belum punya membership.
- Reuse di Action mutasi mana pun yang butuh `organization_id` aktif.

### 2.6 ToastProvider
- Install `sonner`:
  ```bash
  npm install sonner
  ```
- File baru: `resources/js/Components/ui/Toaster.tsx` — wrapper `<Toaster richColors position="bottom-right" />`.
- Render sekali di `resources/js/Layouts/AuthenticatedLayout.tsx` di samping `<FlashBanner />`.
- Helper `resources/js/lib/toast.ts`:
  ```ts
  export function showFlashToast(flash: PageProps['flash']): void { ... }
  ```
  dipanggil di `AuthenticatedLayout` lewat `useEffect` agar setiap kali Inertia flash berubah, otomatis muncul toast.

### Phase 0 Checklist
- [x] `EmptyState.tsx` selesai dengan ilustrasi default lucide.
- [x] `ConfirmDialog.tsx` selesai dengan typed-phrase guard berfungsi.
- [x] `FormField.tsx` selesai dengan red-border + error state.
- [x] `app/Support/Roles.php` selesai dan minimal 1 Action existing direfaktor pakai konstanta ini sebagai sanity check.
- [x] `GetActiveOrganizationContextAction` + DTO selesai, satu Action existing direfaktor pakai (misal `StoreSponsorVendorAction`) dan test ulang pass.
- [x] `sonner` terinstall, `Toaster` ter-mount, flash success/error otomatis jadi toast.
- [x] `npm run build` pass, `php artisan test` pass tanpa regresi.

### Verification (Phase 0)
- 2026-05-17: `StoreSponsorVendorAction` direfaktor memakai `Roles::ORGANIZATION_MANAGERS` dan `GetActiveOrganizationContextAction`; ditambah regression bahwa create sponsor/vendor mengikuti `active_organization_id` session.
- Targeted refactor test: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php tests/Feature/WorkspacePayloadTest.php --stop-on-failure` -> **15 passed, 190 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter gate: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test app/Support/Roles.php app/DTOs/Workspace/ActiveOrganizationContextData.php app/Actions/Workspace/GetActiveOrganizationContextAction.php app/Actions/Organization/StoreSponsorVendorAction.php app/Http/Middleware/HandleInertiaRequests.php tests/Feature/SponsorVendorTest.php` pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **368 passed, 1968 assertions**.

---

## 3. Phase 1 — Organization Management (HIGH)

Cakupan: QA-OPEN-001, QA-OPEN-002, QA-OPEN-003, QA-OPEN-005, dan QA-MASTER 5.1, 5.2, 5.5, 5.6, 5.7, 5.8.

### 3.1 Create Organization (QA-OPEN-001 + 5.1, 5.2)

#### Backend
- Action baru: `app/Actions/Organization/CreateOrganizationAction.php`.
  - Tipe input DTO: `App\DTOs\Organization\CreateOrganizationData { name: string, slug: string, planTier: string }`.
  - Generate slug otomatis kalau kosong (`Str::slug($name)`); kalau collision, pakai pattern `<slug>-<2 digit random>`.
  - Validasi unique slug di DB (db unique sudah ada).
  - Buat record `organizations`, lalu attach `auth()->user()` ke `organization_members` sebagai `organization_owner`, dan buat satu `organization_periods` aktif default tahun berjalan (start = 1 Jan tahun ini, end = 31 Des tahun ini, `is_active=true`).
  - Return ID organisasi.
- Form Request: `StoreOrganizationRequest`:
  - `authorize`: setiap user authenticated boleh create organisasi (tidak butuh role; Prokerin multi-org).
  - Rules:
    - `name` required, string, 3..120, unique combination kalau ingin keras (cukup unique slug saja).
    - `slug` nullable, string, 3..120, regex `^[a-z0-9-]+$`, unique pada `organizations,slug`.
    - `plan_tier` enum `App\Domain\Organization\Enums\PlanTier` default `free`.
- Controller `OrganizationController` (buat baru jika belum ada):
  - `store(StoreOrganizationRequest $request, CreateOrganizationAction $action)`.
  - Set sesi `active_organization_id` ke organisasi baru.
  - Redirect ke `/organization` dengan flash success.
- Route: `Route::post('/organization', [OrganizationController::class, 'store'])->name('organization.store')` di dalam grup `auth`.

#### Frontend
- Update `resources/js/Pages/Organization/Switcher.tsx`:
  - Hapus array static `organizations`.
  - Ambil props `organizations`, `activeOrganizationId` dari controller.
  - Tombol "Buat Organisasi" → toggle inline form pakai `useForm({ name, slug, plan_tier: 'free' })`.
  - Pakai `FormField` + `useForm.errors`.
  - Tombol submit memanggil `form.post(route('organization.store'))`.
  - Empty state: kalau user belum punya organisasi, render `EmptyState` dengan CTA "Buat Organisasi Pertama".

#### Tests
- File baru: `tests/Feature/OrganizationCreateTest.php`.
- Cases:
  - User authenticated bisa create organisasi dengan slug otomatis.
  - Slug duplicate ditolak `unique` (HTTP 422).
  - Slug regex invalid ditolak.
  - User langsung jadi `organization_owner` di membership baru, dan default period aktif tahun berjalan ada.
  - Guest redirect ke `/login`.

### 3.2 Switcher Aktif (QA-OPEN-002 + 5.6)

#### Backend
- Action baru: `app/Actions/Organization/SwitchActiveOrganizationAction.php`.
  - Input: `actorUserId: int, organizationId: int`.
  - Guard: user harus member `organization_members` di organisasi tujuan, kalau tidak → `AuthorizationException`.
  - Simpan `session(['active_organization_id' => $organizationId])`.
- Update `HandleInertiaRequests.php` `activeOrganization()` agar mengambil `session('active_organization_id')` lebih dulu sebelum fallback ke first membership. Ini bikin sidebar/payload langsung pindah konteks.
- Form Request: `SwitchActiveOrganizationRequest` dengan rule `organization_id` integer exists in `organization_members` user-scoped.
- Controller method `OrganizationController::switch(SwitchActiveOrganizationRequest $request, SwitchActiveOrganizationAction $action)`.
- Route: `Route::post('/organization/switch', [OrganizationController::class, 'switch'])->name('organization.switch')`.

#### Frontend
- `Switcher.tsx`: tiap baris organisasi sekarang clickable button `onClick={() => router.post(route('organization.switch'), { organization_id: org.id })}`. Active org ditandai dengan badge "Current".
- Setelah switch sukses, `activeOrganization` di Inertia shared props otomatis update karena middleware re-derive dari session.

#### Tests
- File: `tests/Feature/OrganizationSwitcherTest.php`.
- Cases:
  - User multi-org bisa switch dan dashboard payload pindah konteks.
  - User tidak boleh switch ke org bukan miliknya (403).
  - Sidebar setelah switch menampilkan menu role baru (re-render `SidebarMenuAction`).

### 3.3 Periods CRUD (QA-OPEN-003 + 5.5)

#### Backend
- Action baru:
  - `CreateOrganizationPeriodAction` (owner/admin): create row `organization_periods`. Kalau `is_active=true`, set semua period lain untuk org ini ke false.
  - `UpdateOrganizationPeriodAction` (owner/admin): update name/start/end/active.
  - Optional: `ArchiveOrganizationPeriodAction` (owner/admin): set `is_active=false`.
- Form Request `StoreOrganizationPeriodRequest`, `UpdateOrganizationPeriodRequest`:
  - Rules: `name` required string max 120, `starts_at` required date, `ends_at` required date `after_or_equal:starts_at`, `is_active` boolean.
  - `authorize` cek role manajer di active organization.
- Controller `OrganizationPeriodController` dengan `store`, `update`.
- Routes:
  - `POST /organization/periods` → `OrganizationPeriodController@store` name `organization.periods.store`.
  - `PATCH /organization/periods/{period}` → `OrganizationPeriodController@update` name `organization.periods.update`.
- Update `WorkspacePageController::organizationPeriods` agar render dari payload action `GetOrganizationPeriodsPayloadAction` yang ambil periods dari DB scope active organization.

#### Frontend
- `resources/js/Pages/Organization/Periods.tsx`:
  - Hapus array static `rows`.
  - Tambah modal/inline form pakai `useForm`. Field: name, starts_at (date input), ends_at (date input), is_active toggle.
  - Tabel baris menampilkan periode dari payload, tombol "Set Active" inline mengirim PATCH `organization.periods.update` dengan `is_active=true`.
  - `canManage` flag dari payload menentukan tombol create/edit muncul.

#### Tests
- File: `tests/Feature/OrganizationPeriodsTest.php`.
- Cases:
  - Owner bisa create period; period baru dengan `is_active=true` membuat period lama nonaktif.
  - Member tidak bisa create period (403).
  - Update period ke `is_active=true` menonaktifkan period lain.
  - Cross-tenant: owner org2 tidak bisa update period org1.

### 3.4 Calendar Data-Backed (QA-OPEN-005 + 5.7)

#### Backend
- Action baru: `app/Actions/Workspace/GetOrganizationCalendarPayloadAction.php`.
- Output (per bulan dipilih):
  - `events`: gabungan dari `projects.starts_at`/`ends_at`, `meetings.starts_at`, `attendance_sessions.starts_at`.
  - Field per item: `id, type ('project'|'meeting'|'attendance'), title, startsAt, endsAt, link`.
- Tambah parameter `?month=YYYY-MM` di `WorkspacePageController::organizationCalendar`. Kalau kosong default ke bulan berjalan.

#### Frontend
- `resources/js/Pages/Organization/Calendar.tsx`: replace mock dengan grid kalender 7×5 yang menampilkan dot dan tooltip per hari berdasarkan event di payload.
- Tombol prev/next bulan mengirim Inertia GET dengan query `month=YYYY-MM`.
- Empty state per hari: tetap render kotak kosong tanpa text "No data".

#### Tests
- File: `tests/Feature/OrganizationCalendarPayloadTest.php`.
- Case: 1 project + 1 meeting di bulan yang sama → muncul 2 events di payload, link benar.

### 3.5 Edit Organisasi (5.8)

#### Backend
- Action `UpdateOrganizationAction` (owner only).
  - Field updatable: `name`, `logo_path` (sudah ada flow upload), `description` (kalau kolom belum ada, tambah migration `add_description_to_organizations`).
  - Slug **tidak** boleh diubah dari UI (sensitif untuk URL). Kalau perlu, tambah catatan TODO Post-MVP.
- Form Request `UpdateOrganizationRequest`. Authorize role owner saja.
- Controller method `OrganizationController::update`.
- Route: `PATCH /organization` → `organization.update`.

#### Frontend
- `resources/js/Pages/Organization/Setup.tsx` (sudah ada): tambah form edit nama/deskripsi di atas existing logo upload. Pakai `useForm` patch.

#### Tests
- File: `tests/Feature/OrganizationUpdateTest.php`. Owner bisa update name; admin tidak bisa; cross-tenant ditolak.

### Phase 1 Checklist
- [x] QA-OPEN-001: Form create organisasi + route + auto-create owner membership + default period.
- [x] QA-OPEN-002: Switcher data dari DB + action switch + session active org id.
- [x] QA-OPEN-003: Periods data-backed + create/edit + set active flow.
- [x] QA-OPEN-005: Calendar data-backed dengan event aggregator.
- [x] 5.8: Edit organization name + description.
- [x] Update `QA-REPORT-PROKERIN.md`: pindahkan QA-OPEN-001/002/003/005 ke "Bug Fixed".

### Verification (Phase 1)
- 2026-05-17: Phase 1 organization management selesai. Create organization, switch active org, periods create/update/set-active, calendar project/meeting/attendance aggregator, and organization profile name/description update sudah wired ke Inertia UI dan route mutasi.
- Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php --stop-on-failure` -> **14 passed, 101 assertions**.
- Regression smoke: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> **27 passed, 430 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter gate: targeted Pint check pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **382 passed, 2069 assertions**.

---

## 4. Phase 2 — Member & Invitation (HIGH)

Cakupan: QA-OPEN-004, QA-OPEN-014, QA-MASTER 6.1–6.4, 6.8, S2.3.

### 4.1 Invite Member End-to-End (QA-OPEN-004 + 6.1, 6.2, S2.3)

#### Backend
- Cek state existing: `organization_invitations` table sudah ada beserta `DecideInvitationAction`. Pastikan flow lengkap.
- Action baru `SendOrganizationInvitationAction` (owner/admin):
  - Input: `actorUserId, email, role`.
  - Guard role: hanya `Roles::ORGANIZATION_MANAGERS`.
  - Reject duplicate aktif (status pending) untuk email yang sama di org yang sama.
  - Reject kalau email sudah jadi member aktif organisasi.
  - Generate token unique (`hash('sha256', Str::random(60))`), simpan `expires_at = now()+7 days`.
  - Dispatch notifikasi `OrganizationInvitationNotification` (database + email channel).
- Form Request `SendOrganizationInvitationRequest`:
  - `authorize`: cek role manajer.
  - Rules: `email` email, `role` ada di whitelist (`secretary`, `treasurer`, `project_lead`, `division_coordinator`, `member`, `viewer` — **bukan** `organization_owner`).
- Controller `OrganizationInvitationController::store`.
- Route `POST /organization/invitations` name `organization.invitations.store`.
- Controller juga butuh route accept/decline:
  - `GET /invitations/{token}` → public preview (kalau user belum login, tampilkan tombol login dulu).
  - `POST /invitations/{token}/accept` → wajib auth, panggil `DecideInvitationAction` accept; pastikan email user match invitation email.
  - `POST /invitations/{token}/decline`.

#### Frontend
- `resources/js/Pages/Members/Invites.tsx`:
  - Hapus array static; ambil payload `invitations: [{ email, role, status, expiresAt, sentAt }]`.
  - Tombol "Invite" → modal `ConfirmDialog`-style atau inline form (lebih baik inline panel) berisi email + role select.
  - Pakai `useForm`.
- File baru: `resources/js/Pages/Invitations/Show.tsx` (public-ish): menampilkan undangan, tombol accept/decline, info expiry.
- Update `SidebarMenuAction` tidak perlu, sudah ada link Members → Invites.

#### Tests
- File: `tests/Feature/OrganizationInvitationFlowTest.php`.
- Cases:
  - Owner bisa kirim invitation; record tersimpan dengan status pending.
  - Member tidak bisa kirim (403). Verifikasi S2.3.
  - Duplicate invitation aktif untuk email sama ditolak.
  - Email yang sudah member aktif ditolak.
  - User dengan email yang match bisa accept; user lain tidak bisa accept token ini.
  - Decline mengubah status dan invitation tidak bisa di-accept ulang.
  - Cross-tenant: invitation token org1 tidak bisa accept oleh user dari org2 (kecuali email match — flow normal).
  - Token expired ditolak.

### 4.2 Members Overview Data-Backed (QA-OPEN-014)

#### Backend
- Action baru `GetMembersOverviewPayloadAction`:
  - Metrics: total member aktif, pending invitation, member baru 30 hari, role breakdown.
  - Items: list user dengan role, email, joined_at, status.
  - Tenant scope: active org.
- Update `WorkspacePageController` membersIndex memakai action ini.

#### Frontend
- `resources/js/Pages/Members/Index.tsx`:
  - Hapus `ModuleOverview` mock; render kartu metrics + tabel member dari payload.
  - Tambah filter client-side (search by name/email, filter by role) — pakai `useState` + `useMemo`.

#### Tests
- File: `tests/Feature/MembersOverviewPayloadTest.php`. Cases: tenant scope, metrics correct, role breakdown.

### 4.3 Remove Member (6.8)

#### Backend
- Action `RemoveOrganizationMemberAction` (owner only):
  - Reject menghapus organisasi terakhir owner-nya (sama seperti last-owner protection existing).
  - Hapus row `organization_members` (tetap bisa restore kalau soft delete diaktifkan di Phase 11; sementara hard delete OK).
- Form Request `RemoveOrganizationMemberRequest` dengan `authorize` owner only.
- Route `DELETE /organization/members/{member}` name `organization.members.destroy`.

#### Frontend
- Tabel members punya tombol "Hapus" yang membuka `ConfirmDialog` (hard confirm: ketik nama member).
- Setelah sukses, toast success.

#### Tests
- File: `tests/Feature/RemoveOrganizationMemberTest.php`.

### Phase 2 Checklist
- [x] QA-OPEN-004: Invitation form, accept/decline route, duplicate guard, role guard.
- [x] QA-OPEN-014: Members overview data-backed.
- [x] 6.8: Remove member dengan typed confirm.
- [x] Update `QA-REPORT-PROKERIN.md` untuk QA-OPEN-004 dan QA-OPEN-014.

### Verification (Phase 2)
- 2026-05-17: Phase 2 member/invitation selesai. Invite form, pending duplicate/member guard, public token preview, auth accept/decline, expired-token rejection, members overview payload, filters UI, and owner-only remove member dengan typed confirm sudah wired.
- Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationInvitationFlowTest.php tests/Feature/MembersOverviewPayloadTest.php --stop-on-failure` -> **9 passed, 75 assertions**.
- Regression smoke: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/OrganizationCreateTest.php tests/Feature/OrganizationManagementTest.php tests/Feature/OrganizationInvitationFlowTest.php tests/Feature/MembersOverviewPayloadTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> **35 passed, 502 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter gate: targeted Pint check pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **391 passed, 2144 assertions**.

---

## 5. Phase 3 — Proker Lifecycle (HIGH, paling kritis untuk Flow 1)

Cakupan: QA-OPEN-013, QA-OPEN-017, QA-OPEN-018, QA-MASTER 7.5, 7.8, F1, F6.2.

### 5.1 Proker Index Tenant-Scoped (QA-OPEN-013, QA-OPEN-017, F6.2)

#### Backend
- Action baru `GetProkerIndexPayloadAction`:
  - Tenant scope: organisasi user aktif.
  - Filter: `?status=`, `?period=`, `?search=`.
  - Return list project dengan slug, status, progress, tanggal, lead, member count.

#### Frontend
- `resources/js/Pages/Proker/Index.tsx`:
  - Hapus `workspaceMock`. Render dari payload.
  - Card item link ke `/proker/{slug}` (bukan `/proker/sample`).
  - Tambah search input + filter status (client-side untuk MVP).
  - Empty state pakai `EmptyState`.

#### Tests
- File: `tests/Feature/ProkerIndexPayloadTest.php`. Tenant scope, filter status, filter period, cross-tenant tidak bocor (owner org2 tidak melihat proker org1).
- Update `tests/Feature/Security/MultiTenantFinanceAccessTest.php` style atau buat baru `MultiTenantProkerIndexTest.php` yang assert F6.2 lulus.

### 5.2 Status Transition (7.5, QA-OPEN-018)

#### Backend
- State machine valid transitions di `App\Domain\Project\ProjectStatus`:
  - `draft → planning → proposal_review → rab_approval → active → completed`
  - `*` → `archived`
  - `revision_requested` cabang dari `proposal_review`/`rab_approval`.
- Action `TransitionProjectStatusAction` (sudah ada `ProjectStatusTransitionAction`? cek dulu; kalau ada gunakan, kalau belum lengkap perluas):
  - Validasi transisi sah. Throw `AuthorizationException` kalau tidak.
  - Role yang boleh: owner/admin/project_lead.
- Form Request `TransitionProjectStatusRequest` dengan rule `status` enum dan slug exists.
- Route: `PATCH /proker/{project}/status` name `proker.status.update`.

#### Frontend
- Di `Proker/Show.tsx` (detail), tambah dropdown/transition button. Setiap pilihan memunculkan `ConfirmDialog` ringan ("Yakin ubah status ke X?").

#### Tests
- File: `tests/Feature/ProkerStatusTransitionTest.php`.
- Cases: valid transition pass, invalid transition (e.g., `draft → completed`) ditolak, member tidak boleh trigger.

### 5.3 Progress Recompute (7.8, QA-OPEN-018, F1.2)

#### Backend
- Update `app/Actions/Project/ProjectProgressCalculationAction.php`:
  - Hitung berdasarkan ratio task `done`/total active task project.
- Hook ke `UpdateTaskStatusAction` (sudah ada): setiap kali task status berubah, panggil ulang progress action dan persist ke `projects.progress`.
- Setelah progress = 100 dan semua task done, ada flag siap pindah ke `completed` (UI minta klik manual; otomatis hanya kalau requirement jelas, untuk sementara manual).

#### Tests
- Update `tests/Feature/TaskInteractionTest.php` atau buat `tests/Feature/ProjectProgressRecomputeTest.php`:
  - Buat 4 task → tandai 2 done → progress 50.
  - Tandai semua done → progress 100.
  - Cross-tenant guard tetap.

### 5.4 Project Members Management (untuk Flow 1)

#### Backend
- Action `AssignProjectMemberAction`, `RemoveProjectMemberAction`:
  - Owner/admin/project_lead boleh assign.
  - Anggota harus `organization_members` di org yang sama.
- Routes `POST /proker/{project}/members`, `DELETE /proker/{project}/members/{member}`.

#### Frontend
- Detail proker → tab "Anggota Tim". Tabel anggota, tombol "Tambah Anggota" buka modal pilih user dari org.

#### Tests
- File: `tests/Feature/ProjectMembersManagementTest.php`.

### Phase 3 Checklist
- [x] QA-OPEN-013: Proker index dari DB.
- [x] QA-OPEN-017 / F6.2: Cross-tenant no leak proker index.
- [x] QA-OPEN-018 / 7.5: Status transition lengkap dengan guard.
- [x] QA-OPEN-018 / 7.8: Progress 100% saat semua task done.
- [x] Project members assign/remove.
- [x] `QA-REPORT-PROKERIN.md` updated untuk QA-OPEN-013/017/018.

### Verification (Phase 3)
- Targeted project lifecycle: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ProkerIndexPayloadTest.php tests/Feature/ProkerStatusTransitionTest.php tests/Feature/ProjectMembersManagementTest.php tests/Feature/ProjectDetailTest.php tests/Feature/TaskInteractionTest.php --stop-on-failure` -> `21 passed, 200 assertions`.
- Targeted proker/workspace/security smoke: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ProkerIndexPayloadTest.php tests/Feature/ProkerStatusTransitionTest.php tests/Feature/ProjectMembersManagementTest.php tests/Feature/ProjectDetailTest.php tests/Feature/TaskInteractionTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `34 passed, 529 assertions`.
- Pint targeted: pass.
- npm lint: pass.
- npm build: pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `404 passed, 2246 assertions`.
- Browser smoke: belum dijalankan di fase ini; automated route/payload/regression sudah hijau.

---

## 6. Phase 4 — Task Operasional (HIGH)

Cakupan: QA-OPEN-006, QA-OPEN-007, QA-OPEN-019, QA-MASTER 9.3, 9.4, 9.6, 9.7.

### 6.1 Task Overview Data-Backed (QA-OPEN-006)

#### Backend
- Action `GetTaskOverviewPayloadAction`:
  - Metrics: total task aktif, overdue (due < now & status ≠ done), milestone minggu ini, task selesai 7 hari terakhir.
  - Items: 5 task urgent berdasarkan due date.
- Update `WorkspacePageController::taskIndex` memakai action ini.

#### Frontend
- `resources/js/Pages/Task/Index.tsx`: ganti `ModuleOverview` ke layout custom. Tombol "Tambah Task" → buka panel quick-add dari sub-section 6.4.

#### Tests
- File: `tests/Feature/TaskOverviewPayloadTest.php`.

### 6.2 Assign PIC Form (QA-OPEN-007 / 9.3 / 9.4)

#### Backend
- Sudah ada `TaskPicAssignmentAction` dengan guard membership. Buka payload list user yang available di project untuk dropdown.
- Form Request `AssignTaskPicRequest`.
- Route `PATCH /tasks/{task}/pic` name `tasks.pic.update`.

#### Frontend
- `resources/js/Pages/Task/Assignments.tsx`:
  - Hapus rows static.
  - Render task list per project dari payload, dropdown PIC per row.
  - `useForm` per row, atau lebih simple: gunakan inertia `router.patch` dengan `preserveScroll`.

#### Tests
- File: `tests/Feature/AssignTaskPicTest.php`.
- Cases: valid assignment, non-member rejected (9.4), guard role.

### 6.3 Quick-Add Task (9.7)

#### Backend
- Action `CreateTaskAction`. Validasi: pilih project dari org, default status `backlog`.
- Form Request `StoreTaskRequest`.
- Route `POST /tasks` name `tasks.store`.

#### Frontend
- Komponen `TaskQuickAdd.tsx` di Kanban dan Index. Pakai `useForm`. Field: title, project, due_at (opsional).

#### Tests
- File: `tests/Feature/CreateTaskTest.php`.

### 6.4 Overdue Visual State (9.6)

#### Frontend
- `resources/js/Pages/Task/Kanban.tsx` dan card task lain: tambah badge merah kalau `dueAt < now` dan status ≠ done. Pakai utility helper `isOverdue(dueAt)` di `lib/dates.ts`.

#### Tests
- File: `tests/Feature/TaskOverdueBadgePayloadTest.php` (assert flag `isOverdue` di payload), atau cukup unit test untuk helper.

### Phase 4 Checklist
- [x] QA-OPEN-006: Task overview data-backed.
- [x] QA-OPEN-007 / 9.3, 9.4: Assign PIC end-to-end.
- [x] QA-OPEN-019 / 9.7: Quick-add task.
- [x] 9.6: Overdue visual state.
- [x] Update `QA-REPORT-PROKERIN.md` untuk QA-OPEN-006/007/019.

### Verification (Phase 4)
- Targeted task suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskOverviewPayloadTest.php tests/Feature/AssignTaskPicTest.php tests/Feature/CreateTaskTest.php tests/Feature/TaskOverdueBadgePayloadTest.php tests/Feature/TaskInteractionTest.php --stop-on-failure` -> `15 passed, 125 assertions`.
- Targeted task/workspace/security smoke: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskOverviewPayloadTest.php tests/Feature/AssignTaskPicTest.php tests/Feature/CreateTaskTest.php tests/Feature/TaskOverdueBadgePayloadTest.php tests/Feature/TaskInteractionTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `28 passed, 454 assertions`.
- Pint targeted: pass.
- npm lint: pass.
- npm build: pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `415 passed, 2328 assertions`.
- Browser smoke: belum dijalankan di fase ini; automated payload/mutation/regression sudah hijau.

---

## 7. Phase 5 — Finance End-to-End (HIGH)

Cakupan: QA-OPEN-008, QA-OPEN-009, QA-OPEN-016, QA-MASTER 10.1, 10.2, 10.3, 10.10, 10.11, 10.12.

### 7.1 Role-Gate Finance GET Routes (QA-OPEN-016 / 10.11)

#### Backend
- Tambah middleware atau Policy check di setiap route finance:
  - `/finance`, `/finance/budget-draft`, `/finance/realization`, `/finance/approval`.
- Cara paling rapi: bikin middleware `EnsureFinanceAccess` (di `app/Http/Middleware/`) yang cek role user di active org ada di `Roles::FINANCE_VIEWERS`. Kalau tidak → abort 403.
- Daftarkan middleware di `app/Http/Kernel.php` route middleware aliases (mis. `finance`).
- Update routes `Route::middleware(['auth', 'finance'])->prefix('finance')` (gabung dengan auth existing).
- Update Action payload finance juga: tetap derive org dari user (tidak boleh trust request), tapi tambahkan defensive guard yang throws kalau user tidak punya akses (defense in depth).

#### Tests
- Update `tests/Feature/Security/MultiTenantFinanceAccessTest.php` atau buat `tests/Feature/FinanceAccessGuardTest.php`:
  - Member biasa GET /finance → 403.
  - Treasurer GET /finance → 200 dan payload tenant.
  - Owner org2 GET /finance → tidak bocor org1.

### 7.2 Finance Overview Data-Backed (QA-OPEN-008)

#### Backend
- Action `GetFinanceOverviewPayloadAction`:
  - Metrics: total RAB approved, total realisasi approved, sisa anggaran, jumlah transaksi review.
  - Charts: monthly realization, top kategori belanja.
- Update controller method `financeIndex` memakai action.

#### Frontend
- `resources/js/Pages/Finance/Index.tsx`: ganti `ModuleOverview` ke kartu metrics + grafik bar sederhana (no chart lib mahal, pakai `Recharts` jika belum ada — atau bar manual dengan div widths).

### 7.3 Budget Draft CRUD (QA-OPEN-009 / 10.1, 10.2, 10.3)

#### Backend
- Action `CreateBudgetLineAction`, `UpdateBudgetLineAction`, `DeleteBudgetLineAction` (treasurer/owner/admin).
- Form Request masing-masing.
- Routes:
  - `POST /finance/budget-lines`
  - `PATCH /finance/budget-lines/{budgetLine}`
  - `DELETE /finance/budget-lines/{budgetLine}`
- Hapus mock `workspaceMock` di payload `GetBudgetDraftPayloadAction`. Tarik dari DB.

#### Frontend
- `resources/js/Pages/Finance/BudgetDraft.tsx`:
  - Hilangkan `workspaceMock`.
  - Tabel inline edit: setiap row punya tombol Save/Cancel/Delete.
  - "Tambah Item" buka panel inline form.
  - Live total client-side (sub total, grand total).
  - Indikator over-budget (UX-09 dipindah ke fase ini karena ringan).

#### Tests
- File: `tests/Feature/BudgetLineCrudTest.php`. Create/update/delete + role guard + cross-tenant.

### 7.4 Remaining Budget Math (10.12)

- Audit perhitungan di `CalculateBudgetSummaryAction`. Pastikan `remaining = sum(planned approved) - sum(realized approved)`. Tambah unit test eksplisit.

### Phase 5 Checklist
- [x] QA-OPEN-016 / 10.11: Finance GET role-gate.
- [x] QA-OPEN-008: Finance overview data-backed.
- [x] QA-OPEN-009 / 10.1, 10.2, 10.3: Budget line CRUD.
- [x] 10.10: RAB vs Realization summary chart.
- [x] 10.12: Remaining budget math.
- [x] Update `QA-REPORT-PROKERIN.md` untuk seluruh Phase 5.

### Verification (Phase 5)
- Partial QA-OPEN-016 targeted finance/security: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/MultiTenantFinanceAccessTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/BudgetReceiptRealizationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> `19 passed, 255 assertions`.
- Partial QA-OPEN-008 targeted finance overview: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/FinanceOverviewPayloadTest.php tests/Feature/Security/MultiTenantFinanceAccessTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/BudgetReceiptRealizationTest.php --stop-on-failure` -> `17 passed, 130 assertions`.
- QA-OPEN-009 targeted budget CRUD: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/BudgetLineCrudTest.php` -> `9 passed, 39 assertions`.
- 10.10: `Finance/Index.tsx` RAB vs Realisasi chart sekarang menampilkan planned bar (abu-abu) overlay realized bar (hijau / merah saat over-budget) dengan persentase per kategori dan legend (Planned, Realized, Over budget).
- 10.12: `CalculateBudgetSummaryAction` + `Money::subtract` clamping ke 0 sudah ditest di `tests/Unit/CalculateBudgetSummaryActionTest.php` (4 test, termasuk overspend flag).
- Pint targeted (15 files): pass.
- npm build: pass.
- Full regression after Phase 5 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> `436 passed, 2469 assertions`.

---

## 8. Phase 6 — Documents (HIGH)

Cakupan: QA-OPEN-010, QA-OPEN-011, 12.1, 12.2, 12.5, 12.7, 12.8, S4.1, S4.3.

### 8.1 Document Upload Real Form (QA-OPEN-010 / 12.1, 12.2)

#### Backend
- Cek `app/Actions/Document/ValidateDocumentUploadAction.php` (sudah ada). Pastikan dipakai untuk MIME + size.
- Action `StoreDocumentAction`:
  - Field: `file`, `folder`, `visibility`, `project_id` (opsional).
  - Simpan ke S3 (disk `s3` atau `documents`), buat row `documents`.
- Form Request `StoreDocumentRequest`:
  - `file` required, `mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png,zip`, `max:10240` (10 MB).
  - `folder` string, `visibility` enum.
- Route `POST /documents` name `documents.store`.

#### Frontend
- `resources/js/Pages/Documents/UploadCenter.tsx`:
  - Replace dropzone visual dengan input `<input type="file">`. Pakai `useForm({ ..., file: null }, { forceFormData: true })`.
  - Drop area boleh tetap dekoratif tapi handle `onDrop` masuk ke state `file`.
  - Tampilkan progress (Inertia `progress` callback).

#### Tests
- File: `tests/Feature/DocumentUploadTest.php`.
- Cases:
  - Owner upload PDF valid → row tersimpan, file ada di disk fake.
  - Upload .php / .exe → 422.
  - Upload SVG dengan payload `<script>` → 422 atau sanitized (S4.3).
  - Upload >10 MB → 422.
  - Cross-tenant: user org2 mencoba upload ke project org1 → 403.

### 8.2 Folders Data-Backed (QA-OPEN-011 / 12.8)

#### Backend
- Action `GetDocumentFolderTreePayloadAction`: aggregate `documents.folder` per organisasi.
- Update controller folders.

#### Frontend
- `resources/js/Pages/Documents/Folders.tsx`: render tree dari payload, klik folder filter list.

### 8.3 Visibility Rules (12.5, 12.7)

- Audit `DocumentVisibility` enum. Tambah test feature: owner private hanya owner+uploader, public terlihat semua org member, committee terlihat committee saja.

### Phase 6 Checklist
- [x] QA-OPEN-010 / 12.1, 12.2: Upload real, MIME guard, size guard.
- [x] QA-OPEN-011 / 12.8: Folders data-backed.
- [x] 12.5, 12.7: Visibility audit + tests.
- [x] S4.1, S4.3: PHP/SVG payload reject.
- [x] Update `QA-REPORT-PROKERIN.md`.

### Verification (Phase 6)
- 2026-05-17: Upload Center diganti dari dropzone dummy menjadi form upload nyata (`documents.store`) dengan file input/drop, `forceFormData`, progress Inertia, folder/visibility/project fields, dan S3-backed `StoreDocumentAction`.
- 2026-05-17: Folder page kini data-backed via `GetDocumentFolderTreePayloadAction`; visibility download/payload meng-cover private, restricted, committee, dan public.
- Targeted documents/payload suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/DocumentUploadTest.php tests/Feature/DocumentDownloadTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Unit/ValidateDocumentUploadActionTest.php --stop-on-failure` -> **29 passed, 268 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Browser smoke: `http://127.0.0.1:8004/documents/upload-center` renders upload form, file picker CTA, and recent uploads; `http://127.0.0.1:8004/documents/folders` renders data-backed folders plus document download links after login as `owner@prokerin.test`.
- Formatter gate: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test app/Actions/Document/StoreDocumentAction.php app/Actions/Document/ValidateDocumentUploadAction.php app/Actions/Document/CreateDocumentDownloadUrlAction.php app/Actions/Workspace/GetDocumentUploadCenterPayloadAction.php app/Actions/Workspace/GetDocumentFolderTreePayloadAction.php app/Domain/Document/DocumentVisibility.php app/Http/Controllers/DocumentController.php app/Http/Controllers/WorkspacePageController.php app/Http/Requests/StoreDocumentRequest.php routes/web.php tests/Feature/DocumentUploadTest.php` pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **436 passed, 2469 assertions**.

---

## 9. Phase 7 — Reports & LPJ (MEDIUM)

Cakupan: QA-OPEN-015, QA-OPEN-020, 13.2, 13.7, 13.8.

### 9.1 Reports Overview Data-Backed (QA-OPEN-015)

#### Backend
- Action `GetReportsOverviewPayloadAction`: metrics proposal per status, LPJ per status, export queue terbaru.
- Update controller reports.

#### Frontend
- `resources/js/Pages/Reports/Index.tsx`: replace ModuleOverview, render kartu metrics + tabel queue.

### 9.2 LPJ Checklist Toggle (QA-OPEN-020 / 13.2)

#### Backend
- Action `ToggleLpjChecklistItemAction` (sudah mungkin ada, kalau tidak buat).
- Route `PATCH /reports/lpj-checklist/items/{item}` name `reports.lpj-checklist.items.update`.

#### Frontend
- `resources/js/Pages/Reports/LpjChecklist.tsx`: replace tombol dummy → checkbox per item kirim PATCH `is_complete`.

### 9.3 LPJ Export Trigger (QA-OPEN-020 / 13.7)

#### Backend
- Action `QueueLpjExportAction`:
  - Mirip `QueueMeetingMinutesExportAction`: buat row `document_exports`, dispatch `GenerateDocumentExportJob`.
  - `ExportDocumentType::Lpj` sudah ada.
- Route `POST /reports/lpj-checklist/{project}/export` name `reports.lpj-checklist.export`.

#### Frontend
- Tombol "Export LPJ PDF" di halaman LPJ checklist. Disable kalau belum approved.

### 9.4 LPJ Mengikat Data Eksekusi (13.8)

- `GetLpjChecklistPayloadAction` menampilkan summary task done count, total budget realized, attendance count agar user bisa lihat data eksekusi yang akan masuk LPJ.

### Phase 7 Checklist
- [x] QA-OPEN-015: Reports overview data-backed.
- [x] QA-OPEN-020 / 13.2: Checklist toggle.
- [x] QA-OPEN-020 / 13.7: LPJ export trigger.
- [x] 13.8: Execution data summary di payload.
- [x] Update `QA-REPORT-PROKERIN.md`.

### Verification (Phase 7)
- 2026-05-17: `/reports` diganti dari `ModuleOverview` hardcoded menjadi payload database via `GetReportsOverviewPayloadAction` untuk proposal statuses, LPJ statuses, export queue, dan proker terkait dokumen.
- 2026-05-17: LPJ checklist item kini punya ID dan checkbox PATCH `reports.lpj-checklist.items.update`; payload menampilkan summary eksekusi task, realisasi budget, dan attendance.
- 2026-05-17: Export LPJ PDF aktif via `QueueLpjExportAction` dan `reports.lpj-checklist.export`; job `GenerateDocumentExportJob` di-queue setelah project completed.
- Targeted reports/LPJ suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ReportsPhase7Test.php tests/Feature/LpjApprovalTest.php tests/Feature/WorkspacePayloadTest.php --stop-on-failure` -> **22 passed, 227 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Browser smoke: `http://127.0.0.1:8004/reports` renders data-backed overview sections; `http://127.0.0.1:8004/reports/lpj-checklist` renders checklist, export button, and execution summary after login as `owner@prokerin.test`.
- Formatter gate: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test app/Actions/Workspace/GetReportsOverviewPayloadAction.php app/Actions/Workspace/GetLpjChecklistPayloadAction.php app/Actions/Report/ToggleLpjChecklistItemAction.php app/Actions/Report/QueueLpjExportAction.php app/Actions/DocumentExport/GenerateDocumentExportContentAction.php app/Http/Controllers/LpjChecklistItemController.php app/Http/Controllers/LpjExportController.php app/Http/Controllers/WorkspacePageController.php app/Http/Requests/UpdateLpjChecklistItemRequest.php routes/web.php tests/Feature/ReportsPhase7Test.php` pass.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **442 passed, 2518 assertions**.

---

## 10. Phase 8 — Certificate Visual Preview (LOW)

Cakupan: QA-OPEN-012, 18 review.

### 10.1 Visual Preview
- Tambah modal/iframe preview di template editor:
  - Preview render `template_html` dengan placeholder sample (`{{recipient_name}} = Contoh User`, `{{certificate_number}} = PRK-2026-...`, dll.) di iframe sandbox.
- Tambah panel referensi placeholder.

### Phase 8 Checklist
- [x] QA-OPEN-012: Visual preview rendered.
- [x] Reference variabel placeholder tercantum.

### Verification (Phase 8)
- 2026-05-17: `resources/js/Pages/Certificates/Templates.tsx` sekarang punya panel referensi placeholder dan preview visual berbasis iframe sandbox untuk inline preview + modal.
- 2026-05-17: Placeholder certificate (`{{recipient_name}}`, `{{certificate_number}}`, `{{project_name}}`, `{{organization_name}}`, `{{issued_at}}`, `{{signature_label}}`, `{{signature_name}}`, `{{verification_url}}`) diganti dengan data contoh saat preview render.
- Targeted certificate suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/DigitalCertificateTest.php tests/Unit/CertificateNumberGeneratorTest.php --stop-on-failure` -> **12 passed, 91 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Browser smoke: `http://127.0.0.1:8004/certificates/templates` render panel placeholder, iframe inline, tombol `Preview Visual`, dan modal `Preview Sertifikat` dengan sample `Contoh User` + `PRK-2026-BEMFT-0001`.

---

## 11. Phase 9 — UX Sweep (MEDIUM)

Cakupan: UX-01, UX-04, UX-06, UX-12, UX-13.

- Sweep semua `Pages/*/Index.tsx` dan ganti blank state dengan `EmptyState`.
- Konversi Inertia flash → toast (`sonner`) untuk semua mutasi sukses/gagal.
- Tambah breadcrumbs di halaman bertingkat (proker detail, certificates edit, finance approval).
- Tambah client-side filter input sederhana di list pages: proker, members, documents, certificates.
- Audit semua tombol destructive supaya pakai `ConfirmDialog`.

### Phase 9 Checklist
- [x] EmptyState di semua halaman utama.
- [x] Toast aktif untuk flash.
- [x] Breadcrumbs di halaman bertingkat.
- [x] Client-side filter di list pages.
- [x] Confirm dialog di destructive actions.

### Verification (Phase 9)
- 2026-05-17: Tambah `resources/js/Components/ui/Breadcrumb.tsx` dan pasang di `Proker/Show`, `Finance/Approval`, serta `Certificates/Templates`.
- 2026-05-17: Filter client-side ditambahkan untuk `/documents/upload-center`, `/documents/folders`, dan `/certificates`; `/proker` dan `/members` sudah punya filter dasar dari fase sebelumnya.
- 2026-05-17: EmptyState dipakai untuk certificate empty/filter miss, document empty/filter miss, dan approval queue/workflow kosong.
- 2026-05-17: Destructive confirm diganti ke `ConfirmDialog` untuk archive proker, remove project member, status transition confirmation, dan delete budget line typed phrase.
- Toast flash tetap aktif via `AuthenticatedLayout` + `showFlashToast` + `sonner` dari Phase 0.
- Targeted UX impacted suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ProjectDetailTest.php tests/Feature/ProjectMembersManagementTest.php tests/Feature/ProkerStatusTransitionTest.php tests/Feature/BudgetLineCrudTest.php tests/Feature/DocumentUploadTest.php tests/Feature/DigitalCertificateTest.php --stop-on-failure` -> **41 passed, 282 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Browser smoke: `/certificates`, `/documents/upload-center`, `/finance/approval`, `/proker/sample`, dan `/certificates/templates` render tanpa blank; filter/breadcrumb/empty-state signal terlihat; klik `Arsipkan` membuka `ConfirmDialog` typed phrase.

---

## 12. Phase 10 — Notifications (MEDIUM)

Cakupan: UX-14, 15.6, 15.7.

- Bell icon dropdown 5 notifikasi terbaru (UX-14). Pakai endpoint kecil `GET /notifications/recent` (tetap Inertia partial reload via `router.reload({ only: ['recentNotifications'] })`).
- Trigger notification:
  - Approve proposal → kirim ke submitter (15.6).
  - Request revision LPJ → kirim ke submitter (15.7).
- Per-user toggle WhatsApp opt-in (kalau belum ada UI), simpan di `users.whatsapp_number` dan flag `notifications.whatsapp_opt_in` (boolean kolom user, butuh migration kalau belum ada).

### Phase 10 Checklist
- [x] Bell dropdown rendering.
- [x] Notif submitter saat approval proposal.
- [x] Notif submitter saat LPJ revision.
- [x] Per-user opt-in WhatsApp setting.

### Verification (Phase 10)
- Tests notifikasi proposal/LPJ + browser smoke bell dropdown.
- 2026-05-17: Header bell memakai `NotificationBell` dengan dropdown 5 notifikasi terbaru, badge unread, mark single/all read, dan refresh Inertia partial `notifications` saat dropdown dibuka.
- 2026-05-17: Endpoint kecil `GET /notifications/recent` tersedia dan tenant/user-scoped ke notifikasi user aktif.
- 2026-05-17: Proposal approval dan LPJ revision mengirim notification ke project lead/submitter; WhatsApp opt-in tersedia di profile (`whatsapp_number`, `whatsapp_opt_in`) dan channel skip saat user opt-out.
- Targeted Phase 10 suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Notifications/NotificationDropdownTest.php tests/Feature/Notifications/ApprovalNotificationsTest.php tests/Feature/Notifications/WhatsAppOptInTest.php --stop-on-failure` -> **10 passed, 35 assertions**.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **466 passed, 2588 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test app/Actions/Notification/GetRecentNotificationsAction.php app/Http/Controllers/NotificationRecentController.php app/Http/Middleware/HandleInertiaRequests.php tests/Feature/Notifications/NotificationDropdownTest.php` -> **pass**.
- Formatter full: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` -> **pass**.

---

## 13. Phase 11 — Security & Hardening (HIGH)

Cakupan: TECH-03, TECH-05, TECH-08, S4.1, S4.3, plus impersonation expiry.

### 13.1 Rate Limit Sensitive Routes (TECH-03)

- Konfigurasi `RouteServiceProvider` atau `app/Providers/AppServiceProvider.php`:
  - `Login`: 5 per minute per IP+email.
  - `Forgot password`: 3 per 15 minutes per IP.
  - `Invite member`: 20 per hour per org.
  - `WhatsApp send job dispatch route`: 100 per hour per org.
  - `/internal-admin/login`: 5 per minute per IP.
- Pakai `RateLimiter::for(...)` named limiters dan attach ke route via `->middleware('throttle:<name>')`.

### 13.2 Soft Deletes Audit (TECH-05)

- Audit dan pasang `SoftDeletes` di model: `Organization`, `Project`, `Document`, `CertificateRecipient`, `SponsorVendor`, `Meeting`, `AttendanceSession`.
- Migration `add_deleted_at_to_*_tables` untuk masing-masing.
- Filament resource yang menampilkan model ini → tambah `Tables\Filters\TrashedFilter`.

### 13.3 Index Audit (TECH-08)

- Migration baru `add_query_indexes_*` per tabel sesuai daftar di QA-MASTER §29 TECH-08.
- Migration harus reversible (drop index di `down()`).

### 13.4 Impersonation Inactivity Expiry

- Middleware `EnsureImpersonationFresh`:
  - Cek session `impersonate_started_at`.
  - Kalau lebih lama dari `config('prokerin.impersonate.max_duration_hours')` → leave + redirect.
- Mount middleware di Inertia app routes.

### 13.5 File Upload SVG/PHP Hardening (S4.1, S4.3)

- Validasi: tolak `mimes:php,phtml,exe,svg`. Kalau SVG perlu, sanitize via `enshrined/svg-sanitize` (install) sebelum simpan.

### Phase 11 Checklist
- [x] Rate limit named limiters terdaftar.
- [x] Soft deletes + Filament TrashedFilter.
- [x] Indexes added.
- [x] Impersonation inactivity middleware.
- [x] SVG/PHP upload reject.

### Verification (Phase 11)
- Tests untuk rate limit (`assertStatus(429)` setelah threshold) di `tests/Feature/Security/RateLimitTest.php` (4 case: login, forgot password, certificate verify, invitation dispatch).
- Tests soft delete + restore di `tests/Feature/Security/SoftDeleteWorkspaceTest.php` (3 case: organization, project, default-scope filter).
- Tests impersonation freshness di `tests/Feature/Security/ImpersonationFreshnessTest.php` (3 case: fresh session kept, expired session leaves & redirect, missing marker leaves).
- SVG/PHP upload guard sudah covered di Phase 6 oleh `StoreDocumentRequest::mimes:pdf,doc,docx,...` (no SVG/PHP allowed) — automated test sudah ada di `tests/Feature/DocumentUploadTest.php`.
- Indexes ditambahkan via migration `2026_05_17_000007_add_query_performance_indexes.php` (notifications, attendance_qr_tokens, projects, budget_lines, documents) — guarded dengan `indexExists()` agar idempotent.
- Targeted Phase 11 suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Security/ImpersonationFreshnessTest.php tests/Feature/Security/SoftDeleteWorkspaceTest.php tests/Feature/Security/RateLimitTest.php` -> **10 passed, 23 assertions**.
- Pint targeted (14 files): pass.
- npm build: pass.
- Full regression after Phase 11: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **452 passed, 2541 assertions**.

---

## 14. Phase 12 — Observability (MEDIUM)

Cakupan: TECH-02, TECH-07.

### 14.1 Sentry
- Install `sentry/sentry-laravel` dan `@sentry/react`.
- Config di `config/sentry.php` + `resources/js/lib/sentry.ts` (init di `app.tsx`).
- Filter exception: 404, validation, AuthenticationException, AuthorizationException tidak dikirim ke Sentry.

### 14.2 Failed Jobs UI (TECH-07)
- Filament resource `FailedJobResource` (read-only, super_admin only) untuk monitor `failed_jobs` table.
- Setiap Job penting (export, certificate, WhatsApp) implement `failed(Throwable $e)` yang kirim notifikasi in-app ke user trigger.
- UI retry: tombol "Retry" di Document Exports list (memanggil endpoint baru `POST /document-exports/{export}/retry` super_admin only).

### Phase 12 Checklist
- [x] Sentry Laravel + React aktif (DSN dari env).
- [x] FailedJobResource Filament.
- [x] failed() di setiap queue job + notif user.
- [x] Retry button untuk document exports.

### Verification (Phase 12)
- Tests dispatch job yang sengaja gagal → notifikasi in-app dibuat.
- 2026-05-17: Sentry Laravel config ditambahkan dengan DSN dari env, `Integration::handles()` aktif di `bootstrap/app.php`, React init via `resources/js/lib/sentry.ts`, dan expected 401/403/404/422-style exceptions difilter.
- 2026-05-17: `FailedJobResource` tersedia di Filament untuk super_admin; queue job export/sertifikat/WhatsApp punya `failed(Throwable)` yang membuat notifikasi database via `QueueJobFailedNotification`.
- 2026-05-17: Retry document export tersedia via Filament action dan endpoint `POST /document-exports/{documentExport}/retry` khusus `super_admin`.
- Targeted Phase 12 suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ObservabilityTest.php --stop-on-failure` -> **4 passed, 12 assertions**.
- Observability + Filament smoke: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/ObservabilityTest.php tests/Feature/SuperAdmin/FilamentAccessTest.php --stop-on-failure` -> **14 passed, 27 assertions**.
- LPJ/Proposal notification regression after WhatsApp no-op fallback: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/LpjApprovalTest.php tests/Feature/ProposalApprovalTest.php --stop-on-failure` -> **20 passed, 61 assertions**.
- Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **465 passed, 2581 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test config/sentry.php bootstrap/app.php app/Actions/DocumentExport/RetryDocumentExportAction.php app/Http/Controllers/DocumentExportRetryController.php app/Filament/Resources/DocumentExports/Tables/DocumentExportsTable.php tests/Feature/ObservabilityTest.php` -> **pass**.
- Formatter full: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` -> **pass**.

---

## 15. Cross-Phase Quality Gates

Sebelum merge tiap fase:

| Gate | Command | Lulus Kalau |
|------|---------|-------------|
| Unit/Feature | `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` | 100% pass, jumlah test > baseline fase. |
| Targeted suite | `php artisan test tests/Feature/<phase>` | 100% pass. |
| Pint | `./vendor/bin/pint --test` | No diff. |
| TypeScript | `npm run build` | No error. |
| Lint | `npm run lint` | No warning baru. |
| Browser smoke | Manual per fase | Catat di Section "Verification" fase. |
| QA-REPORT update | `git diff QA-REPORT-PROKERIN.md` | Fase yang diselesaikan tercatat di Section 2. |

Kalau salah satu gate fail, **jangan merge**. Kembali ke item yang gagal, perbaiki, ulangi sebelum lanjut fase berikutnya.

---

## 16. Master Tracking Checklist (Quick View)

Centang sesuai progress:

### High Severity (blokir launch beta)
- [x] **QA-OPEN-001** Create organization form & route (Phase 1)
- [x] **QA-OPEN-002** Switcher data + action switch (Phase 1)
- [x] **QA-OPEN-003** Periods CRUD (Phase 1)
- [x] **QA-OPEN-004** Invitation form, accept/decline (Phase 2)
- [x] **QA-OPEN-007** Assign PIC end-to-end (Phase 4)
- [x] **QA-OPEN-009** Budget draft CRUD (Phase 5)
- [x] **QA-OPEN-010** Document upload form (Phase 6)
- [x] **QA-OPEN-013** Proker index data-backed (Phase 3)
- [x] **QA-OPEN-016** Finance role gate (Phase 5)
- [x] **QA-OPEN-017** F6.2 cross-tenant proker (Phase 3)
- [x] **QA-OPEN-018** Status transition + progress 100% (Phase 3)
- [x] **QA-OPEN-019** Quick-add task + overdue (Phase 4)
- [x] **QA-OPEN-020** LPJ checklist toggle + export (Phase 7)

### Medium Severity
- [x] **QA-OPEN-005** Calendar data-backed (Phase 1)
- [x] **QA-OPEN-006** Task overview data-backed (Phase 4)
- [x] **QA-OPEN-008** Finance overview data-backed (Phase 5)
- [x] **QA-OPEN-011** Folders data-backed (Phase 6)
- [x] **QA-OPEN-014** Members overview data-backed (Phase 2)
- [x] **QA-OPEN-015** Reports overview data-backed (Phase 7)

### Low / Polish
- [x] **QA-OPEN-012** Certificate visual preview (Phase 8)
- [x] **UX-01** Empty states global (Phase 9)
- [x] **UX-04** Confirm dialog destructive (Phase 9)
- [x] **UX-06** Toast system (Phase 9)
- [x] **UX-12** List filters (Phase 9)
- [x] **UX-13** Breadcrumbs (Phase 9)
- [x] **UX-14** Notification bell dropdown (Phase 10)

### Tech Hardening
- [x] **TECH-02** Sentry (Phase 12)
- [x] **TECH-03** Rate limits (Phase 11)
- [x] **TECH-05** Soft deletes (Phase 11)
- [x] **TECH-07** Failed job handling + retry UI (Phase 12)
- [x] **TECH-08** DB indexes (Phase 11)
- [x] **S4.1 / S4.3** SVG/PHP upload reject (Phase 11)

---

## 17. Aturan Update Dokumen

Setiap kali sebuah item beres:

1. Tandai checkbox di Section 16.
2. Update `QA-REPORT-PROKERIN.md`:
   - Pindahkan baris dari Section 3 (Open Findings) ke Section 2 (Bug Fixed) dengan format yang sudah ada.
   - Tambah catatan tanggal dan ID commit di Section 2 jika perlu.
3. Update `QA-MASTER-PROKERIN.md` Section 30 (Active Bug Log) bila relevan.
4. Update `features.md` Verification Log dengan hasil regression terbaru.
5. Commit dengan format `docs(qa): mark QA-OPEN-XXX fixed and update tracker`.

Dokumen ini hidup. Kalau ada bug baru ditemukan di luar QA-OPEN-001..020, tambahkan di section "Backlog Tambahan" di bawah supaya tidak hilang.

---

## 18. Backlog Tambahan (Catat Di Sini Kalau Ditemukan Bug Baru)

Format singkat:

```
### NEW-BUG-001
- Tanggal: YYYY-MM-DD
- Severity: HIGH/MEDIUM/LOW
- Modul: ...
- Reproduce: ...
- Akar masalah: ...
- Rencana fix: ...
- Fase: tunjuk fase yang relevan atau buat fase baru.
```

(belum ada entri.)

---

*Dokumen ini disusun untuk dieksekusi top-down. Jangan lompat fase tanpa menyelesaikan dependensi (mis. Phase 0 helpers wajib sebelum Phase 1 supaya empty state, confirm dialog, dan toast konsisten).*
