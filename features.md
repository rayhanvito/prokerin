# Prokerin — Feature Specification & Status Tracker

> **This is the single source of truth for all Prokerin feature work.**
> Every AI agent, developer, or contributor MUST read this file in full before building, testing, or marking anything complete.
> `AGENTS.md` governs architecture/conventions. This file governs what to build and what is done.

---

## How to Read This Document

| Symbol | Meaning |
|--------|---------|
| `[x]` | Complete — code exists, routes/UI wired, tests pass, verified in `Verification Log` |
| `[~]` | Partial — scaffold or partial logic exists; NOT safe to consider done |
| `[ ]` | Not started |

**Rules:**
- Never move a module to `[x]` unless: code exists + route/UI or backend endpoint is wired + feature tests pass + result is recorded in the Verification Log.
- Never start the next Post-MVP module if the full test suite is red.
- Update this file (module status + Verification Log + Next Action) after every meaningful change.
- Read `Ringkasan Status` and `Next Action` first on every session start.

---

## Status Summary

| Phase | Range | Status |
|-------|-------|--------|
| MVP Core | M01–M13 | ✅ All complete and verified |
| Post-MVP Wave 1 | M14–M16 | ✅ Complete |
| Post-MVP Wave 2 | M19 | ✅ Complete |
| Post-MVP Wave 2 | M17, M19 | ✅ Complete |
| Post-MVP Wave 2 | M18 | ✅ Complete |
| Post-MVP Wave 2 | M20 | ✅ Complete |
| Cross-module UX | M28.5 | ✅ Complete |
| Post-MVP Wave 3 | M21 | ✅ Complete |
| Post-MVP Wave 3 | M22 | ✅ Complete |
| Post-MVP Planned | M23–M24 | 🔲 Not started |

**Current active risk:** Shell default still points to PHP 8.3. Always prefix Composer/Artisan with `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH` until Homebrew PHP is relinked.

---

## Verification Log

All entries are recorded in reverse-chronological order. Always add a new entry when a module is verified.

- `[x]` 2026-05-17 · M22 payment & ticketing completed: env/config, ticket tiers, payment orders, public ticket selection, free-tier bypass, paid pending order, Midtrans signature webhook, and tier capacity guards.
- `[x]` 2026-05-17 · M22 local migration `2026_05_16_000015_create_payment_ticketing_tables.php` applied and `php artisan db:seed` added demo ticket tiers.
- `[x]` 2026-05-17 · M22 browser smoke passed on `/events/seminar-karier-digital/register`; ticket selector renders `Free Pass` and paid tier pricing.
- `[x]` 2026-05-17 · After M22 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **274 passed, 1389 assertions**.
- `[x]` 2026-05-17 · After M22 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/PaymentTicketingTest.php tests/Feature/EventRegistrationTest.php` → **18 passed, 101 assertions**.
- `[x]` 2026-05-17 · After M22 completion: `npm run build` passed.
- `[x]` 2026-05-17 · M21 completed with internal settings management, queued PDF export through `document_exports`, event registration PDF content generation, browser smoke, build, targeted tests, and full regression.
- `[x]` 2026-05-17 · M21 browser smoke passed on `/events/registrations`; settings form, seeded participant list, and `Export PDF` queue flash render as owner.
- `[x]` 2026-05-17 · After M21 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **267 passed, 1351 assertions**.
- `[x]` 2026-05-17 · After M21 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/EventRegistrationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Unit/PlanDocumentExportActionTest.php` → **15 passed, 111 assertions**.
- `[x]` 2026-05-17 · After M21 completion: `npm run build` passed.
- `[~]` 2026-05-17 · M21 event registration foundation shipped: public registration form, settings/registration tables, guard checks, queued confirmation email notification, internal participant list, CSV export, sidebar link, and seeded demo event.
- `[x]` 2026-05-17 · M21 local migration `2026_05_16_000014_create_event_registration_tables.php` applied and `php artisan db:seed` added public registration settings plus demo participants.
- `[x]` 2026-05-17 · M21 browser smoke passed on public `/events/seminar-karier-digital/register` and internal `/events/registrations`; form, quota, participant list, and CSV action render.
- `[x]` 2026-05-17 · After M21 foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **263 passed, 1337 assertions**.
- `[x]` 2026-05-17 · After M21 foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/EventRegistrationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Dashboard/SidebarMenuActionTest.php` → **14 passed, 107 assertions**.
- `[x]` 2026-05-17 · After M21 foundation: `npm run build` passed.
- `[x]` 2026-05-16 · M28.5 role-aware sidebar menu wired through Inertia shared props, using server-resolved active organization and role-specific menu badges.
- `[x]` 2026-05-16 · M28.5 browser smoke passed on `/dashboard` as `owner@prokerin.test`; `Dashboard Pimpinan`, leadership sidebar groups, finance/handover links render with no console errors.
- `[x]` 2026-05-16 · After M28.5 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **256 passed, 1287 assertions**.
- `[x]` 2026-05-16 · After M28.5 role-aware sidebar: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/Dashboard/DashboardRoleResolverActionTest.php tests/Feature/Dashboard/DashboardVariantRoutingTest.php tests/Feature/Dashboard/SidebarMenuActionTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **15 passed, 186 assertions**.
- `[x]` 2026-05-16 · After M28.5 role-aware sidebar: `npm run build` passed.
- `[x]` 2026-05-16 · M28.5 role-aware dashboard variants wired: `DashboardVariant` enum, server-side resolver, dispatcher action, variant payload actions, and `Dashboard/Index` variant router.
- `[x]` 2026-05-16 · After M28.5 dashboard variants: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Dashboard/DashboardVariantRoutingTest.php` → **2 passed, 119 assertions**.
- `[x]` 2026-05-16 · M28.5 resolver foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/Dashboard/DashboardRoleResolverActionTest.php` → **6 passed, 10 assertions**.
- `[x]` 2026-05-16 · M18 local migration `2026_05_16_000012_create_approval_workflow_tables.php` applied cleanly after shortening MySQL index name.
- `[x]` 2026-05-16 · M20 local migration `2026_05_16_000013_create_sponsor_vendor_tables.php` applied and `php artisan db:seed` added demo sponsor/vendor contacts.
- `[x]` 2026-05-16 · M20 detail browser smoke passed on `/organization/sponsors-vendors/1`; profile, project history, linked document, and back link render with no console errors.
- `[x]` 2026-05-16 · After M20 sponsor/vendor detail history: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **242 passed, 1138 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor detail history: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php` → **5 passed, 29 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor detail history: `npm run build` passed.
- `[x]` 2026-05-16 · M20 form browser smoke passed on `/organization/sponsors-vendors`; create form, edit controls, and seed contacts render with no console errors.
- `[x]` 2026-05-16 · After M20 sponsor/vendor CRUD forms: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **240 passed, 1117 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor CRUD forms: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **13 passed, 203 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor CRUD forms: `npm run build` passed.
- `[x]` 2026-05-16 · M20 browser smoke passed on `/organization/sponsors-vendors?type=vendor&search=Audio`; filtered vendor contact renders with no console errors.
- `[x]` 2026-05-16 · After M20 sponsor/vendor foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **237 passed, 1107 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **10 passed, 193 assertions**.
- `[x]` 2026-05-16 · After M20 sponsor/vendor foundation: `npm run build` passed.
- `[x]` 2026-05-16 · M18 local `php artisan db:seed` refreshed notification rules with `approval_workflow_step_assigned`.
- `[x]` 2026-05-16 · After M18 next-step workflow notifications: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **235 passed, 1070 assertions**.
- `[x]` 2026-05-16 · After M18 next-step workflow notifications: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/LpjApprovalTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php tests/Feature/WorkspacePayloadTest.php` → **44 passed, 249 assertions**.
- `[x]` 2026-05-16 · After M18 next-step workflow notifications: `npm run build` passed.
- `[x]` 2026-05-16 · After M18 Proposal/RAB/LPJ workflow integration: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **233 passed, 1063 assertions**.
- `[x]` 2026-05-16 · After M18 Proposal/RAB/LPJ workflow integration: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/LpjApprovalTest.php` → **32 passed, 111 assertions**.
- `[x]` 2026-05-16 · After M18 Proposal/RAB/LPJ workflow integration: `npm run build` passed.
- `[x]` 2026-05-16 · After M18 workflow timeline: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **228 passed, 1044 assertions**.
- `[x]` 2026-05-16 · After M18 workflow timeline: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/WorkspacePayloadTest.php` → **14 passed, 140 assertions**.
- `[x]` 2026-05-16 · After M18 workflow timeline: `npm run build` passed.
- `[x]` 2026-05-16 · M18 finance approval browser smoke passed on `/finance/approval`; multi-level workflow panel renders with empty/active state.
- `[x]` 2026-05-16 · After M18 route/UI wiring: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **227 passed, 1037 assertions**.
- `[x]` 2026-05-16 · After M18 route/UI wiring: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/BudgetApprovalDecisionTest.php` → **11 passed, 40 assertions**.
- `[x]` 2026-05-16 · After M18 route/UI wiring: `npm run build` passed.
- `[x]` 2026-05-16 · After M18 workflow engine foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **226 passed, 1032 assertions**.
- `[x]` 2026-05-16 · After M18 workflow engine foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php` → **5 passed, 8 assertions**.
- `[x]` 2026-05-16 · After M18 workflow engine foundation: `npm run build` passed.
- `[x]` 2026-05-16 · M17 meeting alert browser smoke passed on `/notifications`; `Meeting Alert` queues WhatsApp alerts and shows success flash.
- `[x]` 2026-05-16 · After M17 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **221 passed, 1024 assertions**.
- `[x]` 2026-05-16 · After M17 completion: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskDeadlineReminderNotificationTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/LpjApprovalTest.php tests/Feature/BudgetReceiptRealizationTest.php tests/Feature/WorkspacePayloadTest.php` → **36 passed, 208 assertions**.
- `[x]` 2026-05-16 · After M17 completion: `npm run build` passed.
- `[x]` 2026-05-16 · M17 notifications browser smoke passed on `/notifications`; WhatsApp card, WhatsApp rule channel, and delivery log table render.
- `[x]` 2026-05-16 · M17 local migration `2026_05_16_000011_create_whatsapp_delivery_logs_table.php` applied cleanly; `php artisan db:seed` refreshed WhatsApp notification defaults.
- `[x]` 2026-05-16 · After M17 WhatsApp foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **220 passed, 1018 assertions**.
- `[x]` 2026-05-16 · After M17 WhatsApp foundation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskDeadlineReminderNotificationTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php` → **9 passed, 25 assertions**.
- `[x]` 2026-05-16 · After M17 WhatsApp foundation: `npm run build` passed.
- `[x]` 2026-05-16 · M19 transition browser smoke passed on `/organization/handover`; page renders incoming-owner and recipient-period summary after migration.
- `[x]` 2026-05-16 · M19 local migration `2026_05_16_000010_add_transition_fields_to_handover_packages.php` applied cleanly.
- `[x]` 2026-05-16 · After M19 incoming-owner policy: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **216 passed, 1009 assertions**.
- `[x]` 2026-05-16 · After M19 incoming-owner policy: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **13 passed, 93 assertions**.
- `[x]` 2026-05-16 · After M19 incoming-owner policy: `npm run build` passed.
- `[x]` 2026-05-16 · M19 export browser smoke passed on `/organization/handover`; accepted package shows `EXPORT PDF`, queues export, and displays success flash.
- `[x]` 2026-05-16 · After M19 export: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **214 passed, 1000 assertions**.
- `[x]` 2026-05-16 · After M19 export: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **11 passed, 84 assertions**.
- `[x]` 2026-05-16 · After M19 export: `npm run build` passed.
- `[x]` 2026-05-16 · M19 package submit/accept browser smoke passed on `/organization/handover`; owner can complete items, submit draft, and accept package with no console errors.
- `[x]` 2026-05-16 · After M19 package submit/accept: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **210 passed, 983 assertions**.
- `[x]` 2026-05-16 · After M19 package submit/accept: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **7 passed, 67 assertions**.
- `[x]` 2026-05-16 · After M19 package submit/accept: `npm run build` passed.
- `[x]` 2026-05-16 · M19 item workflow browser smoke passed on `/organization/handover`; owner can mark a generated checklist item done and revert action appears with no console errors.
- `[x]` 2026-05-16 · After M19 item workflow: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **208 passed, 973 assertions**.
- `[x]` 2026-05-16 · After M19 item workflow: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **5 passed, 57 assertions**.
- `[x]` 2026-05-16 · After M19 item workflow: `npm run build` passed.
- `[x]` 2026-05-16 · M19 initial browser smoke passed for `/organization/handover`; owner can create a draft handover package and generated checklist renders with no console errors.
- `[x]` 2026-05-16 · M19 local migration `2026_05_16_000009_create_handover_tables.php` applied cleanly.
- `[x]` 2026-05-16 · After M19 initial implementation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **206 passed, 966 assertions**.
- `[x]` 2026-05-16 · After M19 initial implementation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **3 passed, 50 assertions**.
- `[x]` 2026-05-16 · After M19 initial implementation: `npm run build` passed.
- `[x]` 2026-05-16 · M16 completion browser smoke passed for `/certificates/templates/1/edit` and public `/verify/11111111-1111-4111-8111-111111111111` with QR visual and no console errors.
- `[x]` 2026-05-16 · After M16 completion polish: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **203 passed, 916 assertions**.
- `[x]` 2026-05-16 · After M16 completion polish: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/CertificateNumberGeneratorTest.php tests/Feature/DigitalCertificateTest.php` → **11 passed, 77 assertions**.
- `[x]` 2026-05-16 · After M16 completion polish: `npm run build` passed.
- `[x]` 2026-05-16 · Landing responsive smoke passed at 375px, 768px, and 1280px after removing horizontal feature-card animation overflow.
- `[x]` 2026-05-16 · After landing responsive polish: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **202 passed, 897 assertions**.
- `[x]` 2026-05-16 · After landing responsive polish: `npm run build` passed (TypeScript + production frontend build).
- `[x]` 2026-05-16 · M16 initial browser smoke passed for `/certificates`, `/certificates/templates`, `/certificates/issue`, and public `/verify/11111111-1111-4111-8111-111111111111`.
- `[x]` 2026-05-16 · M16 local migration + seeder ran clean on local MySQL.
- `[x]` 2026-05-16 · After M16 initial implementation: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **200 passed, 865 assertions**.
- `[x]` 2026-05-16 · After M16 initial implementation: `npm run build` passed (TypeScript + production frontend build).
- `[x]` 2026-05-16 · `composer validate --strict` passed.
- `[x]` 2026-05-16 · PHP 8.4.10 platform check passed: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH composer check-platform-reqs`
- `[x]` 2026-05-16 · MVP baseline: `php artisan test` → **181 passed, 712 assertions**.
- `[x]` 2026-05-16 · After M14: `php artisan test` → **183 passed, 755 assertions**.
- `[x]` 2026-05-16 · After M15: `php artisan test` → **190 passed, 804 assertions**.
- `[x]` 2026-05-16 · M15 migration + seeder ran clean on local MySQL.
- `[x]` 2026-05-16 · `npm run build` passed (production frontend build).
- `[x]` 2026-05-16 · Browser smoke test `/meetings` passed after login as `owner@prokerin.test`.
- `[x]` 2026-05-16 · Browser smoke test `/attendance` passed after login as `owner@prokerin.test`.

---

## Environment Notes

- **PHP requirement:** 8.4+ (Laravel 13.x).
- **PHP 8.4 binary:** `/opt/homebrew/bin/php`.
- **Shell default:** Still points to `/opt/homebrew/opt/php@8.3/bin/php` — use `PATH` prefix.
- **Prefix for all Composer/Artisan:** `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH`
- **Local database:** MySQL via `.env` port `8889`.
- **M14 migration** applied locally on 2026-05-16.
- **M15 migration** applied locally on 2026-05-16. Seeder re-run after M15.
- **M16 migration** applied locally on 2026-05-16. Seeder re-run after M16.
- **M19 migration** applied locally on 2026-05-16.

---

## Foundation (Complete)

All foundational work below is complete and must not be re-scaffolded.

- `[x]` Laravel + Breeze React/Inertia TypeScript scaffold.
- `[x]` Inertia app shell: Viho-inspired fixed sidebar, white header, compact SaaS admin layout, Viho assets in `public/vendor/viho/`.
- `[x]` Shared frontend modules: `VihoSidebar`, `VihoHeader`, `VihoCard`, `VihoDataTable`, `VihoStatusBadge`, `ModuleOverview`, `FlashBanner`, `vihoMenu`.
- `[x]` Breeze auth/profile/account-recovery pages aligned to Viho design tokens.
- `[x]` Inertia shared props: `auth`, `activeOrganization`, `appContext`, flash messages.
- `[x]` Public/workspace routes moved from closures to thin controllers.
- `[x]` Domain enums/value objects: organization, membership, project, task, finance, proposal, report, notification, document, export, `Money`, `Progress`.
- `[x]` DTO and Action pattern adopted for all payload and business logic.
- `[x]` MySQL workspace persistence migrations for all MVP modules.
- `[x]` Idempotent seeders for: demo roles, organizations, periods, projects, tasks, finance, documents, proposals, LPJ, notifications, exports, meetings, role matrix.
- `[x]` Route smoke tests for all active workspace pages.
- `[x]` Dashboard and workspace payloads: database-backed and tenant-scoped.

---

## MVP Modules

---

### M01 · Auth & Account Management

**Status:** `[x]` Complete.

#### What Is Built
- Breeze flows: login, register, forgot password, reset password, confirm password, verify email, profile edit.
- Auth UI styled to Viho tokens.
- Google OAuth: Socialite package, config keys, readiness Action, redirect URL builder, code exchange, callback user sync, web routes, Login/Register entry points.
- Email verification prompt Action.

#### Test Coverage
- Feature tests: authentication, registration, password reset/update/confirmation, email verification, profile, Google OAuth flow.

#### Gaps / Non-Blockers
- Google OAuth production credentials must be set via `.env` — not in code.
- No blocker for MVP.

---

### M02 · Organization Management

**Status:** `[x]` Complete.

#### What Is Built
- Organization setup, switcher, periods, calendar, logo upload UI.
- Active organization/period context injected into Inertia shared props.
- Organization period resolver Action.
- Logo upload: planner, persistence flow, server-derived organization scope.
- Tables: `organizations`, `organization_periods`, `organization_members`, `organization_invitations`.
- Seed: organizations, active periods.

#### Test Coverage
- Feature test: logo upload, organization payload.

#### Gaps / Notes
- Organization calendar is a planning overview/scaffold — not a full drag-drop scheduler. Full drag-drop is Post-MVP.
- Advanced period closing/handover → M19.

---

### M03 · Member & Role Management

**Status:** `[x]` Complete.

#### What Is Built
- Members overview, invite queue, role matrix UI.
- Role permission matrix Action with database-backed payload.
- Invitation decision Action.
- Organization member role mutation backend.
- Owner/admin guardrails and last-owner protection (prevents orphaning an org).
- Seed: multi-role users across organizations.

#### Test Coverage
- Feature tests: role update, workspace payload, invitation decision.

#### Gaps / Notes
- Invitation email delivery is not yet a primary flow (uses database notification).
- Spatie permission formalization can be extended as authorization rules expand.

---

### M04 · Proker (Program Kerja) / Event Management

**Status:** `[x]` Complete.

#### What Is Built
- Proker index, create, detail, edit, status flow, archive.
- Create backend: server-derived org scope, active period/template lookup, unique slug generation, project lead membership guard.
- Detail payload: database-backed, tenant-scoped slug lookup, aggregate metrics, nearby tasks.
- Update backend: tenant-scoped slug lookup, slug regeneration on title change.
- Archive/delete: non-destructive via status `archived`.
- Actions: `ProjectStatusTransitionAction`, `ProjectProgressCalculationAction`, `TemplateDraftAction`.
- Inertia create/edit forms using `useForm`.

#### Test Coverage
- Feature tests: create project, detail payload, update, archive.

#### Gaps / Notes
- Full project member assignment UI (drag-drop multi-select) can be deepened.
- Project-level policy formalization needs expansion as role matrix enforcement matures.

---

### M05 · Template Proker

**Status:** `[x]` Complete.

#### What Is Built
- Template Proker UI.
- Default template plan generator: auto-generates tasks, budget lines, proposal outline, LPJ checklist.
- `project_templates` table + database-backed payload.
- One-click generate flow: creates project → tasks → RAB → proposal → LPJ scaffold atomically.

#### Test Coverage
- Feature test: template generation (full scaffold created correctly).

#### Gaps / Notes
- Custom template builder (user-defined templates) → Post-MVP.
- Template versioning → Post-MVP.

---

### M06 · Timeline & Task Management

**Status:** `[x]` Complete.

#### What Is Built
- Timeline/task overview, kanban board, calendar view, PIC assignment scaffold.
- Database-backed kanban/calendar payloads.
- Quick task status update backend (status transitions).
- Completion timestamp handling on status change.
- Actions: `TaskBoardSummaryAction`, `TaskPicAssignmentAction` (with membership guard).

#### Test Coverage
- Feature tests: task status update, PIC assignment, board payload.

#### Gaps / Notes
- Full create/edit task form is not yet a primary focus (quick-add only).
- Drag-drop kanban not yet implemented — board is click-to-advance.
- Advanced task reminders → M12/M17.

---

### M07 · RAB (Rencana Anggaran Biaya) & Finance

**Status:** `[x]` Complete.

#### What Is Built
- Finance overview, budget draft, realization, approval queue UI.
- Actions: `BudgetSummaryAction`, `BudgetApprovalDecisionAction`, `ReceiptBackedRealizationAction`.
- Receipt upload backend with private receipt document storage (S3-signed).
- Budget transaction persistence.
- Database-backed realization payload.
- Review-stage approve/reject routes + Inertia controls.

#### Test Coverage
- Feature tests: receipt realization, budget approval decision, tenant scope.

#### Gaps / Notes
- Multi-level approval (e.g., Treasurer → Chair → Dean) → M18.
- Finance-specific export report can be deepened after LPJ/export modules mature.

---

### M08 · Proposal Generator

**Status:** `[x]` Complete.

#### What Is Built
- Proposal editor UI, export queue UI.
- Proposal draft builder: auto-fills from project data and template.
- Draft edit persistence per section body.
- Status flows: Draft → Submitted → Approved / Revision Requested → Draft.
- Locked state for submitted/approved proposals.
- Owner/admin: approve or request revision.
- Export queue: queued PDF/DOCX generation from database content.
- Tenant-scoped signed download URL for export artifacts.

#### Test Coverage
- Feature tests: proposal approval, edit, export queue trigger, tenant scope.

#### Gaps / Notes
- Rich text editor (WYSIWYG) not yet integrated — plain textarea only.
- Proposal template customization by user → Post-MVP.

---

### M09 · Document Management

**Status:** `[x]` Complete.

#### What Is Built
- Documents overview, folder structure, upload center UI.
- Document upload validation Action: MIME, file size, visibility, signed URL rule.
- Document download planner: tenant-scoped signed download route.
- Tables: `documents` with visibility levels (private, restricted, committee, public).
- Recent documents database-backed in upload center.
- Private/restricted/committee download handling.

#### Test Coverage
- Feature tests: document download, storage permission, tenant scope, visibility rules.

#### Gaps / Notes
- Full upload center form (all document types) not yet complete.
- S3/R2 production credentials are environment-only — never in code.

---

### M10 · LPJ (Laporan Pertanggungjawaban) Generator

**Status:** `[x]` Complete.

#### What Is Built
- LPJ checklist UI.
- `LpjReadinessAction`: validates all checklist items before submission.
- Checklist persistence per project.
- Database-backed tenant-scoped checklist payload.
- Status flows: Draft → Review Submitted → Approved / Revision Requested.
- Owner/admin: approve or request revision.
- Export queue planner integrated with M08 export pipeline.

#### Test Coverage
- Feature tests: LPJ approval, readiness guard, tenant scope.

#### Gaps / Notes
- Full LPJ document editor (section-by-section editing) not yet built.
- LPJ export layout/PDF polish can be improved post-stabilization.

---

### M11 · Dashboard Monitoring

**Status:** `[x]` Complete.

#### What Is Built
- Viho-style dashboard UI.
- Actions: aggregate metrics, priority projects, weekly focus, member summary.
- Database-backed: overview metrics, priority projects, weekly focus tasks, member summary.
- Tenant scoping for authenticated user's organizations.

#### Test Coverage
- Anti-leak unit coverage: metrics, priority projects, tasks, budget, LPJ, member summary.

#### Gaps / Notes
- Drilldown analytics are per-module. Advanced cross-module analytics → Post-MVP.

---

### M12 · Notification (Basic)

**Status:** `[x]` Complete.

#### What Is Built
- Notification rules/channel UI.
- `DefaultNotificationRuleAction`: sets up sensible defaults per organization.
- Tables: `notification_rules`, Laravel's built-in `notifications` table.
- Database-backed rules payload.
- Queued task deadline reminder notification.
- Channels: email + database.
- Simulate reminder route/button for testing.
- Tenant-scoped reminder dispatch Action.

#### Test Coverage
- Feature tests: notification reminder dispatch, tenant scope, channel selection.

#### Gaps / Notes
- WhatsApp reminder channel → M17.
- Per-user notification preferences UI not yet detailed.

---

### M13 · Admin Panel (Internal)

**Status:** `[x]` Complete.

#### What Is Built
- Internal admin planning UI scaffold.
- Backend readiness payload, resource plan, system health counters.
- Filament PHP installed at `/internal-admin`.
- Filament resources: Organization, User, DocumentExport.
- Destructive bulk actions removed from MVP resources (safety).

#### Test Coverage
- Feature tests: admin panel payload, resource visibility.

#### Gaps / Notes
- Admin-level Policies not yet formally enforced.
- Resource coverage should expand as Policies mature.

---

## Post-MVP Modules

---

### M14 · Rapat & Notulen (Meeting & Minutes)

**Status:** `[x]` Complete for initial Post-MVP scope.

#### What Is Built
- Migration: `2026_05_16_000006_create_meeting_minute_tables.php`
- Tables: `meetings`, `meeting_attendees`, `meeting_minutes`.
- Seed: demo meetings, attendees, published minutes.
- Action: `GetMeetingMinutePayloadAction` (tenant-scoped).
- Route: `meetings.index` → `/meetings`.
- Sidebar: "Rapat & Notulen" with badge `M14`.
- Inertia page: `resources/js/Pages/Meetings/Index.tsx`.
- UI: meeting metrics, agenda list, attendee count, present count, minute status, latest decisions, action items.

#### Test Coverage
- Feature tests: payload correctness, anti-leak tenant scope.
- Route smoke test added.
- Browser smoke test: `/meetings` passed.

#### What Is NOT Yet Built (Next Scope)
- [ ] Create/edit meeting form (date, title, agenda, invited members).
- [ ] Publish/edit minutes (section editor per meeting).
- [ ] Interactive attendance taking during meetings (check-in/check-out per attendee).
- [ ] Minutes export to PDF/DOCX.

**Before extending M14:** Add Form Request, controller mutation, Policies, and feature tests before enriching the UI.

---

### M15 · Absensi QR (QR Code Attendance)

**Status:** `[x]` Complete for initial Post-MVP scope.

#### What Is Built
- Migration: `2026_05_16_000007_create_attendance_tables.php`
- Tables: `attendance_sessions`, `attendance_qr_tokens`, `attendance_records`.
- Session scope: organization + optional project + optional meeting.
- QR token: stored as SHA-256 hash with expiry, revoked timestamp, last-used timestamp.
- Action: `CheckInAttendanceQrAction` — validates token, checks membership, prevents duplicates, checks expiry, rejects cross-tenant.
- Action: `RecordManualAttendanceAction` — manual fallback by owner/admin/secretary/project_lead.
- Guards implemented: tenant membership, anti-duplicate scan, expired token, cross-tenant rejection.
- Routes: `attendance.index`, `attendance.check-in.store`, `attendance.manual.store`.
- Sidebar: "Absensi QR" with badge `M15`.
- Inertia page: `resources/js/Pages/Attendance/Index.tsx`.
- UI: attendance metrics, session list, QR/manual counts, expiry display, recent check-ins.
- Seeder: demo attendance session linked to M14 meeting.

#### Test Coverage
- Feature tests: payload, valid check-in, duplicate scan rejection, expired token rejection, cross-tenant rejection, manual fallback, manual role guard.
- Route smoke test added.
- Browser smoke test: `/attendance` passed.

#### What Is NOT Yet Built (Next Scope)
- [ ] QR image generation (currently form accepts token string — must generate scannable QR image).
- [ ] Camera scanner PWA (browser-based QR camera scan without native app).
- [ ] Regenerate/revoke QR token UI.
- [ ] Create/edit attendance session form.
- [ ] Attendance export (CSV/PDF per session).

---

### M16 · Sertifikat Digital (Digital Certificate)

**Status:** `[x]` Complete.

#### Product Goal
Issue digitally-signed participation/achievement certificates for members who completed projects, attended events, or held organizational roles. Certificates are verifiable via a public URL (no account required).

#### Database Design
```
certificate_templates
  - id, organization_id, name, description
  - template_html (Blade/HTML with placeholders)
  - signature_label, signature_name
  - is_active, created_at, updated_at

certificate_recipients
  - id, organization_id, template_id, user_id (nullable for external)
  - recipient_name, recipient_email
  - project_id (nullable), meeting_id (nullable)
  - certificate_number (unique, system-generated)
  - issued_at, issued_by (user_id)
  - verification_token (signed UUID, used in public URL)
  - pdf_path (S3 path after generation)
  - created_at, updated_at
```

#### Backend to Build
- [x] Migration: `2026_05_16_000008_create_certificate_tables.php` with `certificate_templates` and `certificate_recipients`.
- [x] Seed: demo certificate template and recipients for BEM Fakultas Teknologi.
- [x] `CreateCertificateTemplateAction` — create/update with org scope + validation.
- [x] `IssueCertificateBatchAction` — bulk issue to list of recipients; triggers PDF job per recipient.
- [x] `GenerateCertificatePdfJob` (queued) — renders HTML template → DomPDF MVP engine → uploads to S3 → stores `pdf_path`.
- [x] `VerifyCertificateAction` — public verification by `verification_token`; returns recipient + issue details without exposing internal IDs.
- [x] `CertificateNumberGenerator` — format: `PRK-{YEAR}-{ORG_SLUG}-{SEQUENCE}`, unique per organization per year.
- [x] Form Request classes: `StoreCertificateTemplateRequest`, `IssueCertificateRequest`.
- [x] Policy: `CertificatePolicy` — owner/admin can issue, all authenticated org members can view, public can verify.
- [x] Routes:
  - `GET /certificates` — index (list issued, templates)
  - `GET /certificates/templates` — template manager
  - `POST /certificates/templates` — create template
  - `GET /certificates/templates/{id}/edit` — edit template route wired
  - `PUT /certificates/templates/{id}` — update template
  - `GET /certificates/issue` — issue form
  - `POST /certificates/issue` — issue batch
  - `GET /certificates/{certificate_number}/download` — signed download (member only)
  - `GET /verify/{token}` — **public** verification page (no auth required)

#### Frontend to Build
- [x] `resources/js/Pages/Certificates/Index.tsx` — list of issued certificates with stats.
- [x] `resources/js/Pages/Certificates/Templates.tsx` — template list, create/edit form, active toggle, and content preview.
- [x] `resources/js/Pages/Certificates/Issue.tsx` — select template, select recipients (from members or manual), preview, issue.
- [x] `resources/js/Pages/Certificates/Verify.tsx` — public verification page (unauthenticated) shows certificate details and scannable QR visual.
- [x] Sidebar: "Sertifikat Digital" with badge `M16`.

#### Test Coverage Required (before marking `[x]`)
- [x] Unit: `CertificateNumberGenerator` — unique per org per year, sequential, no collision.
- [x] Feature: issue certificate → PDF job dispatched → `pdf_path` stored after job.
- [x] Feature: verify by `verification_token` → returns correct recipient data.
- [x] Feature: cross-tenant rejection — cannot view/download another org's certificates.
- [x] Feature: public verification route is accessible without authentication.
- [x] Feature: non-owner/admin cannot issue certificates.

#### Verification
- `[x]` 2026-05-16 · `npm run build` passed after template edit UX + QR verification visual.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **203 passed, 916 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/CertificateNumberGeneratorTest.php tests/Feature/DigitalCertificateTest.php` → **11 passed, 77 assertions**.
- `[x]` 2026-05-16 · Browser smoke passed for `/certificates/templates/1/edit` and public `/verify/11111111-1111-4111-8111-111111111111`; QR visual rendered and no console errors.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/CertificateNumberGeneratorTest.php tests/Feature/DigitalCertificateTest.php` → **10 passed, 58 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **200 passed, 865 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed for `/certificates`, `/certificates/templates`, `/certificates/issue`, and public `/verify/11111111-1111-4111-8111-111111111111`.

#### Remaining Before `[x]`
- [x] Add richer template edit/activate UX instead of routing edit back to the create/list page.
- [x] Add a scannable QR code visual on the public verification page.
- [x] PDF engine decision: keep DomPDF as the accepted M16 MVP engine; revisit Browsershot when certificate design needs browser-grade CSS rendering.

#### Commit Message Convention
`feat: add digital certificate module (M16)`

---

### M17 · WhatsApp Reminder

**Status:** `[x]` Complete and verified.

#### Product Goal
Send proker deadline reminders, approval notifications, and meeting alerts directly to members' WhatsApp — increasing response speed vs email alone.

#### Scope to Build
- [x] `.env.example` variables: `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`, `WHATSAPP_FROM_NUMBER`.
- [x] `WhatsAppNotificationChannel` — Laravel notification channel abstraction (implements `send()`).
- [x] `SendWhatsAppReminderJob` (queued) — wraps HTTP call to provider; handles timeout.
- [x] `whatsapp_delivery_logs` table: `id`, `organization_id`, `user_id`, `message_type`, `status` (queued/sent/failed), `provider_response`, `sent_at`, `failed_at`.
- [x] Retry logic: 3 attempts with exponential backoff; mark `failed` after exhaustion.
- [x] Tenant/user opt-in guard: only send if user has WhatsApp delivery enabled in `notification_rules`.
- [x] Admin UI: WhatsApp delivery log per organization.
- [x] Extend WhatsApp delivery beyond task deadline reminders to approval notifications and meeting alerts.
- [x] Replace direct HTTP facade use with a swappable fake/real provider class.

#### Rules
- Never hardcode provider token, URL, or phone number in code.
- Never send real messages from tests or local — use a fake provider class.
- Fake provider must implement the same interface as real provider.

#### Test Coverage Required
- [x] Unit/feature: `WhatsAppNotificationChannel` queues provider job.
- [x] Feature: job dispatched when task deadline < 24h.
- [x] Feature: delivery log written on success and failure.
- [x] Feature: opt-out rule does not queue WhatsApp message.
- [x] Feature: proposal, LPJ, finance approval, and meeting alert workflows queue WhatsApp jobs when opted in.

#### Verification
- `[x]` 2026-05-16 · Browser smoke passed for `/notifications`; `Meeting Alert` button queues meeting WhatsApp alerts and shows success flash.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskDeadlineReminderNotificationTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/LpjApprovalTest.php tests/Feature/BudgetReceiptRealizationTest.php tests/Feature/WorkspacePayloadTest.php` → **36 passed, 208 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **221 passed, 1024 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed for `/notifications`; WhatsApp card, WhatsApp channel rule, and delivery log table render.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000011_create_whatsapp_delivery_logs_table.php`.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan db:seed` refreshed WhatsApp default channel and seeded dev WhatsApp numbers.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/TaskDeadlineReminderNotificationTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php` → **9 passed, 25 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **220 passed, 1018 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.

---

### M18 · Multi-Level Approval Workflow

**Status:** `[x]` Complete and verified.

#### Product Goal
Replace single-approver model with configurable multi-level approval chains for Proposal, RAB, and LPJ — supporting organizations that require Treasurer → Chair → Advisor sign-off sequences.

#### Scope to Build
- [x] `approval_workflow_definitions` table: `id`, `organization_id`, `workflow_type` (proposal/rab/lpj), `steps` (JSON ordered array of role_id/user_id).
- [x] `approval_instances` table: `id`, `workflow_definition_id`, `subject_type` (morphable), `subject_id`, `status`, `current_step`.
- [x] `approval_step_records` table: `id`, `instance_id`, `step_order`, `approver_id`, `decision` (approved/rejected/revision), `note`, `decided_at`.
- [x] `ProcessApprovalStepAction` — advances or terminates workflow.
- [x] `DelegateApprovalAction` — reassign a pending step to another eligible member.
- [x] Audit trail: step decisions are immutable after final decision; delegation is logged in `approval_delegations`.
- [x] UI: approval queue per user (what I need to approve) on `finance.approval`, with decision/delegation controls.
- [x] Workflow status timeline per subject, tenant-scoped and rendered on Proposal, RAB approval, and LPJ surfaces.
- [x] Integrate workflow engine into Proposal, RAB, and LPJ submission/decision routes, including final subject status sync.
- [x] Trigger next-step notifications when workflow starts or advances, via in-app notification and WhatsApp rule.

#### Test Coverage Required
- [x] Feature: full workflow executes in order (step 1 → step 2 → approved).
- [x] Feature: rejection at step 2 terminates workflow.
- [x] Feature: revision request sends subject back to submitter state.
- [x] Feature: cross-tenant — user cannot approve another org's workflow instance.
- [x] Feature: delegate reassignment is logged.

#### Verification
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000012_create_approval_workflow_tables.php`.
- `[x]` 2026-05-16 · Workflow start/advance now notifies active approver with `approval_workflow_step_assigned`; local `php artisan db:seed` refreshed notification rules.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/LpjApprovalTest.php tests/Unit/GetDefaultNotificationRulesActionTest.php tests/Feature/WorkspacePayloadTest.php` → **44 passed, 249 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **235 passed, 1070 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Proposal/RAB/LPJ routes now start or process active workflow instances and sync final subject status when the workflow is approved/rejected/revision-requested.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/ProposalApprovalTest.php tests/Feature/BudgetApprovalDecisionTest.php tests/Feature/LpjApprovalTest.php` → **32 passed, 111 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **233 passed, 1063 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Workflow timeline per subject renders through shared `ApprovalWorkflowTimeline` component on Proposal Editor, Finance Approval rows, and LPJ Checklist.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/WorkspacePayloadTest.php` → **14 passed, 140 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **228 passed, 1044 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed for `/finance/approval`; multi-level workflow panel renders.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php tests/Feature/BudgetApprovalDecisionTest.php` → **11 passed, 40 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **227 passed, 1037 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/MultiLevelApprovalWorkflowTest.php` → **5 passed, 8 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **226 passed, 1032 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.

---

### M19 · Handover Kepengurusan (Board Transition)

**Status:** `[x]` Complete and verified.

#### What Already Exists
- Route/page `organization.handover`.
- Viho-style planning/readiness UI scaffold (display only).
- Migration: `2026_05_16_000009_create_handover_tables.php`.
- Tables: `handover_packages`, `handover_items`.
- Action: `InitiateHandoverPackageAction` — owner/admin creates one draft package for the active period, with generated checklist items.
- Action: `UpdateHandoverItemStatusAction` — owner/admin or assignee can mark draft checklist items `pending`/`done`.
- Action: `UpdateHandoverPackageStatusAction` — owner/admin submits completed draft packages and accepts submitted packages.
- Action: `AssignHandoverTransitionAction` — owner/admin assigns recipient period and explicit incoming owner before acceptance.
- Action: `QueueHandoverPackageExportAction` — owner/admin queues accepted handover packages as PDF via `document_exports`.
- Action: `GetHandoverPayloadAction` — tenant-scoped payload with live metrics, package snapshot, and checklist items.
- Route: `POST /organization/handover` — creates initial handover package.
- Route: `PATCH /organization/handover/items/{item}` — updates item status.
- Route: `PATCH /organization/handover/packages/{package}/status` — submits or accepts package status.
- Route: `PATCH /organization/handover/packages/{package}/transition` — assigns recipient period and incoming owner.
- Route: `POST /organization/handover/packages/{package}/export` — queues accepted package export.
- UI: Handover page now shows database-backed metrics, package status, snapshot, generated checklist, item status buttons, transition assignment form, submit/accept actions, and accepted-package PDF export.
- Tests: payload, package initiation, item status mutation, transition assignment, incoming-owner acceptance guard, submit/accept flow, export queue/PDF generation, owner/admin/assignee guard.

#### What Still Needs to Be Built
- [x] `handover_packages` table: `id`, `organization_id`, `from_period_id`, `to_period_id`, `created_by`, `status`, `submitted_at`, `accepted_at`, plus JSON snapshot.
- [x] `handover_items` table: `id`, `package_id`, `category` (asset/document/role/finance), `label`, `description`, `status`, `assignee_id`.
- [x] Data snapshot: capture project statuses, finance balances, open tasks, and outstanding LPJ at handover freeze time.
- [x] `InitiateHandoverAction` — creates package, auto-generates items from active data.
- [x] Handover checklist UI: generated items render and draft item status can be toggled pending/done.
- [x] Submit/accept flow: draft package can be submitted after all checklist items are done, then accepted.
- [x] Archive/export handover package as PDF.
- [x] Access policy: owner/admin can initiate/submit; explicit incoming owner must accept when assigned, with owner/admin fallback only when no incoming owner is set.

#### Verification
- `[x]` 2026-05-16 · Browser smoke passed for transition summary on `/organization/handover`; incoming-owner and recipient-period fields render after migration.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000010_add_transition_fields_to_handover_packages.php`.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **13 passed, 93 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **216 passed, 1009 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed for accepted-package export on `/organization/handover`; `EXPORT PDF` queues export and shows success flash.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **11 passed, 84 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **214 passed, 1000 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **7 passed, 67 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **210 passed, 983 assertions**.
- `[x]` 2026-05-16 · Browser smoke passed for package submit/accept flow on `/organization/handover`.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **5 passed, 57 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **208 passed, 973 assertions**.
- `[x]` 2026-05-16 · Browser smoke passed for item status toggle on `/organization/handover`; owner can mark one generated checklist item done.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/HandoverPackageTest.php` → **3 passed, 50 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **206 passed, 966 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000009_create_handover_tables.php`.
- `[x]` 2026-05-16 · Browser smoke passed for `/organization/handover`; owner can create a draft package, generated checklist renders, no console errors.

---

### M20 · Sponsor & Vendor Database

**Status:** `[x]` Complete and verified.

#### Product Goal
Maintain a reusable contact book of sponsors and vendors per organization — searchable by category, linked to historical projects, with contact person and document tracking.

#### Scope to Build
- [x] `sponsors_vendors` table: `id`, `organization_id`, `type` (sponsor/vendor), `name`, `category`, `contact_person`, `phone`, `email`, `address`, `status` (active/inactive), `notes`.
- [x] `sponsor_vendor_project_links` table: `id`, `sponsor_vendor_id`, `project_id`, `role_description`, `amount`, `linked_at`.
- [x] `sponsor_vendor_documents` table: `id`, `sponsor_vendor_id`, `document_id` (FK to documents).
- [x] Inertia pages: list is searchable/filterable, create/edit forms are wired, and detail page shows project/document history.
- [x] Tests: tenant scoping, cross-org read prevention, and CRUD authorization (admin+ only) covered.

#### Verification
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000013_create_sponsor_vendor_tables.php`.
- `[x]` 2026-05-16 · Sponsor/vendor detail page wired with tenant-scoped profile, linked project history, and linked documents.
- `[x]` 2026-05-16 · Browser smoke passed for `/organization/sponsors-vendors/1`; profile, project history, linked document, and back link render with no console errors.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php` → **5 passed, 29 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **242 passed, 1138 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Sponsor/vendor create and edit forms wired on list page with owner/admin authorization.
- `[x]` 2026-05-16 · Browser smoke passed for `/organization/sponsors-vendors`; create form, edit controls, `Bank Jatim Youth Program`, and `CV Audio Visual Nusantara` render with no console errors.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/SponsorVendorTest.php tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **13 passed, 203 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **240 passed, 1117 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan db:seed` added demo sponsor/vendor contacts, project links, and document links.
- `[x]` 2026-05-16 · Browser smoke passed for `/organization/sponsors-vendors?type=vendor&search=Audio`; page renders filtered `CV Audio Visual Nusantara` with no console errors.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/WorkspacePayloadTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **10 passed, 193 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **237 passed, 1107 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.

---

### M28.5 · Role-Aware Dashboard

**Status:** `[x]` Complete.

#### Product Goal
Make the first workspace screen and navigation adapt to the user's resolved organization/project role, so each board member sees the most relevant KPIs, work queues, and shortcuts without relying on client-submitted role data.

#### What Is Built
- `[x]` `DashboardVariant` enum for `pimpinan`, `sekretaris`, `bendahara`, `operasional`, `member`, and `viewer`.
- `[x]` Server-side `DashboardRoleResolverAction` with hierarchy: owner/admin → secretary → treasurer → project lead/division coordinator → member → viewer.
- `[x]` `DashboardPayloadAction` dispatcher and per-variant payload actions for leadership, secretary, treasurer, operational, and member dashboards.
- `[x]` `Dashboard/Index` Inertia router page with variant-specific React dashboard components and Indonesian labels.
- `[x]` Role-aware `SidebarMenuAction` shared through Inertia props, including cached badge counts for approval work, pending tasks, and unread notifications.
- `[x]` `VihoSidebar` consumes server-side `sidebarMenu`, maps icons locally, keeps a static fallback, and highlights active relative routes.
- `[x]` `resources/js/Data/roleMenus.ts` documents the frontend role menu contract for future UI work.

#### Test Coverage
- `[x]` Unit test for role resolver priority and fallback.
- `[x]` Feature test for dashboard variant routing across seeded roles.
- `[x]` Feature test for tenant-scoped dashboard payload isolation.
- `[x]` Feature test for sidebar role visibility and badge scoping.
- `[x]` Workspace route smoke test passes with role-aware shared props.

#### Verification
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/Dashboard/DashboardRoleResolverActionTest.php` → **6 passed, 10 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/Dashboard/DashboardVariantRoutingTest.php` → **2 passed, 119 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/Dashboard/DashboardRoleResolverActionTest.php tests/Feature/Dashboard/DashboardVariantRoutingTest.php tests/Feature/Dashboard/SidebarMenuActionTest.php tests/Feature/WorkspaceRouteSmokeTest.php` → **15 passed, 186 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed on `/dashboard` as `owner@prokerin.test`; `Dashboard Pimpinan`, role-aware leadership sidebar, finance links, and handover link render with no console errors.
- `[x]` 2026-05-16 · Full regression: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **256 passed, 1287 assertions**.

#### Gaps / Notes
- Browser smoke covered the owner/pimpinan session; automated route tests cover all seeded variants (`owner`, `admin`, `sekretaris`, `bendahara`, `lead`, `koordinator`, `member`, `viewer`).
- Current implementation uses local `organization_members.role` and `project_members.role` columns because that is the active project data model; Spatie can be layered later when formal permission guards are expanded.

---

### M21 · Event Registration (Public)

**Status:** `[x]` Complete.

#### Product Goal
Allow non-members of an organization to register for public events/projects — enabling BEM/UKM to run ticketed or open events.

#### Scope to Build
- [x] `event_registrations` table: `id`, `project_id`, `participant_name`, `participant_email`, `phone`, `institution`, `status` (pending/confirmed/cancelled), `registered_at`.
- [x] `event_registration_settings` table: `id`, `project_id`, `is_open`, `capacity`, `opens_at`, `closes_at`, `require_payment`.
- [x] Public route: `GET /events/{project_slug}/register` — unauthenticated form.
- [x] Guards: capacity check, duplicate email per event, registration window check.
- [x] Confirmation email queued on successful registration.
- [x] Internal UI: registration list, CSV export, queued PDF export, and settings management are wired.
- [x] Tests: capacity enforcement, duplicate email rejection, tenant/project scope isolation.

#### Verification
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000014_create_event_registration_tables.php`.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan db:seed` added demo event registration settings and participants.
- `[x]` 2026-05-17 · Browser smoke passed for public `/events/seminar-karier-digital/register` and internal `/events/registrations`.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/EventRegistrationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Feature/Dashboard/SidebarMenuActionTest.php` → **14 passed, 107 assertions**.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **263 passed, 1337 assertions**.
- `[x]` 2026-05-17 · `npm run build` passed.
- `[x]` 2026-05-17 · Settings management UI/action added for open/closed state, capacity, registration window, and payment requirement.
- `[x]` 2026-05-17 · PDF export added through `document_exports` queue and `GenerateDocumentExportContentAction`.
- `[x]` 2026-05-17 · Browser smoke passed for owner on `/events/registrations`; settings form, participant list, and PDF queue flash render.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/EventRegistrationTest.php tests/Feature/WorkspaceRouteSmokeTest.php tests/Unit/PlanDocumentExportActionTest.php` → **15 passed, 111 assertions**.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **267 passed, 1351 assertions**.
- `[x]` 2026-05-17 · `npm run build` passed.

---

### M22 · Payment & Ticketing

**Status:** `[x]` Complete.

#### Product Goal
Enable paid event registration via Midtrans (or compatible provider). Free and paid registrations coexist per event.

#### Scope to Build
- [x] `.env.example` variables: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`.
- [x] `ticket_tiers` table: `id`, `project_id`, `name`, `price`, `capacity`, `is_active`.
- [x] `payment_orders` table: `id`, `registration_id`, `tier_id`, `amount`, `status`, `provider_order_id`, `paid_at`, `expires_at`.
- [x] Midtrans webhook handler: verifies signature, updates order status.
- [x] Free tier: bypass payment, directly confirm registration.
- [x] Tests: fake webhook signature verification, order status transitions, capacity enforcement per tier.

#### Verification
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan migrate` applied `2026_05_16_000015_create_payment_ticketing_tables.php`.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan db:seed` added `Free Pass`, `Early Bird`, and `Regular` demo ticket tiers.
- `[x]` 2026-05-17 · Browser smoke passed for public `/events/seminar-karier-digital/register`; ticket tier selector renders free and paid tiers.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Feature/PaymentTicketingTest.php tests/Feature/EventRegistrationTest.php` → **18 passed, 101 assertions**.
- `[x]` 2026-05-17 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **274 passed, 1389 assertions**.
- `[x]` 2026-05-17 · `npm run build` passed.

#### Rules
- Never hardcode payment credentials.
- Webhook signature must always be verified server-side.
- Never confirm a registration without verified payment (for paid tiers).

---

### M23 · AI Assistant

**Status:** `[ ]` Not started.

#### Product Goal
Augment the Prokerin workflow with AI-powered suggestions — e.g., proposal drafting from project data, LPJ summary generation, task priority suggestions, dashboard insight summaries.

#### Scope to Build (define use case before any code)
- [ ] Define exact use cases and user-facing surfaces (which pages, which actions).
- [ ] `.env.example` variables: `AI_PROVIDER`, `AI_API_KEY`, `AI_MODEL`.
- [ ] `AiPromptAction` base class: wraps provider call, logs prompt hash + token count, enforces tenant data minimization.
- [ ] `DraftProposalWithAiAction` — takes project data, returns proposal section suggestions.
- [ ] `SummarizeLpjWithAiAction` — takes LPJ checklist + project data, returns summary narrative.
- [ ] `AiUsageLog` table: `id`, `organization_id`, `user_id`, `action_type`, `prompt_tokens`, `completion_tokens`, `created_at`.
- [ ] Permission guard: AI features gated by organization plan tier.
- [ ] Tests: prompt payload construction (no sensitive data leakage), permission guard.

#### Rules
- Never send member personal data (phone, email, KTP) to AI provider.
- Never start without an explicit use-case scoped spec.
- Provider must be swappable via env (not hardcoded to any vendor).

---

### M24 · Campus Dashboard B2B

**Status:** `[ ]` Not started.

#### Product Goal
Give campus administrators (e.g., Dean's office, Student Affairs) a read-only aggregate view across all student organizations on their campus — enabling oversight without operational access.

#### Scope to Build
- [ ] `campuses` table: `id`, `name`, `domain`, `admin_user_id`.
- [ ] `campus_organization_links` table: `id`, `campus_id`, `organization_id`.
- [ ] `super_admin` and `campus_admin` Spatie roles.
- [ ] `CampusDashboardPayloadAction` — aggregates metrics across linked orgs; strict org data isolation (no cross-campus leakage).
- [ ] Inertia page: read-only analytics dashboard (project counts, finance totals, LPJ rates, active members).
- [ ] Tests: campus_admin can see linked orgs only; cannot see unlinked orgs; cannot mutate any data.

---

## Next Action (Ordered Priority)

### After M28.5
1. **Define M23 (AI Assistant) use cases and data minimization design** before writing code.
2. **M24 (Campus Dashboard)** as the B2B/enterprise growth layer.
3. **Before starting the next module, run baseline verification if the working tree is dirty or dependencies changed**:
   ```bash
   npm run build
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
   ```

---

## Product Roadmap Context (For Market Strategy)

The following context is provided to help AI agents and contributors understand the broader product vision and prioritize correctly.

### Target Market: Indonesian Student Organizations
- **Primary segment:** BEM (Badan Eksekutif Mahasiswa), HIMA (Himpunan Mahasiswa), UKM (Unit Kegiatan Mahasiswa).
- **Geography (initial):** Java — Surabaya, Malang, Yogyakarta, Bandung.
- **Pain points addressed:** Late proker planning, slow proposal approval, untracked task delegation, messy project finances, scattered Google Drive documentation, delayed LPJ submission, failed board handover.

### Go-To-Market Phases
1. **MVP validation** (M01–M13): Free tier for 1 organization, 1 active period, up to 20 members.
2. **Growth (Post-MVP Wave 1)** (M14–M16): Add QR attendance and digital certificates — strong viral loop as members share certificates on LinkedIn/social.
3. **Retention (Post-MVP Wave 2)** (M17–M19): WhatsApp reminders, advanced approval, handover — makes renewal automatic.
4. **Monetization (Post-MVP Wave 3)** (M21–M22): Event registration + ticketing — revenue-sharing model with organizations.
5. **B2B / Enterprise** (M24): Campus-level dashboard sold to Student Affairs departments at universities.

### Key Differentiators vs Indonesian Market
1. **Proker-native:** Built specifically around Indonesian student organization workflows (proker → proposal → RAB → LPJ) — not a generic project management tool.
2. **Template system:** One-click proker generation from templates eliminates 80% of planning overhead.
3. **LPJ automation:** LPJ is generated from actual execution data — not a blank document.
4. **QR attendance:** Purpose-built for rapat (meetings) and proker events — not a standalone HR tool.
5. **Digital certificates:** Viral growth lever — members share certificates, organizations get exposure.
6. **WhatsApp-first notifications:** Indonesian users live on WhatsApp; email-only tools lose engagement.

### Technical Moat to Build
- **Data continuity:** Handover module (M19) means institutional memory survives board transitions — a unique lock-in.
- **Compliance-ready:** Audit trail, signed documents, and multi-level approval (M18) positions Prokerin for accredited university adoption.
- **AI layer (M23):** Once data is rich, AI-generated proposal drafts and LPJ summaries become a strong upsell.

---

## Important Commit History

| Hash | Message |
|------|---------|
| `2246a35` | `feat: add role-aware sidebar menu (M28.5)` |
| `f712cce` | `feat: add role-aware dashboard variants (M28.5)` |
| `7815d21` | `feat: add dashboard role resolver (M28.5)` |
| `f2c148a` | `docs: record MVP verification handoff` |
| `4f37fb5` | `docs: mark MVP validation complete` |
| `af3de2a` | `feat: add meeting minutes module` |
| `82044d8` | `feat: add qr attendance module` |

---

_Last updated: 2026-05-17. Update this file immediately after every module status change, migration addition, test result, or Next Action change._
