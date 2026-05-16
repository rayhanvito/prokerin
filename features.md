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
| Post-MVP Wave 1 | M14–M15 | ✅ Complete (initial scope) |
| Post-MVP Active | M16 | 〜 Partial implementation verified |
| Post-MVP Planned | M17–M24 | 🔲 Not started |

**Current active risk:** Shell default still points to PHP 8.3. Always prefix Composer/Artisan with `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH` until Homebrew PHP is relinked.

---

## Verification Log

All entries are recorded in reverse-chronological order. Always add a new entry when a module is verified.

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

**Status:** `[~]` Partial implementation verified. **← CURRENT ACTIVE TARGET**

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
- [~] `CreateCertificateTemplateAction` — create/update with org scope + validation exists; dedicated edit UI still needs polish.
- [x] `IssueCertificateBatchAction` — bulk issue to list of recipients; triggers PDF job per recipient.
- [~] `GenerateCertificatePdfJob` (queued) — renders HTML template → DomPDF fallback → uploads to S3 → stores `pdf_path`. Browsershot engine swap remains a polish item.
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
- [~] `resources/js/Pages/Certificates/Templates.tsx` — template list + create form exists; edit/activate toggle and richer preview still pending.
- [x] `resources/js/Pages/Certificates/Issue.tsx` — select template, select recipients (from members or manual), preview, issue.
- [~] `resources/js/Pages/Certificates/Verify.tsx` — public verification page (unauthenticated) shows certificate details; QR code visual still pending.
- [x] Sidebar: "Sertifikat Digital" with badge `M16`.

#### Test Coverage Required (before marking `[x]`)
- [x] Unit: `CertificateNumberGenerator` — unique per org per year, sequential, no collision.
- [x] Feature: issue certificate → PDF job dispatched → `pdf_path` stored after job.
- [x] Feature: verify by `verification_token` → returns correct recipient data.
- [x] Feature: cross-tenant rejection — cannot view/download another org's certificates.
- [x] Feature: public verification route is accessible without authentication.
- [x] Feature: non-owner/admin cannot issue certificates.

#### Verification
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test tests/Unit/CertificateNumberGeneratorTest.php tests/Feature/DigitalCertificateTest.php` → **10 passed, 58 assertions**.
- `[x]` 2026-05-16 · `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → **200 passed, 865 assertions**.
- `[x]` 2026-05-16 · `npm run build` passed.
- `[x]` 2026-05-16 · Browser smoke passed for `/certificates`, `/certificates/templates`, `/certificates/issue`, and public `/verify/11111111-1111-4111-8111-111111111111`.

#### Remaining Before `[x]`
- [ ] Add richer template edit/activate UX instead of routing edit back to the create/list page.
- [ ] Add a scannable QR code visual on the public verification page.
- [ ] Decide whether to install/configure Browsershot for certificate PDF generation or keep DomPDF fallback as the accepted MVP engine.

#### Commit Message Convention
`feat: add digital certificate module (M16)`

---

### M17 · WhatsApp Reminder

**Status:** `[ ]` Not started.

#### Product Goal
Send proker deadline reminders, approval notifications, and meeting alerts directly to members' WhatsApp — increasing response speed vs email alone.

#### Scope to Build
- [ ] `.env.example` variables: `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`, `WHATSAPP_FROM_NUMBER`.
- [ ] `WhatsAppNotificationChannel` — Laravel notification channel abstraction (implements `send()`).
- [ ] `SendWhatsAppReminderJob` (queued) — wraps HTTP call to provider; handles timeout.
- [ ] `whatsapp_delivery_logs` table: `id`, `organization_id`, `user_id`, `message_type`, `status` (queued/sent/failed), `provider_response`, `sent_at`, `failed_at`.
- [ ] Retry logic: 3 attempts with exponential backoff; mark `failed` after exhaustion.
- [ ] Tenant/user opt-in guard: only send if user has WhatsApp delivery enabled in `notification_rules`.
- [ ] Admin UI: WhatsApp delivery log per organization.

#### Rules
- Never hardcode provider token, URL, or phone number in code.
- Never send real messages from tests or local — use a fake provider class.
- Fake provider must implement the same interface as real provider.

#### Test Coverage Required
- [ ] Unit: `WhatsAppNotificationChannel` sends via fake provider.
- [ ] Feature: job dispatched when task deadline < 24h.
- [ ] Feature: delivery log written on success and failure.
- [ ] Feature: opt-out user does not receive message.

---

### M18 · Multi-Level Approval Workflow

**Status:** `[ ]` Not started.

#### Product Goal
Replace single-approver model with configurable multi-level approval chains for Proposal, RAB, and LPJ — supporting organizations that require Treasurer → Chair → Advisor sign-off sequences.

#### Scope to Build
- [ ] `approval_workflow_definitions` table: `id`, `organization_id`, `workflow_type` (proposal/rab/lpj), `steps` (JSON ordered array of role_id/user_id).
- [ ] `approval_instances` table: `id`, `workflow_definition_id`, `subject_type` (morphable), `subject_id`, `status`, `current_step`.
- [ ] `approval_step_records` table: `id`, `instance_id`, `step_order`, `approver_id`, `decision` (approved/rejected/revision), `note`, `decided_at`.
- [ ] `ProcessApprovalStepAction` — advances or terminates workflow; triggers next step notification.
- [ ] `DelegateApprovalAction` — reassign a pending step to another eligible member.
- [ ] Audit trail: every step decision is immutable once recorded.
- [ ] UI: approval queue per user (what I need to approve), workflow status timeline per subject.

#### Test Coverage Required
- [ ] Feature: full workflow executes in order (step 1 → step 2 → approved).
- [ ] Feature: rejection at step 2 terminates workflow and notifies submitter.
- [ ] Feature: revision request sends subject back to submitter.
- [ ] Feature: cross-tenant — user cannot approve another org's workflow instance.
- [ ] Feature: delegate reassignment is logged.

---

### M19 · Handover Kepengurusan (Board Transition)

**Status:** `[~]` Partial scaffold only.

#### What Already Exists
- Route/page `organization.handover`.
- Viho-style planning/readiness UI scaffold (display only).

#### What Still Needs to Be Built
- [ ] `handover_packages` table: `id`, `organization_id`, `from_period_id`, `to_period_id`, `created_by`, `status`, `submitted_at`, `accepted_at`.
- [ ] `handover_items` table: `id`, `package_id`, `category` (asset/document/role/finance), `label`, `description`, `status`, `assignee_id`.
- [ ] Data snapshot: capture project statuses, finance balances, open tasks, and outstanding LPJ at handover freeze time.
- [ ] `InitiateHandoverAction` — creates package, auto-generates items from active data.
- [ ] Handover checklist UI: item-by-item completion by responsible members.
- [ ] Archive/export handover package as PDF.
- [ ] Access policy: only outgoing owner + incoming owner can manage handover.

---

### M20 · Sponsor & Vendor Database

**Status:** `[ ]` Not started.

#### Product Goal
Maintain a reusable contact book of sponsors and vendors per organization — searchable by category, linked to historical projects, with contact person and document tracking.

#### Scope to Build
- [ ] `sponsors_vendors` table: `id`, `organization_id`, `type` (sponsor/vendor), `name`, `category`, `contact_person`, `phone`, `email`, `address`, `status` (active/inactive), `notes`.
- [ ] `sponsor_vendor_project_links` table: `id`, `sponsor_vendor_id`, `project_id`, `role_description`, `amount`, `linked_at`.
- [ ] `sponsor_vendor_documents` table: `id`, `sponsor_vendor_id`, `document_id` (FK to documents).
- [ ] Inertia pages: list (searchable/filterable), detail (with history), create/edit form.
- [ ] Tests: tenant scoping, CRUD authorization (admin+ only), cross-org read prevention.

---

### M21 · Event Registration (Public)

**Status:** `[ ]` Not started.

#### Product Goal
Allow non-members of an organization to register for public events/projects — enabling BEM/UKM to run ticketed or open events.

#### Scope to Build
- [ ] `event_registrations` tables: `id`, `project_id`, `participant_name`, `participant_email`, `phone`, `institution`, `status` (pending/confirmed/cancelled), `registered_at`.
- [ ] `event_registration_settings` table: `id`, `project_id`, `is_open`, `capacity`, `opens_at`, `closes_at`, `require_payment`.
- [ ] Public route: `GET /events/{project_slug}/register` — unauthenticated form.
- [ ] Guards: capacity check, duplicate email per event, registration window check.
- [ ] Confirmation email sent on successful registration.
- [ ] Internal UI: registration list, export to CSV/PDF.
- [ ] Tests: capacity enforcement, duplicate email rejection, tenant/project scope isolation.

---

### M22 · Payment & Ticketing

**Status:** `[ ]` Not started. **DO NOT START BEFORE M21 IS STABLE.**

#### Product Goal
Enable paid event registration via Midtrans (or compatible provider). Free and paid registrations coexist per event.

#### Scope to Build
- [ ] `.env.example` variables: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`.
- [ ] `ticket_tiers` table: `id`, `project_id`, `name`, `price`, `capacity`, `is_active`.
- [ ] `payment_orders` table: `id`, `registration_id`, `tier_id`, `amount`, `status`, `provider_order_id`, `paid_at`, `expires_at`.
- [ ] Midtrans webhook handler: verifies signature, updates order status.
- [ ] Free tier: bypass payment, directly confirm registration.
- [ ] Tests: fake webhook signature verification, order status transitions, capacity enforcement per tier.

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

### Immediate M16 Polish
1. **Add richer template edit/activate UX** — edit route exists, but the UI still needs a selected-template edit state and active/inactive toggle.
2. **Add QR code visual to public verification page** — keep it client-rendered or generated from a local package; do not depend on an unaudited external image endpoint.
3. **Decide PDF engine** — either install/configure Browsershot for certificate PDFs or explicitly accept DomPDF fallback for M16 MVP.
4. **Run verification again**:
   ```bash
   npm run build
   PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
   ```
5. **If polish is complete, mark M16 `[x]` and commit:** `feat: add digital certificate module (M16)`

### After M16
6. **Evaluate M19 (Handover)** — partial scaffold exists; prioritize over M17/M18 if the next semester handover deadline is approaching.
7. **Start M17 (WhatsApp Reminder)** if notification engagement is a growth lever.
8. **Start M18 (Multi-Level Approval)** if enterprise/academic institution clients need it.
9. **Start M21 (Event Registration)** when Prokerin is ready to enable public-facing event management.
10. **M22 (Payment)** only after M21 is stable.
11. **M23 (AI Assistant)** only after defining explicit use cases and completing data minimization design.
12. **M24 (Campus Dashboard)** as the B2B/enterprise growth layer.

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

### Pricing Strategy (Suggested)
| Tier | Target | Features |
|------|--------|----------|
| Free | 1 org, up to 20 members | M01–M13 (MVP) |
| Starter (Rp 99K/month) | Small HIMA | + M14 meetings, M15 QR attendance |
| Pro (Rp 299K/month) | BEM/UKM | + M16 certificates, M17 WhatsApp, M18 approval |
| Campus (custom) | University | M24 dashboard, all features, SLA |

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
| `f2c148a` | `docs: record MVP verification handoff` |
| `4f37fb5` | `docs: mark MVP validation complete` |
| `af3de2a` | `feat: add meeting minutes module` |
| `82044d8` | `feat: add qr attendance module` |

---

_Last updated: 2026-05-16. Update this file immediately after every module status change, migration addition, test result, or Next Action change._
