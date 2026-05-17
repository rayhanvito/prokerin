# AGENTS.md - Prokerin Blueprint and Development Rules

This file is the technical blueprint and guardrail for Prokerin. Every AI agent and developer must read this file before changing code. For product explanation, feature list, user flows, setup, and status, read `README.md`.

Root markdown policy:

- Only `README.md`, `AGENTS.md`, and `CLEAN-CODE-CHECKLIST.md` are allowed as root project documentation.
- Do not create extra root `.md` files such as roadmap, feature status, QA plan, sprint plan, or architecture notes unless the project owner explicitly asks.
- If project status or feature documentation changes, update `README.md`.
- If architecture, code rules, or development guardrails change, update `AGENTS.md`.
- If clean-code targets or completion status changes, update `CLEAN-CODE-CHECKLIST.md`.

---

## 1. Project Identity

Name: Prokerin

Product: Web App/PWA SaaS for Indonesian student organizations to manage program kerja (proker) from planning to handover.

Target users:

- BEM
- HIMA
- UKM
- Event committees
- Campus communities
- Campus-level admins for B2B dashboard, currently hibernated until paying customer signal

Core promise:

Prokerin reduces student organization chaos by centralizing proker planning, proposal, task, RAB, finance realization, documentation, meeting minutes, attendance, certificates, LPJ, handover, letters, microsites, calendar sync, notifications, search, and inventory.

Current status:

- MVP and active Post-MVP feature modules are code-complete.
- Some modules are maintenance/frozen/hibernate by product decision.
- Ongoing work should focus on launch QA, bug fixes, hardening, landing polish, and production readiness unless the owner explicitly approves new feature scope.

---

## 2. Source of Truth

Use these as source of truth:

1. Tests: executable behavior truth.
2. Database migrations: persistence truth.
3. Routes/controllers/actions: app behavior truth.
4. `README.md`: product, setup, feature status, user flows, usage documentation.
5. `AGENTS.md`: development rules and architecture guardrails.

Do not depend on deleted legacy docs. Their relevant contents have been consolidated into README and AGENTS.

---

## 3. Architecture

Architecture style: Modular Monolith.

Rules:

- Keep one Laravel monolith.
- Do not split into microservices.
- Do not create a standalone React SPA.
- Do not introduce a separate REST API layer for core app flows.
- Use Laravel web routes + Inertia rendering.
- Use JSON endpoints only for small AJAX-only interactions such as search, notification state, or subscriptions.

Layering:

```text
Request
-> FormRequest authorization + validation
-> Controller
-> Action / Domain service
-> Model / DB query
-> Inertia response / redirect / small JSON response
```

Controllers must stay thin:

- Accept FormRequest/Request.
- Call Action.
- Return redirect, Inertia render, download, or JSON.
- No business workflow in controller.
- No direct complex DB logic in controller unless truly tiny and existing local pattern uses it.

Business logic belongs in:

- `app/Actions`
- `app/Domain`
- dedicated service/value object/DTO when useful

Models should not become service objects. Keep model logic minimal.

---

## 4. Tech Stack Rules

Backend:

- PHP 8.4+
- Laravel currently installed in this project
- Eloquent/query builder
- Redis for cache and queue
- Filament for internal admin
- Laravel Reverb for realtime
- WebPush package for browser push
- S3-compatible private storage for uploads

Frontend:

- React + TypeScript
- Inertia.js
- Tailwind CSS
- shadcn/ui/Radix primitives
- lucide-react icons
- Vite

Package managers:

- PHP packages: Composer only.
- JS packages: npm only.
- Never install PHP packages with npm.
- Never install JS packages with Composer.

---

## 5. Commands

Use PHP 8.4 prefix on local macOS if shell still points to another PHP:

```bash
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH
```

Development:

```bash
php artisan serve
npm run dev
php artisan queue:work
php artisan reverb:start
```

Verification:

```bash
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
npm run lint
npm run build
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
```

Database:

```bash
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
```

Production/staging warnings:

- Never run `migrate:fresh` on staging/production.
- Never run seeders on staging/production without explicit deployment plan.
- Run `php artisan optimize:clear` after pulling changes to staging/production.
- Use `php artisan migrate --force` for production migrations.

---

## 6. Directory Rules

Allowed placement:

- Inertia page: `resources/js/Pages/[Domain]/[PageName].tsx`
- Shared React component: `resources/js/Components/[Domain]/[Component].tsx`
- shadcn/ui primitive: `resources/js/Components/ui/`
- Viho-derived shell/card/sidebar/header: `resources/js/Components/Viho/`
- Hook: `resources/js/hooks/useName.ts`
- Utility: `resources/js/lib/name.ts`
- Type definitions: `resources/js/types/`
- PHP action: `app/Actions/[Domain]/[ActionName].php`
- Domain enum/value object: `app/Domain/[Domain]/`
- Model: `app/Models/`
- Controller: `app/Http/Controllers/`
- Form request: `app/Http/Requests/`
- Job: `app/Jobs/`
- Notification: `app/Notifications/`
- Policy: `app/Policies/`
- Seeder: `database/seeders/`
- Migration: `database/migrations/`

Do not:

- Create new top-level folders without confirmation.
- Place app code outside the project.
- Import runtime code from external template folders.
- Modify shadcn/ui generated base components directly unless explicitly required.
- Put business logic inside controllers.

---

## 7. Naming Conventions

PHP:

- Class/file: PascalCase.
- Model: singular PascalCase, e.g. `Organization`.
- Controller: `NameController`.
- Action: `VerbNounAction`.
- Policy: `ModelPolicy`.
- Job: descriptive PascalCase.
- Variables/methods: camelCase.
- Constants: UPPER_SNAKE.
- Always add `declare(strict_types=1);` to PHP files.

TypeScript/React:

- Page/component file: PascalCase.tsx.
- Hook: camelCase with `use` prefix.
- Utility: camelCase.ts.
- Types/interfaces: PascalCase.
- Variables/functions: camelCase.
- Constants: UPPER_SNAKE.
- Avoid `any`; use explicit types or `unknown`.

Database:

- Tables: snake_case plural.
- Columns: snake_case.
- Foreign keys: singular table name + `_id`.
- Pivot tables: alphabetical where applicable.
- Add indexes for tenant keys, foreign keys, statuses, and common filters.

Git:

- Feature branch: `feat/name`.
- Fix branch: `fix/name`.
- Refactor branch: `refactor/name`.
- Chore branch: `chore/name`.
- Commit format: `feat: description`, `fix: description`, `docs: description`, `db: description`, etc.

---

## 8. Multi-Tenancy Rules

Prokerin is multi-tenant by `organization_id`.

Mandatory:

- Every organization data query must be scoped to the authenticated user's organization.
- Derive organization from active session/membership, not request body.
- Respect users with multiple organizations.
- Respect project-level membership where relevant.
- Cross-tenant data access must fail.
- Tests must cover cross-tenant guard for sensitive flows.

Common sources:

- Session key: `active_organization_id`
- Org membership table: `organization_members`
- Project membership table: `project_members`
- Helper/action patterns: `GetActiveOrganizationContextAction`, role helpers, policies, FormRequest authorization.

Never:

- Trust `organization_id` from client.
- Trust role from client.
- Return raw unscoped Eloquent models to Inertia.
- Join related data without tenant constraints.

---

## 9. Authorization Rules

Authorization should happen before mutation.

Preferred layers:

1. FormRequest `authorize()` for HTTP input.
2. Policy for model-level rules.
3. Action guard for workflow/tenant-specific authorization.
4. Spatie permission/role checks for role-level gates.

Controllers should not contain manual role logic except tiny local pattern exceptions already established.

Role names are stable:

- `super_admin`
- `organization_owner`
- `organization_admin`
- `project_lead`
- `secretary`
- `treasurer`
- `division_coordinator`
- `member`
- `viewer`

Do not rename role strings without updating all references, seeders, tests, and UI.

---

## 10. Data Flow and Inertia Rules

Core app:

- Use Inertia pages.
- Pass page data from controllers via `Inertia::render()`.
- Use `Link` from `@inertiajs/react` for internal navigation.
- Use `useForm()` for mutations.
- Use redirects + flash messages for standard mutations.
- Use `router.visit`, `router.reload`, or small JSON endpoints only where the UX needs it.

Avoid:

- Fetching core page data in `useEffect`.
- Building duplicate REST resources.
- Manual full page anchors for internal app routes.
- Returning raw Eloquent models with sensitive fields.

Small JSON endpoints are acceptable for:

- Global search.
- Notification read/recent.
- Web push subscribe/unsubscribe.
- Export job polling if implemented.

JSON shape for new endpoints when applicable:

```json
{ "success": true, "data": {}, "message": "..." }
```

---

## 11. PHP Rules

Always:

- Use `declare(strict_types=1);`.
- Type parameters and return types.
- Prefer readonly constructor properties where suitable.
- Prefer enums for stable domain states.
- Use FormRequest validation.
- Use `config()` instead of `env()` outside config files.
- Use Laravel helpers when they improve readability.
- Keep actions single-responsibility.

Avoid:

- Untyped return values.
- `mixed` unless unavoidable.
- Business logic in controllers/models.
- Raw SQL destructive statements.
- Heavy sync work inside request lifecycle.
- Swallowing exceptions without user-safe feedback/logging.

---

## 12. TypeScript and React Rules

Always:

- Define explicit prop types.
- Define function return types for non-trivial functions.
- Use `interface` for object shapes.
- Use `type` for unions/intersections.
- Use `cn()` for conditional class names.
- Use Inertia `useForm()` for forms.
- Use lucide-react icons when an icon exists.
- Keep page components focused on layout/data wiring.

Avoid:

- `any`.
- Inline styles unless truly dynamic.
- String concatenation for class names.
- Large components with too many concerns.
- Client-side fetch for initial page data.
- LocalStorage auth/session patterns.
- React Router.
- Reactstrap/Bootstrap runtime patterns from Viho templates.

Component order:

1. Imports.
2. Types/interfaces.
3. Component.
4. Hooks.
5. Handlers/helpers.
6. JSX.
7. Export.

---

## 13. Styling and UI Rules

Visual baseline: Viho-inspired compact admin SaaS.

Design language:

- Fixed/sidebar admin shell.
- White header.
- Soft page background.
- Flat 4px cards/buttons.
- Subtle shadows.
- Dense operational layout.
- Professional, practical, not marketing-heavy inside app.

Core palette:

- Primary: `#24695c`
- Secondary/accent: `#ba895d`
- Success/dark green: `#1b4c43`
- Danger: `#d22d3d`
- Page background: `#f5f7fb`
- Border: `#e6edef`
- Body text: `#242934`
- Muted text: `#59667a`, `#717171`

Rules:

- Use Tailwind utility classes.
- Do not use `!important`.
- Do not hand-edit shadcn/ui primitives.
- Use Viho components for app shell/cards/status where appropriate.
- Use mobile-first responsive design.
- Test new components at mobile and desktop.
- Avoid purple/indigo generic SaaS styling unless explicitly required.
- Avoid oversized marketing hero patterns inside operational app screens.

Landing pages can be more editorial, but must stay on-brand and performant.

---

## 14. Storage and File Rules

Uploads:

- Validate MIME and size server-side.
- Store in S3-compatible/private storage.
- Do not store user upload files directly in `public/`.
- Download via signed or authorized server route.
- Never expose raw S3 paths to frontend.

Generated exports:

- PDF/DOCX generation should run in queue when heavy.
- Store output path privately.
- Expose download route after authorization.

Public assets:

- App icons, manifest, service worker, landing OG image, and vendor visual assets may live in `public/`.
- Do not place sensitive user data in `public/`.

---

## 15. Queue, Scheduler, and Realtime Rules

Heavy work should be queued:

- PDF export.
- DOCX export.
- Email sending.
- Web push.
- Deadline reminders.
- Overdue loan checks.

Scheduler:

- Put scheduled jobs in `routes/console.php` unless project structure changes.
- Existing examples include kepanitiaan auto archive and inventory overdue checks.

Realtime:

- Use Laravel Reverb.
- Use private channels for user-specific notifications.
- Use Echo client.
- Keep fallback endpoint for recent notifications.

Web push:

- Use service worker push/click handlers.
- VAPID keys must be env/config only.

---

## 16. Module Status Guardrails

Active feature modules are complete. New work should not randomly add scope.

Complete core modules:

- Auth/account
- Organization
- Member/role
- Proker
- Template proker
- Task/timeline
- RAB/finance
- Proposal generator
- Document management
- LPJ generator
- Dashboard
- Notifications
- Filament admin
- Rapat/notulen
- Absensi QR
- Web push
- Realtime notification
- Onboarding
- Rich text editor
- Kepanitiaan mode
- Public microsite
- Surat menyurat
- Global search
- Calendar sync
- Inventory

Maintenance/frozen/hibernate:

- Certificate: maintenance only.
- Multi-level approval: maintenance only.
- Handover: partial/maintenance, bug fixes allowed.
- Payment/ticketing: beta/pro-tier opt-in only.
- AI assistant: frozen expansion.
- Campus dashboard: hibernate until paying customer.

Deferred/dropped:

- Tier Momentum modules such as activity feed, generic approval, personal my day, sponsorship pipeline, and member skill tracker are not active.
- Re-evaluate only after public launch and real feedback.

---

## 17. Feature Development Workflow

Before starting:

1. Read README and AGENTS.
2. Check current tests.
3. Inspect existing code patterns.
4. Identify tenant and authorization boundaries.
5. Keep scope narrow.

Implementation order for new module:

1. Migration.
2. Enum/value object.
3. Model if needed.
4. Action.
5. FormRequest.
6. Controller.
7. Route.
8. Payload action for Inertia page.
9. React page/component.
10. Tests.
11. README/AGENTS updates if status/rules changed.

Before marking done:

- Targeted tests pass.
- Full test suite pass unless explicitly blocked.
- `npm run lint` pass.
- `npm run build` pass.
- `./vendor/bin/pint --test` pass.
- Manual/browser/device QA recorded in README if relevant.

---

## 18. Testing Rules

Framework:

- PHPUnit/Pest according to current project setup.
- Prefer feature tests for workflows.
- Prefer unit tests for pure actions/value objects.

Priority:

1. Authorization and tenant isolation.
2. Business logic actions.
3. Core user workflows.
4. Finance/progress calculations.
5. Export and queue behavior.
6. UI payload shape.

Required for new risky work:

- Happy path.
- Forbidden role.
- Cross-tenant denial.
- Validation errors.
- State transition.

Do not over-test:

- Third-party package internals.
- Simple getters/casts with no logic.
- Filament internals.

---

## 19. Security Rules

Never:

- Commit `.env` or secrets.
- Expose credentials to frontend.
- Expose raw storage paths.
- Trust client role, tenant, or ownership fields.
- Skip authorization for mutations.
- Return stack traces in production.
- Store payment/secret tokens in frontend.
- Hardcode API keys.

Always:

- Use config/env boundary.
- Use CSRF-protected web routes.
- Validate upload MIME/size.
- Use signed/authorized downloads.
- Add rate limiting for public/sensitive endpoints where needed.
- Keep impersonation secure and auditable.

---

## 20. Performance Rules

Database:

- Avoid N+1 queries.
- Eager load when using Eloquent relationships.
- Add indexes for foreign keys, status filters, tokens, and tenant filters.
- Paginate or limit large datasets.

Frontend:

- Avoid heavy libraries for tiny utilities.
- Use dynamic import for heavy components when useful.
- Do not optimize prematurely with memo everywhere.
- Keep landing pages performant.

Backend:

- Use cache for expensive stable payloads when appropriate.
- Use queues for heavy work.
- Avoid synchronous export/email operations.

---

## 21. Documentation Rules

Approved root docs:

- `README.md`
- `AGENTS.md`
- `CLEAN-CODE-CHECKLIST.md`

When feature status changes:

- Update README module/status sections.
- Update README flow if user behavior changes.
- Update AGENTS only if development rules/architecture change.

Do not add:

- `features.md`
- `POST-MVP-ROADMAP.md`
- `BUG-FIX-PLAN.md`
- `LANDING PAGE PLAN.md`
- `SUPER-ADMIN-V2-PLAN.md`
- Any new root markdown planning file

If temporary planning is needed, keep it outside root or ask the owner first.

---

## 22. Clean Code Operating Prompt

Use this prompt when the owner asks for clean code, refactor, audit, hardening, or codebase cleanup. The goal is to improve maintainability without changing product behavior, breaking tenant isolation, or drifting away from Prokerin's architecture.

```text
You are a senior Laravel + Inertia + React engineer working on Prokerin.

Mission:
Clean the codebase so it becomes simpler, safer, easier to maintain, and more consistent with AGENTS.md, without changing user-facing behavior unless a bug is explicitly found and fixed.

Mandatory first steps:
1. Read AGENTS.md and README.md.
2. Check git status and identify unrelated dirty files.
3. Inspect the target module before editing.
4. Map current behavior, routes, policies, requests, actions, payloads, and tests.
5. Decide the smallest safe cleanup scope.

Non-negotiable constraints:
- Preserve current behavior by default.
- Do not introduce new product features.
- Do not delete, move, or rename files without owner confirmation.
- Do not change database schema unless the cleanup requires an additive, clearly justified migration.
- Do not relax authorization, validation, tenant scoping, or upload/storage security.
- Do not convert Inertia flows into REST/API flows.
- Do not put business logic in controllers or React pages.
- Do not modify shadcn/ui base components directly.
- Do not create new root markdown files beyond owner-approved docs.
- Do not touch frozen/maintenance modules except for clear bug fixes or low-risk cleanup.

Clean code priorities:
1. Correctness and security before style.
2. Tenant isolation before developer convenience.
3. Explicit validation and authorization before compact code.
4. Existing project patterns before new abstractions.
5. Small cohesive actions before large generic services.
6. Clear names before comments.
7. Focused tests before broad rewrites.

Backend cleanup rules:
- Keep controllers thin: authorize, receive FormRequest, call Action, return redirect/Inertia response.
- Move business logic to app/Actions or app/Domain.
- Use FormRequest for validation.
- Use Policies for model authorization.
- Scope every organization-owned query by organization_id or an approved tenant boundary.
- Use typed method signatures and declare(strict_types=1) for PHP files.
- Prefer enums/value objects for repeated states.
- Avoid raw arrays for complex payload construction when a dedicated action/resource already fits.
- Eager-load relationships needed by views.
- Keep exports, notifications, and heavy work queued.

Frontend cleanup rules:
- Keep Inertia pages focused on layout and data wiring.
- Move reusable UI into resources/js/Components.
- Move reusable logic into hooks or lib utilities.
- Use Link/router/useForm from Inertia for app navigation and mutations.
- Avoid client-side fetch unless the endpoint is intentionally AJAX-only.
- Avoid any, inline styles, manual class string joins, and large page-local helper blocks.
- Preserve Viho admin visual direction and compact operational UI.
- Keep responsive behavior intact.

Testing and verification:
1. Add or update tests when cleanup touches behavior, authorization, validation, tenant scope, calculations, exports, queues, or payload shape.
2. Run the narrowest relevant tests first.
3. Run full verification before done when feasible:
   - ./vendor/bin/pint --test
   - npm run lint
   - npm run build
   - php artisan test
4. If any command cannot run, explain why and list residual risk.
5. Record user-visible flow changes in README only if behavior actually changed.

Execution style:
- Work in small commits by logical scope.
- Prefer one module at a time.
- Do not mix formatting-only churn with behavior fixes unless unavoidable.
- Keep diffs reviewable.
- When finding a bug during cleanup, fix it only if the fix is local and clearly covered by tests.
- If the bug requires product or architecture decisions, stop and ask.

Definition of done:
- The cleaned code is easier to read.
- The cleaned code follows AGENTS.md and existing conventions.
- No behavior regression is introduced.
- Tenant/security boundaries remain intact.
- Relevant tests and build gates pass or blockers are clearly documented.
- Git status contains only intentional changes.
```

Recommended cleanup sequence:

1. Run a read-only audit for one module and list risks.
2. Clean controller/request/action boundaries.
3. Clean authorization and tenant scoping.
4. Clean payload builders and React page decomposition.
5. Clean naming and duplication.
6. Add missing focused tests.
7. Run gates and commit.

Stop and ask the owner before:

- Splitting modules.
- Introducing a new package.
- Changing UX flow.
- Replacing architecture patterns.
- Removing an existing feature.
- Performing broad formatting across unrelated files.

---

## 23. Git Rules

Commit after meaningful completed changes.

Commit examples:

```text
feat: add inventory asset management
fix: prevent cross-tenant finance access
docs: consolidate project documentation
refactor: extract proposal draft sanitizer
test: cover inventory loan return flow
db: add calendar sync token
```

Do not:

- Revert unrelated user changes.
- Commit unrelated dirty work.
- Use destructive git commands unless explicitly requested.
- Commit generated secrets or local environment files.

If worktree contains unrelated changes, ignore them or stage only your files.

---

## 24. Production Readiness Checklist

Before production launch:

- Full test suite green.
- Pint green.
- TypeScript green.
- Frontend build green.
- Migrations tested on staging.
- Queue worker supervised.
- Reverb supervised.
- Scheduler configured.
- Storage credentials configured.
- Mail configured.
- VAPID configured if web push enabled.
- Google OAuth redirect configured.
- HTTPS enabled.
- Public microsite OG checked.
- QR scanner checked on Android Chrome and iOS Safari.
- Web push checked on supported browsers.
- Calendar feed checked in real calendar clients.
- Export PDF/DOCX visually checked.
- Backup strategy confirmed.

---

## 25. When Unsure

Ask before changing code if:

- The change would introduce a new product module.
- The change touches frozen/maintenance/hibernate modules beyond bug fix.
- The change requires deleting/moving/renaming files not explicitly requested.
- The change changes tenant/authorization model.
- The change adds a new external service.
- The change creates new root documentation.
- The change may run destructive database commands.

Otherwise, follow existing patterns, keep changes scoped, test thoroughly, and preserve Prokerin's architecture.
