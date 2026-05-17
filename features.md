# features.md — Prokerin

> **Single source of truth** untuk status fitur Prokerin. Semua AI/agent yang melanjutkan pekerjaan **wajib baca file ini lebih dulu** sebelum memutuskan apa yang akan dibangun, di-test, atau ditandai selesai (sesuai aturan `AGENTS.md` §13).
>
> Update file ini setiap kali: status modul berubah, hasil test/build berubah, ada migration baru, atau modul Post-MVP bergerak ke depan.

---

## Ringkasan Status

- **Versi:** v0.1.0 (MVP + Post-MVP Wave 1 progress)
- **Verifikasi terakhir:** 2026-05-17
- **Test suite:** **543 passed, 2943 assertions** (`php artisan test`, durasi ~35 detik)
- **Build frontend:** `npm run build` ✅ pass
- **PHP code style:** `./vendor/bin/pint --test` ✅ pass
- **TypeScript:** `npm run lint` (`tsc --noEmit`) ✅ pass

| Kategori | Modul Total | Selesai | Partial | Pending |
|---|---:|---:|---:|---:|
| MVP (M01–M16) | 16 | 15 | 1 (M16) | 0 |
| Post-MVP Wave 1 — Tier IMMEDIATE | 5 | 5 | 0 | 0 |
| Post-MVP Tier GROWTH | 6 | 5 | 0 | 1 |
| Modul existing FROZEN/Maintenance | 5 | 5 | 0 | 0 |
| **Total tracked** | **32** | **30** | **1** | **1** |

> **Tier MOMENTUM dihapus dari roadmap aktif (2026-05-17).** Modul M45 My Day, M46 Sponsorship Pipeline, M47 Member Skill, M41 Activity Feed, dan M42 Generic Approval ditunda untuk prioritas deploy MVP. Akan di-re-evaluate pasca-deploy berdasarkan feedback user nyata.

---

## Verifikasi Terakhir (2026-05-17)

| Gate | Komando | Hasil |
|---|---|---|
| Test suite | `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` | 543 passed / 2943 assertions / 35.45s |
| PHP style | `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` | ✅ pass |
| Frontend build | `npm run build` | ✅ pass |
| TypeScript | `npm run lint` (`tsc --noEmit`) | ✅ pass |
| Vitest harness | `npx vitest --run` | ✅ harness ready (no test files yet) |

Migration terakhir di-apply: `2026_05_17_000016_add_calendar_sync_token_to_users_table.php`.

---

## MVP — M01 sampai M16

Semua modul MVP sudah punya kode, route, UI, dan backend integration. M16 ditandai partial karena visual preview enhancement belum 100% (FROZEN per POST-MVP-ROADMAP §0.5 — maintenance only).

| ID | Modul | Domain folder | Migration | Status |
|---|---|---|---|---|
| M01 | Auth & Account (Login, Register, Google OAuth, email verification) | `app/Domain/{-}` (Breeze + custom Auth Actions) | `users` + `add_google_oauth_columns_to_users_table` | ✅ |
| M02 | Organization (setup, periode, logo, calendar, switcher) | `Organization` | `create_prokerin_workspace_tables`, `add_internal_notes_to_organizations_table`, `add_description_to_organizations_table` | ✅ |
| M03 | Member & Role (invite, role matrix, permission guard) | `Membership` | `create_permission_tables` | ✅ |
| M04 | Proker / Project (buat, edit, status flow, archive) | `Project` | `create_prokerin_workspace_tables` | ✅ |
| M05 | Template Proker (one-click generate proker dari template) | `Project` (TemplateGeneration controller) | included in workspace tables | ✅ |
| M06 | Task & Timeline (Kanban, calendar, PIC assignment) | `Task` | included in workspace tables | ✅ |
| M07 | RAB & Finance (budget, realisasi, approval queue) | `Finance` | included in workspace tables | ✅ |
| M08 | Proposal Generator (auto-fill, edit, approval, PDF/DOCX export) | `Proposal` | included in workspace tables | ✅ |
| M09 | Document Management (upload, folder, access control, S3) | `Document`, `DocumentExport` | included in workspace tables | ✅ |
| M10 | LPJ Generator (checklist, readiness, approval, export) | `Report` | included in workspace tables | ✅ |
| M11 | Dashboard (metrics, proker prioritas, focus mingguan) | `Dashboard`, `Workspace` | — | ✅ |
| M12 | Notifikasi (email + database channel, deadline reminder) | `Notification` | `create_notifications_table` | ✅ |
| M13 | Admin Panel — Filament internal | `Admin`, `SuperAdmin` | — | ✅ |
| M14 | Rapat & Notulen (agenda, attendee, keputusan, action items) | `Meeting` | `create_meeting_minute_tables` | ✅ |
| M15 | Absensi QR (issue/revoke token, check-in, manual fallback, anti-duplikat, CSV/SVG) | `Attendance` | `create_attendance_tables` | ✅ |
| M16 | Sertifikat Digital (issue, verifikasi publik, PDF) | `Certificate` | `create_certificate_tables` | 〜 Partial — FROZEN, maintenance only (visual preview enhancement di-defer) |

---

## Post-MVP Wave 1 — Tier IMMEDIATE

Source spec: `POST-MVP-ROADMAP.md` §2. Tier ini selesai dalam window 10–12 minggu dari kickoff Post-MVP.

| ID | Modul | Status | Verifikasi |
|---|---|---|---|
| **M27** | Mobile QR Camera Scanner (PWA + html5-qrcode) | ✅ | 2026-05-17 — `tests/Feature/QrCameraCheckInTest.php` + suite QR attendance: 20 passed / 92 assertions. Browser smoke confirmed `/attendance`, Scan QR modal, manual fallback, manifest, and service worker. Native Chrome Android/Safari iOS physical-device smoke tetap launch-QA karena environment ini tidak menyediakan kamera HP fisik. |
| **M44** | Web Push Notifications (laravel-notification-channels/webpush) | ✅ | 2026-05-17 — VAPID keys, migration `push_subscriptions`, `EnableWebPushBanner` di AppLayout, service worker push handler. Targeted: WebPush+Notifications+Auth tests 8 passed / 150 assertions. Browser smoke confirmed manifest + service worker push/click handlers; OS-notification permission/delivery remains launch-QA. |
| **M28** | Onboarding Wizard (5 step: period → invite → proker → RAB → preview) | ✅ | 2026-05-17 — migration onboarding columns + step tracking, actions complete/skip, modal step navigation, auto-detect existing data per step. Targeted: 12 passed / 223 assertions. Final Tier IMMEDIATE full suite: 516 passed / 2783 assertions. |
| **M25** | Rich Text Editor (Tiptap) — Proposal & LPJ | ✅ | 2026-05-17 — `RichTextEditor`, `EditorToolbar`, `RichTextRenderer` di `resources/js/Components/Editor/`. Backend `app/Actions/RichText/{SanitizeRichTextAction,RenderTiptapHtmlAction}` + integrasi ke `UpdateProposalDraftAction` + document export renderer. Backward-compatible dengan string body legacy. Targeted: 18 passed / 57 assertions. Final full suite after Tier IMMEDIATE: 516 passed / 2783 assertions. Manual PDF/DOCX visual smoke remains launch-QA. |
| **M26** | Real-Time Notifications (Laravel Reverb broadcasting) | ✅ | 2026-05-17 — Reverb installed (`config/broadcasting.php`, `config/reverb.php`, `routes/channels.php`), `.env.example` has Reverb/Vite variables, database notifications broadcast through `UserNotificationCreated` + `BroadcastDatabaseNotification`, and `NotificationBell` uses Echo-backed `useNotifications()` with recent endpoint fallback. Targeted notifications/security suite: 13 passed / 174 assertions. Browser smoke confirmed bell dropdown; `php artisan reverb:start --host=127.0.0.1 --port=8081` starts cleanly. Full suite: 516 passed / 2783 assertions. Two-browser live websocket smoke remains launch-QA. |

---

## Post-MVP Tier GROWTH (bulan 4-6)

M30, M31, M39, M29, dan M43 selesai; sisa modul belum dikerjakan. Source spec: `POST-MVP-ROADMAP.md` §3. Estimasi 8–10 minggu solo dev untuk seluruh tier.

| ID | Modul | Status | Catatan |
|---|---|---|---|
| M30 | Kepanitiaan Mode (ad-hoc committee, lifecycle 3-6 bulan, auto-archive 90 hari) | ✅ | 2026-05-17 — migration `mode + event_date + auto_archive_at`, enum `OrganizationMode`, `OrganizationModeGate`, `CreateKepanitiaanAction`, daily `AutoArchiveKepanitiaanJob`, controller guards, sidebar filtering, setup form, and `KepanitiaanDashboard/Index`. Targeted: `KepanitiaanModeTest` 4 passed / 28 assertions. Full suite: 520 passed / 2811 assertions. |
| M31 | Public Proker Microsite (halaman publik per proker, OG meta, gallery) | ✅ | 2026-05-17 — migration `proker_microsites` + `proker_microsite_gallery`, public route `/e/{orgSlug}/{prokerSlug}`, authenticated settings page, publish/unpublish, banner/gallery upload, gallery reorder/delete, cache layer, registration CTA integration, and Proker detail link. Targeted: `MicrositeTest` 6 passed / 45 assertions. Full suite: 526 passed / 2856 assertions. `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass. WhatsApp/social OG preview remains launch-QA. |
| M39 | Surat Menyurat Generator (template, sequencing nomor, sign, send, bulk) | ✅ | 2026-05-17 — migration `create_letter_generator_tables`, enum `LetterType` + `LetterStatus`, CRUD template, draft/submit/sign/send/bulk actions, DomPDF generation to `letters/{org_id}/{letter_id}.pdf`, Inertia pages `Letters/{Index,Templates,Create,Show}`, sidebar menu, and 6 default templates. Targeted: `LetterNumberPatternTest` + `LetterGeneratorTest` 7 passed / 41 assertions. Full suite: 533 passed / 2897 assertions. `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass. Visual PDF polish/download smoke remains launch-QA. |
| M29 | Global Search (Cmd+K) — Laravel Scout | ✅ | 2026-05-17 — Laravel Scout installed with database driver config, searchable surfaces for Project/Task/Document/Meeting/User, tenant-scoped `GlobalSearchAction`, JSON route `/search`, Cmd+K/Ctrl+K modal in Viho header, grouped results, keyboard navigation, and recent searches. Targeted: `GlobalSearchTest` 5 passed / 11 assertions. Full suite: 538 passed / 2908 assertions. `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass. Manual browser keyboard smoke remains launch-QA. |
| M43 | Calendar Sync (.ics Feed) | ✅ | 2026-05-17 — migration `add_calendar_sync_token_to_users`, `RegenerateCalendarSyncTokenAction`, `BuildIcsFeedAction`, public route `/calendar/{token}.ics`, profile generate/regenerate/copy UI, invalid token empty VCALENDAR behavior, and tenant-scoped meetings/projects/tasks feed. Targeted: `CalendarIcsFeedTest` 5 passed / 35 assertions. Full suite: 543 passed / 2943 assertions. `npm run lint`, `npm run build`, and `./vendor/bin/pint --test` pass. Real Google/Apple/Outlook subscription interval remains launch-QA. |
| M40 | Inventory & Asset Management (loan, return, integrasi M19 Handover) | 🔲 | 2 migrations + enum, integrasi M19 (snapshot) + M27 (QR scan redirect). |

---

## Tier MOMENTUM — DIHAPUS (2026-05-17)

Tier ini (M45 Personal "My Day", M46 Sponsorship Pipeline Tracker, M47 Member Skill Tracker, M41 Activity Feed, M42 Generic Approval Workflow) **dihapus dari roadmap aktif** untuk fokus prioritas deploy MVP. Spec detail di `POST-MVP-ROADMAP.md` juga sudah dihapus.

**Re-evaluation trigger** (kapan boleh dipertimbangkan kembali):
- MVP sudah deploy publik dan minimal 3 bulan production usage.
- Ada signal feedback user nyata yang membutuhkan modul-modul ini (mis. user keluhkan tidak ada agenda harian terpusat → re-prioritize M45).
- Kapasitas dev cukup untuk eksplorasi tier ini tanpa mengorbankan stability MVP.

---

## Modul Existing — FROZEN / Maintenance

Per POST-MVP-ROADMAP.md §0.5: kelima modul ini **tidak boleh ditambah scope baru**. Bug critical boleh di-fix; fitur baru tidak.

| ID | Modul | Status | Alasan freeze |
|---|---|---|---|
| M16 | Certificate (Sertifikat Digital) | ✅ Maintenance | QA-OPEN-012 visual preview enhancement di-skip. |
| M18 | Multi-Level Approval | ✅ Maintenance | Multi-step jadi Pro tier perk; tidak ada enhancement lebih jauh. |
| M19 | Handover Kepengurusan | ✅ Partial / Maintenance | Migration `create_handover_tables` + `add_transition_fields_to_handover_packages` ✅. Sebagian flow (export, status, transition, item update) sudah jalan. Lihat routes `handover.*`. |
| M22 | Payment & Ticketing (Midtrans) | ✅ Beta / Pro-tier opt-in only | `create_payment_ticketing_tables` + `MidtransWebhookController`. Jangan promote sebagai main feature. |
| M23 | AI Assistant (DraftProposalWithAi, SummarizeLpjWithAi) | ✅ Maintenance | `create_ai_usage_logs_table` + `app/Domain/Ai`. Freeze expansion 6 bulan post-launch. |
| M24 | Campus Dashboard B2B | ✅ Hibernate | `create_campus_dashboard_tables` + `app/Domain/Campus`. Tidak ada enhancement sampai paying campus customer signed. |

---

## Inisiatif Khusus

### Landing Polish (`landing-polish` spec — aktif)

Spec di `.kiro/specs/landing-polish/`. Dieksekusi sebagai pengganti `LCMS01` (CMS landing) yang dibatalkan 2026-05-17.

- **Tujuan**: polishing tiga halaman publik Prokerin (`/`, `/features`, `/pricing`) — copy final, visual on-brand, responsive 4 viewport, dark mode, SEO + OG, performance Lighthouse mobile ≥ 90.
- **Scope**: hanya `resources/js/Pages/Landing/*`, `resources/js/Components/Landing/*`, `resources/js/Layouts/LandingLayout.tsx`, `resources/js/lib/analytics.ts`, `resources/js/hooks/useScrollDepthTracking.ts`, `resources/js/types/index.d.ts`, `app/Http/Controllers/LandingController.php`, `resources/views/app.blade.php`, `public/og-image.png`, `public/robots.txt`, `public/sitemap.xml`, favicon set di `public/`, dan harness Vitest. Tidak ada migration database, tidak ada Filament resource, tidak ada model baru.
- **Status**: Sprint 0 selesai (audit, types, Vitest harness), Sprint 1 dimulai (1.1 HeroSection ✅, 1.2 SocialProofBar ✅, 1.3 ProblemSection queued).
- **Source**: `LANDING PAGE PLAN.md`, `.kiro/specs/landing-polish/{requirements,design,tasks,audit-notes}.md`.

### Bug Fix Plan

Spec `BUG-FIX-PLAN.md` dirujuk oleh `POST-MVP-ROADMAP.md` §0.6 sebagai prerequisite Tier IMMEDIATE (foundation Phase 0: EmptyState, ConfirmDialog, FormField, Roles helper, Toaster sonner). File spec asli **tidak ada di root saat ini** — kemungkinan di-archive setelah Phase 0 implementasinya merged. Bila perlu reference detail, cek git history atau commit yang menyebut "Phase 0".

### Super Admin V2

Spec di `SUPER-ADMIN-V2-PLAN.md` (root). Filament admin enhancement.

### QA

- `.kiro/specs/prokerin-qa/` — spec QA aktif

---

## Migration Timeline (terbaru di atas)

| Tanggal | Migration | Modul terkait |
|---|---|---|
| 2026-05-17 | `add_calendar_sync_token_to_users` | M43 Calendar Sync |
| 2026-05-17 | `create_letter_generator_tables` | M39 Surat Menyurat Generator |
| 2026-05-17 | `create_proker_microsite_tables` | M31 Public Proker Microsite |
| 2026-05-17 | `add_kepanitiaan_mode_to_organizations_table` | M30 Kepanitiaan Mode |
| 2026-05-17 | `add_step_tracking_to_organizations_onboarding` | M28 Onboarding |
| 2026-05-17 | `create_push_subscriptions_table` | M44 Web Push |
| 2026-05-17 | `create_feature_flags_table` | infra |
| 2026-05-17 | `add_last_login_at_to_users_table` | M01 Auth |
| 2026-05-17 | `add_whatsapp_opt_in_to_users_table` | infra (preparasi M17 WhatsApp) |
| 2026-05-17 | `add_query_performance_indexes` | infra |
| 2026-05-17 | `add_soft_deletes_to_workspace_tables` | infra |
| 2026-05-17 | `add_description_to_organizations_table` | M02 |
| 2026-05-17 | `add_onboarding_completed_at_to_organizations_table` | M28 |
| 2026-05-17 | `create_activity_logs_table` | infra (audit log umum; spec M41 Activity Feed dihapus) |
| 2026-05-17 | `add_internal_notes_to_organizations_table` | M02 |
| 2026-05-17 | `create_permission_tables` | M03 |
| 2026-05-16 | `create_campus_dashboard_tables` | M24 |
| 2026-05-16 | `create_ai_usage_logs_table` | M23 |
| 2026-05-16 | `create_payment_ticketing_tables` | M22 |
| 2026-05-16 | `create_event_registration_tables` | M21 |
| 2026-05-16 | `create_sponsor_vendor_tables` | M20 |
| 2026-05-16 | `create_approval_workflow_tables` | M18 |
| 2026-05-16 | `create_whatsapp_delivery_logs_table` | M17 |
| 2026-05-16 | `add_transition_fields_to_handover_packages` | M19 |
| 2026-05-16 | `create_handover_tables` | M19 |
| 2026-05-16 | `create_certificate_tables` | M16 |
| 2026-05-16 | `create_attendance_tables` | M15 |
| 2026-05-16 | `create_meeting_minute_tables` | M14 |
| 2026-05-16 | `create_notifications_table` | M12 |
| 2026-05-16 | `add_google_oauth_columns_to_users_table` | M01 |
| 2026-05-16 | `create_prokerin_workspace_tables` | M02–M11 (workspace bootstrap) |

---

## Next Action

1. **Run launch-device QA for Tier IMMEDIATE** — native Chrome Android + Safari iOS camera scan, OS web push permission/notification, and two-browser Reverb smoke with `php artisan reverb:start`.
2. **Resume `landing-polish` spec** — lanjut dari task 1.3 (`Update ProblemSection.tsx`). 60 leaf task tersisa, terorganisir dalam 18 wave DAG. Lihat `.kiro/specs/landing-polish/tasks.md`.
3. **Continue Tier GROWTH** dengan M40 Inventory & Asset Management setelah launch-device QA / landing-polish checkpoint berikutnya.

---

## Aturan Update File Ini

- **Ubah status modul ke `✅`** hanya bila: code merged, route/UI/backend integration jalan, test coverage di-record, dan verifikasi (manual atau otomatis) sudah dicatat di sini.
- **Tambah entry ke "Migration Timeline"** setiap kali ada migration baru di `database/migrations/`.
- **Update "Verifikasi Terakhir"** setiap kali test/build di-jalankan (cantumkan tanggal + angka pass/assertions).
- **Jangan menggandakan detail modul yang sudah ada di `POST-MVP-ROADMAP.md`** — link ke section sumbernya saja.
- **Setelah update ini**, jalankan `git diff features.md` dan pastikan tidak ada angka yang inkonsisten dengan output test terbaru.
