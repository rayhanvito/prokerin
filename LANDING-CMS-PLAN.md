# LANDING-CMS-PLAN.md — Prokerin
## Implementasi CMS Landing Page Lewat Super Admin Panel (Filament)

> Wajib baca dulu: `AGENTS.md`, `features.md`, `super-admin-panel.md`, `BUG-FIX-PLAN.md`.
> Module ini akan diberi kode `LCMS01` di `features.md` setelah selesai.
> Dokumen ini menjelaskan rencana lengkap untuk memindahkan semua konten landing page (`Pages/Landing/Home.tsx`, `Features.tsx`, `Pricing.tsx`) yang sekarang hardcoded di komponen React menjadi data-backed yang bisa di-edit lewat panel Filament `/internal-admin`.

---

## 0. Konteks dan Tujuan

### 0.1 State Sekarang
- Landing page hidup di `resources/js/Pages/Landing/{Home,Features,Pricing}.tsx`.
- Konten per section di `resources/js/Components/Landing/*.tsx` masih **hardcoded** sebagai array konstanta atau JSX literal:
  - `HeroSection.tsx` → headline, sub-copy, CTA labels hardcoded.
  - `SocialProofBar.tsx` → list trust logos hardcoded.
  - `ProblemSection.tsx` → list problem points hardcoded.
  - `FeatureShowcase.tsx` → list feature highlights hardcoded.
  - `HowItWorksSection.tsx` → list steps hardcoded.
  - `TestimonialsSection.tsx` → 3 testimonial hardcoded.
  - `PricingSection.tsx` → 4 tier (Free/Starter/Pro/Campus) + features list hardcoded.
  - `FaqSection.tsx` → 10 FAQ hardcoded.
  - `CtaBanner.tsx` → headline, CTA hardcoded.
  - `Navbar.tsx`/`Footer.tsx` → nav items, social, copyright hardcoded.
  - `DemoVideoModal.tsx` → URL embed hardcoded.
- `LandingController.php` cuma return `Inertia::render('Landing/Home')` tanpa props.

### 0.2 Setelah LCMS01 Selesai
- Tim non-teknis (CMO, marketing, sekretaris yang dipercaya) bisa edit semua konten landing dari panel `/internal-admin/landing/*` tanpa redeploy.
- Setiap perubahan otomatis tercache di Redis 5 menit, invalidasi otomatis saat save.
- Live preview pakai token sementara sebelum publish.
- Audit log lengkap (siapa, apa, kapan).
- React component landing terima props dari `LandingController` (data-backed), shape props menggantikan array hardcoded.
- Schema didesain support multi-locale ke depan (kolom `locale` ada walau MVP cuma `id`).

### 0.3 Non-Goals
- **Bukan** full headless CMS seperti Strapi atau Sanity. Tidak ada API REST eksternal.
- **Bukan** WYSIWYG drag-drop page builder. Section dan urutan tetap fix di React; hanya **isi** yang editable.
- **Bukan** A/B testing engine (di-defer ke modul terpisah kalau dibutuhkan).
- **Bukan** content delivery network (CDN) khusus landing — masih lewat Vite + S3 untuk asset.

---

## 1. Constraint Wajib (AGENTS.md Compliance)

Sebelum mulai, baca ulang aturan-aturan ini:

| AGENTS.md | Aturan | Bagaimana dipatuhi di LCMS01 |
|---|---|---|
| §6 PHP | `declare(strict_types=1);` di setiap file PHP | Semua Action, DTO, Model, Form Request wajib |
| §6 PHP | Type hint parameter dan return | Wajib di Action `execute(...)` dan controller method |
| §6 PHP | Pakai readonly properties dan enum | DTO landing pakai readonly, section type pakai enum |
| §4 Structure | Business logic di Action, bukan controller | Semua logic publish/preview/cache di `app/Actions/Landing/` |
| §9 API | No REST API, semua via Inertia | `LandingController` tetap render Inertia, tidak ada `/api/landing/*` |
| §11 Performance | Cache expensive aggregates di Redis | `landing_settings:{key}` dan `landing_items:{type}` di Redis 5 menit |
| §11 Performance | Eager-load relationships | Asset relation auto-eager di Filament resource |
| §15 Do Not | Tidak boleh `env()` di app code | `config/landing.php` baru, akses via `config('landing.cache_ttl')` |
| §15 Do Not | Tidak boleh modifikasi `Components/ui/` | Bikin landing-specific component baru, jangan ubah shadcn |
| §15 Do Not | Tidak boleh return raw model ke Inertia | Selalu pakai array hasil DTO `toArray()` atau Resource |
| §10 Multi-tenant | Wajib `organization_id` scoping | Tidak relevan untuk landing (single-tenant Prokerin), tapi audit log per super admin tetap |
| §16 Env | Tambah env baru ke `.env.example` | `LANDING_CACHE_TTL`, `LANDING_PREVIEW_TTL` |

---

## 2. Arsitektur Singkat

```
┌────────────────────────────────────────────────────────────────────┐
│  Filament Panel (/internal-admin)                                  │
│  ├── Landing > Hero (Settings Page)                                │
│  ├── Landing > CTA Banner (Settings Page)                          │
│  ├── Landing > Footer (Settings Page)                              │
│  ├── Landing > Demo Video (Settings Page)                          │
│  ├── Landing > SEO Meta (Settings Page, tab per page)              │
│  ├── Landing > Pricing Tiers (Resource list/edit)                  │
│  ├── Landing > Testimonials (Resource list/edit)                   │
│  ├── Landing > FAQ Items (Resource list/edit)                      │
│  ├── Landing > Feature Highlights (Resource list/edit)             │
│  ├── Landing > How It Works Steps (Resource list/edit)             │
│  ├── Landing > Problem Points (Resource list/edit)                 │
│  └── Landing > Trusted Logos (Resource list/edit)                  │
└────────────────────────────────────────────────────────────────────┘
                                │ save/publish
                                ▼
┌────────────────────────────────────────────────────────────────────┐
│  Storage Layer                                                     │
│  ├── landing_settings (singleton sections)                         │
│  ├── landing_content_items (collection sections)                   │
│  ├── landing_setting_versions (version history)                    │
│  └── S3 disk: landing/assets/* (images, screenshots)               │
└────────────────────────────────────────────────────────────────────┘
                                │ cache layer
                                ▼
┌────────────────────────────────────────────────────────────────────┐
│  Redis Cache                                                       │
│  ├── landing:settings:{key} (TTL 5 minutes)                        │
│  ├── landing:items:{type} (TTL 5 minutes)                          │
│  └── landing:home_payload (TTL 1 minute, stampede-safe)            │
└────────────────────────────────────────────────────────────────────┘
                                │ payload
                                ▼
┌────────────────────────────────────────────────────────────────────┐
│  LandingController (Inertia)                                       │
│  ├── home() → GetLandingHomePayloadAction → Inertia::render        │
│  ├── features() → GetLandingFeaturesPayloadAction                  │
│  └── pricing() → GetLandingPricingPayloadAction                    │
└────────────────────────────────────────────────────────────────────┘
                                │ props
                                ▼
┌────────────────────────────────────────────────────────────────────┐
│  React Landing Pages (data-backed)                                 │
│  ├── Pages/Landing/Home.tsx (terima props lengkap)                 │
│  ├── Components/Landing/HeroSection.tsx (props-driven)             │
│  └── (semua landing component refactor jadi pure presentational)   │
└────────────────────────────────────────────────────────────────────┘
```

---

## 3. Database Schema

### 3.1 Migration `landing_settings` (singleton sections)
```
id                  bigint pk
section_key         varchar(60) unique  -- 'hero','cta_banner','footer','seo_home','seo_features','seo_pricing','demo_video','navbar'
locale              varchar(8) default 'id' indexed   -- siap multi-bahasa
data                json                              -- whole payload section
asset_path          varchar(255) nullable             -- main image (hero bg, cta bg, og image, dll)
updated_by_user_id  bigint nullable fk users
published_at        timestamp nullable                -- null = draft
timestamps
unique (section_key, locale)
```

### 3.2 Migration `landing_content_items` (collection sections)
```
id                  bigint pk
section_type        varchar(40) indexed  -- 'feature','testimonial','pricing_tier','faq','trust_logo','how_it_works_step','problem_point','navbar_link','footer_link'
locale              varchar(8) default 'id' indexed
sort_order          int default 0 indexed
is_published        boolean default false indexed
data                json
asset_path          varchar(255) nullable
updated_by_user_id  bigint nullable fk users
timestamps
index (section_type, locale, is_published, sort_order)
```

### 3.3 Migration `landing_setting_versions` (rollback)
```
id                  bigint pk
landing_setting_id  bigint fk landing_settings cascade
data                json                  -- snapshot
asset_path          varchar(255) nullable
created_by_user_id  bigint fk users
created_at          timestamp
index (landing_setting_id, created_at)
```

(Versioning untuk koleksi dianggap overkill di MVP — koleksi cukup pakai soft delete kalau perlu rollback per item. Lihat phase 9 di bawah.)

### 3.4 Asset Disk
- Tambahkan disk `landing` di `config/filesystems.php`:
  ```
  'landing' => [
      'driver' => 's3',
      'root' => 'landing',
      'visibility' => 'public',
      ...
  ]
  ```
  atau pakai `s3` existing dengan prefix `landing/`. Direkomendasikan disk terpisah supaya kalau bucket berbeda nantinya lebih mudah.

### 3.5 Seed Data Awal
Bikin `LandingContentSeeder` yang menyalin **persis** isi hardcoded sekarang ke tabel. Tujuannya: setelah migrate + seed, landing tampak identik dengan sebelumnya.
- 1 row di `landing_settings` untuk hero, cta_banner, footer, demo_video, navbar.
- 3 row di `landing_settings` untuk seo_home, seo_features, seo_pricing (ambil dari `structuredData` props existing).
- N row di `landing_content_items` per `section_type` mengikuti array yang ada di komponen sekarang. Set `is_published=true`, `sort_order` berurutan.

---

## 4. Domain Layer

### 4.1 Enums Baru
- `app/Domain/Landing/LandingSectionKey.php`
  ```
  enum LandingSectionKey: string {
      case Hero = 'hero';
      case CtaBanner = 'cta_banner';
      case Footer = 'footer';
      case Navbar = 'navbar';
      case DemoVideo = 'demo_video';
      case SeoHome = 'seo_home';
      case SeoFeatures = 'seo_features';
      case SeoPricing = 'seo_pricing';
      public function label(): string { ... }
  }
  ```
- `app/Domain/Landing/LandingItemType.php`
  ```
  enum LandingItemType: string {
      case PricingTier = 'pricing_tier';
      case Testimonial = 'testimonial';
      case FaqItem = 'faq';
      case FeatureHighlight = 'feature';
      case HowItWorksStep = 'how_it_works_step';
      case ProblemPoint = 'problem_point';
      case TrustedLogo = 'trust_logo';
      case NavbarLink = 'navbar_link';
      case FooterLink = 'footer_link';
      public function label(): string { ... }
  }
  ```

### 4.2 DTO Section-Specific
Sekian DTO readonly di `app/DTOs/Landing/`. Contoh dua yang utama:

`HeroSectionData`:
```
- eyebrow: string
- headline: string
- headlineAccent: string
- subCopy: string
- primaryCtaLabel: string
- primaryCtaHref: string
- secondaryCtaLabel: string
- trustNote: string
- backgroundImagePath: ?string
```

`PricingTierData`:
```
- name: string
- monthlyPrice: string
- yearlyPrice: string
- description: string
- ctaLabel: string
- ctaHref: string
- isHighlighted: bool
- features: array<int, string>
- missingFeatures: array<int, string>
- sortOrder: int
- isPublished: bool
```

DTO lain mengikuti pattern yang sama. Setiap DTO punya:
- `static fromArray(array $data, ?string $assetUrl = null): self`
- `toArray(): array`
- `validateOrFail(): void` (asserting required string non-empty, dst, throw `\InvalidArgumentException`)

### 4.3 Model Eloquent
- `app/Models/LandingSetting.php` — fillable: `section_key, locale, data, asset_path, updated_by_user_id, published_at`. Cast `data` => `array`, `published_at` => `datetime`. Scope `published()`, `forKey(LandingSectionKey $key, string $locale = 'id')`.
- `app/Models/LandingContentItem.php` — fillable: `section_type, locale, sort_order, is_published, data, asset_path, updated_by_user_id`. Cast `data` => `array`, `is_published` => `bool`. Scope `published()`, `ofType(LandingItemType $type, string $locale = 'id')`, `ordered()`.
- `app/Models/LandingSettingVersion.php` — read-mostly.

---

## 5. Action Layer

Lokasi: `app/Actions/Landing/`.

### 5.1 Read Actions

#### `GetLandingHomePayloadAction`
- Input: `?string $locale = 'id', ?string $previewToken = null`.
- Output: array dengan key:
  - `hero: HeroSectionData->toArray()`
  - `socialProof.logos: array<TrustedLogoData>`
  - `problem: array{ title, intro, items: array<ProblemPointData> }`
  - `features.items: array<FeatureHighlightData>`
  - `howItWorks: array{ heading, steps: array<HowItWorksStepData> }`
  - `testimonials.items: array<TestimonialData>`
  - `pricing: array{ heading, sub, tiers: array<PricingTierData> }`
  - `faq.items: array<FaqItemData>`
  - `cta: CtaBannerData->toArray()`
  - `seo: SeoMetaData->toArray()` (key seo_home)
- Implementasi:
  - Kalau `previewToken` ada dan valid, ambil dari draft (lihat preview action).
  - Kalau tidak, baca cache `landing:home_payload` (TTL 60 detik, anti-stampede pakai `Cache::remember(..., function ()` + `lock`).
  - Cache miss → query DB, build DTO, cache, return.

#### `GetLandingFeaturesPayloadAction`, `GetLandingPricingPayloadAction`
- Pattern sama, key cache berbeda.

#### `GetLandingSectionAction`
- Generic helper untuk Filament resource: `execute(LandingSectionKey $key, string $locale): array`. Tidak di-cache (panel butuh data fresh).

#### `GetLandingItemsAction`
- `execute(LandingItemType $type, string $locale, ?bool $publishedOnly = null): array`. Tidak di-cache untuk pemakaian di Filament.

### 5.2 Write Actions

#### `PublishLandingSectionAction`
- Input: `int $actorUserId, LandingSectionKey $key, string $locale, array $data, ?UploadedFile $asset = null`.
- Logic:
  1. Validasi `data` cocok dengan DTO section yang sesuai.
  2. Upload asset ke S3 disk `landing` kalau ada (delete file lama).
  3. Buat snapshot ke `landing_setting_versions` sebelum update.
  4. `LandingSetting::updateOrCreate([section_key, locale], [...])` set `published_at = now()`.
  5. Invalidate cache: `Cache::forget('landing:settings:'.$key->value.':'.$locale)` dan `Cache::forget('landing:home_payload')` (juga features/pricing payload kalau key berdampak).
  6. Audit log via `LogActivityAction::execute('landing.section.publish', $setting, ['key' => $key->value])`.

#### `SaveLandingDraftSectionAction`
- Sama seperti publish tapi `published_at` tetap null. Tidak invalidate cache produksi.

#### `RollbackLandingSectionAction`
- Input: `int $actorUserId, int $versionId`.
- Restore `data` + `asset_path` dari version, simpan version baru sebagai forward snapshot, invalidate cache, audit log.

#### `StoreLandingItemAction`, `UpdateLandingItemAction`, `DeleteLandingItemAction`
- CRUD koleksi. `delete` adalah soft delete (lihat phase 9). Invalidate cache section type + home/features/pricing payload yang terdampak.

#### `ReorderLandingItemsAction`
- Input: `int $actorUserId, LandingItemType $type, array<int, int> $orderedIds` (`[id => sort_order]`).
- Update sort_order dalam transaction, invalidate cache, audit log.

#### `IssueLandingPreviewTokenAction`
- Input: `int $actorUserId, ?int $ttlMinutes = 30`.
- Generate UUID, simpan di Redis dengan key `landing:preview:{uuid}` value `{ user_id, issued_at, ttl }`. TTL 30 menit.
- Return token UUID.

#### `ResolveLandingPreviewSnapshotAction`
- Input: `string $token`.
- Validasi token ada di Redis, ambil semua draft (yang belum `published_at` atau yang draft di koleksi via field flag baru kalau perlu). Build payload home/features/pricing seperti normal tapi dari draft.

### 5.3 Cache Layer Helper
- File: `app/Support/LandingCache.php` (final class statis):
  - `public static function homePayloadKey(string $locale): string`
  - `public static function settingsKey(LandingSectionKey $key, string $locale): string`
  - `public static function itemsKey(LandingItemType $type, string $locale): string`
  - `public static function flushAll(): void` (dipanggil di seeder dan tombol "Flush Cache" di panel kalau perlu).

---

## 6. Form Request Layer

Lokasi: `app/Http/Requests/Landing/`.

Setiap section punya Form Request sendiri. Contoh `PublishHeroRequest`:
```
authorize(): bool
    return $this->user() !== null
        && $this->user()->hasRole('super_admin');

rules(): array {
    return [
        'eyebrow'         => ['required', 'string', 'max:120'],
        'headline'        => ['required', 'string', 'max:200'],
        'headline_accent' => ['nullable', 'string', 'max:200'],
        'sub_copy'        => ['required', 'string', 'max:600'],
        'primary_cta_label' => ['required', 'string', 'max:60'],
        'primary_cta_href'  => ['required', 'string', 'max:255'],
        'secondary_cta_label' => ['nullable', 'string', 'max:60'],
        'trust_note'      => ['nullable', 'string', 'max:255'],
        'asset'           => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,webp'],
        'publish'         => ['nullable', 'boolean'],
    ];
}
```

Pattern serupa untuk: `PublishCtaBannerRequest`, `PublishFooterRequest`, `PublishNavbarRequest`, `PublishDemoVideoRequest`, `PublishSeoMetaRequest`, dan untuk koleksi `StorePricingTierRequest`, `UpdatePricingTierRequest`, dst.

---

## 7. Filament Panel UI

### 7.1 Settings Pages (Singleton Sections)

Lokasi: `app/Filament/Pages/Landing/`.

Setiap page extend `Filament\Pages\Page` dengan custom view atau pakai pattern `HasForms`. Contoh `HeroSettingsPage`:

```
class HeroSettingsPage extends Page implements HasForms {
    use InteractsWithForms;
    protected static ?string $navigationGroup = 'Landing CMS';
    protected static ?string $navigationLabel = 'Hero Section';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.landing.hero-settings';

    public ?array $data = [];

    public function mount(GetLandingSectionAction $action): void {
        $payload = $action->execute(LandingSectionKey::Hero, 'id');
        $this->form->fill($payload);
    }

    public function form(Form $form): Form {
        return $form->schema([
            TextInput::make('eyebrow')->required()->maxLength(120),
            TextInput::make('headline')->required()->maxLength(200),
            TextInput::make('headline_accent')->maxLength(200),
            Textarea::make('sub_copy')->required()->maxLength(600),
            Grid::make(2)->schema([
                TextInput::make('primary_cta_label')->required(),
                TextInput::make('primary_cta_href')->required(),
            ]),
            TextInput::make('secondary_cta_label'),
            TextInput::make('trust_note'),
            FileUpload::make('asset')->image()->disk('landing')->directory('hero')->maxSize(2048),
        ]);
    }

    public function save(PublishLandingSectionAction $action, ...): void {
        $action->execute(...);
        Notification::make()->success()->title('Hero published')->send();
    }

    public function preview(IssueLandingPreviewTokenAction $issue): RedirectResponse {
        $token = $issue->execute(auth()->id());
        return redirect('/?landing_preview='.$token, secure: true);
    }
}
```

Setiap page punya 2 tombol: "Save Draft" dan "Save & Publish". Tambahan tombol "Preview" buka tab baru ke landing dengan token.

Pages yang harus dibuat:
- `HeroSettingsPage`
- `CtaBannerSettingsPage`
- `FooterSettingsPage`
- `NavbarSettingsPage`
- `DemoVideoSettingsPage`
- `SeoMetaSettingsPage` (3 tab: Home, Features, Pricing)

### 7.2 Resources (Collection Sections)

Lokasi: `app/Filament/Resources/Landing/`.

#### `PricingTierResource`
- Model: `LandingContentItem` dengan global scope `ofType(LandingItemType::PricingTier)`.
- Table:
  - Columns: name (dari `data->name`), monthly_price, yearly_price, is_highlighted, is_published, sort_order.
  - Reorderable (Filament built-in `->reorderable('sort_order')`).
  - Filter is_published.
- Form:
  - Schema sesuai `PricingTierData` field. Pakai `Repeater::make('features')` dan `Repeater::make('missing_features')` untuk array string.
  - Toggle is_highlighted (max 1 row boleh true; validasi di Action).
  - Toggle is_published.

#### `TestimonialResource`
- Form: name, role, organization, quote, photo (FileUpload `disk('landing')->directory('testimonials')->image()->circleCropper()`), is_featured, sort_order, is_published.

#### `FaqResource`
- Form: question (TextInput), answer (RichEditor markdown), sort_order, is_published.
- Reorderable.

#### `FeatureHighlightResource`
- Form: title, description, icon_name (Select dari list lucide icons yang dipakai di Prokerin — sediakan static array di DTO), screenshot (FileUpload), sort_order, is_published.

#### `HowItWorksStepResource`
- Form: step_number (auto-incrementing kalau kosong), title, description, sort_order, is_published.

#### `ProblemPointResource`
- Form: title, description, icon_name, sort_order, is_published.

#### `TrustedLogoResource`
- Form: name, link, logo (FileUpload `disk('landing')->directory('trusted-logos')->image()->maxSize(512)`), sort_order, is_published.

#### `NavbarLinkResource`, `FooterLinkResource`
- Form: label, href, sort_order, is_published, group (footer butuh group: "Product"/"Company"/"Resources").

### 7.3 Sidebar Group
Di `AdminPanelProvider`:
```
->navigationGroups([
    NavigationGroup::make('Landing CMS')
        ->collapsed(false)
        ->icon('heroicon-o-globe-alt'),
    ...
])
```
Set `protected static ?string $navigationGroup = 'Landing CMS'` di setiap resource/page yang relevan.

### 7.4 Authorization
- Setiap resource/page panggil `SuperAdminGate::canAccess()` di `canViewAny()` (sudah pattern existing).
- Tambah optional permission Spatie kalau mau granular: `super_admin` dapat semua, role baru `landing_editor` dapat akses CMS saja tanpa user/org management. Untuk MVP cukup `super_admin`.

---

## 8. Frontend Refactor (React)

### 8.1 Update `LandingController`
```
public function home(GetLandingHomePayloadAction $action): Response {
    $payload = $action->execute('id', $this->resolvePreviewToken());
    return Inertia::render('Landing/Home', $payload);
}
```

`resolvePreviewToken()` membaca query string `?landing_preview=...`, validasi via Redis, return token kalau valid. Halaman dengan preview token wajib `noindex`.

### 8.2 Refactor Component Landing
Pattern: tiap section yang sekarang punya array hardcoded, refactor jadi presentational component yang terima props.

`HeroSection.tsx`:
```
interface HeroSectionProps {
    eyebrow: string;
    headline: string;
    headlineAccent: string | null;
    subCopy: string;
    primaryCtaLabel: string;
    primaryCtaHref: string;
    secondaryCtaLabel: string;
    trustNote: string;
    backgroundImageUrl: string | null;
}
```
`Home.tsx`:
```
<HeroSection {...payload.hero} />
<SocialProofBar logos={payload.socialProof.logos} />
<ProblemSection {...payload.problem} />
<FeatureShowcase items={payload.features.items} />
<HowItWorksSection heading={payload.howItWorks.heading} steps={payload.howItWorks.steps} />
<TestimonialsSection items={payload.testimonials.items} />
<PricingSection heading={payload.pricing.heading} sub={payload.pricing.sub} tiers={payload.pricing.tiers} />
<FaqSection items={payload.faq.items} />
<CtaBanner {...payload.cta} />
```

Hapus `useState` array constants, ganti `useState` cukup untuk UI state (open FAQ, billing toggle).

### 8.3 Update Type Definitions
File baru: `resources/js/types/landing.d.ts` dengan interface lengkap untuk `LandingHomePayload`, `LandingFeaturesPayload`, `LandingPricingPayload`. Re-export dari `resources/js/types/index.d.ts` kalau perlu.

### 8.4 Preview Mode Indicator
Kalau payload datang dengan flag `isPreview: true`, tampilkan banner sticky di atas landing: "Mode Preview · Token kadaluarsa pukul HH:MM".

---

## 9. Versioning, Soft Delete, Rollback

### 9.1 Singleton Settings
- Setiap publish auto-snapshot ke `landing_setting_versions`.
- Filament page `HeroSettingsPage` tambah action "Lihat Riwayat" (modal) menampilkan versions list dengan tombol "Restore".
- Restore memanggil `RollbackLandingSectionAction`.

### 9.2 Collection Items
- Pakai `SoftDeletes` di `LandingContentItem`.
- Filament resource `Tables\Filters\TrashedFilter` dan action `Tables\Actions\RestoreAction`.

### 9.3 Schedule Publish (Optional Future)
- Tambah kolom `publish_at` di `landing_content_items` (kalau dibutuhkan).
- Job `PublishScheduledLandingItemsJob` jalan setiap 5 menit (cron) yang flip `is_published=true` jika `publish_at <= now()`. Di-defer ke iterasi 2 kalau tidak urgent.

---

## 10. Cache Strategy Detail

### 10.1 Key Naming
| Key | Isi | TTL | Invalidasi |
|---|---|---|---|
| `landing:home_payload:id` | Full payload Home (DTO arrays) | 60s | Setiap save section yang dipakai Home, atau item type yang dipakai Home |
| `landing:features_payload:id` | Full payload Features | 60s | Save section yang dipakai Features |
| `landing:pricing_payload:id` | Full payload Pricing | 60s | Save pricing tier, save seo_pricing, save cta_banner |
| `landing:settings:hero:id` | Single section data | 5m | Save hero |
| `landing:items:pricing_tier:id` | Sorted published items | 5m | CRUD pricing tier |

TTL pendek (60s) untuk full payload supaya invalidation miss tetap cepat refresh. Kalau super admin sering edit di staging, bisa set lebih kecil via `LANDING_CACHE_TTL=10`.

### 10.2 Cache Stampede Protection
Pakai `Cache::lock('landing:lock:home_payload', 5)` di dalam `Cache::remember`. Hanya satu request yang regenerate, sisanya tunggu pakai `->block(3)`.

### 10.3 Manual Flush
Tombol "Flush Cache Landing" di Filament dashboard (page "Landing CMS Operations") yang call `LandingCache::flushAll()`. Audit log.

---

## 11. Audit Log Integration

Action keys (gunakan `LogActivityAction` existing):
- `landing.section.publish` (target = LandingSetting, payload = `{ key, locale, has_asset }`)
- `landing.section.draft_saved` (target = LandingSetting)
- `landing.section.rollback` (target = LandingSetting, payload = `{ from_version_id }`)
- `landing.item.created` (target = LandingContentItem, payload = `{ type }`)
- `landing.item.updated` (target = LandingContentItem)
- `landing.item.deleted` (target = LandingContentItem)
- `landing.item.reordered` (target = LandingContentItem `0` placeholder, payload = `{ type, ordered_ids }`)
- `landing.cache.flushed` (target = current super admin user, no payload)
- `landing.preview.issued` (target = current super admin user, payload = `{ token_hash }`)

---

## 12. Test Strategy

Lokasi tests baru: `tests/Feature/Landing/` dan `tests/Unit/Landing/`.

### 12.1 Unit Tests
- `tests/Unit/Landing/HeroSectionDataTest.php` — fromArray, validateOrFail.
- `tests/Unit/Landing/PricingTierDataTest.php` — same.
- Lakukan untuk semua DTO yang punya validasi non-trivial.

### 12.2 Feature Tests
- `LandingPayloadTest.php`
  - Home payload mengembalikan struktur lengkap.
  - Cache hit kedua kali (assert query count berkurang).
  - Cache invalidate setelah PublishLandingSectionAction (Heroe edit).
- `PublishLandingSectionTest.php`
  - Super admin bisa publish hero, audit log tercatat.
  - Non super admin (organization_owner) ditolak 403.
  - File asset valid disimpan di disk fake.
  - File invalid (PHP, oversize) ditolak.
- `LandingItemCrudTest.php`
  - Super admin create/update/delete pricing tier.
  - Reorder mengubah sort_order benar.
  - Soft delete + restore.
- `LandingPreviewTest.php`
  - Token valid → React render dengan data draft.
  - Token expired → fallback ke published.
  - Token milik user lain → ditolak (preview tetap published).
- `LandingNoIndexHeaderTest.php`
  - Response saat preview mode wajib `X-Robots-Tag: noindex, nofollow`.

### 12.3 Browser Smoke (manual checklist)
- Publish hero baru → reload `/` → headline berubah.
- Toggle pricing tier publish off → tier hilang dari `/pricing`.
- Reorder FAQ → urutan berubah di `/`.
- Upload logo trust → muncul di SocialProofBar.
- Klik Preview di Filament → tab baru tampilkan draft, ada banner preview.

---

## 13. Environment Variables Tambahan

Tambahkan ke `.env.example`:
```
LANDING_CACHE_TTL=300
LANDING_HOME_PAYLOAD_TTL=60
LANDING_PREVIEW_TTL_MINUTES=30
LANDING_DISK=landing
LANDING_LOCALE_DEFAULT=id
LANDING_ALLOWED_LOCALES=id
```

Tambahkan ke `config/landing.php`:
```
return [
    'cache_ttl' => (int) env('LANDING_CACHE_TTL', 300),
    'home_payload_ttl' => (int) env('LANDING_HOME_PAYLOAD_TTL', 60),
    'preview_ttl_minutes' => (int) env('LANDING_PREVIEW_TTL_MINUTES', 30),
    'disk' => env('LANDING_DISK', 'landing'),
    'default_locale' => env('LANDING_LOCALE_DEFAULT', 'id'),
    'allowed_locales' => array_filter(explode(',', (string) env('LANDING_ALLOWED_LOCALES', 'id'))),
];
```

---

## 14. Eksekusi Per Fase

Eksekusi top-down. Jangan loncat fase.

### Phase 1 — Schema & Domain (1-2 hari)
- [ ] Migration `landing_settings`, `landing_content_items`, `landing_setting_versions`.
- [ ] Disk `landing` di `config/filesystems.php`.
- [ ] Enum `LandingSectionKey`, `LandingItemType`.
- [ ] DTO untuk semua section + collection.
- [ ] Model `LandingSetting`, `LandingContentItem`, `LandingSettingVersion`.
- [ ] `config/landing.php` dan update `.env.example`.
- [ ] Seeder `LandingContentSeeder` yang menyalin isi hardcoded sekarang ke DB.
- [ ] Run migration + seed di local, verifikasi rows ada.

### Phase 2 — Read Action & Cache Layer (1 hari)
- [ ] `LandingCache` helper.
- [ ] `GetLandingSectionAction`, `GetLandingItemsAction`.
- [ ] `GetLandingHomePayloadAction`, `GetLandingFeaturesPayloadAction`, `GetLandingPricingPayloadAction`.
- [ ] Test Feature `LandingPayloadTest::test_home_payload_uses_cache`.

### Phase 3 — Frontend Data-Backed (1-2 hari)
- [ ] Refactor `LandingController` agar return Inertia props.
- [ ] Refactor semua `Components/Landing/*.tsx` jadi pure presentational dengan props.
- [ ] Update `Pages/Landing/Home.tsx`, `Features.tsx`, `Pricing.tsx` agar terima dan distribusi props ke komponen.
- [ ] Tambah `types/landing.d.ts`.
- [ ] Browser smoke: tampilan landing identik dengan sebelum refactor (karena seed konten sama).
- [ ] `npm run build` pass.

### Phase 4 — Filament Settings Pages (2 hari)
- [ ] `HeroSettingsPage`, `CtaBannerSettingsPage`, `FooterSettingsPage`, `NavbarSettingsPage`, `DemoVideoSettingsPage`.
- [ ] `SeoMetaSettingsPage` dengan tabs Home/Features/Pricing.
- [ ] Form Request `PublishHeroRequest`, dst.
- [ ] Action `PublishLandingSectionAction`, `SaveLandingDraftSectionAction`.
- [ ] Save sukses → flash notification, audit log tercatat.
- [ ] Save invalidate cache, landing produksi langsung refresh (verify manual).

### Phase 5 — Filament Resources (Collection) (2-3 hari)
- [ ] `PricingTierResource`, `TestimonialResource`, `FaqResource`, `FeatureHighlightResource`, `HowItWorksStepResource`, `ProblemPointResource`, `TrustedLogoResource`.
- [ ] `NavbarLinkResource`, `FooterLinkResource`.
- [ ] Reorder action di Filament table.
- [ ] Form Request CRUD per tipe.
- [ ] Action `StoreLandingItemAction`, `UpdateLandingItemAction`, `DeleteLandingItemAction`, `ReorderLandingItemsAction`.
- [ ] Soft delete + Trashed filter aktif.
- [ ] Tests CRUD + reorder.

### Phase 6 — Live Preview (1 hari)
- [ ] `IssueLandingPreviewTokenAction`, `ResolveLandingPreviewSnapshotAction`.
- [ ] Update `LandingController` agar baca `?landing_preview=` query param.
- [ ] Banner preview di React landing.
- [ ] Header `X-Robots-Tag: noindex, nofollow` saat preview.
- [ ] Tombol "Preview" di setiap Settings Page Filament + tombol "Preview Halaman" global.
- [ ] Test feature preview.

### Phase 7 — Versioning Singleton (1 hari)
- [ ] `RollbackLandingSectionAction`.
- [ ] Filament action "Lihat Riwayat" di Settings Pages.
- [ ] Modal list versions, tombol "Restore".
- [ ] Test feature rollback.

### Phase 8 — Polish & Verification (1 hari)
- [ ] Sweep semua section: data benar-benar dari DB, tidak ada lagi array hardcoded.
- [ ] Audit log lengkap untuk semua action.
- [ ] Pint + lint + build hijau.
- [ ] Full regression `php artisan test` pass, jumlah test naik > baseline.
- [ ] Browser smoke: edit setiap section dari panel → refresh `/` → konten ter-update dalam 60 detik (TTL home payload).
- [ ] Update `features.md`: tambah section `LCMS01 · Landing CMS` ke `## Internal Tooling` (atau bikin section baru `Marketing & Growth Tooling`) dengan status `[x]` dan verification log.
- [ ] Update `QA-MASTER-PROKERIN.md`: tambah Section "Landing CMS" dengan checklist test cases.

---

## 15. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Cache stale lebih dari 60 detik karena Redis hiccup | Konten lama tampil sebentar setelah publish | Tambah tombol "Flush Cache Landing" manual. Gunakan `Cache::tags(['landing'])->flush()` kalau driver support. |
| Image upload besar memori | Worker out of memory saat resize | Pakai job `OptimizeLandingImageJob` queued, panel langsung simpan path original, optimasi async. Tampilkan placeholder sampai job selesai. |
| Tim non-teknis salah ubah CTA href ke URL invalid | CTA jadi 404 | Validasi `url` di Form Request untuk `*_cta_href` (allow internal route via regex `^/[a-z0-9/-]+$` ATAU absolute URL). |
| Markdown FAQ disisipi script | XSS di landing | Pakai parser markdown yang strip HTML by default (`league/commonmark` dengan `disallowedRawHtml`). Test feature. |
| Preview token bocor publik | Konten draft tampil ke pengunjung | Token UUID v4, TTL 30 menit, single-user binding. Audit log issued tokens. |
| Locale ID-only sekarang, nanti EN ditambah → migrasi data tidak konsisten | Multi-bahasa setengah jadi | Schema sudah punya kolom `locale` dari awal. Saat tambah EN, fill row baru dengan locale='en' tanpa ubah schema. |
| Filament panel jadi padat dengan ~12 item baru | Sidebar overflow | Group `Landing CMS` collapsible, navigationSort konsisten. |

---

## 16. Master Checklist (Quick View)

### Phase 1 Schema
- [ ] Migration 3 tabel
- [ ] Disk `landing`
- [ ] Enum + DTO + Model
- [ ] Config + ENV
- [ ] Seeder

### Phase 2 Read Layer
- [ ] LandingCache helper
- [ ] Get section/item/payload actions
- [ ] Test cache hit

### Phase 3 Frontend
- [ ] LandingController data-backed
- [ ] Refactor 13 komponen jadi props
- [ ] Update 3 Pages/Landing
- [ ] types/landing.d.ts
- [ ] Build & smoke parity

### Phase 4 Settings Pages Panel
- [ ] 5 Settings Pages + SEO tabs
- [ ] Form Request publish
- [ ] PublishLandingSectionAction
- [ ] Audit log + cache invalidate

### Phase 5 Resources Panel
- [ ] 9 Resources
- [ ] Reorder action
- [ ] Form Request CRUD
- [ ] CRUD actions
- [ ] Soft delete

### Phase 6 Preview
- [ ] Token issue/resolve
- [ ] Controller preview branch
- [ ] React banner + noindex header

### Phase 7 Rollback
- [ ] Rollback action
- [ ] Filament riwayat modal

### Phase 8 Verification
- [ ] No hardcoded array tersisa
- [ ] Audit log lengkap
- [ ] Pint/lint/build hijau
- [ ] Full regression test pass
- [ ] features.md & QA-MASTER updated

---

*Setelah dokumen ini selesai dieksekusi, semua konten landing page Prokerin bisa di-edit oleh tim non-teknis lewat `/internal-admin/landing/*` tanpa redeploy. CMS ini dirancang aman, ter-audit, dan punya rollback path.*
