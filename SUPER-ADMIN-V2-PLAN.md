# SUPER-ADMIN-V2-PLAN.md — Prokerin
## Upgrade Komprehensif Filament Super Admin Panel: UX, Resource, Tools, Security

> Wajib baca dulu: `AGENTS.md`, `features.md`, `super-admin-panel.md` (SA01 spec), `BUG-FIX-PLAN.md`.
> **Update 2026-05-17**: Referensi `LANDING-CMS-PLAN.md` dihapus karena LCMS01 dibatalkan (lihat `LANDING-CMS-PLAN.md` Section A — landing page tetap hardcoded, hanya di-polish manual).
> Module ini diberi kode `SA02` (UX & Resource expansion) dan `SA03` (Security hardening) di `features.md` setelah selesai.
> Dokumen ini menjelaskan rencana upgrade Filament panel Prokerin dari kondisi "MVP berfungsi" (state SA01) menjadi panel internal yang siap dipakai operasional production: branding match Viho, dashboard yang insightful, resource lengkap untuk semua modul Prokerin, tools admin (broadcast, feature flag, email template), dan security hardening (2FA, IP allowlist, re-auth, audit comprehensive).

---

## 0. Konteks dan Tujuan

### 0.1 State Sekarang (Setelah SA01)
Panel `/internal-admin` punya:
- 5 Resource: User, Organization, Project (read-only), NotificationRule, DocumentExport.
- 3 Widget dashboard: PlatformStatsOverview, RecentOrganizationsTable, RecentUsersTable.
- Impersonation lab404 dengan banner di Inertia app.
- Activity log table sudah ada tapi belum ada UI.
- Spatie roles `super_admin`, `campus_admin` ter-seed.
- Filament default theme (warna Teal, font default Inter).

### 0.2 Setelah SA02 + SA03 Selesai

**SA02 (UX & Resource Expansion)** — panel tampil branded ala Prokerin (Viho aesthetic), dashboard kaya insight, ada resource untuk semua data sensitif (activity log, failed jobs, payment, AI usage, WhatsApp delivery, certificate, event registration, campus, invitation), ada tools internal (broadcast announcement, feature flag, email template manager, system health page).

**SA03 (Security Hardening)** — 2FA wajib super_admin, IP allowlist optional, re-auth untuk action sensitif, session timeout aman, robots.txt + X-Robots-Tag, rate limit panel, comprehensive audit log untuk semua mutation.

### 0.3 Non-Goals
- **Bukan** SSO Google/Microsoft (di-defer).
- **Bukan** SCIM provisioning.
- **Bukan** chat support / Crisp/Intercom embed dalam panel.
- **Bukan** native mobile admin app.
- **Bukan** GraphQL admin API.

---

## 1. Constraint Wajib (AGENTS.md Compliance)

| AGENTS.md | Aturan | Penerapan di SA02/SA03 |
|---|---|---|
| §6 PHP | strict_types, type hints | Semua Action, Resource, Widget |
| §4 Structure | Filament Resource boleh return query, tapi business logic tetap di Action | Bulk actions, retry, broadcast → delegate ke Action |
| §10 Multi-tenant | Resource lintas org yang sensitif harus tetap audit-aware | Activity log otomatis untuk every mutation |
| §11 Performance | Eager-load di getEloquentQuery | Wajib untuk User-with-roles, Org-with-owner |
| §15 Do Not | Tidak modify shadcn primitives | Filament punya theme system sendiri, jangan touch `Components/ui/` |
| §15 Do Not | env() hanya di config | Tambah `config/admin.php` baru untuk SA02/SA03 settings |
| §11 Performance | Cache filament navigation di production | Mention di deployment doc, jangan blocking dev |
| §16 Env | Tambahkan ke .env.example | Daftar env baru di Phase 11 |

---

## 2. Phase Overview

| Phase | Fokus | Modul | Estimasi |
|---|---|---|---|
| 1 | Branding & Theme | Custom Filament theme match Viho | 2 hari |
| 2 | Dashboard V2 | Trend chart, plan distro, health card, top engaged orgs | 3 hari |
| 3 | Resource Polish | User, Organization, Project, NotificationRule, DocumentExport | 3 hari |
| 4 | Activity & Failed Jobs | ActivityLogResource, FailedJobResource, retry flow | 2 hari |
| 5 | Operational Resources | Invitation, Campus, PaymentOrder, EventRegistration, WhatsAppDeliveryLog, AiUsageLog, CertificateRecipient | 3-4 hari |
| 6 | Internal Tools | Broadcast, FeatureFlag, EmailTemplate, SystemHealth, OnboardingChecklist | 4 hari |
| 7 | Sidebar & Search | Navigation grouping, global search, breadcrumb polish | 1 hari |
| 8 | Bulk Action & Audit | Safe bulk actions + audit per action | 2 hari |
| 9 | Security Layer 1 | 2FA mandatory super_admin, session timeout | 2 hari |
| 10 | Security Layer 2 | IP allowlist, re-auth critical actions, rate limit, robots/CSP | 2 hari |
| 11 | Verification & Doc | Tests, env, docs, features.md update | 1 hari |

Total estimasi 25-28 hari kerja efektif. Bisa dibagi 2 milestone:
- **SA02** = Phase 1-8 (UX + Resources + Tools).
- **SA03** = Phase 9-10 (Security).
- Phase 11 lintas keduanya.

---

## 3. Phase 1 — Branding & Theme

### 3.1 Tujuan
Panel terlihat satu napas dengan Prokerin: warna `#24695c` primary, `#ba895d` secondary, font Plus Jakarta Sans, flat 4px corner, sidebar putih dengan teks gelap, header tipis sesuai Viho.

### 3.2 Custom Theme File
- Jalankan `php artisan make:filament-theme admin` (Filament docs).
- Generated file: `resources/css/filament/admin/theme.css`.
- Override Tailwind config `tailwind.config.preset.js` Filament:
  ```
  primary: { 50..900 derived from #24695c }
  fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui'] }
  borderRadius: { DEFAULT: '4px', sm: '4px', md: '4px', lg: '8px' }
  ```
- Register di `AdminPanelProvider`:
  ```
  ->viteTheme('resources/css/filament/admin/theme.css')
  ```
- Custom CSS di `theme.css`:
  - Override `.fi-sidebar` background `#ffffff`, border `#e6edef`.
  - Override `.fi-topbar` background `#ffffff`, shadow `0 1px 0 #e6edef`.
  - Override card radius jadi 4px.
  - Override pill/badge radius jadi 4px.
  - Disable dark mode theme (kalau workspace tidak support).

### 3.3 Logo & Favicon
- `AdminPanelProvider`:
  ```
  ->brandLogo(asset('vendor/viho/logo-prokerin.svg'))
  ->brandLogoHeight('1.5rem')
  ->favicon(asset('favicon.ico'))
  ```
- Sediakan logo monochrome khusus header panel kalau perlu.

### 3.4 Empty State Polish
Setiap Resource override:
```
->emptyStateIcon('heroicon-o-folder-open')
->emptyStateHeading('Belum ada data')
->emptyStateDescription('Data akan muncul di sini begitu...')
->emptyStateActions([Action::make('refresh')->...])
```

### 3.5 Checklist Phase 1
- [ ] Custom theme generated dan ter-load.
- [ ] Tailwind preset override match Viho tokens.
- [ ] Sidebar putih, header tipis, radius 4px.
- [ ] Logo brand muncul, favicon panel.
- [ ] Empty state per resource.
- [ ] Browser screenshot before/after di QA report.

### 3.6 Verification
- `npm run build` pass.
- Login `superadmin@prokerin.test`, navigasi semua resource: tampilan konsisten dengan workspace utama.
- No regression di existing tests.

---

## 4. Phase 2 — Dashboard V2

### 4.1 Widget Baru

#### `UserGrowthChart`
- Extends `Filament\Widgets\ChartWidget`.
- Type `line`. X-axis: 30 hari terakhir. Y-axis: cumulative user count.
- Data dari `users.created_at` group by date.
- Cache 5 menit.

#### `OrganizationGrowthChart`
- Same pattern, untuk organisasi.

#### `PlanDistributionChart`
- Type `doughnut`. Distribusi `organizations.plan_tier` (free/starter/pro/campus).
- Update setiap kali ada plan tier change (cache invalidate).

#### `EngagedOrganizationsTable`
- TableWidget. List 10 organisasi dengan login terakhir terbaru di antara member-nya.
- Column: name, plan_tier, last_active_at, member_count, projects_count.
- Sort by last_active_at desc.
- Butuh kolom `users.last_login_at` (migration baru, lihat Phase 3).

#### `PlatformHealthCard`
- StatsOverviewWidget custom yang menampilkan:
  - Redis up/down
  - Database ping (ms)
  - Queue depth (`exports`, `default`, `whatsapp`)
  - S3 reachable
  - Mail service reachable
- Implementasi: `App\Actions\Admin\GetPlatformHealthAction` cek tiap dependency dengan timeout 1 detik. Cache 30 detik.

#### `FailedJobsCounter`
- StatsOverviewWidget. Total failed jobs minggu ini, dengan link ke FailedJobResource.

#### `ActiveProkerByPhase`
- TableWidget. Distribusi project status (planning, active, completed, archived).

#### `TopStorageOrganizations`
- TableWidget (kalau metering storage sudah ada). Defer kalau belum.

### 4.2 Layout Dashboard
Override default Filament Dashboard dengan custom page `app/Filament/Pages/PrkAdminDashboard.php`:
```
protected static string $view = 'filament.pages.prk-admin-dashboard';
public function getWidgets(): array {
    return [
        PlatformStatsOverview::class,
        FailedJobsCounter::class,
        UserGrowthChart::class,
        OrganizationGrowthChart::class,
        PlanDistributionChart::class,
        ActiveProkerByPhase::class,
        EngagedOrganizationsTable::class,
        RecentOrganizationsTable::class,
        RecentUsersTable::class,
        PlatformHealthCard::class,
    ];
}
```

Set di panel provider:
```
->pages([App\Filament\Pages\PrkAdminDashboard::class])
```

### 4.3 Checklist Phase 2
- [x] 7 widget baru.
- [x] PrkAdminDashboard page sebagai default landing panel.
- [ ] Cache layer per widget.
- [x] PlatformHealthAction.
- [x] Test feature: dashboard load < 2s di staging.

### 4.4 Verification
- Login super_admin → dashboard tampil grafik growth + distribusi plan + health card.
- `php artisan test tests/Feature/SuperAdmin/DashboardWidgetsTest.php` pass.
- 2026-05-17: Dashboard V2 ditambahkan via `PrkAdminDashboard` dengan `UserGrowthChart`, `OrganizationGrowthChart`, `PlanDistributionChart`, `EngagedOrganizationsTable`, `PlatformHealthCard`, `FailedJobsCounter`, dan `ActiveProkerByPhase`.
- Targeted V2 suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2 --stop-on-failure` -> **19 passed, 61 assertions**.
- Full regression after SA02/SA03 slice: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **487 passed, 2656 assertions**.
- Frontend gates: `npm run lint` pass; `npm run build` pass.
- Formatter gate: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` -> **pass**.

---

## 5. Phase 3 — Resource Polish

### 5.1 UserResource

Tambahan:
- Migration `add_last_login_at_to_users_table` (kolom `last_login_at` timestamp nullable, indexed).
- Listener `UpdateLastLoginAt` di event `Login` Laravel default.
- Column "Last Login" di table.
- Filter "Logged in last 7 days", "Logged in last 30 days", "Never logged in".
- Avatar circular di list, fallback inisial.
- Action "Resend verification email" untuk user `email_verified_at` null.
- Bulk action "Force verify selected" (typed confirm "VERIFY"):
  - Update `email_verified_at = now()`.
  - Audit log `user.email.force_verify_bulk`.
- Filter date range `created_at`.

### 5.2 OrganizationResource

Tambahan:
- Tabs di edit page (`Filament\Schemas\Components\Tabs`):
  - **General** (existing fields).
  - **Members** — tabel inline list `organization_members` dengan role badge.
  - **Projects** — list project read-only.
  - **Billing** — plan tier history (lihat Phase 4 ActivityLogResource cross-link).
  - **Activity** — log `landing.*` dan `org.*` filtered by org.
- Health badge di list:
  - Green: ada login member dalam 7 hari.
  - Yellow: 7-30 hari.
  - Red: >30 hari atau no login ever.
- Action "View as super admin (read-only)":
  - Set session `view_as_org_id`.
  - Redirect ke `/dashboard`.
  - Workspace controller mendeteksi `view_as_org_id` dan render dashboard read-only (banner: "Mode super admin · view-only").
  - Berbeda dari impersonation: tidak menjadi user, tetap super admin tapi konteks org.
  - Audit log `org.view_as_super_admin`.

### 5.3 ProjectResource

Tambahan:
- Action "View Health Report" buka modal:
  - Total task vs done.
  - Total RAB planned vs realized.
  - Proposal status + timestamp.
  - LPJ status + readiness %.
  - Last task update.
- Filter "Stuck" (proker `proposal_review` >14 hari).
- Filter "Completed but no LPJ" (status completed dan LPJ tidak submitted).

### 5.4 NotificationRuleResource

Tambahan:
- Preview "Cara muncul di user" — modal render contoh notifikasi (template + placeholder dummy).
- Stats per rule: berapa kali fire 7/30 hari, success rate WhatsApp/email (butuh data dari `whatsapp_delivery_logs` dan email tracking; lihat Phase 5).

### 5.5 DocumentExportResource

Tambahan:
- Filter status (queued/processing/completed/failed).
- Action "Retry" untuk status failed (super_admin only):
  - Re-dispatch `GenerateDocumentExportJob`.
  - Reset status ke `queued`.
  - Audit log `document_export.retry`.
- Column file size (dari S3 metadata kalau available).
- Action "Download" generate signed URL 5 menit.

### 5.6 Checklist Phase 3
- [x] Migration last_login_at.
- [x] Listener login update.
- [x] User table column + filter + bulk action force verify.
- [ ] Org tabs (general/members/projects/billing/activity).
- [ ] Org health badge.
- [ ] Org "View as super admin" mode.
- [x] Project health report modal + filter.
- [ ] NotificationRule preview modal + stats.
- [ ] DocumentExport retry + download + filter.

### 5.7 Verification
- `php artisan test tests/Feature/SuperAdmin/UserResourceTest.php` (extend existing).
- `php artisan test tests/Feature/SuperAdmin/OrganizationResourceTest.php` (extend).
- Test baru `ProjectHealthReportTest`, `DocumentExportRetryTest`.
- 2026-05-17: `last_login_at` migration + login listener ditambahkan; UserResource kini punya kolom/filter last login dan bulk force verify dengan typed `VERIFY` + audit log.
- 2026-05-17: ProjectResource kini punya health report modal dan filter `stuck_proposal_review` serta `completed_without_lpj`.
- 2026-05-17: NotificationRuleResource punya preview modal contoh tampilan notifikasi; stats per rule masih pending.
- Targeted user/resource suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/UserResourceTest.php tests/Feature/SuperAdmin/FilamentAccessTest.php tests/Feature/SuperAdmin/V2 --stop-on-failure` -> **26 passed, 87 assertions**.

---

## 6. Phase 4 — Activity & Failed Jobs

### 6.1 ActivityLogResource (Read-only)

- Resource over `activity_logs` table.
- Columns: created_at, user (relation `user.name`), action, target_type (humanized), target_id, ip_address, payload (compact JSON preview).
- Filters:
  - Action (select dari distinct actions).
  - User.
  - Target type.
  - Date range.
- Pagination 50.
- Action "View Detail" → modal full payload + user agent.
- No create/edit/delete.
- Bulk action "Export CSV" untuk audit external.

### 6.2 FailedJobResource

- Resource over default Laravel `failed_jobs` table.
- Columns: failed_at, queue, exception_class (parsed dari payload), connection.
- Filters: queue, date range, exception class.
- Action "Retry" (single) — `Artisan::call('queue:retry', ['id' => $record->uuid])`.
- Bulk action "Retry selected".
- Action "Delete" (super_admin only, typed confirm).
- Action "View payload" — modal full JSON + stack trace.

### 6.3 Cross-link
- Di `DocumentExportResource` action retry → ada link ke FailedJobResource kalau export gagal.
- Di setiap resource yang trigger job (Notification, Export, AI), kalau status failed, link langsung ke FailedJobResource record terkait.

### 6.4 Checklist Phase 4
- [x] ActivityLogResource read-only dengan filter lengkap.
- [x] FailedJobResource dengan retry action.
- [ ] Cross-link antar resource.
- [x] Test feature retry job.

### 6.5 Verification
- Trigger 1 export gagal di staging → muncul di FailedJobResource → klik retry → status berubah.
- Audit log untuk retry tercatat.
- 2026-05-17: ActivityLogResource read-only ditambahkan. FailedJobResource sudah tersedia dari Phase 12 observability dengan retry single/bulk dan audit log.

---

## 7. Phase 5 — Operational Resources

### 7.1 InvitationResource

- Model: `OrganizationInvitation` (sudah ada).
- Read + filter (status, organization, role, date range).
- Action "Resend invitation email".
- Action "Force expire" untuk invitation yang dianggap basi.
- Cross-link ke `users.email` kalau email match user existing.

### 7.2 CampusResource

- Model: `Campus` (M24).
- CRUD lengkap (sebelumnya hanya bisa via tinker/seeder).
- Form: name, domain, admin_user (select user).
- Relation manager `CampusOrganizationLinks` di edit page (attach/detach organization).
- Audit log untuk semua mutation.

### 7.3 PaymentOrderResource

- Model: `PaymentOrder` (M22).
- Read + filter (status, tier, date range, project).
- Action "Manual mark paid" (super_admin only, typed confirm + alasan).
- Action "Refund" (status -> refunded; tidak panggil Midtrans API otomatis di MVP, hanya update DB + flag manual_refund).
- Cross-link ke registration & event project.

### 7.4 EventRegistrationResource

- Model: `EventRegistration` (M21).
- Read + filter (status, project, date range, ticket tier).
- Action "Manual confirm" untuk pending registration tanpa payment.
- Action "Cancel & refund" (cross-link ke PaymentOrder).
- Bulk action "Export CSV" lintas event/lintas org.

### 7.5 WhatsAppDeliveryLogResource

- Model: `WhatsAppDeliveryLog` (M17).
- Read + filter (status sent/failed/queued, organization, date range).
- Action "Retry" (re-dispatch `SendWhatsAppReminderJob`).
- Detail modal: provider response payload.

### 7.6 AiUsageLogResource

- Model: `AiUsageLog` (M23).
- Read + filter (organization, action_type, date range).
- Stats di header: total tokens 30 hari, top org by usage.
- Action "Mark as abuse" untuk record yang mencurigakan (kolom baru `flagged_at` di migration tambahan).

### 7.7 CertificateRecipientResource

- Model: `CertificateRecipient` (M16).
- Read lintas org + filter.
- Action "Revoke" (kolom baru `revoked_at` di migration tambahan, public verify endpoint check kolom ini dan return 410 Gone kalau revoked).
- Action "Resend signed download URL" (kirim email).

### 7.8 Checklist Phase 5
- [x] InvitationResource.
- [ ] CampusResource + relation manager.
- [ ] PaymentOrderResource + manual paid + refund.
- [ ] EventRegistrationResource + manual confirm + bulk export.
- [ ] WhatsAppDeliveryLogResource + retry.
- [ ] AiUsageLogResource + flag abuse + migration `add_flagged_at_to_ai_usage_logs`.
- [ ] CertificateRecipientResource + revoke + migration `add_revoked_at_to_certificate_recipients`.
- [ ] Update `VerifyCertificateAction` agar return 410 kalau revoked.

### 7.9 Verification
- Test feature per resource: super_admin can list/filter, member tidak bisa akses, mutation audit log tercatat, cross-tenant tidak bocor.
- 2026-05-17: Read-only index resources tersedia untuk Invitation, Campus, PaymentOrder, EventRegistration, WhatsAppDeliveryLog, AiUsageLog, dan CertificateRecipient. Mutating operational actions masih pending sesuai checklist.
- Targeted operational resource suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/OperationalResourcesTest.php --stop-on-failure` -> **2 passed**.

---

## 8. Phase 6 — Internal Tools

### 8.1 Broadcast Announcement Page

#### Schema
- Migration `platform_announcements`:
  ```
  id, title, body (markdown), severity (info|warning|critical), audience (json: { all|plan_tier|role|organization_ids }), starts_at, ends_at, created_by_user_id, timestamps, soft delete
  ```

#### Filament Resource
- `PlatformAnnouncementResource` CRUD.
- Form: title, body (Markdown editor), severity select, audience builder, schedule.
- Validate `ends_at > starts_at`.

#### Frontend Inertia
- `HandleInertiaRequests` share `platformAnnouncements` (active filtered by audience untuk user current).
- Component `AnnouncementBanner.tsx` di top `AuthenticatedLayout` (di atas sidebar) — render kalau ada announcement aktif yang match audience user.
- User bisa dismiss per announcement (simpan di `dismissed_announcements` json kolom user, atau cookie kalau tidak mau migrasi).

#### Action
- `App\Actions\Admin\GetActiveAnnouncementsForUserAction` — return list announcements yang match audience.
- Cache per user 60 detik.

### 8.2 Feature Flag Page

#### Schema
- Migration `feature_flags`:
  ```
  id, key (unique), is_enabled_globally (bool), enabled_organization_ids (json), enabled_plan_tiers (json), description, timestamps
  ```

#### Filament Resource
- `FeatureFlagResource` CRUD.
- Form: key (slug), description, is_enabled_globally, multi-select enabled_organization_ids, multi-select plan tiers.

#### Helper
- `App\Support\FeatureFlag::isEnabled(string $key, ?int $organizationId = null): bool`.
- Helper baca dari DB (cache 5 menit).
- Pakai di kode: `if (FeatureFlag::isEnabled('m22_payment', $orgId)) { ... }`.

### 8.3 Email Template Manager

#### Schema
- Migration `email_templates`:
  ```
  id, key (unique), subject, body_markdown, variables (json sample), updated_by_user_id, timestamps
  ```

#### Filament Resource
- `EmailTemplateResource` CRUD.
- Form: key (read-only after create), subject, body markdown, variables list.
- Action "Send Test" — kirim ke email super_admin saat ini dengan placeholder dummy.

#### Refactor Notification Class
- Notification existing yang generate body inline → query template dari DB pakai key.
- Fallback ke string default kalau template tidak ada (safe).

### 8.4 System Health Page

#### Page
- `app/Filament/Pages/SystemHealthPage.php`.
- Render real-time:
  - Queue depth per queue (Redis LLEN).
  - Redis memory usage.
  - MySQL slow query count (last 1h).
  - S3 reachable + latency ms.
  - Mailer reachable.
  - Sentry recent errors (kalau Sentry aktif).
- Refresh button manual.

### 8.5 Onboarding Checklist Page

#### Page
- `app/Filament/Pages/OnboardingChecklistPage.php`.
- Tabel organisasi yang join dalam 30 hari.
- Per row checklist: logo uploaded, member invited, period active set, first proker created, first proposal submitted.
- Tujuan: support team follow-up org yang stuck.

### 8.6 Checklist Phase 6
- [ ] BroadcastAnnouncement migration + resource + frontend banner.
- [x] FeatureFlag migration + resource + helper.
- [ ] EmailTemplate migration + resource + refactor notifications.
- [x] SystemHealth page.
- [x] OnboardingChecklist page.

### 8.7 Verification
- Buat announcement targeted plan_tier=pro, login sebagai owner pro → banner muncul; login sebagai owner free → tidak muncul.
- Toggle feature flag → kode `FeatureFlag::isEnabled` return value berubah.
- Send test email template → email diterima dengan variabel resolved.
- 2026-05-17: FeatureFlag tool ditambahkan (`feature_flags`, `FeatureFlagResource`, `App\Support\FeatureFlag::isEnabled`) dengan targeting global, organisasi, dan plan tier.
- Targeted FeatureFlag suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/FeatureFlagToolTest.php --stop-on-failure` -> **4 passed, 16 assertions**.
- 2026-05-17: SystemHealth page ditambahkan di navigation group Insights dengan refresh manual berbasis `GetPlatformHealthAction`.
- Targeted SystemHealth suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/SystemHealthPageTest.php --stop-on-failure` -> **3 passed, 7 assertions**.
- 2026-05-17: OnboardingChecklist page ditambahkan di navigation group Operations dengan checklist support untuk organisasi baru 30 hari terakhir.
- Targeted OnboardingChecklist suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/OnboardingChecklistPageTest.php --stop-on-failure` -> **4 passed, 13 assertions**.
- Targeted V2 suite after FeatureFlag + SystemHealth + OnboardingChecklist: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2 --stop-on-failure` -> **19 passed, 61 assertions**.
- Full regression after Phase 6 slice: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` -> **487 passed, 2656 assertions**.
- Frontend gates after Phase 6 slice: `npm run lint` pass; `npm run build` pass.
- Formatter gate after Phase 6 slice: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test` -> **pass**.

---

## 9. Phase 7 — Sidebar & Search

### 9.1 Navigation Groups
Di `AdminPanelProvider`:
```
->navigationGroups([
    NavigationGroup::make('Platform')->icon('heroicon-o-globe-asia-australia'),
    NavigationGroup::make('Operations')->icon('heroicon-o-rectangle-group'),
    NavigationGroup::make('Configuration')->icon('heroicon-o-cog-6-tooth'),
    NavigationGroup::make('Insights')->icon('heroicon-o-chart-bar-square'),
])
```

> Catatan: group "Landing CMS" **tidak ditambahkan** karena LCMS01 dibatalkan (landing page tetap hardcoded). Kalau di masa depan CMS dihidupkan kembali, tambahkan group ini.

Distribusi:
- **Platform**: User, Organization, Campus, Project, Invitation.
- **Operations**: ActivityLog, FailedJob, DocumentExport, WhatsAppDeliveryLog, AiUsageLog, EventRegistration, PaymentOrder, CertificateRecipient.
- **Configuration**: NotificationRule, EmailTemplate, FeatureFlag, BroadcastAnnouncement.
- **Insights**: SystemHealth, OnboardingChecklist.

Setiap resource set `protected static ?string $navigationGroup = '...'` dan `navigationSort` sesuai urutan yang diinginkan.

### 9.2 Global Search
Aktifkan di setiap resource:
```
public static function getGloballySearchableAttributes(): array {
    return ['name', 'email', 'slug'];
}

public static function getGlobalSearchResultDetails(Model $record): array {
    return [...];
}
```

Atur `panel->globalSearch()` di provider.

### 9.3 Breadcrumbs
- Filament built-in dari resource hierarchy. Pastikan setiap resource punya `getRecordTitleAttribute` dan `getRecordTitle($record)` yang descriptive.

### 9.4 Checklist Phase 7
- [x] 4 navigation groups (Platform, Operations, Configuration, Insights — group Landing CMS dibatalkan)
- [x] Setiap resource ada di group yang benar.
- [x] Global search aktif di User, Organization, Project, Invitation, Campus.
- [ ] Breadcrumb tampak di edit/view page.

### 9.5 Verification
- 2026-05-17: Panel mengaktifkan global search dan navigation groups `Platform`, `Operations`, `Configuration`, `Insights`. Group `Landing CMS` **dibatalkan** mengikuti keputusan LCMS01.
- 2026-05-17: Global searchable attributes aktif untuk User, Organization, Project, Invitation, dan Campus.

---

## 10. Phase 8 — Bulk Actions & Audit

### 10.1 Bulk Actions Aman

#### UserResource
- Bulk "Force Verify" (typed "VERIFY") + audit log.
- Bulk "Export CSV" (download list).
- Bulk "Send Newsletter" (defer kalau email template manager tidak siap, link ke EmailTemplateResource).

#### OrganizationResource
- Bulk "Mark Dormant" (set kolom baru `dormant_at`, hide dari workspace queries) (typed "DORMANT").
- Bulk "Export Health Report".

#### InvitationResource
- Bulk "Resend".
- Bulk "Force Expire".

#### FailedJobResource
- Bulk "Retry".

### 10.2 Audit Comprehensive

Tambah event listener `Filament\Events\ResourceMutated` (atau hook `mutateFormDataBeforeSave`) untuk semua resource:
- Setiap create/update/delete otomatis call `LogActivityAction`.
- Action key generated otomatis: `{resource_slug}.{action}` (contoh `user.update`).

Implementasi: trait `AutoLogsActivity` yang di-include di semua resource.

### 10.3 Checklist Phase 8
- [ ] Bulk actions per resource (lihat daftar).
- [ ] Trait `AutoLogsActivity`.
- [ ] Test bulk action audit log tercatat.

---

## 11. Phase 9 — Security Layer 1 (SA03 mulai)

### 11.1 2FA Wajib Super Admin

#### Package
```
composer require pragmarx/google2fa-laravel
```

#### Schema
- Migration `add_two_factor_columns_to_users`:
  ```
  two_factor_secret (text encrypted nullable)
  two_factor_recovery_codes (text encrypted nullable)
  two_factor_confirmed_at (timestamp nullable)
  ```

#### Flow
1. Super admin login pertama kali setelah upgrade → redirect ke `/internal-admin/two-factor/setup`.
2. Page setup: tampilkan QR code (via bacon-qr-code yang sudah ada), user scan dengan Authenticator app, masukkan 6 digit code.
3. Confirm → simpan secret + generate 8 recovery codes (tampilkan sekali, user wajib download).
4. Login berikutnya: setelah email+password, redirect ke `/internal-admin/two-factor/challenge`.
5. Masuk 6 digit → kalau benar lanjut ke panel.

#### Filament Page
- `app/Filament/Pages/TwoFactorSetupPage.php` (mounted di route `/internal-admin/two-factor/setup`).
- `app/Filament/Pages/TwoFactorChallengePage.php` (mounted di `/internal-admin/two-factor/challenge`).
- Middleware `EnsureTwoFactorSetUp` di panel: kalau super_admin tanpa `two_factor_confirmed_at`, force redirect ke setup.
- Middleware `EnsureTwoFactorChallenged` di panel: kalau session tidak punya `two_factor_passed_at` dalam current login, force redirect ke challenge.

#### Re-authentication Flow
- Setiap aksi sensitif (Phase 10) call helper `requireRecentTwoFactor()` yang cek `two_factor_passed_at` < 15 menit; kalau lewat, force re-challenge.

### 11.2 Session Timeout Pendek

#### Config
- `config/admin.php`:
  ```
  'session_idle_minutes' => env('ADMIN_SESSION_IDLE_MINUTES', 30),
  ```
- Middleware `EnsureAdminSessionFresh`:
  - Cek session `admin_last_activity_at`.
  - Kalau lebih lama dari `session_idle_minutes` → flush session, redirect login.
- Update setiap request panel: `$request->session()->put('admin_last_activity_at', now())`.

### 11.3 Checklist Phase 9
- [ ] Package google2fa-laravel.
- [ ] Migration two_factor.
- [ ] TwoFactorSetupPage + TwoFactorChallengePage.
- [ ] Middleware setup + challenge.
- [ ] Recovery codes flow.
- [x] Session timeout middleware.
- [ ] Test feature: login → setup → challenge → access panel.

### 11.4 Verification
- 2026-05-17: Admin idle session timeout middleware ditambahkan memakai `ADMIN_SESSION_IDLE_MINUTES`; session expired redirect ke `/internal-admin/login`.
- Targeted admin security suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/AdminSecurityHeadersTest.php --stop-on-failure` -> **3 passed, 8 assertions**.

---

## 12. Phase 10 — Security Layer 2

### 12.1 IP Allowlist (Optional)

#### Config
```
'allowed_ips' => array_filter(explode(',', (string) env('ADMIN_ALLOWED_IPS', ''))),
```

#### Middleware `EnsureAdminIpAllowed`
- Kalau `allowed_ips` kosong, allow semua.
- Kalau diisi, cek `request()->ip()` ada di list. Tidak ada → 403.
- Apply di panel middleware stack.

#### Documentation
- README/deployment doc: cara isi env, format `192.168.1.1,10.0.0.0/24`.

### 12.2 Re-auth Critical Actions

#### Helper Trait
- `App\Filament\Concerns\RequiresPasswordConfirmation`.
- Pakai di action sensitif: force delete, plan tier change, broadcast send, feature flag global toggle.
- Filament v5 bawaan: `Action::make()->requiresConfirmation()->modalHeading(...)->modalDescription(...)`. Tambahkan custom step input password sebelum confirm.

### 12.3 Rate Limit Panel

#### Limiter Config
Di `App\Providers\RouteServiceProvider`:
```
RateLimiter::for('filament-login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
});
RateLimiter::for('filament-mutation', function (Request $request) {
    return Limit::perMinute(60)->by(auth()->id() ?? $request->ip());
});
```

Apply ke route Filament login dan setiap mutation route.

### 12.4 Robots & Headers

#### `public/robots.txt`
```
User-agent: *
Disallow: /internal-admin/
Disallow: /impersonate/
```

#### Middleware `SetAdminSecurityHeaders`
- `X-Robots-Tag: noindex, nofollow`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security: max-age=31536000` (HTTPS prod)
- `Content-Security-Policy` (audit Filament inline first; pakai nonce kalau perlu).

### 12.5 Backup Codes Flow
- Page `/internal-admin/two-factor/recovery-codes` untuk regenerate codes.
- Wajib re-auth password.
- Codes di-hash di DB, hanya tampilkan plain saat generate.

### 12.6 Checklist Phase 10
- [x] IP allowlist middleware.
- [ ] Re-auth password trait.
- [ ] Rate limit panel login + mutation.
- [x] Robots.txt.
- [x] Security headers middleware.
- [ ] Recovery codes regen flow.

### 12.7 Verification
- 2026-05-17: `config/admin.php`, `EnsureAdminIpAllowed`, `SetAdminSecurityHeaders`, admin env examples, dan `public/robots.txt` ditambahkan. Panel response sekarang menyertakan `X-Robots-Tag: noindex, nofollow` dan `X-Frame-Options: DENY`.
- Targeted security suite: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SuperAdmin/V2/AdminSecurityHeadersTest.php --stop-on-failure` -> **2 passed**.

---

## 13. Phase 11 — Verification & Documentation

### 13.1 Tests

Buat suite di `tests/Feature/SuperAdmin/V2/`:
- `BrandingTest.php` — assert custom theme aktif (via test rendering specific class).
- `DashboardWidgetsTest.php` — semua widget render tanpa error untuk super_admin.
- `UserResourceExtendedTest.php` — bulk verify, last login filter.
- `OrganizationTabsTest.php` — tabs render.
- `ActivityLogResourceTest.php` — list, filter, no mutation route.
- `FailedJobRetryTest.php` — retry job dispatch.
- `OperationalResourcesTest.php` — gabungan resource baru, akses & cross-tenant.
- `BroadcastAnnouncementTest.php` — audience filtering.
- `FeatureFlagToggleTest.php`.
- `EmailTemplateRenderTest.php`.
- `TwoFactorSetupTest.php`.
- `TwoFactorChallengeTest.php`.
- `IpAllowlistTest.php` — set env, request dari IP non-list ditolak.
- `RateLimitFilamentLoginTest.php`.
- `SecurityHeadersTest.php` — assert X-Robots-Tag dll.

### 13.2 Environment Variables

Tambah ke `.env.example`:
```
# Admin Panel
ADMIN_SESSION_IDLE_MINUTES=30
ADMIN_ALLOWED_IPS=
ADMIN_REAUTH_VALID_MINUTES=15
ADMIN_2FA_REQUIRED=true

# Operations
ADMIN_AUDIT_RETENTION_DAYS=365
ADMIN_FAILED_JOBS_RETENTION_DAYS=90
```

`config/admin.php` baru sesuai pattern.

### 13.3 Documentation
- Update `super-admin-panel.md`: section "Post-SA01 Roadmap" → tambah link ke SA02 dan SA03.
- Update `features.md`: section `## Internal Tooling` tambah `SA02` dan `SA03`.
- Update `QA-MASTER-PROKERIN.md` Section 22 (Super Admin tests) dengan test cases baru.
- Update `BUG-FIX-PLAN.md` Phase 11 (Security Hardening) — sebagian item sudah masuk SA03; tandai cross-reference.

### 13.4 Final Verification Gate
```
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
npm run build
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
npm run lint
```

Semua harus pass. Jumlah test naik dari baseline > +50 test.

Browser smoke wajib:
- Login super admin → setup 2FA → masuk panel.
- Edit user, organization, broadcast, feature flag.
- Trigger 1 export gagal → retry sukses.
- Logout, masuk via IP yang tidak di allowlist (pakai env temporary) → ditolak.

---

## 14. Master Checklist (Quick View)

### SA02 — UX & Resource Expansion
- [ ] Phase 1 — Branding & theme
- [ ] Phase 2 — Dashboard V2 widgets
- [ ] Phase 3 — Resource polish (User, Org, Project, NotificationRule, DocumentExport)
- [ ] Phase 4 — ActivityLogResource + FailedJobResource
- [ ] Phase 5 — Operational resources (Invitation, Campus, Payment, EventRegistration, WhatsAppLog, AiUsageLog, Certificate)
- [ ] Phase 6 — Internal tools (Broadcast, FeatureFlag, EmailTemplate, SystemHealth, OnboardingChecklist)
- [ ] Phase 7 — Sidebar groups + global search + breadcrumb
- [ ] Phase 8 — Safe bulk actions + comprehensive audit

### SA03 — Security Hardening
- [ ] Phase 9 — 2FA wajib + session timeout
- [ ] Phase 10 — IP allowlist + re-auth + rate limit + robots/CSP

### Phase 11 — Verification
- [ ] Test suite SA02/SA03 semua hijau
- [ ] Env + config + docs lengkap
- [ ] features.md, QA-MASTER, super-admin-panel.md updated
- [ ] Final gate (test/build/pint/lint) hijau

---

## 15. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| 2FA wajib bikin akses panel mandek kalau super admin lupa device | Akses internal hilang | Recovery codes saat setup, doc internal tentang reset via tinker (audit log wajib). |
| IP allowlist mengunci developer dari akses staging | Dev workflow blocked | Default empty (allow all). Production saja yang isi. Doc deployment jelas. |
| Custom theme break Filament update | Migration panel sulit | Dokumentasikan override yang dilakukan. Prefer override via Tailwind preset, bukan CSS hard-overide. |
| Bulk action salah pencet (force verify, dormant mass) | Data corrupt platform-wide | Wajib typed phrase, audit log, dan limit max 100 row per bulk. |
| Session timeout 30 menit terlalu pendek untuk session edit panjang | Frustrating | Mulai dari 30 menit, kalau ada feedback cukup tegang turunkan ke 60. Semua edit page punya autosave draft (mis. broadcast). |
| Banyak resource baru → sidebar berantakan | Discoverability turun | Wajib navigation groups + global search + sort yang konsisten. |
| Cache widget dashboard out-of-sync setelah event besar | Dashboard tampak salah | Cache TTL pendek (60s) untuk health, longer (5m) untuk growth chart. Tombol "Refresh" manual di widget. |
| Audit log balooning (jutaan row) | Storage cost + slow query | `ADMIN_AUDIT_RETENTION_DAYS=365` + cron prune job. Index `created_at` + `action`. |

---

## 16. Hubungan dengan Modul Lain

| Modul | Hubungan dengan SA02/SA03 |
|---|---|
| LCMS01 | **DIBATALKAN** 2026-05-17. Tidak ada navigation group "Landing CMS" di SA02. Landing page tetap hardcoded, polish manual via React file (lihat `LANDING-CMS-PLAN.md` Section A). |
| BUG-FIX-PLAN Phase 11 | Subset dari SA03: rate limit, soft delete, indexes — sebagian sudah dicatat di sini. Cross-reference. |
| BUG-FIX-PLAN Phase 12 | FailedJobResource & retry → SA02 Phase 4. Sentry → out of scope SA02/SA03 (defer ke phase observability terpisah). |
| M22 Payment | PaymentOrderResource di SA02 Phase 5 — manual refund operation. |
| M23 AI Assistant | AiUsageLogResource di SA02 Phase 5 — monitoring + flag abuse. |
| M24 Campus | CampusResource di SA02 Phase 5 — onboarding kampus baru. |
| M17 WhatsApp | WhatsAppDeliveryLogResource di SA02 Phase 5. |
| M16 Certificate | CertificateRecipientResource + revoke di SA02 Phase 5. |

---

*Setelah dokumen ini selesai dieksekusi, panel `/internal-admin` Prokerin siap dipakai operasional production: branded, kaya insight, lengkap resource untuk semua data sensitif platform, ada tools internal untuk operasional sehari-hari, dan terlindungi dengan 2FA + IP allowlist + re-auth + rate limit + audit comprehensive.*
