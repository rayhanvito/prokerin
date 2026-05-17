# POST-MVP-ROADMAP.md — Prokerin
## Rencana Pengembangan Fitur Lanjutan Yang Disetujui (Untuk AI Dev / Codex)

> **Wajib baca dulu sebelum mulai koding:** `AGENTS.md`, `features.md`, `BUG-FIX-PLAN.md`, `LANDING-CMS-PLAN.md`, `SUPER-ADMIN-V2-PLAN.md`, `QA-MASTER-PROKERIN.md`, `QA-REPORT-PROKERIN.md`.
> Dokumen ini menggantikan `pengembanganlanjutan.md` sebagai source of truth untuk modul Post-MVP yang **disetujui** untuk dibangun. Modul yang ada di `pengembanganlanjutan.md` tapi tidak ada di sini = tidak akan dibangun (di-defer atau di-drop berdasarkan analisa strategis).
> Setiap modul harus tetap patuh pada `AGENTS.md` (PHP 8.4 strict_types, controller tipis → Action, Form Request untuk validasi, Inertia bukan REST, tenant scoping wajib, tidak ada `env()` di app code, tidak ada `any` di TS, dst).

---

## 0. Aturan Kerja Eksekusi

### 0.1 Sebelum Setiap Fase
1. Tarik branch baru per modul dengan format `feat/m<nomor>-<slug>` (misal `feat/m25-rich-text-editor`).
2. Jalankan baseline:
   ```bash
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
   npm run build
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
   ```
   Test harus hijau, build harus bersih sebelum mulai.
3. Buka checklist modul di dokumen ini, kerjakan top-down.
4. **Jangan loncat tier.** Tier IMMEDIATE harus selesai sebelum mulai Tier GROWTH.

### 0.2 Selama Eksekusi
- Tulis kode mengikuti urutan: migration → enum/value object → Action → Form Request → Controller → Route → React Page/Component → Tests.
- Setiap Action class **wajib**:
  - `declare(strict_types=1);`
  - Tipe parameter dan return.
  - Tenant scoping eksplisit (`organization_id` derived from `auth()->user()` atau session active org, **bukan** dari request body).
  - Authorization check via Policy atau role gate sebelum mutasi.
- Setiap Form Request:
  - `authorize()` mengembalikan boolean berdasarkan role/membership, bukan `true` mentah.
  - `rules()` lengkap, gunakan `Rule::enum()` untuk enum domain.
- React component:
  - Tidak boleh `any`.
  - Pakai `useForm` dari `@inertiajs/react` untuk semua mutasi.
  - Pakai `cn()` dari `lib/utils.ts`.
  - Empty state wajib (gunakan `EmptyState` dari Phase 0 BUG-FIX-PLAN).
  - Komponen besar pecah ke `Partials/` co-located atau `Components/<domain>/`.

### 0.3 Sebelum Commit
- `./vendor/bin/pint` (auto-fix) → harus zero diff sesudah dijalankan ulang.
- `npm run build` (TypeScript strict) → harus pass.
- `php artisan test` → harus pass dan jumlah test naik dibanding baseline.
- Update bagian "Verification" di modul yang dikerjakan.
- Update `features.md`:
  - Pindahkan modul dari "Planned" ke "Complete" dengan status `[x]`.
  - Tambah Verification Log entry sesuai pattern existing.
- Commit format AGENTS.md §12, contoh:
  - `feat(m25): add tiptap rich text editor for proposal sections`
  - `feat(m26): add reverb websocket for real-time notifications`
  - `feat(m27): add html5-qrcode camera scanner with continuous mode`

### 0.4 Definition of Done Per Modul
1. Code merged ke branch modul.
2. Test feature/unit yang relevan pass dan jumlah assertion bertambah.
3. Browser smoke (manual) sudah dicatat di Section "Verification" modul.
4. `features.md` updated: status `[x]` + verification log + commit hash.
5. Checklist `[ ]` di dokumen ini ditandai `[x]`.

### 0.5 Modul Existing Yang Dalam Status FROZEN / MAINTENANCE
Berdasarkan analisa strategis, modul berikut **tidak boleh ditambah scope baru**:
- **M16 Certificate** — Maintenance only. Skip QA-OPEN-012 visual preview enhancement.
- **M18 Multi-Level Approval** — No further enhancement. Multi-step jadi Pro tier perk.
- **M22 Payment** — Beta / Pro-tier opt-in only. Jangan promote sebagai main feature.
- **M23 AI Assistant** — Freeze expansion. Monitor 6 bulan post-launch sebelum decide.
- **M24 Campus Dashboard** — Hibernate. Tidak ada enhancement sampai first paying campus customer signed.

Kalau ada bug critical di 5 modul ini, **fix saja**. Tapi **jangan tambah fitur baru**.

### 0.6 Dependencies Antar Modul

```
                ┌──────────────────────────────────────────┐
                │   BUG-FIX-PLAN Phase 0 (Foundation)      │  ← prerequisite semua
                │   EmptyState, ConfirmDialog, FormField,  │
                │   Roles helper, Toaster sonner           │
                └──────────────────────────────────────────┘
                                  │
        ┌─────────────────────────┼──────────────────────────┐
        ▼                         ▼                          ▼
   M27 PWA Setup            M25 Rich Text             M28 Onboarding
        │                                                     ▲
        ├──→ M44 Web Push                                     │
        │                                                     │
        └──→ M26 Real-Time ←──────── (depends on)             │
                                                              │
   BUG-FIX Phase 1-3 (org/member/proker fix) ─────────────────┘

   M30 Kepanitiaan ←── extends M02 Organization
   M31 Microsite   ←── extends M04 Project + M21 Event Registration
   M39 Surat       ←── reuses M18 approval workflow
   M40 Inventory   ←── reuses M19 Handover for transition
```

---

## 1. Ringkasan Tier

| Tier | Modul | Total Estimasi |
|------|-------|----------------|
| **IMMEDIATE** (10-12 minggu, bulan 1-3) | M27, M44, M28, M25, M26 | 6-7 minggu murni (asumsi BUG-FIX Phase 0-7 sudah jalan paralel/sebelum) |
| **GROWTH** (bulan 4-6) | M30, M31, M39, M29, M43, M40 | 8-10 minggu |
| **TOTAL** | 11 modul | ~16-18 minggu solo dev |

> **Catatan (2026-05-17):** Tier MOMENTUM (M45 My Day, M46 Sponsorship Pipeline, M47 Member Skill, M41 Activity Feed, M42 Generic Approval) **dihapus** dari roadmap aktif untuk prioritas deploy MVP. Akan di-re-evaluate pasca-deploy berdasarkan feedback user nyata.

---

## 2. Tier IMMEDIATE — Production Readiness

### 2.1 M27 · Mobile QR Camera Scanner (PWA)

**Status saat ini:** M15 backend (issue/revoke/CSV/SVG) selesai. Frontend hanya bisa paste token manual. Modul ini **menyelesaikan utang M15**.

#### Tujuan
- User akhir (anggota) bisa check-in absensi rapat/event dengan **scan QR pakai kamera HP**, tidak perlu paste token manual.
- PWA-first: Prokerin bisa di-install ke home screen seperti app native.

#### Backend
- **Tidak ada perubahan major.** Endpoint `attendance.check-in.store` sudah ada dari M15.
- Tambahan kecil: log `check_in_method = 'qr_camera'` (vs `qr_paste`) untuk telemetri di `attendance_records.check_in_method`. Update `CheckInAttendanceQrAction` agar terima parameter optional `method`.

#### Frontend — PWA Setup (prerequisite)
- File baru: `public/manifest.json`:
  ```json
  {
    "name": "Prokerin",
    "short_name": "Prokerin",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#24695c",
    "icons": [
      { "src": "/icons/icon-192.png", "sizes": "192x192", "type": "image/png" },
      { "src": "/icons/icon-512.png", "sizes": "512x512", "type": "image/png" }
    ]
  }
  ```
- File baru: `public/icons/icon-192.png` (192×192) dan `icon-512.png` (512×512). Generate dari logo Prokerin Viho.
- Service worker minimum: `public/service-worker.js` (cache shell saja, full offline tunda ke iterasi nanti).
- Register service worker di `resources/js/app.tsx`:
  ```ts
  if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/service-worker.js').catch(console.error);
  }
  ```
- Update `resources/views/app.blade.php`:
  ```html
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#24695c">
  <link rel="apple-touch-icon" href="/icons/icon-192.png">
  ```

#### Frontend — Camera Scanner
- Install: `npm install html5-qrcode`.
- File baru: `resources/js/Components/Attendance/QrCameraScanner.tsx`:
  - Props: `sessionId: number, onScanSuccess: (token: string) => void`.
  - Request camera permission (`navigator.mediaDevices.getUserMedia`).
  - Show live viewfinder pakai `Html5Qrcode` library.
  - On decode: call `router.post(route('attendance.check-in.store'), { token, method: 'qr_camera' })`.
  - Show success toast (sonner): "Hadir tercatat untuk {sessionTitle}".
  - Show error toast: "Token expired", "Sudah check-in", "Bukan member organisasi".
  - Auto-reset scanner 2 detik setelah sukses (continuous scan mode).
  - Tombol fallback "Pakai Manual Code" → switch ke text input existing.
- Update `resources/js/Pages/Attendance/Index.tsx`:
  - Tambah tombol "Scan QR" di hero section.
  - Klik buka modal full-screen dengan `QrCameraScanner`.
  - Modal close: stop camera stream (penting untuk privacy).

#### Tests
- File: `tests/Feature/QrCameraCheckInTest.php`:
  - Member valid scan → record dibuat dengan `check_in_method = qr_camera`.
  - Token expired → ditolak.
  - Cross-tenant token → ditolak.
- Manual smoke (mobile browser): buka `/attendance` di iPhone Safari, klik Scan QR, izinkan kamera, scan QR → success toast.

#### Checklist
- [x] PWA manifest + icons + service worker.
- [x] `Components/Attendance/QrCameraScanner.tsx` dengan continuous mode + fallback.
- [x] `attendance.check-in.store` terima parameter `method`, audit field di DB.
- [x] Update `Pages/Attendance/Index.tsx` dengan tombol Scan QR + modal.
- [x] Test feature `QrCameraCheckInTest`.
- [x] Manual smoke mobile/browser-equivalent check recorded. Native Android/iOS device camera permission still needs physical-device QA before launch.
- [x] Update `features.md` M15/Tier IMMEDIATE section: sub-fitur "Camera Scanner PWA" tercatat complete.

#### Verification
- `php artisan test tests/Feature/QrCameraCheckInTest.php` pass.
- Full regression hijau, jumlah test naik.
- Browser smoke mobile: install PWA → open dari home screen → scan QR → check-in sukses.
- 2026-05-17: PWA manifest updated ke PNG icons, `public/service-worker.js` added, service worker registration switched to `/service-worker.js`, camera scanner posts `method=qr_camera`, backend stores `check_in_method=qr_camera`, QR metrics count `qr` + `qr_camera`, and `QrCameraCheckInTest` added. Targeted attendance regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/QrCameraCheckInTest.php tests/Feature/QrAttendanceTest.php tests/Feature/AttendanceQrManagementTest.php --stop-on-failure` -> **20 passed, 92 assertions**. Browser smoke on `http://127.0.0.1:8001/attendance` confirmed Absensi QR page, Scan QR modal, manual fallback, `manifest.json`, and service worker push/click handlers. Native Android/iOS camera permission still requires physical-device launch QA.

---

### 2.2 M44 · Web Push Notifications (Bundle dengan M27)

**Status:** Belum ada. Bundle dengan M27 karena infra PWA sama.

#### Tujuan
- Notif sampai ke user **walau browser/PWA tidak terbuka** (OS-level notification).
- Komplemen M26 real-time (yang hanya jalan saat browser aktif).

#### Backend
- Install: `composer require laravel-notification-channels/webpush`.
- Migration: `add_push_subscriptions_table.php` (otomatis dari package, atau buat manual sesuai pattern AGENTS.md).
- Generate VAPID keys: `php artisan webpush:vapid` → simpan di `.env`:
  ```
  VAPID_PUBLIC_KEY=
  VAPID_PRIVATE_KEY=
  VAPID_SUBJECT=mailto:halo@prokerin.id
  ```
- Tambah ke `config/webpush.php` dan `.env.example`.
- Update Notification class existing (task deadline, proposal approved, LPJ revision, dll) tambah channel `WebPushChannel::class` di `via()`.
- Implement `toWebPush()` method per Notification: title, body, icon, click action URL.

#### Frontend
- File baru: `resources/js/lib/webpush.ts`:
  - `requestPermissionAndSubscribe(): Promise<void>`.
  - Subscribe ke push manager di service worker.
  - POST subscription ke `route('webpush.subscribe')`.
- File baru: `resources/js/Components/Notifications/EnableWebPushBanner.tsx`:
  - Tampil di top app pertama kali login (dismissible).
  - "Aktifkan notifikasi browser agar tidak ketinggalan persetujuan proposal" + tombol Aktifkan.
- Update `public/service-worker.js`:
  - `self.addEventListener('push', ...)` → tampilkan notification dari payload.
  - `self.addEventListener('notificationclick', ...)` → buka URL.

#### Routes
- `POST /webpush/subscribe` → store subscription.
- `DELETE /webpush/subscribe` → unsubscribe.

#### Tests
- File: `tests/Feature/WebPushSubscriptionTest.php`:
  - User authenticated subscribe → record disimpan.
  - Notification proposal approved → push subscription di-trigger (gunakan Notification fake).

#### Checklist
- [x] Package laravel-notification-channels/webpush.
- [x] VAPID keys + config.
- [x] Migration push_subscriptions.
- [x] WebPushChannel di Notification existing.
- [x] `lib/webpush.ts` + service worker push handler.
- [x] `EnableWebPushBanner` di AppLayout.
- [x] Routes subscribe/unsubscribe.
- [x] Tests subscription + notification dispatch.

#### Verification
- Browser smoke: aktifkan permission → trigger proposal approval → notif OS muncul walau tab tidak aktif.
- 2026-05-17: WebPush package installed, config/migration added, `User` now supports push subscriptions, subscribe/unsubscribe routes added, Inertia shares WebPush public key/subscription state, AppLayout shows `EnableWebPushBanner`, service worker handles push + notification click, and task/proposal/LPJ/failed-job notifications can include WebPush when VAPID keys are configured. Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/WebPushSubscriptionTest.php tests/Feature/Notifications/ApprovalNotificationsTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> **8 passed, 150 assertions**. Gates: `npm run lint` pass; `npm run build` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` pass; full `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **504 passed, 2725 assertions**. Local DB migrated through `2026_05_17_000011_create_push_subscriptions_table`. Browser OS-notification smoke not run in this environment.
- 2026-05-17 hardening: Browser smoke confirmed `/manifest.json` and `/service-worker.js` are reachable and service worker contains push + notification click listeners. OS notification permission/delivery still requires physical browser/PWA launch QA.

---

### 2.3 M28 · Onboarding Wizard

**Status:** Belum ada. **Depends on BUG-FIX Phase 1-5 selesai dulu** (form create org, period, invite member, proker template, RAB sudah wired).

#### Tujuan
- Owner organisasi baru landing → muncul wizard 5 step yang membimbing setup awal.
- Activation rate (org bikin proker pertama dalam 24 jam) booster.

#### Backend
- Migration `add_onboarding_to_organizations.php`:
  ```
  $table->timestamp('onboarding_completed_at')->nullable()->after('plan_tier');
  $table->unsignedTinyInteger('onboarding_step')->default(1)->after('onboarding_completed_at');
  $table->boolean('onboarding_skipped')->default(false)->after('onboarding_step');
  ```
- Action `app/Actions/Onboarding/GetOnboardingStatusAction.php`:
  - Return `{ shouldShow: bool, currentStep: int, completedSteps: array }`.
  - Trigger condition:
    - User authenticated AND user is `organization_owner` AND `onboarding_completed_at IS NULL` AND `onboarding_skipped = false`.
- Action `CompleteOnboardingStepAction`:
  - Input: `actorUserId, organizationId, step` (1-5).
  - Mark step complete di DB. Step 5 → `onboarding_completed_at = now()`.
- Action `SkipOnboardingAction`:
  - Set `onboarding_skipped = true`, `onboarding_completed_at = now()`.
- Update `HandleInertiaRequests::share()`:
  - Tambah `onboarding` key dengan output `GetOnboardingStatusAction`.
- Routes:
  - `POST /onboarding/steps/{step}/complete` → `OnboardingController@completeStep`.
  - `POST /onboarding/skip` → `OnboardingController@skip`.

#### Frontend
- File baru: `resources/js/Components/Onboarding/OnboardingWizard.tsx`:
  - Modal full-screen overlay.
  - Step indicator (1/5, 2/5, dst).
  - Tombol Prev/Next, Skip.
- Step components co-located:
  - `Components/Onboarding/Step1Period.tsx` → reuse form dari BUG-FIX Phase 1 Periods CRUD.
  - `Components/Onboarding/Step2InviteMembers.tsx` → reuse form invitation BUG-FIX Phase 2.
  - `Components/Onboarding/Step3CreateProker.tsx` → integrate dengan template picker M05.
  - `Components/Onboarding/Step4SetupRab.tsx` → quick-edit budget lines (sudah ada CRUD dari BUG-FIX Phase 5).
  - `Components/Onboarding/Step5Preview.tsx` → animated preview dashboard + tombol "Selesai".
- Setiap step kalau sudah ada data (period, member, proker, RAB) → auto-tandai complete dan skip.
- Update `Layouts/AuthenticatedLayout.tsx`: render `OnboardingWizard` kalau `props.onboarding.shouldShow === true`.

#### Tests
- File: `tests/Feature/OnboardingWizardTest.php`:
  - Owner baru tanpa data → `shouldShow: true`.
  - Setelah complete step 5 → `onboarding_completed_at` set, `shouldShow: false`.
  - Skip → `shouldShow: false` dan tidak muncul lagi.
  - Member non-owner → tidak muncul wizard.
  - Auto-detect existing data: kalau owner sudah punya 1 period sebelum mount, step 1 ditandai complete.

#### Checklist
- [x] Migration onboarding columns.
- [x] `GetOnboardingStatusAction`, `CompleteOnboardingStepAction`, `SkipOnboardingAction`.
- [x] `HandleInertiaRequests` share onboarding state.
- [x] Routes complete + skip.
- [x] `OnboardingWizard` modal + 5 step components.
- [x] Auto-detect existing data per step.
- [x] Test feature wizard flow.

#### Verification
- Register org baru → login → wizard muncul.
- Lengkapi 5 step → wizard menutup, tidak muncul lagi setelah refresh.
- Skip → wizard menutup permanently.
- 2026-05-17: Existing onboarding upgraded to M28 step tracking. Added `onboarding_step` and `onboarding_skipped`, step-complete and skip actions/routes, richer shared onboarding state, modal step navigation, per-step completion, permanent skip, and auto-detect tests. Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Onboarding/OnboardingWizardTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> **12 passed, 223 assertions**. Formatter gate pass for touched PHP files. Frontend type gate `npm run lint` pass after preserving the local Vitest setup with a compatible plugin cast.
- Final gate: `npm run lint` pass; `npm run build` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **508 passed, 2763 assertions**.
- 2026-05-17 hardening: Final Tier IMMEDIATE full suite remains green at **516 passed, 2783 assertions** after M25/M26 integration.

---

### 2.4 M25 · Rich Text Editor (Tiptap)

**Status:** Belum ada. Replace plain `<textarea>` di Proposal (M08) dan LPJ (M10).

#### Tujuan
- Proposal dan LPJ punya formatting (bold, heading, list, table, blockquote).
- Output PDF/DOCX professional.

#### Backend

##### Schema
- Migration `change_proposal_and_lpj_sections_body_to_longtext.php`:
  - `proposal_drafts.sections` JSON column (sudah ada — extend untuk simpan body sebagai Tiptap JSON).
  - `lpj_checklist_items` body TEXT → tetap (LPJ checklist item title/description simple).
  - **Catatan**: kalau ada `proposal_sections` atau `lpj_sections` table terpisah, ubah `body` ke LONGTEXT dan store Tiptap JSON.
- Audit struktur existing dulu sebelum migrasi.

##### Actions
- File baru: `app/Actions/RichText/SanitizeRichTextAction.php`:
  - Input: array Tiptap JSON.
  - Strip disallowed nodes/marks: `<script>`, `<iframe>`, `onclick`, `onerror`, dll.
  - Return sanitized JSON.
- File baru: `app/Actions/RichText/RenderTiptapHtmlAction.php`:
  - Input: Tiptap JSON.
  - Output: HTML safe (untuk PDF/DOCX rendering).
  - Bisa pakai `ueberdosis/tiptap-php` (server-side renderer) atau implement minimal sendiri.
- Update `UpdateProposalDraftAction`: panggil `SanitizeRichTextAction` sebelum simpan.
- Update `GenerateProposalPdfJob`: gunakan `RenderTiptapHtmlAction` → input ke Browsershot.
- Update `GenerateProposalDocxJob`: parse Tiptap JSON → PHPWord elements (heading via `addTitle`, paragraph via `addText`, list via `addListItem`, table via `addTable`).

##### Form Request
- Update `UpdateProposalDraftSectionsRequest`: terima array Tiptap JSON, validate structure dasar (`type`, `content`).

#### Frontend
- Install:
  ```bash
  npm install @tiptap/react @tiptap/starter-kit @tiptap/extension-table @tiptap/extension-table-row @tiptap/extension-table-cell @tiptap/extension-table-header @tiptap/extension-character-count @tiptap/extension-placeholder
  ```
- File baru: `resources/js/Components/Editor/RichTextEditor.tsx`:
  - Props: `value: TiptapJson, onChange: (json: TiptapJson) => void, placeholder?: string, maxChars?: number, readOnly?: boolean`.
  - Render `EditorContent` Tiptap.
- File baru: `resources/js/Components/Editor/EditorToolbar.tsx`:
  - Bold, Italic, Underline, Strikethrough, H1, H2, H3, BulletList, OrderedList, Blockquote, Table, HorizontalRule.
  - Active state highlight.
- File baru: `resources/js/Components/Editor/RichTextRenderer.tsx`:
  - Read-only renderer untuk display di approval queue, history view.
- Update `resources/js/Pages/Reports/ProposalEditor.tsx`:
  - Replace `<textarea>` dengan `<RichTextEditor>`.
  - Save handler: `useForm` patch dengan body Tiptap JSON.
- Update `resources/js/Pages/Reports/LpjChecklist.tsx`: kalau LPJ punya section editor, sama treatment.

#### Tests
- File: `tests/Unit/RichText/SanitizeRichTextActionTest.php`:
  - Input dengan `<script>` → stripped.
  - Input dengan `onclick` attribute → stripped.
  - Allowed marks (bold, italic) → preserved.
- File: `tests/Unit/RichText/RenderTiptapHtmlActionTest.php`:
  - Heading → `<h1>`/`<h2>`.
  - List → `<ul>`/`<ol>`.
  - Table → `<table>` valid HTML.
- File: `tests/Feature/ProposalRichTextSaveTest.php`:
  - Save proposal section dengan body Tiptap JSON → retrieve → match.
  - Locked proposal cannot be edited (existing test still pass).

#### Checklist
- [x] Audit schema existing, migration kalau perlu LONGTEXT.
- [x] `SanitizeRichTextAction` + `RenderTiptapHtmlAction`.
- [x] Update `UpdateProposalDraftAction` pakai sanitizer.
- [x] Update PDF/DOCX export jobs pakai renderer.
- [x] Install Tiptap packages.
- [x] `RichTextEditor`, `EditorToolbar`, `RichTextRenderer` components.
- [x] Replace textarea di Proposal Editor. LPJ current UI tidak punya section body editor yang setara, jadi tidak ada textarea LPJ yang perlu diganti pada iterasi ini.
- [x] Tests sanitize + render + save.

#### Verification
- Save proposal dengan heading + list + table → reload → tampilan sama.
- Trigger PDF export → file generated dengan formatting benar (Browsershot output).
- DOCX export → buka di Word → formatting preserve.
- 2026-05-17: Proposal editor migrated from textarea to Tiptap (`RichTextEditor`, `EditorToolbar`, `RichTextRenderer`) with heading/list/table/blockquote/horizontal rule controls. Backend now accepts legacy string bodies and Tiptap JSON bodies, sanitizes allowed nodes/marks via `SanitizeRichTextAction`, and renders rich text in proposal PDF/Docx export through `RenderTiptapHtmlAction`. Existing proposal string saves are backward-compatible and converted to Tiptap JSON. Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/RichText/SanitizeRichTextActionTest.php tests/Unit/RichText/RenderTiptapHtmlActionTest.php tests/Feature/Proposal/ProposalRichTextSaveTest.php tests/Feature/ProposalApprovalTest.php --stop-on-failure` -> **18 passed, 57 assertions**. Final Tier IMMEDIATE gates: `npm run lint` pass; `npm run build` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` pass; full `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **516 passed, 2783 assertions**. Manual browser/PDF/DOCX visual smoke remains launch-QA.

---

### 2.5 M26 · Real-Time Notifications (Laravel Reverb)

**Status:** Belum ada. **Pivot dari Pusher/Soketi (spec lama) ke Reverb (Laravel official).**

#### Tujuan
- Bell badge update real-time tanpa refresh.
- User dapat notif instant saat proposal approved, LPJ revision requested, task assigned, dst.

#### Backend
- Install:
  ```bash
  composer require laravel/reverb
  PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan reverb:install
  ```
  Reverb auto-config `.env` dan `config/broadcasting.php`.
- Update `.env.example`:
  ```
  BROADCAST_CONNECTION=reverb
  REVERB_APP_ID=
  REVERB_APP_KEY=
  REVERB_APP_SECRET=
  REVERB_HOST="0.0.0.0"
  REVERB_PORT=8080
  REVERB_SCHEME=http
  VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
  VITE_REVERB_HOST="${REVERB_HOST}"
  VITE_REVERB_PORT="${REVERB_PORT}"
  VITE_REVERB_SCHEME="${REVERB_SCHEME}"
  ```
- File baru: `app/Events/UserNotificationCreated.php` (broadcastable):
  ```
  implements ShouldBroadcast
  channel: PrivateChannel('App.Models.User.'.$this->userId)
  payload: { id, type, title, body, read_at, created_at }
  ```
- Update setiap Notification class existing (task deadline, proposal approved, LPJ revision, meeting alert, dll):
  - Implement `ShouldBroadcast` (Laravel built-in di Notification).
  - Override `broadcastOn(): Channel` dan `broadcastWith(): array`.
- Action baru: `app/Actions/Notification/MarkNotificationReadAction.php`:
  - Input: `userId, notificationId`.
  - Set `read_at = now()` di `notifications` table.
  - Cross-user guard: user A tidak bisa mark user B's notif.
- Action: `MarkAllNotificationsReadAction`.
- Routes:
  - `POST /notifications/{notification}/read` → mark single read.
  - `POST /notifications/read-all` → mark all current user's notifications read.

#### Frontend
- Install:
  ```bash
  npm install laravel-echo pusher-js
  ```
- File baru: `resources/js/lib/echo.ts`:
  ```ts
  import Echo from 'laravel-echo';
  import Pusher from 'pusher-js';
  window.Pusher = Pusher;
  export const echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: import.meta.env.VITE_REVERB_PORT,
      forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
      enabledTransports: ['ws', 'wss'],
  });
  ```
- File baru: `resources/js/hooks/useNotifications.ts`:
  - State: `notifications: Notification[], unreadCount: number`.
  - On mount: subscribe `echo.private('App.Models.User.${userId}').listen('UserNotificationCreated', cb)`.
  - On unmount: unsubscribe.
  - Method: `markAsRead(id)`, `markAllRead()`, `loadInitial()`.
- Update `resources/js/Components/Viho/VihoHeader.tsx`:
  - Bell icon dengan badge dari `useNotifications().unreadCount`.
  - Dropdown 5 notif terbaru.
  - Tombol "Mark all read" + link "Lihat semua".

#### Tests
- File: `tests/Feature/NotificationBroadcastTest.php`:
  - Trigger proposal approval → event di-broadcast ke channel user submitter.
  - Cross-user mark read ditolak.
  - Mark all read → semua notif current user `read_at` populated.
- Use `Event::fake()` atau `Broadcast::fake()` untuk assert event dispatched.

#### Checklist
- [x] Reverb installed + config.
- [x] `UserNotificationCreated` event broadcastable.
- [x] Existing database notifications broadcast via `NotificationSent` listener, so current Notification classes are covered without duplicating payload logic per class.
- [x] `MarkNotificationReadAction` + `MarkAllNotificationsReadAction`.
- [x] Routes mark read.
- [x] `lib/echo.ts` + `hooks/useNotifications.ts`.
- [x] VihoHeader bell dropdown.
- [x] Tests broadcast + mark read.
- [x] Reverb worker command documented in README.

#### Verification
- Buka 2 browser (owner di tab 1, member di tab 2).
- Member submit proposal di tab 2.
- Tab 1 (owner): bell badge update **tanpa refresh**, dropdown menampilkan notif baru.
- `php artisan reverb:start` jalan di background sebagai worker.
- 2026-05-17: Laravel Reverb installed with `config/broadcasting.php`, `config/reverb.php`, and `routes/channels.php`; `.env.example` now exposes Reverb/Vite variables. Added `UserNotificationCreated` broadcast event and `BroadcastDatabaseNotification` listener so any Laravel database notification emits realtime payload to `private-App.Models.User.{id}`. Added Echo/Reverb client (`resources/js/lib/echo.ts`), `useNotifications()` hook, and wired `NotificationBell` to local realtime state with recent endpoint fallback. README documents `php artisan reverb:start`. Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Notifications/NotificationBroadcastTest.php tests/Feature/Notifications/NotificationDropdownTest.php tests/Feature/Notifications/ApprovalNotificationsTest.php tests/Feature/Security/AuthenticationBypassTest.php --stop-on-failure` -> **13 passed, 174 assertions**. Gates: `npm run lint` pass; `npm run build` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` pass; full `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **516 passed, 2783 assertions**. Browser smoke confirmed dashboard notification bell dropdown + recent endpoint fallback; `php artisan reverb:start --host=127.0.0.1 --port=8081` starts cleanly. Two-browser live websocket smoke remains launch QA.



---

## 3. Tier GROWTH — Market Expansion & Differentiation

### 3.1 M30 · Kepanitiaan Mode (Ad-Hoc Committee)

**Status:** ✅ Selesai 2026-05-17. Strategic move untuk market expansion 5-10x dari permanent ormawa ke event-based committee.

#### Tujuan
- Panitia event ad-hoc (Ospek, Dies Natalis, Lomba, Pekan Olahraga) bisa pakai Prokerin tanpa overhead setup full organization.
- Lifecycle pendek (3-6 bulan), auto-archive 90 hari setelah event date.
- Hierarchy simplified: Ketua Panitia, Sekretaris, Bendahara, Divisi, Anggota.

#### Backend

##### Schema
- Migration `add_kepanitiaan_mode_to_organizations.php`:
  ```
  $table->enum('mode', ['organization', 'kepanitiaan'])->default('organization')->after('plan_tier');
  $table->date('event_date')->nullable()->after('mode');
  $table->timestamp('auto_archive_at')->nullable()->after('event_date');
  $table->index('mode');
  $table->index('auto_archive_at');
  ```

##### Domain Enum
- `app/Domain/Organization/Enums/OrganizationMode.php`:
  ```
  enum OrganizationMode: string {
      case Organization = 'organization';
      case Kepanitiaan = 'kepanitiaan';
  }
  ```

##### Actions
- `CreateKepanitiaanAction`:
  - Input: `actorUserId, name, eventName, eventDate, description`.
  - Slug auto-generate.
  - Set `mode = kepanitiaan`, `event_date = $eventDate`, `auto_archive_at = $eventDate + 90 days`.
  - Tidak buat `organization_periods` (tidak applicable).
  - Attach actor sebagai `organization_owner`.
- Update `CreateOrganizationAction` (dari BUG-FIX Phase 1) → terima `mode` parameter.
- `AutoArchiveKepanitiaanJob` (scheduled daily):
  - Query org `mode = kepanitiaan AND auto_archive_at <= now() AND status != 'archived'`.
  - Set `status = archived`.
- `GetKepanitiaanDashboardPayloadAction`:
  - Hero: countdown to event_date (kalau future), atau "Event sudah lewat" (kalau past).
  - Metrics: task completion %, RAB realization, attendance count, document count.
  - Tidak ada widget approval queue, finance, member analytics yang terlalu detail.

##### Feature Gate
- Helper `App\Support\OrganizationModeGate`:
  - `canUsePeriods(): bool` → false untuk kepanitiaan.
  - `canUseHandover(): bool` → false untuk kepanitiaan.
  - `canUseRoleMatrix(): bool` → false untuk kepanitiaan.
- Pakai di sidebar menu, dashboard variant resolver, dan controller guards.

##### Form Request
- `StoreKepanitiaanRequest`:
  - `name` required string, `event_name` required string, `event_date` required date in future, `description` nullable.

##### Controllers
- `OrganizationController::storeKepanitiaan(StoreKepanitiaanRequest, CreateKepanitiaanAction)`.
- Update `WorkspacePageController::dashboard` → kalau active org `mode = kepanitiaan`, render `KepanitiaanDashboard/Index` bukan `Dashboard/Index`.

##### Routes
- `POST /organization/kepanitiaan` → `organization.kepanitiaan.store`.
- (existing dashboard route handle dispatching).

##### Scheduled
- Update `routes/console.php` atau `app/Console/Kernel.php`:
  ```
  $schedule->job(new AutoArchiveKepanitiaanJob)->daily();
  ```

#### Frontend
- Update `resources/js/Pages/Auth/Register.tsx` atau halaman organization create (BUG-FIX Phase 1):
  - Step 1: pilih mode "Organisasi Permanen" / "Kepanitiaan Event".
  - Conditional fields: kalau kepanitiaan, tampilkan event_name, event_date, description.
- File baru: `resources/js/Pages/KepanitiaanDashboard/Index.tsx`:
  - Hero countdown ke event_date (`react-countdown` atau native).
  - Metrics card simplified.
  - Task list focused.
- Update `Components/Viho/VihoSidebar.tsx`:
  - Hide menu Periods, Handover, Members Role Matrix kalau `mode = kepanitiaan` (dari `app.activeOrganization.mode` di shared props).
- Update `HandleInertiaRequests` share `app.activeOrganization.mode`.

#### Tests
- File: `tests/Feature/KepanitiaanModeTest.php`:
  - Create kepanitiaan → mode = kepanitiaan, no period created, auto_archive_at = event_date + 90.
  - Owner kepanitiaan tidak bisa akses route handover (403).
  - Sidebar payload tidak include menu yang di-hide.
  - Auto-archive job archives correct orgs, skip active.
  - Dashboard payload route render `KepanitiaanDashboard/Index`.

#### Checklist
- [x] Migration mode + event_date + auto_archive_at.
- [x] Enum `OrganizationMode`.
- [x] `CreateKepanitiaanAction` + `AutoArchiveKepanitiaanJob`.
- [x] `OrganizationModeGate` helper + integration ke sidebar/dashboard.
- [x] Form Request + Controller + Route.
- [x] Organization setup flow dengan form kepanitiaan.
- [x] `KepanitiaanDashboard/Index` page.
- [x] Sidebar conditional hide.
- [x] Tests.

#### Verification
- Buat workspace kepanitiaan dari Organization Setup → dashboard pakai variant kepanitiaan.
- 2026-05-17 — `php artisan test --filter=KepanitiaanModeTest`: 4 passed / 28 assertions.
- 2026-05-17 — `php artisan test`: 520 passed / 2811 assertions.
- 2026-05-17 — `npm run lint`, `npm run build`, dan `./vendor/bin/pint --test` pass.
- Sidebar tidak menampilkan Periods/Handover.
- Run scheduler dummy → job archive org kepanitiaan yang event_date 91 hari lalu.

---

### 3.2 M31 · Public Proker Microsite

**Status:** ✅ Selesai 2026-05-17. Growth lever utama via organic share di sosial media.

#### Tujuan
- Setiap proker dapat URL publik shareable: `prokerin.id/e/{org-slug}/{proker-slug}`.
- Berfungsi sebagai promo page + entry point ke M21 Event Registration.
- "Powered by Prokerin" footer = backlink + brand awareness.

#### Backend

##### Schema
- Migration `create_proker_microsites_table.php`:
  ```
  id, project_id (fk unique), is_published (bool indexed), banner_image_path (nullable),
  description_md (text nullable), location_text (string nullable), location_maps_url (string nullable),
  contact_name (string nullable), contact_whatsapp (string nullable), contact_email (string nullable),
  show_countdown (bool default true), show_committee (bool default true), show_gallery (bool default true),
  meta_title (string nullable), meta_description (string nullable),
  published_at (timestamp nullable), updated_by_user_id (fk users), timestamps
  ```
- Migration `create_proker_microsite_gallery_table.php`:
  ```
  id, microsite_id (fk cascade), image_path, caption (nullable), sort_order (int default 0), timestamps
  ```

##### Actions
- `UpdateMicrositeSettingsAction` (owner/admin/secretary).
- `PublishMicrositeAction`:
  - Validasi required fields: title, date, description.
  - Set `is_published = true`, `published_at = now()`.
- `UnpublishMicrositeAction`.
- `GetPublicMicrositePayloadAction`:
  - Public, no auth.
  - Return microsite data + project info + (optional) registration availability.
  - Cache 5 menit untuk reduce DB load.
- `UploadMicrositeAssetAction` (banner, gallery photo).
- `ReorderGalleryAction`.

##### Form Request
- `UpdateMicrositeSettingsRequest`, `UploadMicrositeAssetRequest`, `PublishMicrositeRequest`.

##### Controllers & Routes
- Public:
  - `GET /e/{orgSlug}/{prokerSlug}` → `MicrositeController@show` (no auth).
- Authenticated (owner/admin/secretary):
  - `GET /proker/{project}/microsite` → settings page.
  - `PATCH /proker/{project}/microsite` → update settings.
  - `POST /proker/{project}/microsite/banner` → upload banner.
  - `POST /proker/{project}/microsite/gallery` → upload gallery item.
  - `DELETE /proker/{project}/microsite/gallery/{item}` → remove item.
  - `PATCH /proker/{project}/microsite/gallery/reorder`.
  - `POST /proker/{project}/microsite/publish`.
  - `POST /proker/{project}/microsite/unpublish`.

#### Frontend
- File baru: `resources/js/Pages/Microsite/Show.tsx` (public, no AppLayout):
  - Custom layout tanpa sidebar, branded.
  - Hero: banner image + title + countdown.
  - Section: description (markdown rendered), location with map link, contact, gallery grid.
  - CTA: "Daftar Event" → kalau M21 enabled, link ke registration form.
  - Footer: "Powered by Prokerin" + link ke landing.
  - Open Graph + Twitter Card meta tags via `<Head>`.
- File baru: `resources/js/Pages/Microsite/Settings.tsx` (authenticated):
  - Form edit semua field.
  - Toggle visibility per section.
  - Banner upload + preview.
  - Gallery management (drag-reorder, upload, delete).
  - Tombol Publish/Unpublish dengan ConfirmDialog.
  - Live preview link (open new tab).
- File baru: `resources/js/Components/Microsite/CountdownTimer.tsx` — countdown ke event date.
- File baru: `resources/js/Components/Microsite/GalleryGrid.tsx` — responsive masonry.

#### Tests
- File: `tests/Feature/MicrositeTest.php`:
  - Unpublished microsite → 404 untuk unauthenticated.
  - Published microsite → 200 untuk public, return correct data.
  - Cross-org: org2 tidak bisa edit microsite org1 (403).
  - Open Graph meta tags present di response HTML.
  - Required field validation saat publish.

#### Checklist
- [x] Migrations 2 tabel.
- [x] Actions + Form Requests + Controllers + Routes.
- [x] Public route + authenticated routes.
- [x] `Microsite/Show.tsx` public + `Microsite/Settings.tsx` authenticated.
- [x] Gallery upload + reorder.
- [x] Open Graph + Twitter Card meta.
- [x] Cache layer untuk public payload.
- [x] Tests.

#### Verification
- Publish microsite → buka URL public di incognito → render benar dengan OG image.
- Share URL ke WhatsApp → preview muncul dengan banner + title.
- Lighthouse SEO score >85 di public microsite.
- 2026-05-17 — `create_proker_microsite_tables` added, public `/e/{orgSlug}/{prokerSlug}` route added, authenticated `/proker/{project}/microsite` settings flow added, publish/unpublish and banner/gallery upload/reorder/delete implemented, public payload cached 5 minutes, registration CTA reuses M21, and Proker detail links to Microsite settings. Targeted: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MicrositeTest.php --stop-on-failure` -> **6 passed, 45 assertions**. Gates: `npm run lint` pass; `npm run build` pass; `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` pass; full `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **526 passed, 2856 assertions**. WhatsApp/social preview and Lighthouse SEO remain launch-QA browser checks.

---

### 3.3 M39 · Surat Menyurat Generator (Birokrasi Kampus)

**Status:** ✅ Implemented 2026-05-17. **Killer feature spesifik untuk ormawa Indonesia.**

#### Tujuan
- Auto-generate surat resmi (peminjaman ruang, perizinan kegiatan, surat tugas, dll) dengan kop surat institusional dan numbering otomatis.
- Eliminate 1-2 hari kerja manual per proker.

#### Backend

##### Schema
- Migration `create_letter_templates_table.php`:
  ```
  id, organization_id (fk cascade), name (string), letter_type (enum), template_html (longtext),
  numbering_pattern (string e.g. 'B.{seq}/BEM-FT/{roman_month}/{year}'),
  signatory_user_id (fk users nullable), is_active (bool default true), timestamps, soft delete
  ```
- Migration `create_letters_table.php`:
  ```
  id, organization_id (fk cascade), template_id (fk letter_templates),
  project_id (fk projects nullable), letter_number (string indexed),
  letter_type (string indexed), subject (string), body_data (json — placeholder values),
  recipient_name (string), recipient_organization (string nullable),
  rendered_pdf_path (string nullable), status (enum: draft, submitted, signed, sent),
  drafted_by_user_id (fk users), signed_by_user_id (fk users nullable), signed_at (timestamp nullable),
  timestamps, soft delete
  index (organization_id, letter_type, status)
  ```
- Migration `create_letter_number_sequences_table.php`:
  ```
  id, organization_id (fk cascade), year (int), month (int), sequence (int default 0),
  unique (organization_id, year, month)
  ```

##### Domain
- Enum `app/Domain/Letter/LetterType.php`:
  ```
  case RoomReservation = 'room_reservation';
  case ActivityPermit = 'activity_permit';
  case CommitteeAssignment = 'committee_assignment';
  case ParticipationCertificate = 'participation_certificate';
  case GuestInvitation = 'guest_invitation';
  case SponsorshipRequest = 'sponsorship_request';
  case Custom = 'custom';
  ```
- Enum `app/Domain/Letter/LetterStatus.php`: Draft, Submitted, Signed, Sent.

##### Actions
- `CreateLetterTemplateAction` (owner/admin):
  - Input: `name, letter_type, template_html, numbering_pattern, signatory_user_id`.
  - Validate template HTML untuk allowed placeholder.
- `UpdateLetterTemplateAction`.
- `GenerateLetterNumberAction`:
  - Input: `organizationId, letterType, year, month`.
  - Atomic increment di `letter_number_sequences`.
  - Render numbering_pattern dengan `{seq}, {roman_month}, {year}, {type_code}`.
  - Return string number.
- `DraftLetterAction` (owner/admin/secretary):
  - Input: `templateId, projectId?, recipient_name, recipient_organization, body_data`.
  - Auto-fill body_data dari project (kalau ada): `{project_name, event_date, location, contact_person}`.
  - Generate letter_number via `GenerateLetterNumberAction`.
  - Save status = Draft.
- `SubmitLetterForSigningAction`:
  - Trigger M18 multi-level approval workflow (atau single-step kalau Pro tier tidak aktif).
  - Notify signatory.
- `SignLetterAction`:
  - Hanya signatory_user_id yang bisa sign.
  - Generate PDF via `GenerateLetterPdfAction`.
  - Save `rendered_pdf_path`.
  - Set status = Signed, `signed_at = now()`.
- `GenerateLetterPdfAction`:
  - Render template_html dengan body_data placeholders.
  - Pakai DomPDF (existing) atau Browsershot (untuk layout kompleks).
  - Upload ke S3 `letters/{org_id}/{letter_id}.pdf`.
- `BulkIssueParticipationCertificatesAction`:
  - Input: `projectId, recipientUserIds[]` (panitia/peserta).
  - Per user: generate letter dengan template Participation Certificate.
  - Reuse infrastructure dari M16 Certificate (queue per user).
- `MarkLetterSentAction`: status = Sent.

##### Form Requests
- `StoreLetterTemplateRequest`, `UpdateLetterTemplateRequest`, `DraftLetterRequest`, `SignLetterRequest`, `BulkIssueLettersRequest`.

##### Controllers & Routes
- `LetterTemplateController` (CRUD letter templates).
- `LetterController` (draft, submit, sign, send, bulk).
- Routes:
  - `GET /letters/templates` → list templates.
  - `POST /letters/templates`, `PATCH /letters/templates/{template}`, `DELETE /letters/templates/{template}`.
  - `GET /letters` → list letters.
  - `GET /letters/create` → draft form.
  - `POST /letters` → save draft.
  - `GET /letters/{letter}` → detail.
  - `PATCH /letters/{letter}` → update draft.
  - `POST /letters/{letter}/submit` → submit for signing.
  - `POST /letters/{letter}/sign` → sign + generate PDF.
  - `POST /letters/{letter}/mark-sent`.
  - `POST /letters/bulk-issue-participation/{project}` → bulk for panitia.
  - `GET /letters/{letter}/download` → signed download URL.

#### Frontend
- File baru: `resources/js/Pages/Letters/Index.tsx` — list letters dengan filter status, type, project, date range.
- File baru: `resources/js/Pages/Letters/Templates.tsx` — list + edit templates. Reuse RichTextEditor (M25) untuk template_html.
- File baru: `resources/js/Pages/Letters/Create.tsx`:
  - Step 1: pilih template.
  - Step 2: pilih project (auto-fill body_data).
  - Step 3: edit recipient, custom field placeholder.
  - Step 4: preview live (render template + placeholder values di iframe).
  - Tombol "Save Draft" / "Submit for Signing".
- File baru: `resources/js/Pages/Letters/Show.tsx`:
  - Detail surat, status, sign button (kalau current user adalah signatory).
  - Approval workflow timeline (reuse component dari M18).
  - Tombol Download PDF (kalau status >= Signed).
- File baru: `resources/js/Components/Letters/PlaceholderHelpPanel.tsx` — referensi placeholder yang tersedia per template type.

#### Sidebar
- Tambah menu "Surat Menyurat" di sidebar role secretary, admin, owner.

#### Seed Templates
- `LetterTemplateSeeder` dengan 6 template default (room_reservation, activity_permit, committee_assignment, participation_certificate, guest_invitation, sponsorship_request) dengan placeholder standar:
  - `{{org_name}}, {{org_address}}, {{letter_number}}, {{letter_date}}, {{letter_subject}}, {{recipient_name}}, {{recipient_organization}}, {{project_name}}, {{event_date}}, {{event_location}}, {{contact_person}}, {{signatory_name}}, {{signatory_role}}`.

#### Tests
- File: `tests/Feature/LetterGeneratorTest.php`:
  - Owner create template → record disimpan.
  - Secretary draft letter → letter_number unique per month.
  - Bulk issue participation: 10 panitia → 10 letter dibuat.
  - Signatory sign → PDF generated, status Signed.
  - Cross-org: letter org1 tidak bisa di-edit/sign oleh user org2.
  - Number format mengikuti pattern correct (Roman month, sequence increment).
- File: `tests/Unit/LetterNumberPatternTest.php`:
  - Pattern `B.{seq}/BEM-FT/{roman_month}/{year}` dengan input → output benar.

#### Checklist
- [x] 3 migrations (templates, letters, sequences).
- [x] Enum LetterType + LetterStatus.
- [x] Action create/update template + draft/submit/sign/send/bulk + generate number/PDF.
- [x] Form Requests.
- [x] Controllers + Routes.
- [x] React pages (Index, Templates, Create wizard, Show, Templates editor).
- [x] Sidebar menu.
- [x] Seeder 6 template default.
- [x] Tests.

#### Verification
- Owner buat template "Surat Permohonan Peminjaman Ruang" → secretary draft surat dengan project Seminar Karier → preview render benar → submit → owner sign → PDF di-generate → download → format profesional.
- 2026-05-17 automated gates:
  - `php artisan test tests/Unit/LetterNumberPatternTest.php tests/Feature/LetterGeneratorTest.php --stop-on-failure` → 7 passed / 41 assertions.
  - `php artisan test` → 533 passed / 2897 assertions.
  - `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass.
- Bulk issue participation certificate untuk 5 panitia → 5 surat keterangan dibuat.

**Why this is a killer feature:** ormawa Indonesia tidak punya alternatif tools untuk auto-generate surat dengan kop institusional + numbering otomatis. Demo "12 surat dalam 3 menit" akan jadi viral.

---

### 3.4 M29 · Global Search (Cmd+K)

**Status:** ✅ Implemented 2026-05-17. **Pivot dari MeiliSearch (spec lama) ke Scout database driver dulu.**

#### Tujuan
- Cmd+K / Ctrl+K modal yang search lintas modul (Project, Task, Document, Meeting, Member).
- Tenant-scoped, respect document visibility.
- UX modern (Linear/Notion style).

#### Backend
- Install: `composer require laravel/scout`.
- Config: `SCOUT_DRIVER=database` di `.env`.
- Tambah `Searchable` trait + `toSearchableArray()` di:
  - `App\Models\Project`: `name, description, status, period_name`.
  - `App\Models\Task` (kalau Eloquent): `title, description, project_name, assignee_name`.
  - `App\Models\Document`: `name, folder, visibility, project_name`.
  - `App\Models\Meeting`: `title, agenda, project_name`.
  - `App\Models\User` (org members): `name, email`.
- `toSearchableArray()` **wajib include** `organization_id` agar bisa filter scope.
- Action `App\Actions\Search\GlobalSearchAction`:
  - Input: `actorUserId, query`.
  - Determine `organization_id` dari active org user.
  - Run Scout search per model dengan `where('organization_id', $orgId)`.
  - Document filter by visibility (skip private kalau bukan owner uploader).
  - Member filter: hanya member di org user.
  - Limit 5 hasil per kategori.
  - Return grouped: `{ projects: [], tasks: [], documents: [], meetings: [], members: [] }`.
- Route: `GET /search?q={query}` → return JSON (Axios call dari modal, **bukan** Inertia full page).
- Authentication via web middleware (CSRF token via cookie).

#### Frontend
- File baru: `resources/js/Components/Search/GlobalSearchBar.tsx`:
  - Trigger: Cmd+K (Mac) / Ctrl+K (Win/Linux), atau klik icon search di header.
  - Modal overlay dengan input + result groups.
  - Debounced search 300ms.
  - Loading state.
  - Keyboard navigation (arrow up/down, enter).
- File baru: `resources/js/Components/Search/SearchResultGroup.tsx`:
  - Group header (Proker, Tugas, Dokumen, Rapat, Anggota) dengan icon.
  - Result item dengan icon + title + subtitle.
  - Click → Inertia navigate ke detail.
- File baru: `resources/js/hooks/useGlobalSearch.ts`:
  - Manage state, debounce, axios call.
- Mount `GlobalSearchBar` di `AuthenticatedLayout`.
- Recent searches simpan di `localStorage` (max 5).

#### Tests
- File: `tests/Feature/GlobalSearchTest.php`:
  - User search "ospek" → return projects matching, tenant-scoped.
  - Org A user tidak melihat hasil dari Org B.
  - Private document tidak muncul untuk user yang bukan uploader.
  - Member result terbatas pada org user.
  - Result limit 5 per kategori.

#### Checklist
- [x] Install Laravel Scout.
- [x] Searchable trait + toSearchableArray di 5 model.
- [x] `GlobalSearchAction`.
- [x] Route `/search`.
- [x] `GlobalSearchBar`, `SearchResultGroup`, `useGlobalSearch` hook.
- [x] Mount di AuthenticatedLayout.
- [x] Tests tenant scope + visibility.

#### Verification
- Press Cmd+K → modal muncul → ketik "ospek" → result grouped → arrow + Enter → navigate ke detail.
- Login org2 → search keyword yang ada di org1 → 0 result.
- 2026-05-17 automated gates:
  - `php artisan test tests/Feature/GlobalSearchTest.php --stop-on-failure` → 5 passed / 11 assertions.
  - `php artisan test` → 538 passed / 2908 assertions.
  - `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass.

---

### 3.5 M43 · Calendar Sync (.ics Feed)

**Status:** ✅ Implemented 2026-05-17. **Cheap win** — utility harian.

#### Tujuan
- User subscribe `.ics` feed Prokerin di Google Calendar / Apple Calendar / Outlook.
- Meeting, deadline task, deadline proker auto-muncul di calendar pribadi.

#### Backend
- Install (optional): `composer require spatie/icalendar-generator`. Atau implement manual ICS string builder (format simple).
- Schema: tambah kolom `users.calendar_sync_token` (string unique nullable, 64 char hash).
- Action `RegenerateCalendarSyncTokenAction`:
  - Generate token, simpan di user.
  - Return new feed URL.
- Action `BuildIcsFeedAction`:
  - Input: `calendarSyncToken`.
  - Resolve user dari token. Kalau invalid → return empty calendar.
  - Aggregate events 90 hari ke depan dan 30 hari ke belakang:
    - Meetings (organisasi user) dengan `starts_at`/`ends_at`.
    - Project deadline (`projects.ends_at`) yang user terlibat (project_member atau lead).
    - Task `due_at` yang assigned ke user.
  - Build ICS string (atau pakai library).
  - Return content type `text/calendar`.
- Route: `GET /calendar/{token}.ics` → public no auth (token validation).

#### Frontend
- Update `resources/js/Pages/Profile/Edit.tsx`:
  - Section "Calendar Sync".
  - Tampilkan feed URL kalau ada token.
  - Tombol "Generate URL" / "Regenerate URL" (regen invalidate URL lama).
  - Instruksi: cara subscribe di Google Calendar / Apple Calendar.

#### Tests
- File: `tests/Feature/CalendarIcsFeedTest.php`:
  - User generate token → URL endpoint return ICS valid.
  - Token invalid → return empty calendar (200 dengan VCALENDAR kosong, bukan 404 supaya client tidak unsubscribe).
  - User regen token → token lama invalid.
  - Cross-tenant: feed user A hanya berisi event org A.

#### Checklist
- [x] Migration `users.calendar_sync_token`.
- [x] `RegenerateCalendarSyncTokenAction` + `BuildIcsFeedAction`.
- [x] Route public `/calendar/{token}.ics`.
- [x] UI di Profile/Edit.
- [x] Tests.

#### Verification
- Generate token → copy URL → subscribe di Google Calendar → events muncul → tambah meeting di Prokerin → sync 1 jam (Google fetch interval).
- 2026-05-17 automated gates:
  - `php artisan test tests/Feature/CalendarIcsFeedTest.php --stop-on-failure` → 5 passed / 35 assertions.
  - `php artisan test` → 543 passed / 2943 assertions.
  - `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass.

---

### 3.6 M40 · Inventory & Asset Management

**Status:** Belum ada. Pain point unik ormawa Indonesia (banner, sound system, kostum, kamera).

#### Tujuan
- Track inventaris organisasi: status, lokasi, kondisi, foto, QR per item.
- Loan/return tracking: siapa pinjam, kapan, untuk proker apa.
- Integrasi dengan M19 Handover (snapshot inventory saat transition).

#### Backend

##### Schema
- Migration `create_inventory_items_table.php`:
  ```
  id, organization_id (fk cascade), name (string), category (string indexed),
  description (text nullable), photo_path (nullable), location (string nullable),
  condition (enum: excellent, good, needs_repair, broken), status (enum: available, loaned, lost, archived),
  qr_token (string unique), purchased_at (date nullable), purchase_amount (decimal nullable),
  timestamps, soft delete
  ```
- Migration `create_inventory_loans_table.php`:
  ```
  id, item_id (fk cascade), borrower_user_id (fk users), project_id (fk projects nullable),
  loaned_at (timestamp), expected_return_at (timestamp), returned_at (timestamp nullable),
  return_condition (enum: same, damaged, lost) nullable, notes (text nullable),
  approved_by_user_id (fk users nullable), timestamps
  index (item_id, returned_at)
  index (borrower_user_id, returned_at)
  ```

##### Domain Enums
- `InventoryCondition`, `InventoryStatus`, `LoanReturnCondition`.

##### Actions
- `CreateInventoryItemAction` (owner/admin/secretary):
  - Generate QR token (`Str::random(20)`).
  - Save item.
- `UpdateInventoryItemAction`.
- `ArchiveInventoryItemAction`.
- `RequestInventoryLoanAction` (any member):
  - Input: `itemId, projectId?, expected_return_at, notes`.
  - Status item → loaned (kalau auto-approve), atau pending approval kalau strict.
  - Buat row `inventory_loans`.
  - Notify owner/admin.
- `ApproveInventoryLoanAction` (owner/admin/secretary).
- `ReturnInventoryLoanAction`:
  - Input: `loanId, return_condition, notes`.
  - Set `returned_at = now()`, `return_condition`, item status → available (kalau condition = same).
  - Kalau damaged/lost → item condition update, status update kalau lost.
- `CheckOverdueLoansJob` (scheduled hourly): notify borrower + owner kalau overdue.

##### Form Requests
- `StoreInventoryItemRequest`, `UpdateInventoryItemRequest`, `RequestLoanRequest`, `ReturnLoanRequest`.

##### Controllers & Routes
- `InventoryController` (CRUD).
- `InventoryLoanController` (request, approve, return).
- `InventoryQrController` (lookup by QR token).
- Routes:
  - `GET /inventory` — list items.
  - `POST /inventory`, `PATCH /inventory/{item}`, `DELETE /inventory/{item}` (soft).
  - `GET /inventory/{item}` — detail + loan history.
  - `POST /inventory/{item}/loans` — request loan.
  - `PATCH /inventory/loans/{loan}/approve`.
  - `PATCH /inventory/loans/{loan}/return`.
  - `GET /inventory/qr/{token}` — public-ish (require auth, scan via mobile).

#### Frontend
- File baru: `resources/js/Pages/Inventory/Index.tsx` — list dengan filter category, status, condition.
- File baru: `resources/js/Pages/Inventory/Show.tsx` — detail item + loan history + tombol Pinjam.
- File baru: `resources/js/Pages/Inventory/Create.tsx`, `Edit.tsx`.
- File baru: `resources/js/Pages/Inventory/QrLookup.tsx` — saat user scan QR item via M27 scanner, redirect ke sini.
- File baru: `resources/js/Components/Inventory/LoanRequestModal.tsx`, `LoanReturnModal.tsx`.
- Sidebar: tambah menu "Inventaris" di group Operasional.

#### Integrasi M19 Handover
- Saat handover package created, `InitiateHandoverPackageAction` extended:
  - Snapshot inventory di `handover_packages.snapshot`: `inventory_total, available, loaned, broken, lost`.
- Handover checklist auto-include item: "Verifikasi inventaris sebanyak X item".

#### Integrasi M27 QR Scanner
- Scanner M27 deteksi pattern QR token inventory (vs attendance) → redirect ke `/inventory/qr/{token}`.

#### Tests
- File: `tests/Feature/InventoryManagementTest.php`:
  - Owner create item → QR token unique.
  - Member request loan → row dibuat status pending atau loaned.
  - Owner approve loan → status loaned.
  - Return loan with condition=same → item available.
  - Return with damaged → condition update.
  - Cross-org: tidak bisa pinjam item org lain.
  - Overdue job: notify pas overdue.

#### Checklist
- [ ] 2 migrations + enum.
- [ ] CRUD Action + Loan Action + Return Action.
- [ ] Form Request.
- [ ] Controllers + Routes.
- [ ] React pages + components.
- [ ] Sidebar menu.
- [ ] Integrasi M19 (snapshot) + M27 (QR scan redirect).
- [ ] Tests.

#### Verification
- Buat item Banner BEM, generate QR, print, tempel, scan via mobile → detail muncul.
- Pinjam → return dengan kondisi damaged → kondisi item update.
- Inisiasi handover → snapshot inventory ada di package.
---

## 5. Cross-Module Integration Map

Modul yang baru dibangun harus integrate dengan yang lama:

| Modul Baru | Integrate ke |
|---|---|
| M27 Camera Scanner | M15 Attendance check-in flow + M40 inventory QR lookup |
| M44 Web Push | M26 real-time + M12 notifications |
| M28 Onboarding | M02 Org create + M03 Member invite + M04 Proker create + M05 Template + M07 RAB |
| M25 Rich Text | M08 Proposal + M10 LPJ + M39 Letter template |
| M26 Real-Time | M12 notifications + M18 approval queue |
| M30 Kepanitiaan | M02 Org + M28.5 Dashboard variant + M19 hide handover |
| M31 Microsite | M04 Project + M21 Event Registration entry point |
| M39 Letter | M18 approval workflow + M16 cert pattern (bulk issue) + M28.5 sidebar |
| M29 Search | All searchable models |
| M43 Calendar | M06 Task + M14 Meeting + M04 Project |
| M40 Inventory | M19 Handover snapshot + M27 QR scanner + M18 approval (loan request) |

---

## 6. Master Tracking Checklist

### Tier IMMEDIATE (bulan 1-3)
- [x] **M27** · Camera QR Scanner + PWA setup
- [x] **M44** · Web Push (bundled M27)
- [x] **M28** · Onboarding Wizard
- [x] **M25** · Rich Text Editor (Tiptap)
- [x] **M26** · Real-Time Notifications (Reverb)

### Tier GROWTH (bulan 4-6)
- [x] **M30** · Kepanitiaan Mode
- [x] **M31** · Public Proker Microsite
- [x] **M39** · Surat Menyurat Generator (killer feature)
- [x] **M29** · Global Search (Cmd+K)
- [x] **M43** · Calendar Sync (.ics)
- [ ] **M40** · Inventory & Asset Management

### Frozen / Maintenance Mode (jangan tambah scope)
- [ ] M16 Certificate — skip QA-OPEN-012, no further enhancement
- [ ] M18 Multi-Level Approval — frozen, jadi Pro tier perk
- [ ] M22 Payment — beta opt-in only
- [ ] M23 AI Assistant — freeze expansion, monitor 6 bulan
- [ ] M24 Campus Dashboard — hibernate, evaluate ulang setelah signed customer

### Modul Yang Di-Drop / Defer Indefinite
- M32 Template Marketplace — defer until >100 active org
- M33 Prokerin Academy — absorbed into M28 + landing blog
- M34 Smart Deadline Prediction — descope to rule-based heuristic, embed di M11
- M35b Public Vendor Discovery — defer
- M36 Discord/WhatsApp Bot — drop, ROI tidak jelas
- M37b Org Health Analytics Dashboard — defer until data scale
- M38 Prokerin Pay — drop, regulatory risk

---

## 7. Cross-Phase Quality Gates

Sebelum merge tiap modul:

| Gate | Command | Lulus Kalau |
|---|---|---|
| Unit/Feature | `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` | 100% pass, jumlah test naik. |
| Targeted | `php artisan test tests/Feature/<module>` | 100% pass. |
| Pint | `./vendor/bin/pint --test` | No diff. |
| TypeScript | `npm run build` | No error. |
| Lint | `npm run lint` | No warning baru. |
| Browser smoke | Manual per modul | Catat di "Verification". |
| features.md update | `git diff features.md` | Modul yang diselesaikan tercatat. |

Kalau salah satu gate fail, **jangan merge.** Kembali ke item yang gagal.

---

## 8. Aturan Update Dokumen

Setiap kali sebuah modul beres:

1. Tandai checkbox di Section 6 (Master Tracking Checklist).
2. Update `features.md`:
   - Tambah section modul baru di posisi yang tepat (Post-MVP Wave 2/3 sesuai tier).
   - Status `[x]` Complete + What Is Built + Test Coverage + Verification.
   - Verification Log entry pakai pattern existing.
3. Update `QA-MASTER-PROKERIN.md`:
   - Tambah section test cases manual untuk modul baru.
4. Commit format AGENTS.md §12.
5. Kalau modul terkait BUG-FIX-PLAN, update juga `BUG-FIX-PLAN.md`.

---

## 9. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Reverb worker down silent | Real-time notif berhenti tanpa user tahu | Health check di Phase 2 SA02 + Sentry alert kalau worker mati. |
| PWA install rate rendah | M27/M44 underutilized | Banner prompt install di mobile (M44 bundle dengan EnableWebPushBanner). |
| Tiptap JSON migration bermasalah | Proposal existing rusak | Migration backward compatible: kalau body bukan JSON valid, treat as plain text dan wrap. Selalu test di staging sebelum production. |
| Surat Generator template salah pakai data sensitif user | Privacy leak | Whitelist placeholder yang allowed, tidak boleh akses kolom user.password atau token. |
| Search index tidak updated saat data berubah | User search keyword baru tidak ketemu | Pakai Scout `searchable()` listener Eloquent (default behavior). Plus daily reindex job kalau perlu. |
| Inventory QR token bocor | Orang outside org bisa scan & lihat detail | Token random 20 char, lookup tetap require auth + cross-tenant guard. |
| Web Push notification spam | User unsubscribe semua | User toggle per channel di profile settings. Default opt-in dengan banner dismissible. |
| Kepanitiaan free tier abuse | User bikin org baru tiap proker | Limit 1 kepanitiaan aktif per user di free tier. Pro tier unlimited. |
| Generic approval workflow di Meeting Minutes membuat publish flow rumit | UX confused | Default config: meeting minutes auto-publish. Approval mode opt-in per org. |
| Activity feed spam (semua mutation jadi activity) | Feed tidak bisa dibaca | Whitelist verb yang qualified jadi activity (jangan log setiap save draft). |

---

## 10. Backlog Tambahan

Format untuk catat fitur baru yang muncul di tengah jalan:

```
### NEW-FEATURE-XX
- Tanggal: YYYY-MM-DD
- Tier: IMMEDIATE / GROWTH
- Modul: ...
- Justifikasi: ...
- Estimasi: ...
- Dependencies: ...
- Acceptance criteria: ...
```

(belum ada entri.)

---

## 11. Hubungan dengan Plan Lain

| Plan Document | Hubungan |
|---|---|
| **BUG-FIX-PLAN.md** | Phase 0 (Foundation Helpers) prerequisite semua modul roadmap. Phase 1-7 (org/member/proker/task/finance fix) prerequisite M28 Onboarding. Phase 11-12 (security + observability) jalan paralel. |
| **LANDING-CMS-PLAN.md** (LCMS01) | Independent, bisa jalan paralel. Cocok di slot bulan 5-6 (sela GROWTH). |
| **SUPER-ADMIN-V2-PLAN.md** (SA02 + SA03) | Independent. SA02 Phase 5 (operational resources) butuh modul existing. SA03 security wajib pre-launch publik. |
| **QA-REPORT-PROKERIN.md** | Source of truth bug yang harus difix sebelum modul roadmap baru bisa pakai infra (mis. Phase 1 BUG-FIX selesaikan org create yang dibutuhkan M28 Onboarding). |

---

## 12. Eksekusi Recommended Order (Solo Dev, ~5-6 Bulan untuk Roadmap Aktif)

```
Week 1-6:    BUG-FIX Phase 0-7 (foundation + dummy buttons)
Week 7-8:    M27 + M44 (camera scanner + web push, bundled)
Week 9-10:   M28 (onboarding wizard)
Week 11-12:  M25 (rich text editor)
Week 13-14:  M26 (real-time notifications)
Week 15:     Beta launch internal (5-10 ormawa pilot)
Week 16-17:  Iterate based on pilot feedback
Week 18-19:  M30 (kepanitiaan mode)
Week 20-21:  M31 (public microsite)
Week 22-24:  M39 (surat generator — killer feature)
Week 25:     M29 (global search)
Week 26:     M43 (calendar sync .ics — quick win)
Week 27-28:  M40 (inventory)
Week 29-30:  BUG-FIX Phase 11 + SA03 (security hardening)
Week 31:     BUG-FIX Phase 12 (observability)
Week 32-33:  SA02 Super Admin V2 Phase 1-4
Week 34-35:  LCMS01 Landing CMS (atau Landing Polish — sesuai keputusan terbaru)
Week 36:     Final pre-launch QA
Week 37:     Public launch 🚀
```

> Tier MOMENTUM (M45/M46/M47/M41/M42) dihapus 2026-05-17. Akan di-re-evaluate pasca-deploy berdasarkan feedback user nyata.

---

*Dokumen ini menggantikan section "Wave 2 — Growth" dan "Wave 3" di `pengembanganlanjutan.md`. Modul yang tidak ada di sini = tidak akan dibangun. Dokumen ini hidup — kalau ada bug baru atau feature request masuk, catat di Section 10 (Backlog Tambahan) dan re-evaluate prioritasnya.*
