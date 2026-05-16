# AGENTS.md — Prokerin

---

## 1. Project Overview

- **Name**        : Prokerin
- **Description** : Web App / PWA SaaS for student organizations to manage program kerja (proker) — from planning, proposal, timeline, task, RAB, execution, documentation, to LPJ and board handover.
- **Goal**        : Eliminate the 7 chaos areas of student organizations: late proker, slow proposals, untracked tasks, messy finances, scattered documentation, delayed LPJ, and poor handover.
- **Target Users**: Student organization boards (BEM, HIMA, UKM), event committees, and campus communities — primarily in Java (Surabaya, Malang, Yogyakarta, Bandung).
- **Version**     : v0.1.0
- **Status**      : Active Development — MVP Phase

---

## 2. Tech Stack

- **Language**        : PHP 8.4+
- **Framework**       : Latest Laravel currently installed in this project (Laravel 13.x at scaffold time)
- **Frontend**        : React (via Inertia.js) — NOT a standalone SPA
- **Bridge**          : Inertia.js — connects Laravel routes directly to React pages
- **Styling**         : Tailwind CSS (project scaffold currently uses Breeze/Vite Tailwind config; upgrade deliberately, not casually)
- **UI Components**   : shadcn/ui (Radix UI primitives)
- **Database**        : MySQL 8.x for local MVP development (port `8889` in the current local `.env`)
- **ORM**             : Eloquent (Laravel)
- **Auth**            : Laravel Breeze + Google OAuth (Socialite)
- **Authorization**   : Spatie Laravel Permission
- **Cache & Queue**   : Redis (Laravel Cache + Queue)
- **Object Storage**  : S3-compatible (Cloudflare R2 / MinIO / AWS S3)
- **Admin Panel**     : Filament PHP
- **Export PDF**      : Browsershot (preferred) / DomPDF fallback
- **Export DOCX**     : PHPWord
- **Package Manager** : Composer (PHP) + npm (JS assets)
- **Deployment**      : VPS — Nginx + PHP-FPM + Supervisor

---

## 3. Commands

```bash
# Development
php artisan serve              # Start Laravel dev server
npm run dev                    # Start Vite (asset bundler) — run alongside artisan serve

# Build
npm run build                  # Build frontend assets for production

# Code Quality
./vendor/bin/pint              # Laravel Pint — format PHP code
npm run lint                   # ESLint for JS/TS/JSX

# Testing
php artisan test               # Run all tests (PHPUnit / Pest)
php artisan test --filter=Unit # Run unit tests only
php artisan test --filter=Feature # Run feature tests only

# Database
php artisan migrate            # Run pending migrations
php artisan migrate:fresh --seed  # Fresh DB with seeders (dev only)
php artisan db:seed            # Seed data without dropping tables

# Queue
php artisan queue:work         # Start queue worker (dev)
php artisan queue:listen       # Alternative — auto-reloads on code change

# Storage
php artisan storage:link       # Create public storage symlink

# Admin Panel
php artisan filament:make-resource [Name]  # Scaffold Filament resource
```

> Never use `npm` for PHP packages — always use `composer`.
> Never run `migrate:fresh` or `db:seed` on production.
> Always run `php artisan optimize:clear` after pulling changes to staging/production.

---

## 4. Project Structure

**Architecture**: Modular Monolith — domain modules inside a single Laravel app. Do NOT split into microservices.

```
prokerin/                    # Single monolith project folder; keep app code and docs here.
├── app/
│   ├── Domain/                  # Core business logic — one folder per domain
│   │   ├── Organization/        # Organization management
│   │   ├── Membership/          # Members, roles, invitations
│   │   ├── Project/             # Proker / event management
│   │   ├── Task/                # Tasks, subtasks, assignments
│   │   ├── Finance/             # RAB, transactions, approval
│   │   ├── Proposal/            # Proposal generation
│   │   ├── Report/              # LPJ generation
│   │   ├── Document/            # File management
│   │   └── Notification/        # Notification triggers & delivery
│   ├── Actions/                 # Single-responsibility action classes
│   ├── Http/
│   │   ├── Controllers/         # Thin controllers — delegate to Actions
│   │   ├── Middleware/
│   │   └── Requests/            # Form request validation classes
│   ├── Models/                  # Eloquent models
│   ├── Policies/                # Authorization policies (one per model)
│   ├── Jobs/                    # Queue jobs
│   ├── Notifications/           # Laravel notification classes
│   ├── DTOs/                    # Data Transfer Objects
│   └── Support/                 # Helpers, traits, base classes
├── resources/
│   ├── js/
│   │   ├── Pages/               # Inertia page components (one per route)
│   │   ├── Components/          # Shared React components
│   │   │   ├── ui/              # shadcn/ui base components
│   │   │   └── [domain]/        # Domain-specific components
│   │   ├── Layouts/             # App layouts (AppLayout, AuthLayout)
│   │   ├── hooks/               # Custom React hooks
│   │   ├── lib/                 # Utilities, helpers, cn()
│   │   └── types/               # TypeScript types and interfaces
│   └── views/
│       └── app.blade.php        # Single Blade entry point for Inertia
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── web.php                  # All routes (Inertia uses web routes, not API routes)
├── tests/
│   ├── Unit/
│   └── Feature/
└── config/
```

**File placement rules:**

- New Inertia page → `resources/js/Pages/[Domain]/[PageName].tsx`
- Shared React component → `resources/js/Components/[domain]/`
- Viho-derived app shell/component → `resources/js/Components/Viho/`
- Viho-derived menu/data → `resources/js/Data/vihoMenu.ts`
- Viho copied assets → `public/vendor/viho/`
- shadcn/ui primitive → `resources/js/Components/ui/` (auto-generated, don't hand-edit)
- Business logic → `app/Actions/[ActionName].php` or `app/Domain/[Domain]/`
- Eloquent model → `app/Models/`
- Policy → `app/Policies/[ModelName]Policy.php`
- Queue job → `app/Jobs/`
- Form validation → `app/Http/Requests/`
- Do NOT put business logic inside Controllers
- Do NOT create new top-level folders without confirmation
- Do NOT place application code outside `prokerin/`; external template folders are reference-only.
- Do NOT import from `../Viho-*` at runtime. Copy needed assets/patterns into `prokerin/` first.

---

## 5. Naming Conventions

```
# PHP — Files & Classes
- Model           : PascalCase singular      → Organization, ProjectTemplate
- Controller      : PascalCase + Controller  → ProjectController
- Action          : PascalCase + Action      → CreateProjectFromTemplateAction
- Policy          : PascalCase + Policy      → ProjectPolicy
- Job             : PascalCase               → SendTaskDeadlineReminder
- Migration       : snake_case timestamped   → 2026_01_01_create_projects_table
- Seeder          : PascalCase + Seeder      → OrganizationSeeder

# PHP — Inside Code
- Variables       : camelCase               → $projectLead, $budgetTotal
- Constants       : UPPER_SNAKE             → MAX_FILE_SIZE_MB
- Methods         : camelCase               → getProjectProgress(), assignPic()
- Blade templates : kebab-case              → project-detail.blade.php

# JS/TS — Files & Folders
- Inertia Page    : PascalCase.tsx          → ProjectDetail.tsx, CreateProker.tsx
- React Component : PascalCase.tsx          → TaskCard.tsx, BudgetTable.tsx
- Hook            : camelCase, use prefix   → useProjectProgress.ts
- Utility/Helper  : camelCase.ts            → formatCurrency.ts, cn.ts
- TypeScript type : PascalCase.ts           → project.ts, member.ts
- Folder          : kebab-case              → task-management/, rab-finance/

# JS/TS — Inside Code
- Variables       : camelCase               → projectLead, isLoading
- Constants       : UPPER_SNAKE             → MAX_FILE_SIZE, TASK_STATUSES
- Functions       : camelCase               → calculateProgress(), formatRupiah()
- Types/Interfaces: PascalCase              → Project, OrganizationMember
- Enums           : PascalCase              → TaskStatus, ProjectStatus
- CSS Classes     : kebab-case (Tailwind)   → use utility classes directly

# Database
- Tables          : snake_case plural       → organizations, project_members
- Columns         : snake_case              → project_lead_id, event_start_date
- Foreign keys    : [table_singular]_id     → organization_id, project_id
- Pivot tables    : alphabetical_order      → project_member (not member_project)

# Git Branch
- Feature         : feat/[feature-name]     → feat/proposal-generator
- Bug fix         : fix/[bug-name]          → fix/rab-total-calculation
- Hotfix          : hotfix/[name]
- Refactor        : refactor/[name]
- Chore           : chore/[name]            → chore/upgrade-laravel-12
```

---

## 6. Code Conventions

```
# General Approach
- Follow SOLID principles — especially Single Responsibility
- Keep controllers thin: validate input → call Action → return Inertia response
- Business logic lives in Actions or Domain services — never in controllers or models
- DRY: extract reusable logic into shared Actions or traits
- Write readable code over clever/compact code

# PHP
- Strict types: always add declare(strict_types=1) at top of PHP files
- Type hints: always type-hint parameters and return types
- No mixed or untyped return unless absolutely unavoidable
- Use readonly properties and enums where appropriate (PHP 8.4+)
- Use Laravel's built-in helpers (collect(), rescue(), filled(), blank(), etc.)
- Always use Form Request classes for validation — never validate in controllers directly

# TypeScript
- Strict mode enabled in tsconfig.json
- No use of 'any' type — use 'unknown' if type is truly dynamic
- Always define explicit return types for functions
- Use 'interface' for object shapes, 'type' for unions and intersections
- Define Inertia page props types in resources/js/types/

# Inertia.js Pattern
- Use Inertia's Link component instead of <a> for internal navigation
- Pass data from controllers to pages via Inertia::render() only
- Use Inertia shared data (HandleInertiaRequests middleware) for global data (auth, flash)
- Avoid building a separate REST API — all data flows through Inertia web routes

# Import Order (JS/TS)
1. External libraries (React, Inertia, etc.)
2. Internal absolute (@/components, @/lib, etc.)
3. Internal relative (./Component, ../utils)
4. Types and interfaces
5. Assets and styles

# Error Handling
- Always wrap async operations in try-catch
- Never let exceptions bubble unhandled
- Use Laravel's exception handler for API-level errors
- Show user-friendly error messages via Inertia flash or toast
```

---

## 7. Component Rules

```
# React Component Order
1. Imports
2. TypeScript types / interface for props
3. Component definition
4. Hooks (useState, useEffect, custom hooks)
5. Handlers and local functions
6. Return JSX
7. Export

# Props Rules
- Always define explicit TypeScript types for props
- Use default values for optional props
- Maximum 8 props per component — extract sub-components if more needed
- Pass Inertia page props using the usePage() hook, not drilling from layout

# Component Decomposition
- Extract to a separate file if used in more than one place
- Can be co-located in the same file if only used within one parent component
- Keep page components (Pages/) focused on layout and data wiring
- Move complex logic to hooks (hooks/) or utilities (lib/)

# Inertia Pages
- Always type the page props: import { PageProps } from '@/types'
- Use useForm() from @inertiajs/react for all form handling
- Prefer Inertia's router.visit() or Link over manual fetch for navigation
```

---

## 8. Styling Rules

```
# Approach
- Tailwind utility classes directly in JSX — no separate CSS files for component styles unless defining global tokens/base styles
- Do NOT use inline style except for truly dynamic values (e.g., width from JS calculation)
- Do NOT use !important
- Use cn() (from lib/utils.ts) for conditional class merging — never string concatenation

# Visual Direction — Viho Admin Theme
- The UI should visually follow Viho: compact SaaS admin shell, fixed left sidebar, white header, soft page background, flat 4px cards/buttons, subtle shadows, dense operational dashboard layout.
- Use copied Viho assets from `public/vendor/viho/` for logo/avatar/folder imagery when appropriate.
- Prefer Viho-derived components in `resources/js/Components/Viho/` for app shell, cards, dashboard panels, side navigation, and header controls.
- Do not copy full Viho demo modules wholesale. Port only the needed component pattern into TypeScript + Inertia + Tailwind.
- Do not use React Router, Reactstrap, Bootstrap SCSS, or Viho localStorage auth patterns in Prokerin. Convert navigation to Inertia `Link` and auth data to Inertia page props.
- Viho colors are the default visual baseline: primary `#24695c`, secondary `#ba895d`, success `#1b4c43`, danger `#d22d3d`, page background `#f5f7fb`, borders `#e6edef`, body text `#242934`, muted text `#59667a` / `#717171`.
- Avoid purple/indigo SaaS styling unless there is a specific product reason; Prokerin should stay close to Viho's green/brown/admin palette.

# Tailwind Class Order (enforced by Prettier plugin)
layout → spacing → sizing → color → typography → border → shadow → state → animation

# Responsive Design
- Mobile-first approach
- Breakpoints: sm (640px) / md (768px) / lg (1024px) / xl (1280px) / 2xl (1536px)
- Test every new component at sm and lg breakpoints minimum

# Dark Mode
- Support dark mode using Tailwind's dark: prefix
- Always test new components in dark mode before marking done
- Use CSS variables defined in the app's design tokens for semantic colors

# shadcn/ui
- Use shadcn components from resources/js/Components/ui/ as base
- Do NOT modify shadcn/ui files directly — extend via wrapper components
- Use the cn() helper for variant class overrides
- When adding a new shadcn component, run: npx shadcn@latest add [component]

# Design Tokens
- Use semantic color variables (--primary, --muted, --destructive, etc.) defined in globals.css
- Never hardcode hex color values
- Spacing and typography follow Tailwind's default scale
- Until formal CSS variables are introduced, Viho hex tokens may be used in Tailwind arbitrary values to preserve the theme accurately.
```

---

## 9. API & Data Fetching Rules

```
# Inertia-First Approach
- This is NOT a REST API project — data flows through Laravel web routes + Inertia
- All page data is passed via Inertia::render() in controllers
- For server-side mutations: use Inertia forms (useForm) with POST/PUT/PATCH/DELETE
- For real-time or reactive data: use Inertia's reload() or router.reload()

# When to Use Client-Side Fetch (Axios)
- AJAX-only actions that don't require a full page reload (e.g., mark notification read)
- Polling for live status updates (e.g., export job progress)
- All Axios requests must include CSRF token (Laravel handles this via axios defaults)

# Response Format (for any JSON endpoint)
{ success: boolean, data: T | null, message: string }

# Error Handling
- Server validation errors are automatically handled by Inertia (form.errors)
- Use Laravel's validation and let Inertia surface errors to the form
- Use flash messages via session for success/info feedback
- Never expose stack traces or DB errors to the client in production

# File Uploads
- Use Inertia's useForm with files — set forceFormData: true
- Always validate MIME type and file size on the server (not just client)
- Store in S3-compatible storage — never in public/ directly
- Generate signed URLs for file download — never expose raw S3 paths

# Environment
- All external URLs and credentials in .env — never hardcoded
- Use config() helper to access env values in PHP (not env() directly outside config files)
```

---

## 10. Multi-Tenancy & Authorization Rules

```
# Multi-Tenant Model
- All data is scoped by organization_id
- Every query involving org data MUST be scoped to the authenticated user's organization
- Use Eloquent global scopes or explicit where('organization_id', ...) — never forget this

# Authorization
- Use Laravel Policies for every model action (view, create, update, delete)
- Register all policies in AuthServiceProvider
- Use $this->authorize() in controllers — never do manual permission checks in controllers
- Use Spatie Laravel Permission for role-based permissions (hasRole, hasPermissionTo)
- Permission check order: Policy (model-level) → Spatie (role-level)

# Roles (never change role names without updating all references)
- super_admin
- organization_owner
- organization_admin
- project_lead
- secretary
- treasurer
- division_coordinator
- member
- viewer

# Key Rules
- A user can belong to multiple organizations with different roles in each
- Project-level roles are scoped to that project (project_members table)
- Org-level roles are scoped to the organization (organization_members table)
- Never trust client-side role data — always check via Policy/Spatie on the server
```

---

## 11. Performance Rules

```
# Database
- Always eager-load relationships to avoid N+1 queries
  Correct: Project::with(['divisions', 'members', 'tasks'])->find($id)
  Wrong  : foreach ($projects as $p) { $p->tasks; }  // N+1
- Use database indexes on all foreign keys and frequently queried columns
- Paginate large result sets — never return unlimited records to frontend
- Use Redis cache for expensive calculations (e.g., project progress percentage)

# Queue
- All heavy operations must run in the queue: PDF export, DOCX export, email sending
- Never run exports or email sending synchronously in a request
- Use job chains for multi-step operations (generate doc → upload to S3 → notify user)

# Frontend
- Use dynamic import (lazy loading) for heavy page components
- Use React.memo sparingly — profile first, optimize second
- Avoid unnecessary re-renders: check useCallback and useMemo usage
- Import only what you need from libraries (tree-shaking)

# Assets
- All images go through S3 / CDN — never serve from the app server
- Use Vite's asset optimization for bundling
- Avoid loading large JS libraries just for small utilities
```

---

## 12. Git Rules

Every time Codex finishes a meaningful change, commit to git before moving to the next task. This enables easy diff and rollback.

```
# Commit Message Format
feat     : [description of new feature]
fix      : [description of bug fixed]
refactor : [description of refactor]
style    : [styling or formatting change]
docs     : [documentation change]
test     : [test added or changed]
chore    : [config or tooling change]
db       : [migration or seeder change]

# Examples
feat: add proposal generator with auto-fill from project data
feat: implement RAB vs realization comparison view
fix: resolve project progress percentage not updating after task done
fix: scope organization query to authenticated user — prevent data leak
refactor: extract budget calculation into CalculateBudgetTotalAction
db: add transactions table with approval flow columns
chore: configure Supervisor for queue workers

# Additional Rules
- Never commit .env, .env.local, or any file containing secrets
- Never commit storage/ contents or vendor/ directory
- One commit per logical change — do not mix unrelated changes
- Always run php artisan test before committing on feature branches
```

---

## 13. Features

Feature status is maintained in `features.md`. Any AI/agent that needs to continue Prokerin feature work MUST read `features.md` first before deciding what to build, test, or mark complete.

Rules for feature tracking:

- Do not duplicate detailed feature status in `AGENTS.md`.
- Keep `features.md` as the single source of truth for completed work, partial work, pending work, verification results, and next actions.
- Update `features.md` whenever a module status changes, a test/build result changes, a migration is added, or a Post-MVP module moves forward.
- Before starting new feature work, check `features.md` sections `Ringkasan Status`, `Verifikasi Terakhir`, target module breakdown, and `Next Action`.
- Before marking a feature `[x]`, make sure it has code, route/UI or backend integration as applicable, tests, and a recorded verification result in `features.md`.

### Current Handoff Notes — 2026-05-16

- MVP M01-M13 are implemented, listed in `features.md`, and validated by automated tests.
- Frontend build verification passed on 2026-05-16 with `npm run build`.
- PHP platform verification passed on 2026-05-16 with PHP 8.4.10 via `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH composer check-platform-reqs`.
- PHP test verification passed on 2026-05-16 with PHP 8.4.10 via `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` (`181 passed`, `712 assertions`).
- M14 Rapat & Notulen was added after MVP validation; full test verification passed on 2026-05-16 with `183 passed`, `755 assertions`.
- Default shell PHP may still resolve to `/opt/homebrew/opt/php@8.3/bin/php`; use the PHP 8.4 PATH prefix above or relink Homebrew PHP before running Composer/Artisan.
- Post-MVP module status is tracked in `features.md`; do not duplicate the feature checklist here.

---

## 14. Testing

```
# Testing Approach
- Framework   : Pest PHP (preferred) or PHPUnit
- Focus       : Feature tests for core workflows, Unit tests for business logic
- No E2E yet  : Manual QA for now — Playwright/Dusk in post-MVP phase

# What to Test (Priority Order)
1. All Action classes (business logic) — unit tests
2. Authorization/Policy — every role × every action
3. Core feature flows — feature tests (create org, create proker, generate LPJ, etc.)
4. Budget calculations and project progress calculations
5. Multi-tenancy scope — ensure users cannot access other orgs' data

# What NOT to Test
- Simple Eloquent accessors/mutators with no logic
- Filament admin panel internals (tested by Filament team)
- Third-party library behavior

# Test Naming Convention (Pest style)
it('allows project lead to create a task', function () { ... });
it('prevents member from accessing organization finance', function () { ... });
it('calculates project progress correctly when all tasks done', function () { ... });

# Test Pattern (AAA)
- Arrange : set up models, auth user, seed needed data
- Act     : call the action or make the HTTP request
- Assert  : check response, database state, or thrown exception

# Coverage Target
- Minimum : 70% for Actions and Policies
- Priority : Authorization > Business Logic > API endpoints > UI components
```

---

## 15. Do Not

If any instruction or prompt is ambiguous, ASK FIRST before writing code. Do not assume and proceed.

```
# Structure & Files
- Do NOT create new top-level folders without confirmation
- Do NOT delete any file without confirmation
- Do NOT move or rename files without confirmation
- Do NOT modify shadcn/ui base components in Components/ui/ directly
- Do NOT put business logic in Controllers — use Actions

# PHP Code
- Do NOT use env() directly in application code — use config() only
- Do NOT hardcode credentials, URLs, or keys anywhere in code
- Do NOT run raw DB::statement() that drops or truncates tables without confirmation
- Do NOT use Eloquent without scoping to the correct organization_id
- Do NOT skip Form Request validation — always validate via Request classes
- Do NOT put heavy operations in the HTTP request cycle — use queue jobs

# TypeScript / React
- Do NOT use 'any' type
- Do NOT fetch data inside useEffect — use Inertia's data passing or reload()
- Do NOT build a REST API layer — this project uses Inertia (web routes only)
- Do NOT use inline styles for anything achievable with Tailwind utility classes
- Do NOT manually join CSS class strings — always use cn() helper
- Do NOT bring Viho's standalone SPA dependencies into runtime unless there is a confirmed need. Port needed components into the local Inertia architecture.

# Database
- Do NOT run migrate:fresh or any destructive command on staging or production
- Migration creation is approved for MVP persistence work as of 2026-05-16; keep migrations additive, non-destructive, and scoped to Prokerin MVP modules.
- Do NOT expose database credentials anywhere in frontend code
- Do NOT return raw Eloquent model data to Inertia without using API Resources or explicit array

# Security
- Do NOT expose raw S3 file paths — use signed URLs
- Do NOT skip authorization checks — every controller method must call $this->authorize()
- Do NOT skip MIME type validation on file uploads
- Do NOT trust client-submitted organization_id or role — always derive from session/auth

# Scope Creep
- Do NOT implement post-MVP features before MVP modules are complete
- Do NOT add AI features, QR absensi, or payment gateway during MVP phase
- Do NOT build native mobile app during MVP — PWA only
```

---

## 16. Environment Variables

```
# Setup
- Copy .env.example to .env for local development
- Never commit .env to the repository — it is in .gitignore
- Always add new env variables to .env.example (with empty or example values)

# Application
APP_NAME=Prokerin
APP_ENV=local                   # local / staging / production
APP_KEY=                        # Generated by php artisan key:generate
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=prokerin
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue & Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Object Storage (S3-compatible)
AWS_ACCESS_KEY_ID=              # Server-only — NEVER expose to client
AWS_SECRET_ACCESS_KEY=          # Server-only — NEVER expose to client
AWS_DEFAULT_REGION=auto
AWS_BUCKET=prokerin
AWS_ENDPOINT=                   # e.g., https://[account].r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=                  # Server-only — NEVER expose to client
MAIL_FROM_ADDRESS=hello@prokerin.id
MAIL_FROM_NAME=Prokerin

# Google OAuth
GOOGLE_CLIENT_ID=               # Obtain from Google Cloud Console
GOOGLE_CLIENT_SECRET=           # Server-only — NEVER expose to client
GOOGLE_REDIRECT_URI=            # e.g., https://prokerin.id/auth/google/callback

# WhatsApp (post-MVP)
# WHATSAPP_API_URL=
# WHATSAPP_API_TOKEN=           # Server-only

# Payment Gateway (post-MVP)
# MIDTRANS_SERVER_KEY=          # Server-only — NEVER expose to client
# MIDTRANS_CLIENT_KEY=          # Public key — safe for client
# MIDTRANS_IS_PRODUCTION=false
```

---

_Update this file whenever: a new module is started, tech stack changes, new conventions are agreed upon, or a post-MVP feature moves to active development. Accurate AGENTS.md = better Codex output._
