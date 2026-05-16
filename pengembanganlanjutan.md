# Prokerin — Extended Feature Specification (Wave 2 & Beyond)

> **This document extends `features.md`.** Read `features.md` first for M01–M24 and the full project context.
> This file covers M25–M40 (new modules discovered in product analysis) and L01 (Landing Page).
> Same rules apply: never mark `[x]` without code + tests + verification log entry.

---

## Status Summary (Extended Roadmap)

| Phase | Range | Status |
|-------|-------|--------|
| Wave 2 — UX Critical | M25–M28 | 🔲 Not started — high priority |
| Wave 2 — Growth | M29–M32 | 🔲 Not started |
| Wave 3 — Enterprise | M33–M35 | 🔲 Not started |
| Wave 3 — Platform | M36–M38 | 🔲 Not started |
| Landing Page | L01 | 🔲 Not started — can run parallel to any wave |

---

## Build Order Recommendation

Do not start a module if the full test suite is red.
Recommended sequence within each wave is top-to-bottom.
Landing page (L01) can and should be built in parallel — it is a separate codebase.

```
Priority Stack (highest → lowest):
  1. M25 — Rich Text Editor           (critical UX gap)
  2. M26 — Real-Time Notifications    (approval flow blocker)
  3. M27 — Mobile QR Camera Scanner   (M15 unusable without this)
  4. M28 — Onboarding Wizard          (activation rate fix)
  5. L01  — Landing Page              (parallel, marketing)
  6. M29 — Global Search
  7. M30 — Kepanitiaan Mode
  8. M31 — Public Proker Microsite
  9. M32 — Template Marketplace
 10. M33 — Prokerin Academy
 11. M34 — Smart Deadline Prediction
 12. M35 — Vendor Rating & Review
 13. M36 — Discord/WhatsApp Bot
 14. M37 — Organizational Health Analytics
 15. M38 — Prokerin Pay (Dompet Proker)
```

---

## Wave 2 — UX Critical Fixes

These modules fix gaps that cause user churn even if the core MVP works perfectly.

---

### M25 · Rich Text Editor (Proposal & LPJ)

**Status:** `[ ]` Not started.

#### Product Goal
Replace plain textarea in Proposal (M08) and LPJ (M10) editors with a full WYSIWYG editor. Without this, users still write in Microsoft Word and paste plain text — Prokerin becomes a submission portal, not a work tool.

#### Technology Decision
Use **Tiptap** (https://tiptap.dev) — React-compatible, headless, extensible, MIT licensed. Do NOT use Quill (unmaintained) or CKEditor (too heavy).

#### Extensions to Enable
- Bold, Italic, Underline, Strikethrough
- Heading 1, 2, 3
- Bullet list, Ordered list
- Blockquote
- Table (insert, add/remove rows/cols)
- Horizontal rule
- Character count (for section length limits)
- Placeholder text per section

#### Database Changes
- No new tables. Change `proposal_sections.body` and `lpj_sections.body` column type from `TEXT` to `LONGTEXT` (MySQL).
- Store content as **JSON (Tiptap/ProseMirror format)**, not HTML. Render HTML only at display/export time.
- Add migration: `alter_proposal_and_lpj_sections_body_to_longtext.php` (additive, non-destructive).

#### Backend to Build
- [ ] `RenderProposalSectionHtmlAction` — converts stored JSON → HTML for PDF/DOCX export.
- [ ] `SanitizeRichTextAction` — strips disallowed tags/attributes before persistence (server-side, not just client).
- [ ] Update `StoreProposalSectionRequest` and `StoreLpjSectionRequest` validation: accept JSON string, validate structure.

#### Frontend to Build
- [ ] Install: `npm install @tiptap/react @tiptap/starter-kit @tiptap/extension-table @tiptap/extension-character-count`
- [ ] `resources/js/Components/Editor/RichTextEditor.tsx` — reusable Tiptap wrapper component.
- [ ] `resources/js/Components/Editor/EditorToolbar.tsx` — formatting toolbar (bold, heading, list, table buttons).
- [ ] Update `resources/js/Pages/Proposals/Edit.tsx` — replace `<textarea>` with `<RichTextEditor>`.
- [ ] Update `resources/js/Pages/Lpj/Edit.tsx` — same replacement.
- [ ] `resources/js/Components/Editor/RichTextRenderer.tsx` — read-only renderer for display pages (not edit mode).

#### Export Integration
- [ ] Update `GenerateProposalPdfJob` — use `RenderProposalSectionHtmlAction` output as input to Browsershot.
- [ ] Update `GenerateLpjPdfJob` — same.
- [ ] Update `GenerateProposalDocxJob` — parse JSON → PHPWord elements (heading, paragraph, table, list).

#### Test Coverage Required
- [ ] Unit: `SanitizeRichTextAction` strips `<script>` and `onclick` attributes.
- [ ] Unit: `RenderProposalSectionHtmlAction` correctly converts headings, lists, tables from JSON → HTML.
- [ ] Feature: save section with rich content → retrieve → content matches.
- [ ] Feature: locked proposal cannot be edited (existing test must still pass with new editor).
- [ ] Feature: PDF export job triggered → file generated (smoke test, not visual regression).

#### Commit
`feat: add rich text editor for proposal and lpj sections (M25)`

---

### M26 · Real-Time In-App Notifications

**Status:** `[ ]` Not started.

#### Product Goal
Replace polling/manual-refresh notification pattern with real-time push via WebSocket. When a proposal is approved, the submitter sees the bell badge update instantly — without refreshing the page.

#### Technology Stack
- **Laravel Echo** (already in Laravel ecosystem)
- **Soketi** (self-hosted Pusher-compatible server — free, runs on VPS) OR **Pusher** (managed, paid after free tier)
- **Laravel Broadcasting** with `database` or `redis` driver for event persistence

#### `.env.example` Variables to Add
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=127.0.0.1        # use Soketi host for self-hosted
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### Database Changes
- No new tables. Use Laravel's built-in `notifications` table (already created in M12).
- Add `read_at` index on `notifications` table for faster unread count queries.

#### Backend to Build
- [ ] Install: `composer require pusher/pusher-php-server`
- [ ] Configure `config/broadcasting.php` for Pusher/Soketi.
- [ ] `NotificationBroadcastEvent` — broadcastable Laravel event, sends to private channel `App.Models.User.{id}`.
- [ ] Update all existing Notification classes (task deadline, proposal approved, LPJ revision, etc.) to implement `ShouldBroadcast`.
- [ ] `MarkNotificationReadAction` — marks single or all notifications as read; Axios endpoint (not full Inertia reload).
- [ ] Route: `POST /notifications/{id}/read` and `POST /notifications/read-all`.

#### Frontend to Build
- [ ] Install: `npm install laravel-echo pusher-js`
- [ ] `resources/js/lib/echo.ts` — Echo initialization with auth.
- [ ] `resources/js/hooks/useNotifications.ts` — subscribes to private channel, manages local notification state, badge count.
- [ ] Update `VihoHeader.tsx` — bell icon with live badge count from `useNotifications`.
- [ ] `resources/js/Components/Notifications/NotificationDropdown.tsx` — dropdown list of recent notifications, mark-read button, "see all" link.
- [ ] `resources/js/Pages/Notifications/Index.tsx` — full notification history page with filters (unread, by type).

#### Test Coverage Required
- [ ] Feature: notification created → appears in database → `read_at` is null.
- [ ] Feature: mark single notification read → `read_at` populated.
- [ ] Feature: mark all read → all user notifications have `read_at`.
- [ ] Feature: cross-user — user A cannot mark user B's notification as read.
- [ ] Unit: broadcast event has correct channel name format.

#### Commit
`feat: add real-time in-app notifications via websocket (M26)`

---

### M27 · Mobile QR Camera Scanner (PWA)

**Status:** `[ ]` Not started.

#### Product Goal
M15 (QR Attendance) backend is production-ready but the frontend only accepts a manually typed/pasted token string. In the real world, members scan a printed/displayed QR code with their phone camera. This module makes M15 actually usable in the field.

#### Technology Decision
Use **`html5-qrcode`** library (MIT, no native app required, works in Chrome/Safari PWA). Do NOT use a native mobile app — PWA is sufficient and aligns with the project's PWA-first strategy.

#### PWA Setup (prerequisite)
- [ ] Create `public/manifest.json`:
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
- [ ] Create icons: 192×192 and 512×512 Prokerin logo PNG in `public/icons/`.
- [ ] Register service worker in `resources/js/app.tsx` (minimal — cache shell only for now, full offline in M33).
- [ ] Add `<link rel="manifest">` and `<meta name="theme-color">` to `resources/views/app.blade.php`.

#### QR Scanner Feature
- [ ] Install: `npm install html5-qrcode`
- [ ] `resources/js/Components/Attendance/QrCameraScanner.tsx`:
  - Requests camera permission.
  - Shows live viewfinder.
  - On decode: calls `attendance.check-in.store` via Inertia POST.
  - Shows success toast (member name + session name) or error (expired, already scanned, not a member).
  - Auto-resets after 2 seconds for next scan (continuous scan mode).
  - "Use Manual Code" fallback button if camera unavailable.
- [ ] Update `resources/js/Pages/Attendance/Index.tsx`:
  - Add "Scan QR" button → opens scanner modal.
  - Owner/admin: "Generate QR" button for their session → shows QR image.
- [ ] `resources/js/Components/Attendance/QrCodeDisplay.tsx`:
  - Install: `npm install qrcode.react`
  - Renders scannable QR code image from token string.
  - Shows expiry countdown timer.
  - "Regenerate" button (calls revoke + create token endpoint).
- [ ] New backend routes:
  - `POST /attendance/sessions/{session}/qr-token/generate` — creates new QR token, returns token string.
  - `POST /attendance/sessions/{session}/qr-token/revoke` — revokes current active token.
  - Role guard: owner/admin/secretary/project_lead only for generate/revoke.

#### Test Coverage Required
- [ ] Feature: generate QR token → token stored as SHA-256, expiry set correctly.
- [ ] Feature: revoke → token `revoked_at` populated, subsequent check-in with that token rejected.
- [ ] Feature: generate new token after revoke → new token works.
- [ ] Feature: unauthorized role cannot generate token.
- [ ] (Manual): open `/attendance` on mobile browser → "Scan QR" → camera opens → scan valid QR → success toast.

#### Commit
`feat: add PWA manifest and QR camera scanner for attendance (M27)`

---

### M28 · Onboarding Wizard

**Status:** `[ ]` Not started.

#### Product Goal
New organizations that register land on an empty dashboard with no guidance. The activation rate (organizations that create their first proker within 24 hours) is likely very low. An onboarding wizard eliminates this friction by guiding the owner through the 5 actions that make Prokerin useful immediately.

#### Trigger Logic
- Show wizard when: authenticated user is organization owner AND `organization_periods` count = 0 OR `projects` count = 0 AND `onboarding_completed_at` IS NULL.
- Once wizard is dismissed or completed, set `organizations.onboarding_completed_at = now()` and never show again.

#### Database Changes
- [ ] Add column: `organizations.onboarding_completed_at` (nullable timestamp). Migration: `add_onboarding_completed_at_to_organizations.php`.

#### Wizard Steps (5 steps, skippable individually, completable at any time)

**Step 1 — Buat Periode Aktif**
- Input: period name (e.g., "2025/2026"), start date, end date.
- Creates `organization_periods` record.
- Completion indicator: ✅ if period exists.

**Step 2 — Undang Anggota Inti**
- Input: email addresses (multi-input), role per email.
- Sends invitations (uses existing M03 invitation flow).
- Completion indicator: ✅ if at least 1 invitation sent.

**Step 3 — Buat Proker Pertama**
- Shows template picker (uses M05 templates).
- One-click generate from template.
- Completion indicator: ✅ if at least 1 project exists.

**Step 4 — Setup RAB Awal**
- Pre-fills from template budget lines if template was used.
- Quick edit: adjust amounts.
- Completion indicator: ✅ if at least 1 budget line exists.

**Step 5 — Preview Dashboard**
- Shows animated preview of what the dashboard will look like when data is filled.
- "Selesai Setup" button → marks `onboarding_completed_at`, redirects to dashboard.

#### Frontend to Build
- [ ] `resources/js/Components/Onboarding/OnboardingWizard.tsx` — modal overlay wizard, step indicator, prev/next navigation.
- [ ] `resources/js/Components/Onboarding/steps/Step1Period.tsx`
- [ ] `resources/js/Components/Onboarding/steps/Step2InviteMembers.tsx`
- [ ] `resources/js/Components/Onboarding/steps/Step3CreateProker.tsx`
- [ ] `resources/js/Components/Onboarding/steps/Step4SetupRab.tsx`
- [ ] `resources/js/Components/Onboarding/steps/Step5Preview.tsx`
- [ ] Show wizard in `AppLayout.tsx` if `showOnboarding: true` is passed via Inertia shared props.

#### Backend to Build
- [ ] `CheckOnboardingStatusAction` — returns `showOnboarding: bool` based on trigger logic.
- [ ] Update `HandleInertiaRequests` middleware — include `showOnboarding` in shared props.
- [ ] `CompleteOnboardingAction` — sets `onboarding_completed_at`.
- [ ] Route: `POST /onboarding/complete`.

#### Test Coverage Required
- [ ] Feature: new org with no periods → `showOnboarding: true` in shared props.
- [ ] Feature: complete onboarding → `onboarding_completed_at` set → `showOnboarding: false`.
- [ ] Feature: non-owner member does not see wizard (owner-only trigger).

#### Commit
`feat: add onboarding wizard for new organizations (M28)`

---

## Wave 2 — Growth Features

---

### M29 · Global Search

**Status:** `[ ]` Not started.

#### Product Goal
As organizations accumulate data over semesters, finding anything without search becomes painful. A global search bar (Cmd+K / Ctrl+K) that instantly finds proker, tasks, members, documents, and meetings.

#### Technology Decision
Use **Laravel Scout** with **MeiliSearch** (self-hosted, fast, typo-tolerant, free) or **Algolia** (managed, generous free tier). Do NOT implement naive SQL LIKE search — it won't scale and won't support typo tolerance.

#### Indexed Models (all tenant-scoped)
- `Project` — name, description, status, period name.
- `Task` — title, description, project name, assignee name.
- `Document` — name, category (visibility-filtered: only show docs user has access to).
- `Meeting` — title, agenda, date.
- `User` (org members only) — name, email, role.

#### Backend to Build
- [ ] Install: `composer require laravel/scout` + MeiliSearch driver.
- [ ] Add `Searchable` trait to: `Project`, `Task`, `Document`, `Meeting`.
- [ ] `toSearchableArray()` on each model — include `organization_id` for tenant filtering.
- [ ] `GlobalSearchAction` — runs Scout search across all models, merges results, applies tenant scope, respects document visibility.
- [ ] Route: `GET /search?q={query}` — returns JSON (Axios, not Inertia full page).
- [ ] Queue `php artisan scout:import` for initial index population.
- [ ] `.env.example`: `SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST=`, `MEILISEARCH_KEY=`.

#### Frontend to Build
- [ ] `resources/js/Components/Search/GlobalSearchBar.tsx` — trigger: Cmd+K / Ctrl+K or click search icon in header. Opens modal overlay.
- [ ] `resources/js/Components/Search/SearchResultGroup.tsx` — grouped results by type (Proker, Task, Dokumen, Rapat, Anggota) with icons.
- [ ] Keyboard navigation: arrow keys to select, Enter to navigate to result, Escape to close.
- [ ] Debounced search: 300ms after last keystroke.
- [ ] Recent searches stored in localStorage (client only, no server persistence needed).

#### Test Coverage Required
- [ ] Feature: search "ospek" → returns projects with "ospek" in name, tenant-scoped.
- [ ] Feature: org A member cannot see org B's search results.
- [ ] Feature: private document not returned in search for user without access.
- [ ] Unit: `GlobalSearchAction` merges results correctly.

#### Commit
`feat: add global search with MeiliSearch (M29)`

---

### M30 · Kepanitiaan Mode (Ad-Hoc Committee)

**Status:** `[ ]` Not started.

#### Product Goal
Many large events (Ospek, Dies Natalis, Wisuda, Seminar Nasional) are run by temporary committees that don't belong to a permanent organization structure. They need Prokerin's full feature set but without the overhead of setting up a full organization with periods and roles. This unlocks a market segment significantly larger than permanent ormawa.

#### Key Differences from Organization Mode
| Aspect | Organization Mode | Kepanitiaan Mode |
|--------|-----------------|-----------------|
| Lifecycle | Permanent, multi-period | Single event, auto-dissolves |
| Hierarchy | Full role matrix | Simplified: Ketua Panitia, Sekretaris, Bendahara, Divisi, Anggota |
| Periods | Required | Not applicable |
| Handover | Required (M19) | Not applicable |
| Billing | Per-org subscription | Per-event flat fee or free tier |

#### Database Changes
- [ ] Add column: `organizations.mode` ENUM('organization', 'kepanitiaan') DEFAULT 'organization'.
- [ ] Add column: `organizations.event_date` (nullable date) — target event date for kepanitiaan.
- [ ] Add column: `organizations.auto_archive_at` (nullable timestamp) — set to 90 days after `event_date`.
- [ ] Migration: `add_kepanitiaan_mode_to_organizations.php`.

#### Backend to Build
- [ ] `CreateKepanitiaanAction` — creates org with mode=kepanitiaan, no period required, simplified role setup.
- [ ] `AutoArchiveKepanitiaanJob` (scheduled) — runs daily, archives kepanitiaan orgs where `auto_archive_at` < now().
- [ ] Update organization feature guards: hide period management, handover, and post-MVP modules that don't apply to kepanitiaan mode.
- [ ] `KepanitiaanDashboardPayloadAction` — simplified dashboard: event countdown, task completion %, budget summary, attendance summary.

#### Frontend to Build
- [ ] Registration flow: "Buat Organisasi" → choose mode: "Organisasi Permanen" or "Kepanitiaan Event".
- [ ] Kepanitiaan creation form: committee name, event name, event date, description.
- [ ] `resources/js/Pages/KepanitiaanDashboard/Index.tsx` — event countdown hero, simplified metrics.
- [ ] Conditionally hide sidebar items not applicable in kepanitiaan mode (period management, handover, etc.).

#### Test Coverage Required
- [ ] Feature: create kepanitiaan → mode set correctly, no period required.
- [ ] Feature: kepanitiaan dashboard returns countdown and correct metrics.
- [ ] Feature: auto-archive job archives correct orgs and skips active ones.
- [ ] Feature: kepanitiaan member cannot access period management routes.

#### Commit
`feat: add kepanitiaan mode for ad-hoc committees (M30)`

---

### M31 · Public Proker Microsite

**Status:** `[ ]` Not started.

#### Product Goal
Each proker/event gets a public-facing page accessible without login. This serves as both a promotional page (share to social media) and an entry point for M21 (Event Registration). It's the primary organic growth loop — every event shared on Instagram/LINE brings new users to see Prokerin.

#### URL Structure
`prokerin.id/e/{org-slug}/{proker-slug}`
Example: `prokerin.id/e/bem-ub/ospek-maba-2025`

#### Page Content (controlled by org admin, selectable what to show)
- Event name, banner image, description (rich text).
- Date, time, location (online/offline).
- Organizing committee name and logo.
- Countdown timer to event.
- Contact person (WhatsApp link, email).
- Registration button (if M21 is enabled for this proker).
- Gallery (photo upload, post-event).
- "Powered by Prokerin" badge (with link to landing page — organic SEO + branding).

#### Database Changes
- [ ] `proker_microsites` table: `id`, `project_id`, `is_public` (bool), `banner_image_path`, `location_text`, `location_maps_url`, `contact_name`, `contact_whatsapp`, `contact_email`, `show_countdown` (bool), `show_committee` (bool), `show_gallery` (bool), `meta_title`, `meta_description`, `published_at`.
- [ ] `proker_microsite_gallery` table: `id`, `microsite_id`, `image_path`, `caption`, `order`.

#### Backend to Build
- [ ] `UpdateMicrositeSettingsAction` — manages microsite content.
- [ ] `PublishMicrositeAction` — sets `published_at`, validates required fields.
- [ ] `GetMicrositePayloadAction` — public, no auth, returns microsite data with project info.
- [ ] Route (public, no auth): `GET /e/{org_slug}/{proker_slug}` → renders microsite.
- [ ] Route (authenticated): `GET /proker/{slug}/microsite` → settings editor.
- [ ] Gallery upload endpoint with image resize/optimization (Intervention Image).
- [ ] `<meta>` tags for Open Graph (Facebook/WhatsApp preview) and Twitter Card.

#### Frontend to Build
- [ ] `resources/js/Pages/Microsite/Show.tsx` — public page, no AppLayout, custom branded layout.
- [ ] `resources/js/Pages/Microsite/Settings.tsx` — settings editor inside authenticated app.
- [ ] `resources/js/Components/Microsite/CountdownTimer.tsx` — live countdown.
- [ ] `resources/js/Components/Microsite/GalleryGrid.tsx` — responsive photo grid.

#### Test Coverage Required
- [ ] Feature: unpublished microsite returns 404 for unauthenticated user.
- [ ] Feature: published microsite accessible without auth.
- [ ] Feature: cross-org — org A cannot edit org B's microsite.
- [ ] Feature: `meta_title` and Open Graph tags present in rendered HTML.

#### Commit
`feat: add public proker microsite with SEO and gallery (M31)`

---

### M32 · Template Marketplace

**Status:** `[ ]` Not started.

#### Product Goal
Organizations that have built good proker templates can share them to the community. Prokerin curates the best ones. This creates a network effect — more users → more templates → more value for everyone → more users.

#### Template Types
- Proker templates (tasks + RAB + proposal outline + LPJ checklist).
- Budget line templates (RAB presets by event type).
- Proposal section templates (section structure and sample content).

#### Database Changes
- [ ] `marketplace_templates` table: `id`, `source_organization_id`, `submitted_by_user_id`, `type` (proker/rab/proposal), `name`, `description`, `category` (social/academic/sports/arts/religious/community), `status` (draft/submitted/approved/rejected), `use_count`, `rating_avg`, `is_featured`, `approved_at`, `approved_by`.
- [ ] `marketplace_template_ratings` table: `id`, `template_id`, `organization_id`, `rating` (1-5), `review_text`, `rated_at`.
- [ ] `marketplace_template_data` table: `id`, `template_id`, `data` (JSON — actual template content).

#### Backend to Build
- [ ] `SubmitTemplateToMarketplaceAction` — copies org's template data, strips org-specific data, submits for review.
- [ ] `ApproveMarketplaceTemplateAction` (internal admin only via Filament).
- [ ] `UseMarketplaceTemplateAction` — clones template into requesting org, increments `use_count`.
- [ ] `RateMarketplaceTemplateAction` — one rating per org per template.
- [ ] Public route: `GET /marketplace` — no auth required (browsing is public).
- [ ] Authenticated route: `POST /marketplace/{id}/use` — requires login.

#### Frontend to Build
- [ ] `resources/js/Pages/Marketplace/Index.tsx` — browsable grid: filter by category, sort by popular/newest/rating.
- [ ] `resources/js/Pages/Marketplace/Show.tsx` — template detail: description, preview of task structure and budget lines, ratings, "Gunakan Template" button.
- [ ] `resources/js/Pages/Marketplace/Submit.tsx` — submit own template form (authenticated).
- [ ] Internal admin (Filament): template review queue, approve/reject with note.

#### Test Coverage Required
- [ ] Feature: submit template → status = submitted.
- [ ] Feature: approve template → status = approved, appears in marketplace.
- [ ] Feature: use template → cloned into org, `use_count` incremented.
- [ ] Feature: one rating per org per template enforced.
- [ ] Feature: unauthenticated user can browse but cannot use template.

#### Commit
`feat: add template marketplace (M32)`

---

## Wave 3 — Enterprise & Platform

---

### M33 · Prokerin Academy (In-App Learning)

**Status:** `[ ]` Not started.

#### Product Goal
Built-in micro-learning for new pengurus: how to write a proper proposal, how to make a compliant RAB, how to write LPJ that passes review, how to run an effective rapat. This is not just a help center — it's contextual guidance that appears at the right moment in the workflow.

#### Content Types
1. **Contextual Tips** — small callout shown when user first visits a module (e.g., first time on Proposal page: "Tips: Proposal yang kuat punya latar belakang yang menjawab 'Mengapa proker ini penting?'")
2. **Guides** — multi-step article with checklist (e.g., "Panduan Lengkap Buat RAB" → 8 steps with examples)
3. **Templates Explained** — annotated examples of good proposal/LPJ sections
4. **Video Embeds** — 3–5 minute YouTube videos from trusted sources (or Prokerin-produced)

#### Database Changes
- [ ] `academy_content` table: `id`, `module_trigger` (enum: proposal/rab/lpj/task/meeting/general), `type` (tip/guide/video), `title`, `body` (rich text JSON), `video_url`, `order`, `is_published`.
- [ ] `academy_progress` table: `id`, `user_id`, `content_id`, `completed_at` — tracks what user has read.

#### Backend to Build
- [ ] `GetAcademyContentAction` — returns contextual content for a given module trigger, filtered by user's progress (hide already-read tips).
- [ ] `MarkAcademyContentReadAction` — records completion.
- [ ] Filament resource for Academy content management (internal admin creates/edits content).
- [ ] Route: `GET /academy` — full content library.
- [ ] Route: `POST /academy/{content_id}/complete`.

#### Frontend to Build
- [ ] `resources/js/Components/Academy/ContextualTip.tsx` — dismissible callout with "Pelajari Lebih" expand option. Shown on first module visit.
- [ ] `resources/js/Components/Academy/AcademyDrawer.tsx` — slide-in drawer for guides, accessible from "?" icon in each module header.
- [ ] `resources/js/Pages/Academy/Index.tsx` — full learning library, grouped by topic, with completion progress indicator.
- [ ] Progress tracker: "Kamu sudah selesaikan 4/12 panduan" shown on dashboard.

#### Test Coverage Required
- [ ] Feature: tip shown on first visit, not shown after marked complete.
- [ ] Feature: `GetAcademyContentAction` returns only published content.
- [ ] Feature: cross-user — completion is per-user, not global.

#### Commit
`feat: add prokerin academy in-app learning system (M33)`

---

### M34 · Smart Deadline Prediction

**Status:** `[ ]` Not started.

#### Product Goal
Use historical execution data across all organizations (anonymized) to predict whether a proker is on track to meet its deadline. Surface a warning when the prediction model detects risk.

#### How It Works
1. Collect: for each completed project, record `planned_duration_days`, `actual_duration_days`, `task_count`, `member_count`, `budget_lines_count`, `revision_count` (proposal/LPJ revisions).
2. Simple linear regression model trained on this data to predict `expected_delay_days` for active projects.
3. Surface warning if `expected_delay_days > 7`: "Berdasarkan proker serupa, proker ini berisiko telat ~{N} hari."

#### Privacy Rules
- All training data is anonymized — no organization name, no project name, no member name in the model.
- Model trained server-side, never expose raw cross-org data.
- Feature can be disabled per organization via settings.

#### Database Changes
- [ ] `project_completion_stats` table: `id`, `project_id`, `planned_duration_days`, `actual_duration_days`, `task_count`, `member_count`, `budget_lines_count`, `revision_count`, `completed_at`. Populated by a job when project status → completed.

#### Backend to Build
- [ ] `RecordProjectCompletionStatsJob` — triggered when project archived/completed.
- [ ] `TrainDeadlinePredictionModelJob` (weekly scheduled) — retrains model from `project_completion_stats`, stores coefficients in `app_settings`.
- [ ] `PredictProjectDeadlineRiskAction` — applies trained coefficients to active project features, returns risk score and estimated delay.
- [ ] Update dashboard payload — include `deadline_risk` per active project.

#### Frontend to Build
- [ ] `resources/js/Components/Dashboard/DeadlineRiskBadge.tsx` — red/yellow/green badge on project card: "Risiko Tinggi", "Perlu Perhatian", "On Track".
- [ ] Tooltip: "Berdasarkan {N} proker serupa, estimasi keterlambatan {X} hari."

#### Test Coverage Required
- [ ] Unit: `PredictProjectDeadlineRiskAction` returns correct risk for known inputs.
- [ ] Feature: `RecordProjectCompletionStatsJob` writes correct stats on project completion.
- [ ] Feature: anonymized — stats record contains no org name or member name.

#### Commit
`feat: add smart deadline prediction model (M34)`

---

### M35 · Vendor & Sponsor Rating

**Status:** `[ ]` Not started. **Prerequisite: M20 (Sponsor & Vendor Database) must be complete.**

#### Product Goal
Aggregate vendor/sponsor ratings across organizations. "Catering Pak Harto Malang sudah dipakai 14 BEM se-Malang, rating 4.6/5" becomes a discovery tool that helps organizations find trusted vendors faster.

#### Database Changes (extends M20)
- [ ] `vendor_reviews` table: `id`, `sponsor_vendor_id`, `reviewing_organization_id`, `project_id`, `rating` (1-5), `review_text`, `service_quality`, `price_fairness`, `punctuality`, `would_recommend` (bool), `reviewed_at`.
- [ ] Add columns to `sponsors_vendors`: `global_rating_avg`, `global_review_count`, `is_verified` (Prokerin-verified badge).

#### Backend to Build
- [ ] `SubmitVendorReviewAction` — one review per org per vendor per project.
- [ ] `AggregateVendorRatingsJob` (daily) — recalculates `global_rating_avg` and `global_review_count`.
- [ ] `GetVendorDiscoveryAction` — public query (no auth): search vendors by category + city, sorted by rating. Returns anonymized org count but not org names.
- [ ] Route: `GET /vendors/discover?category=catering&city=malang` — public endpoint.

#### Frontend to Build
- [ ] `resources/js/Pages/Vendors/Discover.tsx` — public vendor directory: search, filter by category/city, sort by rating. "Powered by Prokerin komunitas" header.
- [ ] `resources/js/Components/Vendors/ReviewForm.tsx` — post-project review modal, triggered after project marked complete.
- [ ] Star rating display with breakdown (quality, price, punctuality).

#### Test Coverage Required
- [ ] Feature: one review per org per vendor per project enforced.
- [ ] Feature: public discovery does not expose organization names.
- [ ] Feature: aggregation job calculates correct avg.

#### Commit
`feat: add vendor rating and community discovery (M35)`

---

### M36 · Discord & WhatsApp Bot

**Status:** `[ ]` Not started. **Prerequisite: M17 (WhatsApp Reminder) must be complete.**

#### Product Goal
Indonesian student communities live in Discord (especially technical faculties) and WhatsApp. A bot that lets members interact with Prokerin without opening the web app increases daily active usage significantly.

#### Discord Bot Commands
```
/prokerin status           → Shows org's active proker count, pending tasks
/prokerin tasks mine       → Lists my pending tasks with deadlines
/prokerin task-done [name] → Marks a task complete (requires confirmation)
/prokerin deadline         → Shows tasks due in next 7 days
/prokerin budget-summary   → Shows RAB vs realization for active proker
/prokerin rapat next       → Shows next scheduled meeting
```

#### WhatsApp Bot Commands (via existing M17 channel)
Same commands as Discord, triggered by text message to Prokerin WhatsApp number.

#### Architecture
- [ ] Discord bot: Node.js service (separate from Laravel) using `discord.js`. Communicates with Prokerin via internal API (OAuth token per organization).
- [ ] `POST /api/bot/auth` — org links Discord server to Prokerin org via OAuth flow.
- [ ] `GET /api/bot/tasks?user_discord_id={id}` — returns tasks for Discord user (matched by Discord ID linked to Prokerin account).
- [ ] `POST /api/bot/task/{id}/complete` — marks task complete via bot.
- [ ] Discord ID linking: user goes to Prokerin profile settings → clicks "Hubungkan Discord" → Discord OAuth → stores `discord_user_id` on `users`.

#### Backend to Build
- [ ] `users.discord_user_id` column (nullable).
- [ ] Internal bot API routes (rate-limited, bot-token authenticated).
- [ ] `BotAuthAction` — validates bot token, returns org context.

#### Test Coverage Required
- [ ] Feature: bot API rejects request without valid bot token.
- [ ] Feature: `/tasks mine` returns only requesting user's tasks, tenant-scoped.
- [ ] Feature: task complete via bot → DB updated, notification sent to web user.

#### Commit
`feat: add discord and whatsapp bot integration (M36)`

---

### M37 · Organizational Health Analytics

**Status:** `[ ]` Not started.

#### Product Goal
Surface insights about the organization's health that no individual member would notice: "Rapat kalian rata-rata mulai 47 menit terlambat", "80% task diselesaikan oleh 3 orang yang sama — ada risiko burnout", "Revisi proposal kalian 3x lebih banyak dari rata-rata organisasi sejenis."

#### Analytics Computed
- **Task Distribution Index** — Gini coefficient of task completion per member. High score = workload concentrated in few people.
- **Meeting Punctuality Score** — avg delay between scheduled start and first attendee scan (M15 data).
- **Revision Rate** — proposal and LPJ revision count vs platform avg.
- **Proker Completion Rate** — % of proker completed on time vs total.
- **Finance Accuracy** — RAB vs realization variance % per project.
- **Member Engagement Score** — composite: tasks completed + meetings attended + documents uploaded per member.

#### Privacy Rules
- Cross-org benchmarks are anonymized (show percentile, not names).
- Individual member scores visible to owner/admin only, not to the member themselves (configurable).

#### Backend to Build
- [ ] `ComputeOrgHealthMetricsJob` (weekly scheduled) — computes all metrics, stores in `org_health_snapshots`.
- [ ] `org_health_snapshots` table: `id`, `organization_id`, `period_id`, `computed_at`, `metrics` (JSON).
- [ ] `GetOrgHealthDashboardAction` — retrieves latest snapshot + percentile ranks vs anonymized cross-org data.

#### Frontend to Build
- [ ] `resources/js/Pages/OrgHealth/Index.tsx` — dashboard with gauge charts, trend lines, callout cards for notable findings.
- [ ] `resources/js/Components/OrgHealth/HealthScoreCard.tsx` — score with explanation, percentile badge, trend arrow.
- [ ] "Rekomendasi" section: AI-generated (if M23 active) or rule-based text suggestions based on metric values.

#### Test Coverage Required
- [ ] Unit: Task Distribution Index (Gini) calculates correctly for known inputs.
- [ ] Feature: health metrics job stores snapshot correctly.
- [ ] Feature: cross-org percentile does not expose org names.

#### Commit
`feat: add organizational health analytics dashboard (M37)`

---

### M38 · Prokerin Pay (Dompet Proker)

**Status:** `[ ]` Not started. **This requires separate legal/regulatory assessment. Do NOT start without founder decision.**

#### Product Goal
Organizations deposit event funds directly into Prokerin. Disbursements to vendors are made from Prokerin (via virtual account / bank transfer) — fully traceable. Prokerin earns spread on transactions. This is the highest-revenue feature on the roadmap.

#### Regulatory Note
This feature requires either: (a) partnership with a licensed e-money provider (e.g., Midtrans, DOKU, Xendit) for fund holding, OR (b) obtaining Bank Indonesia e-money license (long process). Do not build fund-holding features without legal clearance.

#### Scope (assuming Xendit partnership)
- [ ] `prokerin_wallets` table: `id`, `organization_id`, `xendit_account_id`, `balance_idr`, `last_synced_at`.
- [ ] `wallet_transactions` table: `id`, `wallet_id`, `type` (deposit/disburse/fee), `amount`, `reference`, `status`, `initiated_by`, `completed_at`.
- [ ] Deposit flow: Prokerin generates virtual account → org treasurer transfers → Xendit webhook confirms → balance updated.
- [ ] Disbursement flow: treasurer initiates transfer to vendor bank account → multi-approval (M18) → Prokerin executes via Xendit Disbursement API.
- [ ] Full audit trail: every rupiah accounted for.
- [ ] `.env.example`: `XENDIT_SECRET_KEY=`, `XENDIT_WEBHOOK_TOKEN=`.

#### Test Coverage Required
- [ ] Feature: webhook signature verified before processing.
- [ ] Feature: disbursement requires approval (M18 integration) before execution.
- [ ] Feature: balance cannot go below zero.

#### Commit
`feat: add prokerin pay wallet and disbursement (M38)`

---

## L01 · Landing Page

**Status:** `[ ]` Not started. **Build in parallel with any wave — separate codebase.**

---

### Overview

The landing page is Prokerin's public face. It must: (1) convert visitors to signups, (2) communicate the product clearly in 5 seconds, (3) rank on Google for relevant Indonesian keywords, (4) look premium enough that BEM/UKM leaders trust it with their organization's data.

### Tech Stack for Landing Page

**Separate from the main Laravel app.** Options:
- **Option A (Recommended):** Next.js 14 (App Router) + Tailwind CSS + Framer Motion. Deploy to Vercel. Domain: `prokerin.id`. App at: `app.prokerin.id`.
- **Option B:** Static HTML + Alpine.js + Vite. Simpler, faster to ship, harder to maintain long-term.
- **Option C:** Add a separate route group in the existing Laravel app (`/`, `/features`, `/pricing`) served by Blade + Inertia. Simpler infrastructure but couples marketing with app.

**Recommendation: Option A** — Next.js on Vercel. Marketing pages need to be independently deployable, fast (Core Web Vitals matter for SEO), and editable without touching the app backend.

---

### Page Architecture

```
prokerin.id/              → Home (main conversion page)
prokerin.id/features      → Full features breakdown
prokerin.id/pricing       → Pricing tiers
prokerin.id/marketplace   → Public template marketplace (M32)
prokerin.id/verify/{token}→ Certificate verification (M16, public)
prokerin.id/e/{slug}      → Proker microsite (M31, public)
prokerin.id/blog          → SEO blog (future)
prokerin.id/about         → About Prokerin team
prokerin.id/contact       → Contact / demo request
```

---

### L01-A · Home Page (`/`)

#### Section 1: Navigation (Sticky)
```
[Prokerin Logo]  Features  Pricing  Marketplace  Blog    [Masuk]  [Coba Gratis →]
```
- Transparent on load, white + shadow on scroll.
- Mobile: hamburger menu, full-screen overlay.
- CTA "Coba Gratis" → `/register` on `app.prokerin.id`.

#### Section 2: Hero
**Headline (big, bold):** "Kelola Proker Organisasi Tanpa Chaos"
**Subheadline:** "Dari perencanaan, proposal, RAB, sampai LPJ — semua dalam satu platform. Khusus untuk BEM, HIMA, dan UKM Indonesia."
**CTA buttons:**
- Primary: "Mulai Gratis Sekarang →" (goes to register)
- Secondary: "Lihat Demo" (opens YouTube demo video in modal)

**Hero Visual:** Animated mockup of the Prokerin dashboard — shows project cards, kanban tasks, finance summary. Use a real screenshot with subtle floating animation (CSS or Framer Motion). NOT a generic illustration.

**Social proof bar (below hero):**
"Dipercaya oleh 500+ organisasi mahasiswa di Indonesia" + logos of 5 recognizable universities (get permission or use "BEM Universitas X" text).

#### Section 3: Problem Statement
Headline: "7 Masalah yang Bikin Pengurus Ormawa Pusing"
7 cards, each with icon + headline + 1-line description:
1. 📅 Proker sering molor dari deadline
2. 📄 Proposal bolak-balik revisi
3. ✅ Task tidak jelas siapa yang pegang
4. 💰 Keuangan berantakan, RAB vs realisasi beda jauh
5. 📁 Dokumen nyebar di mana-mana
6. 📋 LPJ dikerjain H-1 deadline
7. 🔄 Pergantian pengurus = kehilangan semua konteks

**Transition:** "Prokerin dirancang khusus untuk menyelesaikan semua ini."

#### Section 4: Feature Showcase (Alternating Layout)
6 features, each: screenshot left/right + headline + description + link "Pelajari →"

1. **Manajemen Proker** — "Buat, pantau, dan arsip program kerja dengan satu klik. Template otomatis untuk mulai lebih cepat."
2. **Proposal Generator** — "Isi data proker sekali, proposal terisi otomatis. Export ke PDF atau Word dalam hitungan detik."
3. **RAB & Keuangan** — "Budget planning, approval, dan realisasi dalam satu alur. Tidak ada lagi spreadsheet yang salah versi."
4. **Absensi QR** — "Buka kamera, scan QR, hadir tercatat. Laporan kehadiran otomatis per rapat dan per event."
5. **LPJ Otomatis** — "LPJ terbentuk dari data eksekusi nyata — bukan dokumen baru dari nol setiap akhir proker."
6. **Sertifikat Digital** — "Terbitkan sertifikat anggota yang bisa diverifikasi online. Bisa langsung dibagikan ke LinkedIn."

#### Section 5: How It Works (3 Steps)
**Headline:** "Mulai dalam 5 Menit"
1. **Daftarkan Organisasi** — Buat akun, setup periode kepengurusan, undang anggota inti.
2. **Buat Proker dari Template** — Pilih template, sesuaikan, langsung ada task, RAB, dan draft proposal.
3. **Kelola Sampai LPJ** — Pantau progress, kelola keuangan, hasilkan laporan — semua dari satu dashboard.

#### Section 6: Testimonials
3–5 quotes from actual users (BEM/HIMA chairs). Format: photo + name + organization + quote. If no real users yet, use placeholder with note "testimoni akan diisi setelah beta launch."

#### Section 7: Pricing (Summary)
3-card layout (Free, Starter, Pro) with highlights. "Lihat selengkapnya →" to `/pricing`.

#### Section 8: CTA Banner
Full-width green section (#24695c):
Headline: "Siap kelola organisasi tanpa chaos?"
Sub: "Gratis untuk 1 organisasi. Tidak perlu kartu kredit."
Button: "Daftar Sekarang — Gratis →"

#### Section 9: Footer
```
[Logo + tagline]
Product: Features, Pricing, Marketplace, Changelog
Resources: Blog, Academy, API Docs, Status
Company: About, Contact, Privacy Policy, Terms of Service
Social: Instagram, LinkedIn, Twitter/X, Discord
© 2026 Prokerin. All rights reserved.
```

---

### L01-B · Features Page (`/features`)

Full breakdown of all modules, organized by category:
- **Perencanaan:** M04 Proker, M05 Template, M06 Timeline & Task
- **Dokumen & Proposal:** M08 Proposal, M09 Dokumen, M10 LPJ
- **Keuangan:** M07 RAB & Finance
- **Operasional:** M14 Rapat, M15 Absensi QR
- **Pasca Proker:** M16 Sertifikat, M11 Dashboard

Each feature: icon + title + description (3–4 sentences) + screenshot.
SEO: H1 per category, H2 per feature, alt text on all images.

---

### L01-C · Pricing Page (`/pricing`)

| | **Free** | **Starter** | **Pro** | **Campus** |
|--|----------|------------|---------|-----------|
| **Harga** | Rp 0 | Rp 99.000/bln | Rp 299.000/bln | Hubungi kami |
| **Organisasi** | 1 | 1 | 1 | Unlimited |
| **Anggota** | 20 | 50 | Unlimited | Unlimited |
| **Proker Aktif** | 3 | 10 | Unlimited | Unlimited |
| **Penyimpanan** | 500 MB | 5 GB | 20 GB | Custom |
| **M01–M13 MVP** | ✅ | ✅ | ✅ | ✅ |
| **M14 Rapat** | ❌ | ✅ | ✅ | ✅ |
| **M15 Absensi QR** | ❌ | ✅ | ✅ | ✅ |
| **M16 Sertifikat** | ❌ | ❌ | ✅ | ✅ |
| **M17 WhatsApp** | ❌ | ❌ | ✅ | ✅ |
| **M23 AI Assistant** | ❌ | ❌ | ✅ | ✅ |
| **M24 Campus Dashboard** | ❌ | ❌ | ❌ | ✅ |
| **Priority Support** | ❌ | Email | Email + Chat | Dedicated |

FAQ section below pricing table: 8–10 common questions.

---

### L01-D · SEO Strategy

#### Target Keywords (Indonesian)
Primary:
- "aplikasi manajemen organisasi mahasiswa" (low competition, high intent)
- "software BEM" / "aplikasi BEM" 
- "manajemen proker mahasiswa"
- "template proposal kegiatan mahasiswa"
- "aplikasi absensi organisasi"

Secondary:
- "cara buat LPJ yang benar"
- "template RAB kegiatan mahasiswa"
- "aplikasi notulen rapat"
- "sertifikat digital organisasi mahasiswa"

#### Content Strategy
- `/blog` with SEO articles targeting secondary keywords.
- Example articles: "Cara Membuat Proposal Kegiatan yang Lolos Rektorat", "Template RAB Ospek Mahasiswa", "Tips Buat LPJ yang Tidak Ribet".
- Each article ends with CTA to Prokerin.

---

### L01-E · Performance Requirements

| Metric | Target |
|--------|--------|
| Lighthouse Performance | > 90 |
| Lighthouse SEO | > 95 |
| Largest Contentful Paint | < 2.5s |
| First Input Delay | < 100ms |
| Cumulative Layout Shift | < 0.1 |
| Mobile Lighthouse | > 85 |

#### Implementation Rules
- All images in WebP format with fallback.
- Lazy-load images below the fold.
- Critical CSS inlined (Next.js handles this automatically).
- No render-blocking scripts.
- `next/image` for all images (automatic optimization).
- Font: use `next/font` with `display: swap` (recommended: Inter or Plus Jakarta Sans — modern, readable, professional).

---

### L01-F · Design System for Landing Page

#### Colors (consistent with app)
```css
--primary:     #24695c  /* Prokerin green */
--primary-dark:#1b4c43
--secondary:   #ba895d  /* warm brown */
--bg:          #f5f7fb
--text:        #242934
--text-muted:  #59667a
--border:      #e6edef
--white:       #ffffff
```

#### Typography
- **Heading font:** Plus Jakarta Sans (Google Fonts, free) — modern, confident.
- **Body font:** Inter — readable, professional.
- Scale: 14 / 16 / 18 / 20 / 24 / 30 / 36 / 48 / 60 / 72px.

#### Component Checklist for Landing Page
- [ ] `<Navbar>` — sticky, transparent→white on scroll, mobile hamburger.
- [ ] `<HeroSection>` — headline, subheadline, dual CTA, animated dashboard mockup.
- [ ] `<SocialProofBar>` — university logos + org count.
- [ ] `<ProblemSection>` — 7-card problem grid.
- [ ] `<FeatureShowcase>` — alternating image+text, 6 features.
- [ ] `<HowItWorksSection>` — 3-step process.
- [ ] `<TestimonialsSection>` — 3-5 quote cards.
- [ ] `<PricingSection>` — 3-tier card comparison.
- [ ] `<FaqSection>` — accordion, 8-10 questions.
- [ ] `<CtaBanner>` — full-width conversion section.
- [ ] `<Footer>` — 4-column, social links, legal links.
- [ ] `<DemoVideoModal>` — YouTube embed, triggered by "Lihat Demo" button.
- [ ] `<MobileMenu>` — full-screen overlay.

---

### L01-G · Analytics & Tracking Setup

- [ ] Google Analytics 4 (GA4) — page views, conversion events.
- [ ] Google Search Console — SEO monitoring, Core Web Vitals.
- [ ] Conversion events to track:
  - `sign_up_click` — "Coba Gratis" button click.
  - `demo_view` — demo video opened.
  - `pricing_view` — pricing page visited.
  - `register_complete` — successful registration (tracked from app, sent to GA4 via server event).
- [ ] Hotjar or Microsoft Clarity (free) — heatmaps, session recording.
- [ ] Cookie consent banner (required for GDPR/ITE compliance).

---

### L01-H · Test Coverage for Landing Page

- [ ] All 5 CTA buttons link to correct URLs.
- [ ] Demo video modal opens and closes correctly.
- [ ] Mobile navigation works (hamburger → overlay → close).
- [ ] All external links open in `target="_blank"` with `rel="noopener noreferrer"`.
- [ ] Lighthouse CI passes thresholds (> 90 performance, > 95 SEO) in GitHub Actions.
- [ ] All images have non-empty `alt` text.
- [ ] Pricing table renders correctly on mobile (horizontal scroll or stacked layout).

#### Commit Convention for Landing Page
`feat(landing): add hero section and navigation`
`feat(landing): add feature showcase and pricing`
`feat(landing): add SEO metadata and Open Graph`
`chore(landing): configure GA4 and Lighthouse CI`

---

## Master Next Action (Full Roadmap)

### This Week
1. Fix PHP 8.4 shell default or document PATH prefix in README.
2. Run full test suite — confirm 190 tests green.
3. **Start M16 (Sertifikat Digital)** — follow the M16 build sequence in `features.md`.

### After M16
4. Start **M25 (Rich Text Editor)** — highest UX impact, unblocks proposal quality.
5. Start **M26 (Real-Time Notifications)** — unblocks approval workflow responsiveness.
6. Start **M27 (QR Camera Scanner)** — makes M15 actually usable in the field.
7. Start **L01 (Landing Page)** in parallel — assign to a separate person/session.

### After UX Critical Fixes (M25–M28 + L01)
8. **M28 Onboarding Wizard** — fixes activation rate.
9. **M29 Global Search** — MeiliSearch setup, then Scout integration.
10. **M30 Kepanitiaan Mode** — unlocks new market segment.
11. **M31 Public Microsite** — activates organic growth loop.
12. **M32 Template Marketplace** — network effect flywheel.

### Growth Wave
13. **M17 WhatsApp Reminder** — engagement.
14. **M18 Multi-Level Approval** — enterprise readiness.
15. **M19 Handover** (finish partial) — retention.
16. **M20 Vendor Database** — operational utility.
17. **M21 Event Registration** — public-facing monetization setup.
18. **M22 Payment** — only after M21 stable.

### Enterprise Wave
19. **M33 Prokerin Academy** — switching cost moat.
20. **M34 Smart Deadline Prediction** — data intelligence.
21. **M35 Vendor Rating** — community flywheel.
22. **M36 Bot** — developer community engagement.
23. **M37 Org Health Analytics** — enterprise reporting.
24. **M23 AI Assistant** — after explicit use case defined.
25. **M24 Campus Dashboard** — B2B enterprise layer.
26. **M38 Prokerin Pay** — only after legal/regulatory clearance.

---