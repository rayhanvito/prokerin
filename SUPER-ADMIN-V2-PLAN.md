# PROKERIN — Super Admin Panel V2 Prompt
## Untuk: Claude Opus (claude-opus-4-5) via Claude Code
## Kode Modul: SA02 (UX & Resource Expansion) + SA03 (Security Hardening)
## Prerequisite: SA01 sudah selesai — panel `/internal-admin` sudah ada dan berfungsi dasar

---

## INSTRUKSI UNTUK AGENT

Kamu adalah senior full-stack engineer yang bekerja di codebase **Prokerin** — aplikasi manajemen program kerja (proker) untuk organisasi kampus Indonesia. Stack: **Laravel 11 + Filament v3 + Inertia.js + React (TypeScript) + Tailwind CSS**.

Tugasmu: eksekusi upgrade Filament super admin panel dari kondisi MVP (SA01) ke panel production-ready (SA02 + SA03). Dokumen ini adalah **source of truth** — ikuti setiap fase secara berurutan. Setelah tiap fase selesai, jalankan gate verifikasi sebelum lanjut ke fase berikutnya.

**BACA DULU sebelum mulai:**
```bash
cat AGENTS.md
cat app/Providers/Filament/AdminPanelProvider.php
cat app/Filament/Resources/UserResource.php
cat app/Filament/Resources/OrganizationResource.php
cat app/Filament/Pages/PrkAdminDashboard.php 2>/dev/null || echo "Belum ada"
ls app/Filament/Resources/ app/Filament/Pages/ app/Filament/Widgets/
php artisan route:list | grep internal-admin | head -20
```

---

## CONSTRAINT WAJIB (AGENTS.md)

Sebelum menulis kode apapun, internalize constraint ini:

```
PHP     : declare(strict_types=1) di setiap file PHP baru
          Type hint semua parameter dan return type
          Readonly properties di DTO
          Enum untuk nilai tersedia (severity, audience, status)

Structure: Business logic di app/Actions/Admin/ — bukan di Resource/Page
           Resource hanya query + UI, logic di Action
           Filament Resource boleh getEloquentQuery() dengan eager-load
           Tidak ada logika domain di blade/view

Filament : Jangan modifikasi app/Filament/* yang tidak relevan dengan fase ini
           Eager-load relasi di getEloquentQuery()
           Setiap mutation → audit log via LogActivityAction

Security : env() hanya di config/
           Env baru → tambah ke .env.example
           File baru config → config/admin.php

Frontend : Jangan sentuh resources/js/components/ui/ (Shadcn primitives)
           Komponen baru Inertia di resources/js/Components/Admin/ saja
           Tidak ada localStorage — gunakan server-side session

Scope    : LCMS01 DIBATALKAN — tidak ada navigation group "Landing CMS"
           Tidak ada migration untuk landing content
           Fokus hanya SA02 + SA03
```

---

## GATE VERIFIKASI (Jalankan setelah TIAP FASE)

```bash
# Gate wajib — jangan lanjut ke fase berikutnya kalau ada yang merah
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test --stop-on-failure
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
npm run build
npm run lint 2>/dev/null || true
php artisan route:list | grep -c "internal-admin"
```

---

## STATE AWAL (SA01 — sudah ada sebelum kamu mulai)

```
Panel URL  : /internal-admin
Provider   : app/Providers/Filament/AdminPanelProvider.php
Resources  : UserResource, OrganizationResource, ProjectResource (read-only),
             NotificationRuleResource, DocumentExportResource
Pages      : (default Filament dashboard)
Widgets    : PlatformStatsOverview, RecentOrganizationsTable, RecentUsersTable
Auth       : Spatie roles — super_admin, campus_admin
Impersonate: lab404/laravel-impersonate, banner di AuthenticatedLayout
```

---

## BRAND TOKENS PROKERIN

```
Primary  : #24695c
Accent   : #ba895d
Dark     : #1b4c43
Danger   : #d22d3d
Radius   : 4px (card, button, badge — bukan rounded-2xl)
Shadow   : shadow-sm atau shadow
Font     : Plus Jakarta Sans
Sidebar  : background #ffffff, border #e6edef
Topbar   : background #ffffff, shadow 0 1px 0 #e6edef
```

---

## FASE 1 — BRANDING & THEME

> **Goal**: Panel tampil satu napas dengan workspace Prokerin. Sidebar putih, radius 4px, font Plus Jakarta Sans, warna primary #24695c.

### 1.1 Generate Custom Theme

```bash
php artisan make:filament-theme admin
# Output: resources/css/filament/admin/theme.css
```

### 1.2 Tailwind Preset Override

Edit `tailwind.config.preset.js` Filament (atau buat file override jika belum ada):

```js
// resources/css/filament/admin/tailwind.config.js
import preset from '../../../../vendor/filament/filament/tailwind.config.preset.js'

export default {
  presets: [preset],
  content: [
    './app/Filament/**/*.php',
    './resources/views/filament/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#f0f9f7',
          100: '#d3ede9',
          200: '#a7dbd3',
          300: '#75c3b9',
          400: '#4aab9e',
          500: '#24695c',  // primary utama
          600: '#1f5a4e',
          700: '#1b4c43',  // dark
          800: '#163d36',
          900: '#112f2a',
          950: '#0b1f1c',
        },
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        DEFAULT: '4px',
        sm: '4px',
        md: '4px',
        lg: '8px',
        xl: '8px',
      },
    },
  },
}
```

### 1.3 CSS Override

```css
/* resources/css/filament/admin/theme.css */
@import '/vendor/filament/filament/resources/css/theme.css';

@config './tailwind.config.js';

/* Plus Jakarta Sans */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

/* Sidebar putih */
.fi-sidebar {
  background-color: #ffffff !important;
  border-right: 1px solid #e6edef !important;
}

/* Topbar tipis */
.fi-topbar {
  background-color: #ffffff !important;
  box-shadow: 0 1px 0 #e6edef !important;
}

/* Radius 4px menyeluruh */
.fi-btn,
.fi-badge,
.fi-card,
.fi-input-wrapper,
.fi-select-option {
  border-radius: 4px !important;
}

/* Badge accent #ba895d untuk "Paling Populer" atau highlighted */
.fi-badge-color-warning {
  background-color: #ba895d1a !important;
  color: #ba895d !important;
}
```

### 1.4 Register di AdminPanelProvider

```php
// app/Providers/Filament/AdminPanelProvider.php

->viteTheme('resources/css/filament/admin/theme.css')
->brandLogo(asset('vendor/prokerin/logo.svg'))   // sesuaikan path logo yang ada
->brandLogoHeight('1.5rem')
->favicon(asset('favicon.ico'))
```

### 1.5 Empty State Default

Tambahkan helper method di setiap Resource yang belum punya:

```php
// Tambahkan di setiap Resource::table() yang ada:
->emptyStateIcon('heroicon-o-folder-open')
->emptyStateHeading('Belum ada data')
->emptyStateDescription('Data akan muncul di sini setelah tersedia.')
```

### Gate Fase 1

```bash
npm run build
# Buka http://localhost:8000/internal-admin di browser
# ASSERT: sidebar putih, font Plus Jakarta Sans, warna button primary hijau #24695c
# ASSERT: radius card/button 4px (bukan rounded-2xl)
# Screenshot before/after kalau bisa
```

---

## FASE 2 — DASHBOARD V2

> **Goal**: Dashboard kaya insight — growth chart, distribusi plan, health card, engaged orgs.

### 2.1 Migration — `last_login_at` di Users

```bash
php artisan make:migration add_last_login_at_to_users_table
```

```php
// Migration content:
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('last_login_at')->nullable()->after('email_verified_at')->index();
});
```

### 2.2 Listener — Update Last Login

```bash
php artisan make:listener UpdateLastLoginAt --event=Illuminate\\Auth\\Events\\Login
```

```php
// app/Listeners/UpdateLastLoginAt.php
declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

final class UpdateLastLoginAt
{
    public function handle(Login $event): void
    {
        $event->user->update(['last_login_at' => now()]);
    }
}
```

Register di `EventServiceProvider`:
```php
\Illuminate\Auth\Events\Login::class => [
    \App\Listeners\UpdateLastLoginAt::class,
],
```

### 2.3 Action — Platform Health

```bash
php artisan make:action Admin/GetPlatformHealthAction
```

```php
// app/Actions/Admin/GetPlatformHealthAction.php
declare(strict_types=1);

namespace App\Actions\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

final class GetPlatformHealthAction
{
    public function execute(): array
    {
        return Cache::remember('admin:platform_health', 30, function (): array {
            return [
                'database'  => $this->checkDatabase(),
                'redis'     => $this->checkRedis(),
                'storage'   => $this->checkStorage(),
                'queue'     => $this->checkQueueDepth(),
            ];
        });
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::statement('SELECT 1');
            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (\Throwable) {
            return ['status' => 'error', 'latency_ms' => null];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (\Throwable) {
            return ['status' => 'error', 'latency_ms' => null];
        }
    }

    private function checkStorage(): array
    {
        try {
            Storage::disk(config('filesystems.default'))->exists('health-check-probe');
            return ['status' => 'ok'];
        } catch (\Throwable) {
            return ['status' => 'error'];
        }
    }

    private function checkQueueDepth(): array
    {
        try {
            return [
                'status'  => 'ok',
                'default' => (int) Redis::llen('queues:default'),
                'exports' => (int) Redis::llen('queues:exports'),
                'notifications' => (int) Redis::llen('queues:notifications'),
            ];
        } catch (\Throwable) {
            return ['status' => 'error'];
        }
    }
}
```

### 2.4 Buat 7 Widget Baru

Buat semua widget ini di `app/Filament/Widgets/`:

#### `UserGrowthChart`
```bash
php artisan make:filament-widget UserGrowthChart --chart
```
```php
declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

final class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Pertumbuhan User (30 Hari)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = Cache::remember('widget:user_growth', 300, function (): array {
            return User::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray();
        });

        return [
            'datasets' => [[
                'label'           => 'User Baru',
                'data'            => array_values($data),
                'borderColor'     => '#24695c',
                'backgroundColor' => '#24695c1a',
                'fill'            => true,
            ]],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string { return 'line'; }
}
```

#### `OrganizationGrowthChart`
Pola sama dengan `UserGrowthChart`, ganti model ke `Organization`, label "Organisasi Baru".

#### `PlanDistributionChart`
```bash
php artisan make:filament-widget PlanDistributionChart --chart
```
```php
// Type doughnut, data dari Organization::groupBy('plan_tier')->count()
// Warna: free=#94a3b8, starter=#24695c, pro=#ba895d, campus=#1b4c43
protected function getType(): string { return 'doughnut'; }
```

#### `PlatformHealthCard`
```bash
php artisan make:filament-widget PlatformHealthCard --stats-overview
```
```php
// Inject GetPlatformHealthAction, tampilkan DB, Redis, Storage, Queue depth
// Status ok → icon heroicon-o-check-circle, warna success
// Status error → icon heroicon-o-x-circle, warna danger
```

#### `FailedJobsCounter`
```bash
php artisan make:filament-widget FailedJobsCounter --stats-overview
```
```php
// DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count()
// Color: $count > 0 ? 'danger' : 'success'
// URL: FailedJobResource::getUrl('index')
```

#### `ActiveProkerByPhase`
```bash
php artisan make:filament-widget ActiveProkerByPhase --table
```
```php
// Group project by status, tampilkan count per status
// Column: status (badge warna sesuai ProjectStatus enum), jumlah, % dari total
```

#### `EngagedOrganizationsTable`
```bash
php artisan make:filament-widget EngagedOrganizationsTable --table
```
```php
// 10 org dengan last_login_at terbaru di antara member-nya
// Column: name, plan_tier badge, last_active_at, member_count, projects_count
// Query: Organization dengan join ke users via organization_members
// Order by max(users.last_login_at) desc
```

### 2.5 Custom Dashboard Page

```bash
php artisan make:filament-page PrkAdminDashboard
```

```php
// app/Filament/Pages/PrkAdminDashboard.php
declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

final class PrkAdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\PlatformStatsOverview::class,
            \App\Filament\Widgets\FailedJobsCounter::class,
            \App\Filament\Widgets\UserGrowthChart::class,
            \App\Filament\Widgets\OrganizationGrowthChart::class,
            \App\Filament\Widgets\PlanDistributionChart::class,
            \App\Filament\Widgets\ActiveProkerByPhase::class,
            \App\Filament\Widgets\EngagedOrganizationsTable::class,
            \App\Filament\Widgets\PlatformHealthCard::class,
            \App\Filament\Widgets\RecentOrganizationsTable::class,
            \App\Filament\Widgets\RecentUsersTable::class,
        ];
    }
}
```

Register di `AdminPanelProvider`:
```php
->pages([
    \App\Filament\Pages\PrkAdminDashboard::class,
])
->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
```

### Gate Fase 2

```bash
php artisan migrate
php artisan test tests/Feature/SuperAdmin/DashboardWidgetsTest.php --stop-on-failure
# ASSERT: semua widget load tanpa error
# ASSERT: login super admin → dashboard tampil growth chart + health card
```

---

## FASE 3 — RESOURCE POLISH

> **Goal**: Resource yang sudah ada di SA01 diperlengkapi dengan filter, action, tab, dan kolom baru.

### 3.1 UserResource — Tambahan

Tambahkan ke file existing `UserResource.php`:

```php
// Di table() columns — tambahkan:
Tables\Columns\TextColumn::make('last_login_at')
    ->label('Login Terakhir')
    ->dateTime('d M Y H:i')
    ->sortable()
    ->placeholder('Belum pernah login'),

// Di table() filters — tambahkan:
Tables\Filters\Filter::make('logged_in_7_days')
    ->label('Login 7 hari terakhir')
    ->query(fn ($query) => $query->where('last_login_at', '>=', now()->subDays(7))),

Tables\Filters\Filter::make('never_logged_in')
    ->label('Belum pernah login')
    ->query(fn ($query) => $query->whereNull('last_login_at')),

// Di table() actions — tambahkan:
Tables\Actions\Action::make('resend_verification')
    ->label('Kirim Ulang Verifikasi')
    ->icon('heroicon-o-envelope')
    ->visible(fn ($record) => $record->email_verified_at === null)
    ->action(function ($record): void {
        $record->sendEmailVerificationNotification();
        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Email verifikasi terkirim')
            ->send();
    }),

// Di table() bulkActions — tambahkan:
Tables\Actions\BulkAction::make('force_verify')
    ->label('Force Verify Email')
    ->icon('heroicon-o-shield-check')
    ->color('warning')
    ->requiresConfirmation()
    ->modalHeading('Konfirmasi Force Verify')
    ->modalDescription('Ketik VERIFY untuk melanjutkan.')
    ->form([
        \Filament\Forms\Components\TextInput::make('confirmation')
            ->label('Ketik VERIFY')
            ->required()
            ->rules(['in:VERIFY']),
    ])
    ->action(function ($records, array $data): void {
        $records->each(function ($record) {
            $record->update(['email_verified_at' => now()]);
            app(\App\Actions\Admin\LogActivityAction::class)->execute(
                'user.email.force_verify_bulk', $record
            );
        });
        \Filament\Notifications\Notification::make()
            ->success()
            ->title(count($records) . ' email berhasil diverifikasi')
            ->send();
    }),
```

### 3.2 OrganizationResource — Tabs di Edit Page

Buat `OrganizationResource/Pages/EditOrganization.php` jika belum ada, tambahkan tabs:

```php
// Di form() OrganizationResource:
\Filament\Forms\Components\Tabs::make('Tabs')
    ->tabs([
        \Filament\Forms\Components\Tabs\Tab::make('General')
            ->schema([/* existing fields */]),

        \Filament\Forms\Components\Tabs\Tab::make('Members')
            ->schema([
                \Filament\Forms\Components\Placeholder::make('members_list')
                    ->content(fn ($record) => $record
                        ? "Total {$record->members()->count()} anggota"
                        : 'Simpan dulu sebelum lihat anggota'),
            ]),

        \Filament\Forms\Components\Tabs\Tab::make('Projects')
            ->schema([
                \Filament\Forms\Components\Placeholder::make('projects_list')
                    ->content(fn ($record) => $record
                        ? "{$record->projects()->count()} proker terdaftar"
                        : '-'),
            ]),

        \Filament\Forms\Components\Tabs\Tab::make('Activity')
            ->schema([
                \Filament\Forms\Components\Placeholder::make('activity_note')
                    ->content('Lihat ActivityLogResource untuk log lengkap org ini.'),
            ]),
    ])
```

Tambahkan kolom health badge di list:
```php
Tables\Columns\BadgeColumn::make('health')
    ->label('Kesehatan')
    ->state(function ($record): string {
        $lastLogin = $record->members()
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->max('users.last_login_at');

        if (! $lastLogin) return 'inactive';
        $days = now()->diffInDays($lastLogin);
        if ($days <= 7) return 'active';
        if ($days <= 30) return 'idle';
        return 'inactive';
    })
    ->colors([
        'success' => 'active',
        'warning' => 'idle',
        'danger'  => 'inactive',
    ])
    ->icons([
        'heroicon-o-check-circle' => 'active',
        'heroicon-o-clock'        => 'idle',
        'heroicon-o-x-circle'     => 'inactive',
    ]),
```

### 3.3 ProjectResource — Health Report Modal

```php
// Di table() actions ProjectResource — tambahkan:
Tables\Actions\Action::make('health_report')
    ->label('Health Report')
    ->icon('heroicon-o-chart-bar')
    ->modalHeading(fn ($record) => "Health Report: {$record->name}")
    ->modalContent(fn ($record) => view(
        'filament.modals.project-health-report',
        ['project' => $record]
    ))
    ->modalSubmitAction(false),

// Tambah filters:
Tables\Filters\Filter::make('stuck_proposal_review')
    ->label('Stuck di Proposal Review (>14 hari)')
    ->query(fn ($query) => $query
        ->where('status', 'proposal_review')
        ->where('updated_at', '<', now()->subDays(14))),

Tables\Filters\Filter::make('completed_without_lpj')
    ->label('Selesai tapi LPJ belum submit')
    ->query(fn ($query) => $query
        ->where('status', 'completed')
        ->whereDoesntHave('lpjSubmission')),
```

Buat view `resources/views/filament/modals/project-health-report.blade.php`:
```blade
<div class="space-y-4 p-4">
    <dl class="grid grid-cols-2 gap-3 text-sm">
        <dt class="text-gray-500">Task selesai</dt>
        <dd class="font-medium">{{ $project->tasks()->where('status', 'done')->count() }} / {{ $project->tasks()->count() }}</dd>

        <dt class="text-gray-500">Budget planned</dt>
        <dd class="font-medium">Rp {{ number_format($project->budgetLines()->sum('planned_amount'), 0, ',', '.') }}</dd>

        <dt class="text-gray-500">Budget realisasi</dt>
        <dd class="font-medium">Rp {{ number_format($project->budgetLines()->sum('realized_amount'), 0, ',', '.') }}</dd>

        <dt class="text-gray-500">Status proposal</dt>
        <dd class="font-medium">{{ $project->proposalDraft?->status ?? 'Belum ada' }}</dd>

        <dt class="text-gray-500">LPJ readiness</dt>
        <dd class="font-medium">{{ $project->lpjReadinessPercent() ?? 0 }}%</dd>
    </dl>
</div>
```

### 3.4 DocumentExportResource — Retry + Download

```php
// Tambahkan actions di DocumentExportResource:
Tables\Actions\Action::make('retry')
    ->label('Retry')
    ->icon('heroicon-o-arrow-path')
    ->visible(fn ($record) => $record->status === 'failed')
    ->action(function ($record): void {
        $record->update(['status' => 'queued', 'exception_message' => null]);
        \App\Jobs\GenerateDocumentExportJob::dispatch($record);
        app(\App\Actions\Admin\LogActivityAction::class)->execute('document_export.retry', $record);
        \Filament\Notifications\Notification::make()->success()->title('Export di-retry')->send();
    }),

Tables\Actions\Action::make('download')
    ->label('Download')
    ->icon('heroicon-o-arrow-down-tray')
    ->visible(fn ($record) => $record->status === 'completed' && $record->output_path)
    ->url(fn ($record) => \Storage::temporaryUrl($record->output_path, now()->addMinutes(5)))
    ->openUrlInNewTab(),

// Filter status
Tables\Filters\SelectFilter::make('status')
    ->options(['queued' => 'Queued', 'processing' => 'Processing', 'completed' => 'Selesai', 'failed' => 'Gagal']),
```

### Gate Fase 3

```bash
php artisan test tests/Feature/SuperAdmin/UserResourceTest.php \
               tests/Feature/SuperAdmin/OrganizationResourceTest.php \
               --stop-on-failure
# Tambah test baru jika file belum ada
```

---

## FASE 4 — ACTIVITY LOG & FAILED JOBS RESOURCE

> **Goal**: Resource read-only untuk audit trail dan retry failed jobs.

### 4.1 ActivityLogResource (Read-Only)

```bash
php artisan make:filament-resource ActivityLog --view
```

```php
// app/Filament/Resources/ActivityLogResource.php
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\ActivityLog;  // sesuaikan nama model yang ada
use Filament\Resources\Resource;
use Filament\Tables;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Activity Log';
    protected static ?int $navigationSort = 10;
    protected static bool $canCreate = false;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Aktor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'delete') ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(fn () => ActivityLog::distinct()->pluck('action', 'action')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))),
            ])
            ->actions([
                Tables\Actions\Action::make('view_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record) => view(
                        'filament.modals.activity-log-detail',
                        ['log' => $record]
                    ))
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($records): \Symfony\Component\HttpFoundation\StreamedResponse {
                        return response()->streamDownload(function () use ($records): void {
                            echo "Waktu,Aktor,Action,Target,ID\n";
                            foreach ($records as $log) {
                                echo implode(',', [
                                    $log->created_at,
                                    $log->user?->name ?? '-',
                                    $log->action,
                                    class_basename($log->subject_type ?? ''),
                                    $log->subject_id ?? '-',
                                ]) . "\n";
                            }
                        }, 'activity-log-' . now()->format('Ymd') . '.csv');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginate(50);
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
}
```

### 4.2 FailedJobResource

```bash
php artisan make:filament-resource FailedJob
```

```php
// app/Filament/Resources/FailedJobResource.php
// Model: App\Models\FailedJob (buat jika belum ada — over table failed_jobs)
// Kolom: failed_at, queue, connection, exception preview (100 char)
// Action "Retry": Artisan::call('queue:retry', ['id' => $record->uuid]) + audit log
// Action "Delete": typed confirm "DELETE" + audit log
// Action "View Payload": modal full JSON + stack trace
// BulkAction "Retry Selected"
// Filter: queue (select), date range
// Sort: failed_at desc
// canCreate: false, canEdit: false
```

Implementasikan mengikuti pattern `ActivityLogResource` di atas.

Buat view `resources/views/filament/modals/activity-log-detail.blade.php`:
```blade
<div class="space-y-3 p-4 text-sm">
    @if($log->payload)
        <div class="font-medium text-gray-700">Payload:</div>
        <pre class="bg-gray-50 rounded p-3 overflow-auto text-xs max-h-64">{{ json_encode(json_decode(is_string($log->payload) ? $log->payload : json_encode($log->payload)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
    @if(isset($log->ip_address))
        <div><span class="text-gray-500">IP:</span> {{ $log->ip_address }}</div>
    @endif
    @if(isset($log->user_agent))
        <div><span class="text-gray-500">User Agent:</span> {{ $log->user_agent }}</div>
    @endif
</div>
```

### Gate Fase 4

```bash
php artisan test tests/Feature/SuperAdmin/ActivityLogResourceTest.php \
               tests/Feature/SuperAdmin/FailedJobRetryTest.php \
               --stop-on-failure
```

---

## FASE 5 — OPERATIONAL RESOURCES

> **Goal**: Resource baru untuk semua data sensitif platform — invitation, campus, payment, event registration, WhatsApp log, AI usage, certificate.

Buat semua resource ini di `app/Filament/Resources/`. Semua dalam navigation group `Operations`, semua read-only di MVP kecuali yang disebutkan punya mutation action.

### 5.1 InvitationResource

```bash
php artisan make:filament-resource OrganizationInvitation --view
```

```
Model    : OrganizationInvitation
Group    : Operations
Sort     : 20
Columns  : email, organization.name, role (badge), status (badge warna), created_at, expires_at
Filters  : status (pending/accepted/expired/revoked), organization
Actions  : "Resend" (POST resend email, visible jika pending)
           "Force Expire" (update status=expired + audit log, visible jika pending, require confirm)
canCreate: false, canEdit: false, canDelete: false
```

### 5.2 CampusResource

```bash
php artisan make:filament-resource Campus
```

```
Model    : Campus (M24 — sesuaikan nama model yang ada)
Group    : Platform
Sort     : 50
Form     : name, domain, admin_user (Select dari users)
Columns  : name, domain, admin_user.name, organizations_count, created_at
Relation : CampusOrganizationRelationManager (attach/detach organizations)
Audit    : Setiap mutation → LogActivityAction
```

### 5.3 PaymentOrderResource

```bash
php artisan make:filament-resource PaymentOrder --view
```

```
Model    : PaymentOrder (M22)
Group    : Operations
Sort     : 30
Columns  : order_id, organization.name, project.name, amount (format Rp), status (badge), created_at
Filters  : status, date range
Actions  : "Manual Mark Paid" — typed confirm "PAID" + form alasan (textarea required)
                                 → update status=paid + audit log 'payment_order.manual_mark_paid'
           "Refund" — typed confirm "REFUND" + form alasan
                    → update status=refunded + kolom manual_refund_reason + audit log
           Cross-link ke EventRegistration terkait
canCreate: false, canEdit: false, canDelete: false
```

### 5.4 EventRegistrationResource

```bash
php artisan make:filament-resource EventRegistration --view
```

```
Model    : EventRegistration (M21)
Group    : Operations
Sort     : 31
Columns  : name, email, project.name, ticket_tier, payment_status (badge), registered_at, attended (toggle read-only)
Filters  : payment_status, project, date range
Actions  : "Manual Confirm" — visible jika pending, update status=confirmed + audit log
BulkAction: "Export CSV" — download registran per filter aktif
canCreate: false, canEdit: false
```

### 5.5 WhatsAppDeliveryLogResource

```bash
php artisan make:filament-resource WhatsAppDeliveryLog --view
```

```
Model    : WhatsAppDeliveryLog (M17)
Group    : Operations
Sort     : 40
Columns  : created_at, organization.name, recipient (phone masked xxx-xxxx-xxxx), message_type, status (badge), sent_at
Filters  : status (queued/sent/failed), message_type, date range
Actions  : "Retry" — visible jika failed → re-dispatch SendWhatsAppReminderJob + audit log
           "View Response" — modal: provider_response JSON
canCreate: false, canEdit: false, canDelete: false
```

### 5.6 AiUsageLogResource

```bash
php artisan make:filament-resource AiUsageLog --view
```

```
Model    : AiUsageLog (M23)
Group    : Insights
Sort     : 60
Columns  : created_at, user.name, organization.name, action_type (badge), tokens_used, cost_idr (formatted), status, flagged_at
Filters  : organization, action_type, date range, flagged (hanya yang flagged)
Actions  : "Flag Abuse" — visible jika belum flagged → update flagged_at=now() + audit log 'ai_usage_log.flag_abuse'
Migration tambahan: php artisan make:migration add_flagged_at_to_ai_usage_logs
canCreate: false, canEdit: false, canDelete: false
```

### 5.7 CertificateRecipientResource

```bash
php artisan make:filament-resource CertificateRecipient --view
```

```
Model    : CertificateRecipient (M16)
Group    : Operations
Sort     : 50
Columns  : certificate_number, recipient_name, organization.name, project.name, issued_at, revoked_at (badge Revoked jika tidak null)
Filters  : organization, project, status (issued/revoked), date range
Actions  : "Revoke" — visible jika belum revoked → update revoked_at=now() + audit log 'certificate.revoke'
           "Resend URL" — kirim email signed download URL ke recipient
Migration: php artisan make:migration add_revoked_at_to_certificate_recipients
Update   : VerifyCertificateAction → return 410 kalau revoked_at tidak null
canCreate: false, canEdit: false, canDelete: false
```

### Gate Fase 5

```bash
php artisan migrate
php artisan test tests/Feature/SuperAdmin/V2/OperationalResourcesTest.php --stop-on-failure
# ASSERT: semua resource accessible oleh super_admin
# ASSERT: member (non super_admin) → 403 di semua resource
# ASSERT: mutation action tercatat di activity_logs
```

---

## FASE 6 — INTERNAL TOOLS

> **Goal**: Broadcast announcement, Feature Flag, Email Template, System Health, Onboarding Checklist.

### 6.1 Feature Flag (Prioritas Pertama)

**Migration:**
```bash
php artisan make:migration create_feature_flags_table
```
```php
Schema::create('feature_flags', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->string('description')->nullable();
    $table->boolean('is_enabled_globally')->default(false);
    $table->json('enabled_organization_ids')->nullable();  // array of org IDs
    $table->json('enabled_plan_tiers')->nullable();         // ['free','starter','pro','campus']
    $table->timestamps();
});
```

**Helper:**
```php
// app/Support/FeatureFlag.php
declare(strict_types=1);

namespace App\Support;

use App\Models\FeatureFlag as FeatureFlagModel;
use Illuminate\Support\Facades\Cache;

final class FeatureFlag
{
    public static function isEnabled(string $key, ?int $organizationId = null): bool
    {
        $flags = Cache::remember("feature_flags:{$key}", 300, fn () =>
            FeatureFlagModel::where('key', $key)->first()
        );

        if (! $flags) return false;
        if ($flags->is_enabled_globally) return true;

        if ($organizationId && is_array($flags->enabled_organization_ids)) {
            if (in_array($organizationId, $flags->enabled_organization_ids, true)) return true;
        }

        // Check plan tier kalau ada org context
        if ($organizationId && is_array($flags->enabled_plan_tiers)) {
            $org = \App\Models\Organization::find($organizationId);
            if ($org && in_array($org->plan_tier, $flags->enabled_plan_tiers, true)) return true;
        }

        return false;
    }

    public static function flush(string $key): void
    {
        Cache::forget("feature_flags:{$key}");
    }
}
```

**Resource:**
```bash
php artisan make:filament-resource FeatureFlag
```
```
Group   : Configuration
Sort    : 10
Form    : key (slug, read-only setelah create), description, is_enabled_globally (Toggle),
          enabled_organization_ids (Select multiple dari organizations),
          enabled_plan_tiers (CheckboxList: free/starter/pro/campus)
Columns : key, is_enabled_globally (icon), enabled_plan_tiers (badges), updated_at
Action  : Setiap save → Cache::forget("feature_flags:{$record->key}") + audit log
```

### 6.2 Broadcast Announcement

**Migration:**
```bash
php artisan make:migration create_platform_announcements_table
```
```php
Schema::create('platform_announcements', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');  // markdown
    $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
    $table->json('audience')->nullable();
    // audience format: { "type": "all" } atau { "type": "plan_tier", "values": ["pro","campus"] }
    //                  atau { "type": "organization_ids", "values": [1,2,3] }
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('ends_at')->nullable();
    $table->foreignId('created_by_user_id')->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
```

**Resource:**
```bash
php artisan make:filament-resource PlatformAnnouncement
```
```
Group   : Configuration
Sort    : 20
Form    : title, body (MarkdownEditor), severity (Select), audience builder
          (Radio: Semua User / By Plan Tier / By Organization IDs
           + conditional select), starts_at (DateTimePicker), ends_at
Validation: ends_at > starts_at
Columns: title, severity (badge), starts_at, ends_at, created_at
canDelete: soft delete
Audit  : create/update/delete → LogActivityAction
```

**Action `GetActiveAnnouncementsForUserAction`:**
```php
// app/Actions/Admin/GetActiveAnnouncementsForUserAction.php
declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\PlatformAnnouncement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class GetActiveAnnouncementsForUserAction
{
    public function execute(User $user, ?int $organizationId = null): array
    {
        $cacheKey = "announcements:user:{$user->id}:org:{$organizationId}";

        return Cache::remember($cacheKey, 60, function () use ($user, $organizationId): array {
            return PlatformAnnouncement::query()
                ->where(fn ($q) => $q
                    ->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q
                    ->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->get()
                ->filter(fn ($ann) => $this->matchesAudience($ann, $user, $organizationId))
                ->values()
                ->toArray();
        });
    }

    private function matchesAudience(
        PlatformAnnouncement $ann,
        User $user,
        ?int $organizationId
    ): bool {
        $audience = $ann->audience ?? ['type' => 'all'];

        return match ($audience['type'] ?? 'all') {
            'all' => true,
            'plan_tier' => $organizationId && in_array(
                \App\Models\Organization::find($organizationId)?->plan_tier,
                $audience['values'] ?? [],
                strict: true
            ),
            'organization_ids' => in_array(
                $organizationId,
                $audience['values'] ?? [],
                strict: true
            ),
            default => false,
        };
    }
}
```

**Share ke Inertia:**
```php
// app/Http/Middleware/HandleInertiaRequests.php — tambahkan ke share():
'platformAnnouncements' => fn () => auth()->check()
    ? app(\App\Actions\Admin\GetActiveAnnouncementsForUserAction::class)
        ->execute(auth()->user(), session('current_organization_id'))
    : [],
```

**Komponen React:**
```tsx
// resources/js/Components/AnnouncementBanner.tsx
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertTriangle, Info, X } from 'lucide-react';
import { useState } from 'react';

interface Announcement {
  id: number;
  title: string;
  body: string;
  severity: 'info' | 'warning' | 'critical';
}

export default function AnnouncementBanner() {
  const { platformAnnouncements } = usePage<PageProps>().props;
  const [dismissed, setDismissed] = useState<number[]>([]);

  const active = (platformAnnouncements as Announcement[] ?? [])
    .filter((a) => !dismissed.includes(a.id));

  if (!active.length) return null;

  return (
    <div className="space-y-1">
      {active.map((ann) => (
        <div
          key={ann.id}
          className={`flex items-center gap-3 px-4 py-2 text-sm ${
            ann.severity === 'critical'
              ? 'bg-red-600 text-white'
              : ann.severity === 'warning'
                ? 'bg-amber-50 border-b border-amber-200 text-amber-800'
                : 'bg-blue-50 border-b border-blue-200 text-blue-800'
          }`}
        >
          {ann.severity === 'critical'
            ? <AlertTriangle className="h-4 w-4 shrink-0" />
            : <Info className="h-4 w-4 shrink-0" />}
          <span className="flex-1 font-medium">{ann.title}</span>
          <button
            onClick={() => setDismissed((prev) => [...prev, ann.id])}
            className="ml-2 opacity-70 hover:opacity-100"
            aria-label="Tutup pengumuman"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
      ))}
    </div>
  );
}
```

Pasang di `AuthenticatedLayout` di atas sidebar content.

### 6.3 System Health Page

```bash
php artisan make:filament-page SystemHealthPage
```

```php
// app/Filament/Pages/SystemHealthPage.php
declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Admin\GetPlatformHealthAction;
use Filament\Pages\Page;

final class SystemHealthPage extends Page
{
    protected static ?string $navigationGroup = 'Insights';
    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'System Health';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.system-health';

    public array $health = [];

    public function mount(GetPlatformHealthAction $action): void
    {
        $this->health = $action->execute();
    }

    public function refresh(GetPlatformHealthAction $action): void
    {
        \Illuminate\Support\Facades\Cache::forget('admin:platform_health');
        $this->health = $action->execute();
        $this->dispatch('$refresh');
    }
}
```

```blade
{{-- resources/views/filament/pages/system-health.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Platform Health</h2>
            <x-filament::button wire:click="refresh" color="gray" size="sm" icon="heroicon-o-arrow-path">
                Refresh
            </x-filament::button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['database', 'redis', 'storage'] as $service)
                @php $data = $health[$service] ?? ['status' => 'unknown']; @endphp
                <div class="rounded p-4 border {{ $data['status'] === 'ok' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="font-medium capitalize">{{ $service }}</div>
                    <div class="text-sm {{ $data['status'] === 'ok' ? 'text-green-700' : 'text-red-700' }}">
                        {{ strtoupper($data['status']) }}
                        @if(isset($data['latency_ms'])) · {{ $data['latency_ms'] }}ms @endif
                    </div>
                </div>
            @endforeach

            @php $queue = $health['queue'] ?? ['status' => 'unknown']; @endphp
            <div class="rounded p-4 border {{ $queue['status'] === 'ok' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <div class="font-medium">Queue Depth</div>
                <div class="text-xs text-gray-600">
                    default: {{ $queue['default'] ?? '?' }} ·
                    exports: {{ $queue['exports'] ?? '?' }} ·
                    notif: {{ $queue['notifications'] ?? '?' }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
```

### 6.4 Onboarding Checklist Page

```bash
php artisan make:filament-page OnboardingChecklistPage
```

```php
// Menampilkan org baru (<30 hari) dengan checklist:
// ✓ Logo uploaded, ✓ Member invited, ✓ Period set, ✓ First proker created, ✓ First proposal submitted
// Tujuan: support team bisa follow-up org yang stuck onboarding
protected static ?string $navigationGroup = 'Insights';
protected static ?string $navigationSort = 20;
```

### Gate Fase 6

```bash
php artisan migrate
php artisan test tests/Feature/SuperAdmin/V2/FeatureFlagToolTest.php \
               tests/Feature/SuperAdmin/V2/SystemHealthPageTest.php \
               tests/Feature/SuperAdmin/V2/OnboardingChecklistPageTest.php \
               --stop-on-failure
# Harus: 4 passed + 3 passed + 4 passed
```

---

## FASE 7 — SIDEBAR, SEARCH & NAVIGATION

> **Goal**: Navigation bersih dengan 4 group, global search aktif, sidebar tidak berantakan.

### 7.1 Navigation Groups di AdminPanelProvider

```php
// app/Providers/Filament/AdminPanelProvider.php
->navigationGroups([
    NavigationGroup::make('Platform')
        ->icon('heroicon-o-globe-asia-australia')
        ->collapsed(false),
    NavigationGroup::make('Operations')
        ->icon('heroicon-o-rectangle-group')
        ->collapsed(true),
    NavigationGroup::make('Configuration')
        ->icon('heroicon-o-cog-6-tooth')
        ->collapsed(true),
    NavigationGroup::make('Insights')
        ->icon('heroicon-o-chart-bar-square')
        ->collapsed(false),
])
// CATATAN: TIDAK ada group "Landing CMS" — LCMS01 dibatalkan
```

**Distribusi resource ke group:**
```
Platform      : UserResource (sort:10), OrganizationResource (sort:20),
                CampusResource (sort:30), ProjectResource (sort:40),
                InvitationResource (sort:50)

Operations    : ActivityLogResource (sort:10), FailedJobResource (sort:20),
                DocumentExportResource (sort:30), PaymentOrderResource (sort:31),
                EventRegistrationResource (sort:32), WhatsAppDeliveryLogResource (sort:40),
                CertificateRecipientResource (sort:50)

Configuration : NotificationRuleResource (sort:10), FeatureFlagResource (sort:20),
                PlatformAnnouncementResource (sort:30), EmailTemplateResource (sort:40)

Insights      : AiUsageLogResource (sort:10), SystemHealthPage (sort:10),
                OnboardingChecklistPage (sort:20)
```

Pastikan setiap resource/page punya:
```php
protected static ?string $navigationGroup = '...';  // salah satu dari 4 group di atas
protected static ?int $navigationSort = ...;         // integer, urutan dalam group
```

### 7.2 Global Search

Tambahkan ke resource yang punya identifier teks:

```php
// UserResource, OrganizationResource, ProjectResource, InvitationResource, CampusResource
public static function getGloballySearchableAttributes(): array
{
    return ['name', 'email'];  // sesuaikan kolom per model
}

public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
{
    return [
        'Email' => $record->email ?? '-',  // sesuaikan
    ];
}

public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
{
    return $record->name ?? $record->email ?? "ID #{$record->id}";
}
```

Aktifkan di panel provider:
```php
->globalSearch(true)
->globalSearchKeyBindings(['command+k', 'ctrl+k'])
```

### Gate Fase 7

```bash
# Cek sidebar di browser: 4 group muncul, setiap resource di group yang benar
# Tekan Ctrl+K / Cmd+K → search bar muncul
# Ketik nama user/org → hasil muncul
php artisan test tests/Feature/SuperAdmin/FilamentAccessTest.php --stop-on-failure
```

---

## FASE 8 — BULK ACTIONS & AUDIT TRAIT

> **Goal**: Bulk actions aman dengan typed confirmation + audit log otomatis untuk semua mutation.

### 8.1 AutoLogsActivity Trait

```php
// app/Filament/Concerns/AutoLogsActivity.php
declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Actions\Admin\LogActivityAction;
use Illuminate\Database\Eloquent\Model;

trait AutoLogsActivity
{
    protected function afterCreate(): void
    {
        $this->logFilamentMutation('create', $this->record);
    }

    protected function afterSave(): void
    {
        $this->logFilamentMutation('update', $this->record);
    }

    protected function afterDelete(): void
    {
        $this->logFilamentMutation('delete', $this->record);
    }

    private function logFilamentMutation(string $action, ?Model $record): void
    {
        if (! $record) return;

        $resourceSlug = static::getResource()::getSlug();
        app(LogActivityAction::class)->execute(
            "{$resourceSlug}.{$action}",
            $record,
            ['resource' => $resourceSlug, 'actor' => auth()->id()]
        );
    }
}
```

Include trait ini di `EditUser`, `EditOrganization`, `EditCampus`, dan semua Edit Page resource yang punya mutation.

### 8.2 Bulk Actions Tambahan

**OrganizationResource:**
```php
Tables\Actions\BulkAction::make('mark_dormant')
    ->label('Mark Dormant')
    ->icon('heroicon-o-archive-box')
    ->color('warning')
    ->requiresConfirmation()
    ->modalHeading('Konfirmasi Mark Dormant')
    ->form([
        \Filament\Forms\Components\TextInput::make('confirmation')
            ->label('Ketik DORMANT untuk melanjutkan')
            ->required()
            ->rules(['in:DORMANT']),
    ])
    ->action(function ($records): void {
        $records->each(function ($record): void {
            $record->update(['dormant_at' => now()]);
            app(LogActivityAction::class)->execute('organization.mark_dormant', $record);
        });
    }),
```

**InvitationResource:**
```php
Tables\Actions\BulkAction::make('resend_all')
    ->label('Resend Invitation')
    ->action(fn ($records) => $records->each->resendInvitation()),

Tables\Actions\BulkAction::make('force_expire_all')
    ->label('Force Expire')
    ->requiresConfirmation()
    ->action(fn ($records) => $records->each->update(['status' => 'expired'])),
```

**FailedJobResource:**
```php
Tables\Actions\BulkAction::make('retry_all')
    ->label('Retry All Selected')
    ->action(function ($records): void {
        $records->each(fn ($r) => \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => [$r->uuid]]));
    }),
```

### Gate Fase 8

```bash
php artisan test tests/Feature/SuperAdmin/BulkActionAuditTest.php --stop-on-failure
# ASSERT: setelah bulk force verify, activity_logs punya N rows dengan action user.email.force_verify_bulk
# ASSERT: bulk mark dormant tercatat di activity_logs
```

---

## FASE 9 — SECURITY LAYER 1 (SA03)

> **Goal**: 2FA wajib super_admin, session timeout.

### 9.1 Config Admin

```bash
# Buat config/admin.php
cat > config/admin.php << 'EOF'
<?php

declare(strict_types=1);

return [
    'session_idle_minutes' => (int) env('ADMIN_SESSION_IDLE_MINUTES', 30),
    'allowed_ips' => array_filter(explode(',', (string) env('ADMIN_ALLOWED_IPS', ''))),
    'reauth_valid_minutes' => (int) env('ADMIN_REAUTH_VALID_MINUTES', 15),
    '2fa_required' => (bool) env('ADMIN_2FA_REQUIRED', true),
];
EOF
```

Tambahkan ke `.env.example`:
```bash
cat >> .env.example << 'EOF'

# Admin Panel Security
ADMIN_SESSION_IDLE_MINUTES=30
ADMIN_ALLOWED_IPS=
ADMIN_REAUTH_VALID_MINUTES=15
ADMIN_2FA_REQUIRED=true
ADMIN_AUDIT_RETENTION_DAYS=365
EOF
```

### 9.2 Session Timeout Middleware

```bash
php artisan make:middleware EnsureAdminSessionFresh
```

```php
// app/Http/Middleware/EnsureAdminSessionFresh.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminSessionFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        $idleMinutes = config('admin.session_idle_minutes', 30);
        $lastActivity = $request->session()->get('admin_last_activity_at');

        if ($lastActivity && now()->diffInMinutes($lastActivity) > $idleMinutes) {
            $request->session()->flush();
            return redirect()->route('filament.admin.auth.login')
                ->with('status', 'Sesi habis karena tidak aktif. Silakan login kembali.');
        }

        $request->session()->put('admin_last_activity_at', now());

        return $next($request);
    }
}
```

Register di `AdminPanelProvider`:
```php
->authMiddleware([
    \App\Http\Middleware\EnsureAdminSessionFresh::class,
    // ... existing auth middleware
])
```

### 9.3 2FA — Install Package

```bash
composer require pragmarx/google2fa-laravel
composer require bacon/bacon-qr-code
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
```

**Migration:**
```bash
php artisan make:migration add_two_factor_columns_to_users_table
```
```php
Schema::table('users', function (Blueprint $table) {
    $table->text('two_factor_secret')->nullable()->after('password');
    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
    $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
});
```

**2FA Setup Page:**
```bash
php artisan make:filament-page TwoFactorSetupPage
```

```php
// app/Filament/Pages/TwoFactorSetupPage.php
// Route: /internal-admin/two-factor/setup
// Tampilkan QR code via BaconQrCode
// Form input 6 digit confirmation code
// Setelah verify: simpan secret + generate 8 recovery codes
// Tampilkan recovery codes satu kali (user wajib download)
// Redirect ke panel setelah selesai
```

**2FA Challenge Page:**
```bash
php artisan make:filament-page TwoFactorChallengePage
```

```php
// app/Filament/Pages/TwoFactorChallengePage.php
// Route: /internal-admin/two-factor/challenge
// Form: input TOTP code atau recovery code
// Jika valid: set session two_factor_passed_at=now()
// Redirect ke dashboard
```

**Middleware:**
```bash
php artisan make:middleware EnsureTwoFactorSetUp
php artisan make:middleware EnsureTwoFactorChallenged
```

```php
// EnsureTwoFactorSetUp: redirect ke /internal-admin/two-factor/setup
// kalau user super_admin tapi two_factor_confirmed_at null
// dan config('admin.2fa_required') === true

// EnsureTwoFactorChallenged: redirect ke /internal-admin/two-factor/challenge
// kalau session tidak punya two_factor_passed_at atau sudah expired
```

### Gate Fase 9

```bash
php artisan migrate
php artisan test tests/Feature/SuperAdmin/V2/AdminSecurityHeadersTest.php --stop-on-failure
# Manual test: login super_admin → redirect ke 2FA setup → scan QR → enter code → masuk panel
# Idle 31 menit (atau set ADMIN_SESSION_IDLE_MINUTES=1 sementara) → re-login
```

---

## FASE 10 — SECURITY LAYER 2

> **Goal**: IP allowlist, robots.txt, security headers, rate limit.

### 10.1 IP Allowlist Middleware

```bash
php artisan make:middleware EnsureAdminIpAllowed
```

```php
// app/Http/Middleware/EnsureAdminIpAllowed.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminIpAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('admin.allowed_ips', []);

        // Kalau list kosong, allow semua
        if (empty($allowedIps)) {
            return $next($request);
        }

        if (! in_array($request->ip(), $allowedIps, strict: true)) {
            abort(403, 'Akses dari IP ini tidak diizinkan.');
        }

        return $next($request);
    }
}
```

### 10.2 Security Headers Middleware

```bash
php artisan make:middleware SetAdminSecurityHeaders
```

```php
// app/Http/Middleware/SetAdminSecurityHeaders.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetAdminSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
```

### 10.3 Rate Limit Panel Login

```php
// app/Providers/RouteServiceProvider.php (atau AppServiceProvider)
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('filament-login', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->ip() . '|' . $request->input('email'))
        ->response(fn () => response()->json([
            'message' => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
        ], 429));
});
```

### 10.4 Robots.txt

```bash
cat > public/robots.txt << 'EOF'
User-agent: *
Disallow: /internal-admin/
Disallow: /impersonate/
Disallow: /api/

Allow: /
Allow: /features
Allow: /pricing
Allow: /events/

Sitemap: https://prokerin.id/sitemap.xml
EOF
```

### 10.5 Register Semua Middleware di AdminPanelProvider

```php
->middleware([
    \App\Http\Middleware\EnsureAdminIpAllowed::class,
    \App\Http\Middleware\SetAdminSecurityHeaders::class,
])
->authMiddleware([
    \App\Http\Middleware\EnsureAdminSessionFresh::class,
    \App\Http\Middleware\EnsureTwoFactorSetUp::class,
    \App\Http\Middleware\EnsureTwoFactorChallenged::class,
])
```

### Gate Fase 10

```bash
php artisan test tests/Feature/SuperAdmin/V2/AdminSecurityHeadersTest.php \
               tests/Feature/SuperAdmin/V2/IpAllowlistTest.php \
               --stop-on-failure
# ASSERT: /internal-admin response punya header X-Robots-Tag: noindex, nofollow
# ASSERT: /internal-admin response punya X-Frame-Options: DENY
# ASSERT: robots.txt accessible dan berisi Disallow: /internal-admin/
# ASSERT: login 6x dengan email/password salah → 429 di percobaan ke-6
```

---

## FASE 11 — VERIFIKASI FINAL & DOKUMENTASI

### 11.1 Full Test Gate

```bash
# Gate utama — semua harus hijau
PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test
# ASSERT: jumlah test naik ≥ +50 dari baseline SA01
# ASSERT: 0 failure

PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH ./vendor/bin/pint --test
# ASSERT: 0 issue

npm run build
# ASSERT: exit 0

npm run lint 2>/dev/null
# ASSERT: 0 error
```

### 11.2 Browser Smoke Wajib

```
□ Login super_admin → 2FA setup (jika pertama kali) → masuk panel → sidebar 4 group
□ Dashboard: semua 10 widget render tanpa error
□ UserResource: filter last login, bulk force verify dengan typed VERIFY berhasil
□ OrganizationResource: tabs (General, Members, Projects, Activity) muncul
□ ProjectResource: health report modal buka
□ ActivityLogResource: list activity, filter jalan, export CSV
□ FailedJobResource: jika ada failed job → retry berhasil
□ FeatureFlagResource: toggle is_enabled_globally → FeatureFlag::isEnabled() berubah
□ SystemHealth page: status DB/Redis/Storage/Queue tampil
□ OnboardingChecklist page: list org baru 30 hari tampil
□ AnnouncementBanner: buat announcement targeted pro, login owner pro → banner muncul
□ Security: /internal-admin response punya X-Robots-Tag dan X-Frame-Options
□ Security: robots.txt berisi Disallow: /internal-admin/
□ Logout → idle 30 menit → login lagi → 2FA challenge muncul
```

### 11.3 Update Dokumentasi

```bash
# Update features.md — tandai SA02 dan SA03 sebagai completed
# Cari section yang relevan dan tambahkan:
# [x] SA02 — Super Admin UX & Resource Expansion · 2026-XX-XX
# [x] SA03 — Super Admin Security Hardening · 2026-XX-XX

# Update super-admin-panel.md:
# Tambah section "State Setelah SA02+SA03" di atas "Post-SA01 Roadmap"

# Update QA-MASTER-PROKERIN.md Section 19 (Filament Super Admin):
# Tambah checklist item baru untuk semua resource dan fitur baru
```

---

## REFERENSI CEPAT

### Naming Convention Resource Baru
```
app/Filament/Resources/{ModelName}Resource.php
app/Filament/Resources/{ModelName}Resource/Pages/List{ModelName}.php
app/Filament/Resources/{ModelName}Resource/Pages/Create{ModelName}.php (kalau perlu)
app/Filament/Resources/{ModelName}Resource/Pages/Edit{ModelName}.php (kalau perlu)
app/Filament/Pages/{PageName}Page.php
app/Filament/Widgets/{WidgetName}.php
app/Actions/Admin/{ActionName}Action.php
```

### Cek Resource Sudah di Group Yang Benar
```bash
grep -r "navigationGroup" app/Filament/ --include="*.php" | sort
# ASSERT: tidak ada resource tanpa group
# ASSERT: tidak ada group "Landing CMS"
```

### Cek Semua PHP File Punya strict_types
```bash
find app/Filament/ app/Actions/Admin/ -name "*.php" -exec grep -L "declare(strict_types=1)" {} \;
# ASSERT: zero output (semua file sudah punya strict_types)
```