# SA01 · Super Admin Panel (Filament)
## Full Implementation Prompt for AI Agent / Codex

> **Read `AGENTS.md` and `features.md` in full before writing any code.**
> This document is the complete specification for building the Prokerin internal super admin panel using Filament PHP.
> This panel is for the **Prokerin internal team only** — not for organization users.
> **Language rule:** All code, filenames, variable names, and PHP/TS comments follow `AGENTS.md` conventions. All Filament UI labels, column headers, field labels, and action button text must be in **English** (Filament admin is internal tooling, not customer-facing).

---

## 0. Context & Position in the Roadmap

### Why This Module Exists

Prokerin is a multi-tenant SaaS. As organizations grow, the internal team needs a secure, structured way to:

- Inspect and manage all organizations, users, and data across tenants
- Diagnose issues without touching the database directly
- Impersonate users for support debugging
- Monitor platform-wide health metrics
- Manage global configuration (notification defaults, system features)

Without this panel, all internal operations require direct database access or raw Artisan commands — both error-prone and unauditable.

### What This Panel Is NOT

- It is **not** a workspace for organization users (that is the Inertia app)
- It is **not** the `campus_admin` panel (that is M24, not started)
- It is **not** exposed to any organization owner, admin, or member
- It is **not** a replacement for any existing workspace feature

### Architecture Position

```
prokerin/
├── Inertia App (Laravel + React)      ← Organization users (M01–M28.5)
│   └── /dashboard, /proker, etc.
│
├── Filament Panel (Laravel + Filament) ← Internal team only (THIS MODULE)
│   └── /internal-admin
│
└── Future: Campus Admin Panel (M24)   ← Campus admins (not started)
    └── /campus-admin
```

All three share the **same Laravel monolith, same database, same models**. Zero code duplication for data models.

### Prerequisites — verify all are `[x]` in `features.md` before starting

- `[x]` M01 · Auth & Account Management (User model exists)
- `[x]` M02 · Organization Management (Organization model exists)
- `[x]` M03 · Member & Role Management (Spatie Permission installed)
- `[x]` M04 · Proker Management (Project model exists)
- `[x]` M07 · RAB & Finance (Finance models exist)
- `[x]` M08 · Proposal Generator (Proposal model exists)
- `[x]` M10 · LPJ Generator (LPJ model exists)
- `[x]` M28.5 · Role-Aware Dashboard (super_admin role already defined in Spatie)

Run baseline before starting:

```bash
npm run build
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
```

All tests must pass. Do not proceed if any test is red.

### What is explicitly OUT OF SCOPE for SA01

- Campus admin panel (M24 — separate module)
- Any organization-level feature changes
- Dark mode for Filament (use Filament default)
- Custom Filament theme beyond basic branding
- Automated billing or payment management (post-MVP)
- AI-powered analytics (M23 — separate module)

---

## 1. Filament Installation & Configuration

### 1.1 Install Filament

Filament is already listed in the tech stack (`AGENTS.md`). Verify it is installed:

```bash
composer show filament/filament
```

If not installed:

```bash
composer require filament/filament:"^3.0" --break-system-packages
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan filament:install --panels
```

### 1.2 Panel Configuration

Create or update the Filament panel provider at `app/Providers/Filament/AdminPanelProvider.php`:

```php
<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('internal-admin')
            ->colors(['primary' => Color::Teal])
            ->brandName('Prokerin Admin')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
```

### 1.3 Access Gate — Super Admin Only

Create `app/Filament/SuperAdminGate.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament;

use App\Models\User;
use Filament\Panel;

class SuperAdminGate
{
    public static function canAccess(Panel $panel): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasRole('super_admin');
    }
}
```

Register the gate in the panel provider by adding to `panel()`:

```php
->authorizationRules(fn (Panel $panel): bool => \App\Filament\SuperAdminGate::canAccess($panel))
```

### 1.4 Assign Super Admin Role (Seeder)

Add to the existing role seeder (do NOT create a separate seeder file):

```php
// In the existing RoleMatrixSeeder or DatabaseSeeder
use Spatie\Permission\Models\Role;
use App\Models\User;

// Ensure super_admin role exists
Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

// Assign to a dev-only admin user (local/staging only)
$superAdmin = User::firstOrCreate(
    ['email' => 'superadmin@prokerin.internal'],
    [
        'name'              => 'Super Admin',
        'password'          => bcrypt('super-secret-dev-password'),
        'email_verified_at' => now(),
    ]
);

if (app()->environment(['local', 'staging'])) {
    $superAdmin->assignRole('super_admin');
}
```

**Rules:**
- `superadmin@prokerin.internal` is a dev/staging-only seeded account.
- In production, `super_admin` must be assigned manually via `php artisan tinker` or a secure one-time command — never via seeder.
- Never commit production super admin credentials anywhere.

---

## 2. File Structure

```
prokerin/
├── app/
│   ├── Filament/
│   │   ├── SuperAdminGate.php              ← Access gate
│   │   ├── Pages/
│   │   │   └── Dashboard.php               ← Custom Filament dashboard
│   │   ├── Resources/
│   │   │   ├── UserResource.php            ← All platform users
│   │   │   ├── UserResource/
│   │   │   │   └── Pages/
│   │   │   │       ├── ListUsers.php
│   │   │   │       ├── CreateUser.php
│   │   │   │       └── EditUser.php
│   │   │   ├── OrganizationResource.php    ← All organizations
│   │   │   ├── OrganizationResource/
│   │   │   │   └── Pages/
│   │   │   │       ├── ListOrganizations.php
│   │   │   │       ├── CreateOrganization.php
│   │   │   │       └── EditOrganization.php
│   │   │   ├── ProjectResource.php         ← All proker across all orgs
│   │   │   ├── ProjectResource/
│   │   │   │   └── Pages/
│   │   │   │       ├── ListProjects.php
│   │   │   │       └── ViewProject.php
│   │   │   ├── NotificationRuleResource.php ← Global notification config
│   │   │   └── NotificationRuleResource/
│   │   │       └── Pages/
│   │   │           ├── ListNotificationRules.php
│   │   │           └── EditNotificationRule.php
│   │   └── Widgets/
│   │       ├── PlatformStatsOverview.php   ← KPI summary widget
│   │       ├── RecentOrganizationsTable.php ← Latest signups
│   │       └── RecentUsersTable.php        ← Latest registrations
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php      ← Panel config
```

---

## 3. Resources to Build

### 3.1 UserResource

**Route:** `/internal-admin/users`

**Columns (ListUsers table):**

| Column | Source | Sortable | Searchable |
|--------|--------|----------|------------|
| ID | `users.id` | Yes | No |
| Name | `users.name` | Yes | Yes |
| Email | `users.email` | Yes | Yes |
| Roles | Spatie roles (comma-separated) | No | No |
| Organizations | Count of orgs via `organization_members` | No | No |
| Email Verified | `users.email_verified_at` (boolean badge) | Yes | No |
| Registered At | `users.created_at` | Yes | No |

**Filters:**
- By role (multi-select: all Spatie roles)
- By email verified (Yes / No)
- Date range: registered at

**Actions (per row):**
- **View** — read-only detail page showing roles, org memberships, recent activity
- **Edit** — name, email, force-verify email, assign/remove roles
- **Impersonate** — see section 3.1.1 below
- **Delete** — soft confirmation dialog; only allowed if user has no active org ownership (prevent orphan org)

**Form (Create / Edit):**

```
Fields:
- Name           (text, required)
- Email          (email, required, unique)
- Password       (password, optional on edit — leave blank to keep existing)
- Email Verified (toggle — super admin can force-verify)
- Roles          (multi-select from all Spatie roles except super_admin self-assignment)
```

**Rules:**
- A super_admin cannot assign `super_admin` role to another user via the UI — must use Artisan.
- A super_admin cannot delete themselves.
- Deleting a user who is the sole `organization_owner` of any org must be blocked with a clear error message.

#### 3.1.1 User Impersonation

Impersonation allows a super_admin to log in as any user to diagnose issues.

**Package:**

```bash
composer require lab404/laravel-impersonate
```

**Implementation:**
- Add `\Lab404\Impersonate\Models\Impersonate` trait to `app/Models/User.php`.
- Protect via gate: only `super_admin` can impersonate. No one can impersonate another `super_admin`.
- Add `canBeImpersonated()` method to User model: return `!$this->hasRole('super_admin')`.
- Add `canImpersonate()` method to User model: return `$this->hasRole('super_admin')`.
- Add Filament action button "Impersonate" on each user row — triggers `ImpersonateAction`.
- On impersonation start: redirect to `/dashboard` (Inertia workspace, not Filament).
- Add a visible banner inside the Inertia app when impersonation is active: "You are impersonating [Name]. [Stop Impersonating]".
- Add route: `GET /impersonate/leave` → stops impersonation, redirects back to `/internal-admin/users`.

**Banner component (Inertia side):**

Add to `app/Http/Middleware/HandleInertiaRequests.php` shared props:

```php
'impersonating' => session()->has('impersonator_id') ? [
    'active'        => true,
    'impersonator'  => auth()->user()?->getKey() !== session('impersonator_id')
                        ? User::find(session('impersonator_id'))?->name
                        : null,
    'leave_url'     => route('impersonate.leave'),
] : null,
```

Add `ImpersonationBanner.tsx` to `resources/js/Components/` — renders a sticky top bar warning when `impersonating` is not null. Include in `AppLayout.tsx` (not `LandingLayout.tsx`).

**Audit log:** Every impersonation start and stop must be logged to `activity_logs` table (see section 5).

---

### 3.2 OrganizationResource

**Route:** `/internal-admin/organizations`

**Columns (ListOrganizations table):**

| Column | Source | Sortable | Searchable |
|--------|--------|----------|------------|
| ID | `organizations.id` | Yes | No |
| Name | `organizations.name` | Yes | Yes |
| Slug | `organizations.slug` | No | Yes |
| Owner | Owner user name via `organization_members` | No | Yes |
| Members | Count via `organization_members` | No | No |
| Active Projects | Count via `projects` where `status = active` | No | No |
| Plan Tier | `organizations.plan_tier` (see section 3.2.1) | Yes | No |
| Created At | `organizations.created_at` | Yes | No |

**Filters:**
- By plan tier (Free / Starter / Pro / Campus)
- By member count range (slider)
- Date range: created at

**Actions (per row):**
- **View** — read-only detail: members list, active proker list, finance summary total, recent activity
- **Edit** — name, slug, plan tier, notes (internal)
- **Force Delete** — permanently delete an org and all its data (double confirmation required; logs to audit trail)

**Form (Edit only — creation is done via normal signup flow):**

```
Fields:
- Name            (text)
- Slug            (text, must remain globally unique)
- Plan Tier       (select: free / starter / pro / campus)
- Internal Notes  (textarea — visible only to super admins, not shown to org users)
```

#### 3.2.1 Plan Tier Column

Add `plan_tier` column to `organizations` table via migration:

```php
// Migration: 2026_05_17_000001_add_plan_tier_to_organizations_table.php
Schema::table('organizations', function (Blueprint $table): void {
    $table->string('plan_tier')->default('free')->after('slug');
    $table->text('internal_notes')->nullable()->after('plan_tier');
    $table->index('plan_tier');
});
```

Add enum `app/Domain/Organization/Enums/PlanTier.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Organization\Enums;

enum PlanTier: string
{
    case Free    = 'free';
    case Starter = 'starter';
    case Pro     = 'pro';
    case Campus  = 'campus';

    public function label(): string
    {
        return match($this) {
            self::Free    => 'Free',
            self::Starter => 'Starter',
            self::Pro     => 'Pro',
            self::Campus  => 'Campus',
        };
    }
}
```

Cast in `Organization` model:

```php
protected $casts = [
    'plan_tier' => \App\Domain\Organization\Enums\PlanTier::class,
];
```

---

### 3.3 ProjectResource

**Route:** `/internal-admin/projects`

**Purpose:** Read-only visibility into all proker across all organizations. Super admin can view but cannot edit (org data integrity must be preserved by org owners).

**Columns:**

| Column | Source | Sortable | Searchable |
|--------|--------|----------|------------|
| ID | `projects.id` | Yes | No |
| Name | `projects.name` | Yes | Yes |
| Organization | `organizations.name` via relation | No | Yes |
| Status | `projects.status` (badge) | Yes | No |
| Progress | `projects.progress_percentage` (progress bar) | Yes | No |
| Lead | Project lead user name | No | No |
| Start Date | `projects.event_start_date` | Yes | No |
| End Date | `projects.event_end_date` | Yes | No |

**Filters:**
- By status (Planning / Active / Completed / Archived)
- By organization (select from all orgs)
- Date range: start date

**Actions (per row):**
- **View** — read-only: task summary, budget summary, proposal status, LPJ status

**No create / edit / delete actions.** Project lifecycle is managed by org users only.

---

### 3.4 NotificationRuleResource

**Route:** `/internal-admin/notification-rules`

**Purpose:** Manage global default notification rules that seed new organizations. Editing these does not affect existing org rules — only new org seeds.

**Columns:**

| Column | Source | Sortable | Searchable |
|--------|--------|----------|------------|
| ID | `notification_rules.id` | No | No |
| Event Type | `notification_rules.event_type` | Yes | Yes |
| Channels | `notification_rules.channels` (badge list) | No | No |
| Enabled | `notification_rules.is_enabled` (toggle) | No | No |

**Actions:**
- **Edit** — toggle enabled/disabled, change channels (email / in-app / whatsapp)
- **No delete** — notification rules are system-defined

---

## 4. Dashboard Page (Filament)

**Route:** `/internal-admin` (default Filament dashboard)

### 4.1 Stats Overview Widget — `PlatformStatsOverview`

Four stat cards in a row:

```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ Total Users     │ Total Orgs      │ Active Projects │ Plans Breakdown │
│ [count]         │ [count]         │ [count]         │ Free: X         │
│ ▲ +N this week  │ ▲ +N this week  │ across all orgs │ Starter: X      │
│                 │                 │                 │ Pro: X          │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘
```

Data source: direct Eloquent aggregates — no Redis cache needed (Filament panel is low-traffic).

### 4.2 Recent Organizations Widget — `RecentOrganizationsTable`

Table of 10 most recently created organizations:
- Name, Owner, Plan Tier, Member Count, Created At
- Link to OrganizationResource edit page

### 4.3 Recent Users Widget — `RecentUsersTable`

Table of 10 most recently registered users:
- Name, Email, Verified, Roles, Registered At
- Link to UserResource edit page

---

## 5. Audit Log

Every super admin action must be logged. This is non-negotiable for internal tooling.

### 5.1 Table

```php
// Migration: 2026_05_17_000002_create_activity_logs_table.php
Schema::create('activity_logs', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('action');           // e.g. 'impersonate.start', 'user.delete', 'org.plan_tier.change'
    $table->string('target_type');      // Eloquent morph type, e.g. 'App\Models\User'
    $table->unsignedBigInteger('target_id');
    $table->json('payload')->nullable(); // before/after values for edits
    $table->string('ip_address', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'created_at']);
    $table->index(['target_type', 'target_id']);
});
```

### 5.2 Action Class

Create `app/Actions/LogActivityAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class LogActivityAction
{
    public function execute(
        string $action,
        Model $target,
        array $payload = [],
    ): void {
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => get_class($target),
            'target_id'   => $target->getKey(),
            'payload'     => $payload ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
```

### 5.3 Actions to Log

| Action | Log Key | Payload |
|--------|---------|---------|
| Impersonation start | `impersonate.start` | `{ target_user_id, target_user_email }` |
| Impersonation stop | `impersonate.stop` | `{ target_user_id }` |
| User role change | `user.role.change` | `{ before: [], after: [] }` |
| User delete | `user.delete` | `{ email, name }` |
| Organization plan tier change | `org.plan_tier.change` | `{ before: 'free', after: 'pro' }` |
| Organization force delete | `org.force_delete` | `{ name, member_count }` |

---

## 6. Security Rules

These rules are absolute — do not deviate.

```
# Access
- /internal-admin/* routes MUST be gated to super_admin role ONLY
- No middleware relaxation under any condition
- Session-based auth (same web guard as main app) — no separate auth flow needed
- Rate-limit login attempts to /internal-admin: max 5 attempts per minute per IP

# Data Exposure
- Filament resources for User must NEVER display raw password hashes
- Filament resources must NEVER display S3 secret keys or other .env values
- WhatsApp API tokens and other credentials must NEVER appear in any Filament column or form

# Impersonation
- super_admin CANNOT impersonate another super_admin
- Impersonation session must expire after 2 hours of inactivity
- Every impersonation start and stop logged to activity_logs (non-negotiable)
- Stop-impersonating route must always be accessible regardless of Inertia page state

# Audit
- All destructive actions (delete, force-delete) require a typed confirmation input (type org/user name to confirm)
- ActivityLog records are append-only — no update or delete routes for activity_logs
- Super admin cannot delete their own activity log entries
```

---

## 7. Environment Variables

Add to `.env.example`:

```
# Super Admin Panel
FILAMENT_ADMIN_PATH=internal-admin          # Do not change in production without redirect
FILAMENT_BRAND_NAME="Prokerin Admin"

# Impersonation
IMPERSONATE_MAX_DURATION_HOURS=2
```

Access in config — never in application code directly via `env()`.

---

## 8. Test Coverage

### 8.1 Unit Tests

**`tests/Unit/SuperAdmin/SuperAdminGateTest.php`**

```
it('grants access to user with super_admin role')
it('denies access to user with organization_owner role')
it('denies access to user with no roles')
it('denies access to unauthenticated request')
```

**`tests/Unit/SuperAdmin/LogActivityActionTest.php`**

```
it('creates activity_log record with correct action and target')
it('records the authenticated user id')
it('records ip address and user agent from request')
it('stores payload as json when provided')
it('stores null payload when none provided')
```

### 8.2 Feature Tests

**`tests/Feature/SuperAdmin/FilamentAccessTest.php`**

```
it('redirects guest away from internal-admin')
it('redirects organization_owner away from internal-admin')
it('redirects member away from internal-admin')
it('allows super_admin to access internal-admin dashboard')
it('super_admin sees platform stats on dashboard')
```

**`tests/Feature/SuperAdmin/UserResourceTest.php`**

```
it('super_admin can list all users')
it('super_admin can edit user name and email')
it('super_admin can force verify user email')
it('super_admin cannot assign super_admin role via ui')
it('super_admin cannot delete a user who is sole org owner')
it('user delete is logged to activity_logs')
```

**`tests/Feature/SuperAdmin/OrganizationResourceTest.php`**

```
it('super_admin can list all organizations')
it('super_admin can change organization plan tier')
it('plan tier change is logged to activity_logs with before and after values')
it('super_admin cannot edit organization via normal org owner route')
```

**`tests/Feature/SuperAdmin/ImpersonationTest.php`**

```
it('super_admin can impersonate a regular user')
it('super_admin cannot impersonate another super_admin')
it('impersonation start is logged to activity_logs')
it('stop impersonation redirects to internal-admin')
it('impersonation stop is logged to activity_logs')
it('organization_owner cannot trigger impersonation route')
```

### 8.3 Coverage Targets

| Scope | Target |
|-------|--------|
| `SuperAdminGate` | 100% |
| `LogActivityAction` | 100% |
| Filament access control | 100% |
| Impersonation start/stop | 100% |
| Resource CRUD actions | 80% minimum |

---

## 9. Build Order

Follow this order exactly — do not skip steps.

```
1.  Verify Filament is installed (composer show filament/filament)
2.  Create AdminPanelProvider — configure path, colors, brand name
3.  Create SuperAdminGate — register as authorizationRules in panel
4.  Add super_admin seeder entry (dev/staging only)
5.  Run migration: add_plan_tier_to_organizations_table
6.  Run migration: create_activity_logs_table
7.  Create PlanTier enum + cast in Organization model
8.  Create ActivityLog model
9.  Create LogActivityAction
10. Build PlatformStatsOverview widget (dashboard)
11. Build RecentOrganizationsTable widget (dashboard)
12. Build RecentUsersTable widget (dashboard)
13. Build UserResource (list + edit + delete)
14. Build OrganizationResource (list + edit)
15. Build ProjectResource (list + view, read-only)
16. Build NotificationRuleResource (list + edit)
17. Install lab404/laravel-impersonate
18. Add Impersonate trait to User model
19. Add impersonation Filament action to UserResource
20. Add ImpersonationBanner.tsx to Inertia AppLayout
21. Add impersonating shared prop to HandleInertiaRequests
22. Wire activity log calls to all destructive actions and impersonation
23. Write all unit and feature tests
24. Run full test suite + npm run build
25. Browser smoke test (all resources + impersonation flow)
```

---

## 10. Browser Smoke Test Checklist

Run this manually after build before committing:

```
[ ] /internal-admin redirects guest → login page
[ ] /internal-admin redirects organization_owner → 403 or login
[ ] superadmin@prokerin.internal logs in → lands on /internal-admin dashboard
[ ] Dashboard shows correct user count, org count, active project count
[ ] Users list loads, search by email works
[ ] Edit a user: change name, save → name updated in database
[ ] Toggle email_verified on a user → reflects immediately
[ ] Organizations list loads, filter by plan_tier works
[ ] Change plan tier for one org → logged in activity_logs
[ ] Projects list loads, filter by organization works
[ ] Impersonate a member user → redirected to /dashboard as that user
[ ] Impersonation banner visible in Inertia app
[ ] Stop impersonation → redirected back to /internal-admin/users
[ ] activity_logs table has entries for impersonate.start and impersonate.stop
[ ] Attempting to impersonate another super_admin → blocked with error
[ ] npm run build passes with no TypeScript errors
[ ] php artisan test passes — all tests green
```

---

## 11. Pre-Commit Checklist

```bash
# 1. Full test suite green
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test

# 2. TypeScript compiles clean
npm run build

# 3. PHP code formatted
./vendor/bin/pint

# 4. Migrations are additive (no drops, no truncates)
# Review: git diff database/migrations/

# 5. No credentials committed
# Review: git diff .env* config/
```

---

## 12. Commit Convention

```bash
feat(sa01): configure filament panel at /internal-admin with super_admin gate
feat(sa01): add plan_tier and internal_notes to organizations table
feat(sa01): create activity_logs table and LogActivityAction
feat(sa01): add platform stats dashboard widgets
feat(sa01): add UserResource with edit, delete, and role management
feat(sa01): add OrganizationResource with plan tier management
feat(sa01): add ProjectResource (read-only cross-org view)
feat(sa01): add NotificationRuleResource for global defaults
feat(sa01): add user impersonation with ImpersonationBanner in Inertia app
feat(sa01): wire activity logging to all destructive actions and impersonation
test(sa01): add super admin gate, access, resource, and impersonation tests
chore(sa01): verify build and full test suite green
```

One commit per logical step. Never combine all changes into a single commit.

---

## 13. `features.md` Entry — Add After Module is Complete

Add this block to `features.md` under a new section after M28.5:

```markdown
### SA01 · Super Admin Panel (Filament)

**Status:** `[x]` Complete.

#### What Is Built
- Filament panel at `/internal-admin`, gated to `super_admin` role only via `SuperAdminGate`.
- `PlanTier` enum + migration: `plan_tier` and `internal_notes` columns on `organizations`.
- `activity_logs` table + `ActivityLog` model + `LogActivityAction`.
- Dashboard with three widgets: `PlatformStatsOverview`, `RecentOrganizationsTable`, `RecentUsersTable`.
- `UserResource`: list all users, edit name/email/roles/verified status, delete with guard.
- `OrganizationResource`: list all orgs, edit plan tier and internal notes.
- `ProjectResource`: read-only cross-org proker visibility.
- `NotificationRuleResource`: manage global notification rule defaults.
- User impersonation via `lab404/laravel-impersonate` with `ImpersonationBanner` in Inertia app.
- Audit logging for all destructive actions and impersonation start/stop.

#### Test Coverage
- Unit: `SuperAdminGate`, `LogActivityAction`
- Feature: Filament access control (all roles), UserResource CRUD, OrganizationResource, impersonation start/stop/block

#### Verification Log Entry (fill in after running)
- [ ] `npm run build` passed
- [ ] `PATH=... php artisan test` → X passed, Y assertions
- [ ] Browser smoke: /internal-admin accessible only to super_admin
- [ ] All 5 Filament resources load correctly
- [ ] Impersonation flow verified: start → banner visible → stop → back to admin
- [ ] activity_logs records verified for impersonation and plan tier change
```

---

## 14. Do Not

```
# Structure
- Do NOT expose /internal-admin routes via Inertia (Filament is fully server-rendered)
- Do NOT add organization-scoped middleware to Filament routes
- Do NOT import Filament components into the Inertia React app
- Do NOT create a new top-level app folder without confirmation

# Security
- Do NOT allow super_admin role assignment via the Filament UI form
- Do NOT disable CSRF protection on any Filament route
- Do NOT skip audit logging on any destructive action
- Do NOT expose .env values, password hashes, or S3 keys in any Filament resource

# Data
- Do NOT allow super_admin to bypass organization_id scoping in the Inertia app
  (impersonation inherits normal workspace scoping — super_admin sees exactly what the impersonated user sees)
- Do NOT add edit or delete actions to ProjectResource (read-only for super_admin)
- Do NOT run migrate:fresh or db:seed on production

# Scope Creep
- Do NOT build campus_admin panel here (that is M24)
- Do NOT build billing/payment management (post-MVP)
- Do NOT add AI analytics or smart features (M23)
```

---

*Last updated: 2026-05-17. Update this file if new Filament resources are added, the panel path changes, or new audit log actions are defined.*
