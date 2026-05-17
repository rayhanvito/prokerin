# PROKERIN — Landing Polish Prompt
## Untuk: Claude Opus (claude-opus-4-5) via Claude Code
## Status Plan: AKTIF (Landing Polish · menggantikan LCMS01 yang dibatalkan 2026-05-17)

---

## INSTRUKSI UNTUK AGENT

Kamu adalah senior full-stack engineer yang bekerja di codebase **Prokerin** — aplikasi manajemen program kerja (proker) untuk organisasi kampus Indonesia. Stack: **Laravel 11 + Inertia.js + React (TypeScript) + Tailwind CSS + Shadcn/UI**.

Tugasmu: eksekusi **Landing Polish Plan** untuk menyempurnakan landing page yang sudah ada. **Tidak ada migration database, tidak ada Filament resource baru, tidak ada cache layer baru.** Semua perubahan di file React, asset, meta tag, dan analytics.

Baca seluruh prompt ini sebelum mulai. Eksekusi secara berurutan sesuai sprint. Setelah tiap sprint selesai, jalankan `npm run build` dan `./vendor/bin/pint --test` untuk verifikasi.

---

## KONTEKS CODEBASE

### Stack
- **Backend**: Laravel 11, PHP 8.3+, `declare(strict_types=1)` wajib di semua PHP
- **Frontend**: Inertia.js + React 18 + TypeScript strict mode
- **Styling**: Tailwind CSS + Shadcn/UI — jangan modifikasi `resources/js/components/ui/*`
- **Icons**: `lucide-react` saja untuk landing public — jangan campur dengan heroicons
- **State**: Hanya React state (`useState`, `useReducer`) — jangan localStorage
- **Head**: Pakai `<Head>` dari `@inertiajs/react` untuk meta tag

### File Landing yang Relevan
```
resources/js/
├── Pages/Landing/
│   ├── Home.tsx
│   ├── Features.tsx
│   └── Pricing.tsx
├── Components/Landing/
│   ├── Navbar.tsx
│   ├── HeroSection.tsx
│   ├── SocialProofBar.tsx
│   ├── ProblemSection.tsx
│   ├── FeatureShowcase.tsx
│   ├── HowItWorksSection.tsx
│   ├── TestimonialsSection.tsx
│   ├── PricingSection.tsx
│   ├── FaqSection.tsx
│   ├── CtaBanner.tsx
│   ├── Footer.tsx
│   └── DemoVideoModal.tsx
└── types/
    └── index.d.ts (tambah landing types di sini)

app/Http/Controllers/LandingController.php
routes/web.php (tidak diubah)
public/
├── og-image.png       (buat/siapkan)
├── robots.txt         (perbarui)
└── sitemap.xml        (buat)
```

### Brand Prokerin
```
Warna utama  : #24695c (hijau teal gelap)
Warna accent : #ba895d (emas/amber)
Warna gelap  : #1b4c43 (teal lebih gelap)
Warna danger : #d22d3d (merah)
Border radius: 4px (card, button — sesuai Viho design system)
Shadow       : shadow-sm atau shadow (bukan shadow-xl)
```

### Target User Landing
Mahasiswa pengurus organisasi kampus Indonesia: BEM, HIMA, UKM — yang capek pakai Google Docs, grup WhatsApp, dan Excel untuk manage proker, proposal, RAB, LPJ.

---

## SPRINT 1 — COPY & CONTENT FINAL

> **Goal**: Semua copy final, tidak ada `lorem`, `placeholder`, `TODO`, `coming soon` yang tidak disengaja, link dummy, atau button mati.

### 1.1 Baca Codebase Dulu

Sebelum menulis satu baris pun, baca file-file ini:
```bash
# Baca semua komponen landing untuk memahami state saat ini
cat resources/js/Pages/Landing/Home.tsx
cat resources/js/Pages/Landing/Features.tsx
cat resources/js/Pages/Landing/Pricing.tsx
cat resources/js/Components/Landing/HeroSection.tsx
cat resources/js/Components/Landing/PricingSection.tsx
cat resources/js/Components/Landing/FaqSection.tsx
cat resources/js/Components/Landing/Footer.tsx
cat resources/js/Components/Landing/TestimonialsSection.tsx
cat resources/js/Components/Landing/DemoVideoModal.tsx
cat app/Http/Controllers/LandingController.php
```

### 1.2 Copy per Section

Update setiap komponen dengan copy berikut. Pertahankan semua JSX/styling yang sudah ada — **hanya ganti string konten**. Kalau ada field yang belum ada di komponen, tambahkan props atau hardcode dengan nilai final berikut.

#### `HeroSection.tsx`
```
eyebrow      : "Khusus Organisasi Kampus Indonesia"
headline     : "Kelola Proker, Proposal & LPJ Tanpa Ribet"
headlineAccent: "Tanpa Ribet"   ← bagian ini diberi warna accent #ba895d
subCopy      : "Prokerin menyatukan proker, task, RAB, proposal, hingga LPJ dalam satu platform — bukan 10 grup WhatsApp dan 5 Google Doc."
primaryCta   : label="Mulai Gratis Sekarang"  href="/register"
secondaryCta : label="Lihat Fitur Lengkap"    href="/features"
trustNote    : "Gratis untuk 5 anggota pertama. Tidak perlu kartu kredit."
```

#### `SocialProofBar.tsx`
```
heading: "Dipercaya organisasi dari"
logos: 4 placeholder institusi — gunakan text placeholder bergaya monogram:
  - "BEM UI"
  - "HIMA FTUI"
  - "UKM Fotografi ITB"
  - "OSIS SMKN 1 JKT"
Tambahkan keterangan "(Coming Soon — jadilah yang pertama)" di bawah logo row.
```

#### `ProblemSection.tsx`
```
heading: "Kamu yang ini, ya?"
intro: "Kalau kamu pernah frustrasi dengan salah satu dari ini, Prokerin dibuat untuk kamu."
items (3 problem points):
  1. icon: FileX         | title: "Proposal Revisi Terus"
     desc: "File proposal ada di email ketua, revisi di WhatsApp, versi final entah di mana. Meeting jam 9, proposal belum fix."
  2. icon: LayoutDashboard | title: "Task Bocor ke Mana-mana"
     desc: "Siapa penanggung jawab sewa sound system? Sudah beli konsumsi belum? Tidak ada yang tahu sampai H-1."
  3. icon: Calculator    | title: "RAB & LPJ Tak Pernah Sinkron"
     desc: "Budget disetujui 5 juta, ternyata habis 6,2 juta. LPJ pun jadi drama. Tiap tahun terulang."
```

#### `FeatureShowcase.tsx`
```
heading: "Semua yang Kamu Butuhkan, Satu Platform"
subheading: "Dari proposal sampai LPJ, dari task sampai absensi — semuanya terhubung."
features (6 item):
  1. icon: FolderKanban | title: "Manajemen Proker"
     tagline: "Satu proker, satu tempat."
     desc: "Buat proker, assign lead, pantau progress — dari draft sampai LPJ selesai."
  2. icon: CheckSquare  | title: "Task & Kanban"
     tagline: "Tidak ada task yang jatuh lewat celah."
     desc: "Kanban drag-and-drop, deadline reminder otomatis, assignment per divisi."
  3. icon: FileText     | title: "Proposal & LPJ Otomatis"
     tagline: "Template siap pakai, bukan Google Doc kosong."
     desc: "Editor proposal terstruktur, export PDF/DOCX, approval multi-level bawaan."
  4. icon: Wallet       | title: "RAB & Realisasi Keuangan"
     tagline: "Budget tidak bakar sendiri."
     desc: "Rencanakan anggaran, upload bukti pengeluaran, lihat grafik RAB vs realisasi real-time."
  5. icon: QrCode       | title: "Absensi QR Code"
     tagline: "Scan, hadir, selesai."
     desc: "Generate QR token per sesi, check-in via smartphone, export CSV langsung."
  6. icon: Award        | title: "Sertifikat Digital"
     tagline: "Sertifikat yang bisa diverifikasi, bukan sekadar PDF."
     desc: "Template kustom, QR verify publik, batch issue untuk ratusan peserta sekaligus."
```

#### `HowItWorksSection.tsx`
```
heading: "Mulai dalam 10 Menit"
steps (4 langkah):
  1. "Buat Akun"         desc: "Daftar gratis, buat organisasi, undang pengurus."
  2. "Setup Proker"      desc: "Pilih template (Seminar/Makrab/Kompetisi), assign lead, set tanggal."
  3. "Jalankan Bersama"  desc: "Track task, keuangan, dokumen, dan absensi — semua satu layar."
  4. "Selesai dengan LPJ"desc: "Checklist LPJ otomatis ter-isi dari data proker. Export PDF, submit, done."
```

#### `TestimonialsSection.tsx`
```
KEPUTUSAN: Jika belum ada testimonial nyata di codebase ini, HAPUS section ini sepenuhnya
dari Home.tsx dan tambahkan komentar:
  {/* TestimonialsSection ditunda sampai ada testimonial nyata */}
Jangan tampilkan testimonial palsu/fiktif.
```

#### `PricingSection.tsx`
```
heading: "Harga yang Masuk Akal untuk Mahasiswa"
sub: "Mulai gratis. Upgrade saat organisasi kamu berkembang."
tiers (4):
  FREE:
    name: "Free"
    price: "Rp 0"
    period: "/bulan"
    desc: "Untuk organisasi kecil yang baru mulai."
    cta: label="Daftar Gratis" href="/register?plan=free"
    features: ["Maks. 5 anggota","1 proker aktif","Task management","Dokumen (100MB)"]
    missing: ["Finance & RAB","Export PDF/DOCX","Sertifikat digital","Absensi QR","Priority support"]

  STARTER:
    name: "Starter"
    price: "Rp 49.000"
    period: "/bulan"
    desc: "Untuk BEM & HIMA yang serius."
    highlighted: false
    cta: label="Mulai Starter" href="/register?plan=starter"
    features: ["Maks. 25 anggota","5 proker aktif","Semua fitur Free","Finance & RAB","Export PDF/DOCX","Absensi QR"]
    missing: ["Sertifikat digital","White-label","Priority support"]

  PRO (HIGHLIGHTED):
    name: "Pro"
    price: "Rp 99.000"
    period: "/bulan"
    desc: "Untuk UKM dan organisasi dengan banyak proker."
    highlighted: true
    badge: "Paling Populer"
    cta: label="Mulai Pro" href="/register?plan=pro"
    features: ["Anggota tak terbatas","Proker tak terbatas","Semua fitur Starter","Sertifikat digital","Approval workflow multi-level","Notifikasi WhatsApp","Priority support"]
    missing: []

  CAMPUS:
    name: "Campus"
    price: "Hubungi Kami"
    period: ""
    desc: "Untuk kampus yang ingin monitor semua organisasi mahasiswanya."
    cta: label="Hubungi Sales" href="mailto:halo@prokerin.id"
    features: ["Semua fitur Pro","Dashboard kampus","SSO & integrasi SIAKAD","Onboarding dedicated","SLA & uptime guarantee","Custom contract"]
    missing: []
```

#### `FaqSection.tsx`
```
Pertahankan max 7 FAQ. Hapus yang berlebihan. Isi final:

Q1: "Apakah Prokerin gratis?"
A1: "Iya, paket Free selamanya gratis untuk organisasi dengan maks. 5 anggota dan 1 proker aktif. Tidak perlu kartu kredit untuk mulai."

Q2: "Apakah data proker kami aman?"
A2: "Data disimpan di server Indonesia dengan enkripsi dan backup harian. Setiap organisasi di-isolasi — tidak ada satu pun data org lain yang bisa diakses dari akunmu."

Q3: "Bisa pakai di UKM yang anggotanya 200 orang?"
A3: "Tentu. Paket Pro tidak membatasi jumlah anggota. Kamu bisa undang seluruh panitia, assign peran berbeda, dan kelola puluhan proker sekaligus."

Q4: "Apakah ada template proposal dan LPJ?"
A4: "Ada. Prokerin menyediakan template untuk Seminar, Workshop, Kompetisi, dan Makrab — lengkap dengan struktur proposal, checklist LPJ, dan budget line awal yang bisa dikustomisasi."

Q5: "Bagaimana cara migrasi dari Google Docs / spreadsheet?"
A5: "Kamu tidak perlu migrasi semua konten lama. Mulai dari proker berikutnya — buat proker baru, pilih template, upload dokumen lama sebagai lampiran, lanjut dari sana."

Q6: "Apakah bisa dipakai offline?"
A6: "Prokerin adalah aplikasi web dan butuh koneksi internet. Tapi halaman yang sudah dibuka tetap bisa dibaca walau koneksi terputus sesaat."

Q7: "Bagaimana cara upgrade atau downgrade paket?"
A7: "Bisa dilakukan kapan saja dari halaman pengaturan organisasi. Upgrade langsung aktif, downgrade berlaku di akhir periode billing."
```

#### `CtaBanner.tsx`
```
headline: "Mulai Proker Pertamamu Hari Ini"
sub: "Gratis untuk 5 anggota pertama. Setup 10 menit. Tidak perlu approval IT."
cta: label="Daftar Sekarang — Gratis" href="/register"
note: "Sudah punya akun? Masuk di sini." (link ke /login)
```

#### `Footer.tsx`
```
brand: "Prokerin"
tagline: "Manajemen proker untuk organisasi kampus Indonesia."
copyright: `© ${new Date().getFullYear()} Prokerin. Dibuat dengan ☕ di Indonesia.`
appVersion: gunakan `usePage().props.appVersion` atau fallback ke string kosong

Kolom link:
  Produk: Fitur (/features), Harga (/pricing), Template (/#how-it-works)
  Perusahaan: Tentang (/about jika ada, atau hapus), Kontak (mailto:halo@prokerin.id)
  Legal: Kebijakan Privasi (/privacy jika ada), Syarat Penggunaan (/terms jika ada)

Sosial media: HAPUS ikon sosial kalau URL-nya belum nyata. Jangan biarkan link placeholder.

Versi app di footer:
  {props.appVersion && (
    <span className="text-xs text-muted-foreground">v{props.appVersion}</span>
  )}
```

#### `DemoVideoModal.tsx`
```
KEPUTUSAN: Jika URL video belum ada atau kosong:
  - Sembunyikan tombol "Tonton Demo" dari HeroSection
  - Atau ganti tombol dengan: href="/features" label="Lihat Fitur Lengkap"
  - Tambahkan komentar: {/* DemoVideoModal diaktifkan saat video demo tersedia */}
Jangan tampilkan modal dengan video kosong/404.
```

#### `Navbar.tsx`
```
links:
  - label: "Fitur"     href: "/features"
  - label: "Harga"     href: "/pricing"
  - label: "Masuk"     href: "/login"    (ghost/outline style)
  - label: "Daftar"    href: "/register" (primary button style)

Mobile: hamburger drawer dari kiri/kanan
Active state: link aktif ditandai dengan underline atau warna primary
Smooth scroll: anchor ke /#how-it-works, /#pricing harus jalan di halaman /
```

### 1.3 Verifikasi Copy

Setelah semua komponen diupdate, jalankan:
```bash
# Cari semua sisa placeholder
grep -r "lorem\|placeholder\|TODO\|FIXME\|coming soon\|your text\|sample\|dummy" \
  resources/js/Components/Landing/ \
  resources/js/Pages/Landing/ \
  --include="*.tsx" --include="*.ts" -i

# ASSERT: zero output
```

### 1.4 Commit Sprint 1
```bash
./vendor/bin/pint --test
npm run build
git add resources/js/Components/Landing/ resources/js/Pages/Landing/
git commit -m "feat(landing): polish copy semua section - sprint 1"
```

---

## SPRINT 2 — VISUAL, RESPONSIVE & DARK MODE

> **Goal**: Layout sempurna di 4 viewport. Dark mode bersih. Tidak ada horizontal scroll.

### 2.1 Sweep Checklist Visual

Buka setiap komponen Landing, verifikasi dan perbaiki jika perlu:

```
WARNA & BRAND
─────────────
□ Primary color #24695c digunakan konsisten (bukan blue-600, indigo, purple)
□ Accent #ba895d digunakan untuk highlight, badge "Paling Populer", headline accent
□ Tidak ada gradient purple atau warna "SaaS generik" yang tidak relevan
□ Dark mode: semua teks readable (tidak ada hitam di background hitam)
□ Dark mode: chart/icon visible (bukan white-on-white)

TYPOGRAPHY
──────────
□ Heading utama (H1 hero): font-bold text-4xl md:text-5xl lg:text-6xl
□ Section heading: font-semibold text-2xl md:text-3xl
□ Body: text-base leading-relaxed
□ Tidak ada teks < 14px di body
□ Kontras WCAG AA: min 4.5:1 untuk teks normal

SPACING
───────
□ Gap antar section: py-16 atau py-20 (konsisten)
□ Container: max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 (atau sesuai existing)
□ Card padding: p-6 atau p-8 (konsisten)

KOMPONEN
────────
□ Border radius card/button: 4px (sesuai brand, bukan rounded-2xl)
□ Shadow: shadow-sm atau shadow (bukan shadow-xl berat)
□ Icon: semua dari lucide-react, ukuran konsisten (h-5 w-5 atau h-6 w-6)
□ Button primary: bg-[#24695c] text-white hover:bg-[#1b4c43]
□ Button secondary: border border-[#24695c] text-[#24695c] hover:bg-[#24695c]/10
```

### 2.2 Responsive Fix

Untuk setiap viewport, pastikan layout ini:

```tsx
// NAVBAR
// Mobile (≤768px): hamburger, semua link dalam drawer
// Desktop (>768px): link inline

// HERO
// Mobile: stack vertikal, CTA full-width, text-center atau text-left
// Desktop: split 60/40 atau centered dengan visual background

// FEATURE GRID
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

// PRICING TIERS
// Mobile: stack vertikal (satu kolom)
// Tablet: 2 kolom
// Desktop: 4 kolom (Free/Starter/Pro/Campus) — atau 2+2 kalau terlalu sempit
<div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

// FAQ
// Selalu full-width, accordion style

// FOOTER
// Mobile: 1 kolom stack
// Desktop: 4 kolom (brand + 3 kolom link)
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
```

Test horizontal scroll:
```bash
# Tambahkan sementara di CSS untuk detect overflow
# body { border: 2px solid red; } dan lihat apakah ada elemen melebihi body width
# Root cause fix — jangan pakai overflow-x: hidden sebagai plester
```

### 2.3 Touch Target

Semua tombol dan link di mobile: minimum `min-h-[44px] min-w-[44px]`.
FAQ accordion: padding vertical minimal `py-3` supaya mudah di-tap.

### 2.4 Dark Mode

Pastikan dark mode berfungsi dengan cek semua class di Landing component:
```tsx
// Pattern yang benar:
className="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
className="border border-slate-200 dark:border-slate-700"
className="text-slate-600 dark:text-slate-400"

// Yang sering salah:
// - Warna hardcoded #333 tanpa dark variant
// - bg-white tanpa dark:bg-*
// - Ikon SVG fill hardcoded hitam
```

### 2.5 Focus & Accessibility Sweep

```tsx
// Semua button/link wajib punya focus-visible ring:
className="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#24695c] focus-visible:ring-offset-2"

// Form field (newsletter jika ada) harus punya <label>
// Modal (FAQ, demo video) harus bisa ditutup dengan Escape
// Heading order: H1 di hero, H2 di section heading, H3 di card heading
// Alt text untuk semua <img>
```

### 2.6 Commit Sprint 2
```bash
./vendor/bin/pint --test
npm run build
git add resources/js/Components/Landing/
git commit -m "feat(landing): visual polish, responsive fix, dark mode - sprint 2"
```

---

## SPRINT 3 — SEO, META TAG & ANALYTICS

> **Goal**: Siap di-index Google, OG image jalan di WhatsApp/Twitter, analytics terpasang.

### 3.1 Update LandingController

```php
// app/Http/Controllers/LandingController.php
// Tambah SEO props ke setiap method

public function home(): \Inertia\Response
{
    return Inertia::render('Landing/Home', [
        'seo' => [
            'title'       => 'Prokerin · Kelola Proker, Proposal & LPJ Organisasi Kampus',
            'description' => 'Platform manajemen program kerja untuk BEM, HIMA, dan UKM kampus. Proker, task, RAB, LPJ, absensi QR, dan sertifikat — satu tempat.',
            'ogImage'     => asset('og-image.png'),
            'canonical'   => url('/'),
        ],
    ]);
}

public function features(): \Inertia\Response
{
    return Inertia::render('Landing/Features', [
        'seo' => [
            'title'       => 'Fitur Prokerin · Manajemen Proker Lengkap untuk Organisasi Kampus',
            'description' => 'Kanban task, editor proposal PDF, RAB realtime, QR absensi, sertifikat digital, notifikasi WhatsApp — semua dalam satu platform.',
            'ogImage'     => asset('og-image.png'),
            'canonical'   => url('/features'),
        ],
    ]);
}

public function pricing(): \Inertia\Response
{
    return Inertia::render('Landing/Pricing', [
        'seo' => [
            'title'       => 'Harga Prokerin · Gratis untuk Organisasi Kampus Kecil',
            'description' => 'Paket Free, Starter Rp49rb/bln, Pro Rp99rb/bln, dan Campus untuk universitas. Tidak perlu kartu kredit untuk mulai.',
            'ogImage'     => asset('og-image.png'),
            'canonical'   => url('/pricing'),
        ],
    ]);
}
```

### 3.2 Update Pages/Landing/*.tsx

Tambahkan `<Head>` di setiap page:

```tsx
// Pages/Landing/Home.tsx
import { Head } from '@inertiajs/react';

interface HomeProps {
  seo: { title: string; description: string; ogImage: string; canonical: string; };
}

export default function Home({ seo }: HomeProps) {
  return (
    <>
      <Head>
        <title>{seo.title}</title>
        <meta name="description" content={seo.description} />

        {/* Open Graph */}
        <meta property="og:type" content="website" />
        <meta property="og:title" content={seo.title} />
        <meta property="og:description" content={seo.description} />
        <meta property="og:image" content={seo.ogImage} />
        <meta property="og:url" content={seo.canonical} />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:site_name" content="Prokerin" />

        {/* Twitter Card */}
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={seo.title} />
        <meta name="twitter:description" content={seo.description} />
        <meta name="twitter:image" content={seo.ogImage} />

        {/* Canonical */}
        <link rel="canonical" href={seo.canonical} />

        {/* Structured Data */}
        <script type="application/ld+json">{JSON.stringify({
          "@context": "https://schema.org",
          "@type": "SoftwareApplication",
          "name": "Prokerin",
          "applicationCategory": "BusinessApplication",
          "description": seo.description,
          "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR",
          },
          "operatingSystem": "Web",
          "url": seo.canonical,
        })}</script>
      </Head>
      {/* ... rest of page */}
    </>
  );
}
```

Pattern yang sama untuk `Features.tsx` dan `Pricing.tsx` (tanpa structured data `SoftwareApplication` yang hanya di Home).

### 3.3 OG Image

Buat file `public/og-image.png` berukuran **1200×630px**:

```bash
# Jika ada tool imagemagick:
convert -size 1200x630 \
  -background '#24695c' \
  -fill white \
  -font DejaVu-Sans-Bold \
  -pointsize 72 \
  -gravity center \
  -annotate 0 "Prokerin\nKelola Proker Organisasi Kampus" \
  public/og-image.png

# Kalau tidak ada imagemagick, buat placeholder sederhana:
# — file PNG 1200x630 warna #24695c dengan teks "Prokerin"
# — Atau gunakan https://og-playground.vercel.app untuk generate manual
```

### 3.4 Favicon Set

Pastikan file-file ini ada di `public/`:
```bash
# Cek file favicon yang ada:
ls public/*.png public/*.ico public/*.svg 2>/dev/null

# Yang harus ada:
# favicon.ico           (32x32)
# favicon-192.png       (192x192) — untuk Android
# apple-touch-icon.png  (180x180) — untuk iOS
```

Kalau belum ada, buat dari logo Prokerin atau generate placeholder:
```bash
# Placeholder favicon (ganti dengan yang proper nanti):
convert -size 32x32 xc:'#24695c' \
  -fill white -gravity center -pointsize 20 \
  -annotate 0 "P" public/favicon.ico 2>/dev/null || \
echo "imagemagick tidak ada — buat favicon manual"
```

Tambahkan ke `resources/views/app.blade.php`:
```html
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
```

### 3.5 `public/robots.txt`

```txt
User-agent: *
Allow: /
Allow: /features
Allow: /pricing
Allow: /events/

# Block panel dan API
Disallow: /admin
Disallow: /internal-admin
Disallow: /api/

Sitemap: https://prokerin.id/sitemap.xml
```

### 3.6 `public/sitemap.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://prokerin.id/</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://prokerin.id/features</loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://prokerin.id/pricing</loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
</urlset>
```

### 3.7 Analytics Event Tracking

Buat file `resources/js/lib/analytics.ts`:

```typescript
/**
 * Analytics helper — abstraksi tipis di atas Plausible/Umami/GA4.
 * Swap provider dengan ganti implementasi di sini, tanpa ubah caller.
 */

type AnalyticsEvent =
  | 'landing_cta_primary_clicked'
  | 'landing_cta_secondary_clicked'
  | 'landing_pricing_tier_clicked'
  | 'landing_video_played'
  | 'landing_signup_completed'
  | 'landing_scroll_25'
  | 'landing_scroll_50'
  | 'landing_scroll_75'
  | 'landing_scroll_100';

interface EventProperties {
  tier?: string;
  variant?: 'a' | 'b';
  [key: string]: string | number | boolean | undefined;
}

export function trackEvent(event: AnalyticsEvent, props?: EventProperties): void {
  // Plausible
  if (typeof window !== 'undefined' && 'plausible' in window) {
    (window as any).plausible(event, { props });
    return;
  }
  // Umami
  if (typeof window !== 'undefined' && 'umami' in window) {
    (window as any).umami.track(event, props);
    return;
  }
  // GA4 gtag
  if (typeof window !== 'undefined' && 'gtag' in window) {
    (window as any).gtag('event', event, props);
    return;
  }
  // Dev fallback
  if (import.meta.env.DEV) {
    console.debug('[analytics]', event, props);
  }
}

/** Scroll depth tracker — pasang sekali di Home.tsx */
export function useScrollDepthTracking(): void {
  if (typeof window === 'undefined') return;
  const reported = new Set<number>();
  const checkpoints = [25, 50, 75, 100];

  const handler = () => {
    const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
    checkpoints.forEach((cp) => {
      if (scrolled >= cp && !reported.has(cp)) {
        reported.add(cp);
        trackEvent(`landing_scroll_${cp}` as AnalyticsEvent);
      }
    });
  };

  window.addEventListener('scroll', handler, { passive: true });
  return () => window.removeEventListener('scroll', handler);
}
```

Pasang di komponen:

```tsx
// HeroSection.tsx — primary CTA
import { trackEvent } from '@/lib/analytics';

<a
  href={primaryCtaHref}
  onClick={() => trackEvent('landing_cta_primary_clicked')}
  className="..."
>
  {primaryCtaLabel}
</a>

// PricingSection.tsx — tier CTA
<a
  href={tier.ctaHref}
  onClick={() => trackEvent('landing_pricing_tier_clicked', { tier: tier.name })}
>
  {tier.ctaLabel}
</a>

// Home.tsx — scroll depth
import { useScrollDepthTracking } from '@/lib/analytics';
import { useEffect } from 'react';

export default function Home(props: HomeProps) {
  useEffect(useScrollDepthTracking, []);
  // ...
}
```

### 3.8 `lang="id"` di app.blade.php

```bash
# Cek file blade
grep -n "html" resources/views/app.blade.php | head -5
```

Pastikan tag `<html>` punya `lang="id"`:
```html
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
```

### 3.9 Commit Sprint 3
```bash
./vendor/bin/pint --test
npm run build
git add resources/js/ public/ resources/views/app.blade.php app/Http/Controllers/LandingController.php
git commit -m "feat(landing): SEO meta, OG image, sitemap, analytics tracking - sprint 3"
```

---

## SPRINT 4 — PERFORMANCE AUDIT & OPTIMASI

> **Goal**: Lighthouse mobile score ≥ 90, LCP < 2.5s, CLS < 0.1.

### 4.1 Build Production & Audit

```bash
npm run build
php artisan serve --env=production &
# Buka http://localhost:8000 di browser
# Jalankan Lighthouse via CLI:
npx lighthouse http://localhost:8000 \
  --preset=desktop \
  --output=json \
  --output-path=qa-results/lighthouse-desktop.json

npx lighthouse http://localhost:8000 \
  --form-factor=mobile \
  --output=json \
  --output-path=qa-results/lighthouse-mobile.json

# Parse score
node -e "
const r = require('./qa-results/lighthouse-mobile.json');
const s = r.categories;
console.log('Performance:', Math.round(s.performance.score * 100));
console.log('Accessibility:', Math.round(s.accessibility.score * 100));
console.log('Best Practices:', Math.round(s['best-practices'].score * 100));
console.log('SEO:', Math.round(s.seo.score * 100));
"
# ASSERT: Performance ≥ 90, SEO ≥ 95
```

### 4.2 Image Optimization

```bash
# Cek semua image di public/
find public/ -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" | head -20

# Convert PNG ke WebP (hemat 30-70%)
for f in public/images/*.png public/*.png; do
  [ -f "$f" ] || continue
  npx sharp-cli --input "$f" --output "${f%.png}.webp" 2>/dev/null || true
done

# Atau pakai imagemagick:
for f in public/images/*.jpg; do
  [ -f "$f" ] || continue
  convert "$f" -quality 85 "${f%.jpg}.webp" 2>/dev/null || true
done
```

Update referensi di komponen React untuk pakai WebP:
```tsx
// Contoh di HeroSection atau SocialProofBar
<picture>
  <source srcSet="/images/hero-bg.webp" type="image/webp" />
  <img src="/images/hero-bg.png" alt="Prokerin dashboard" loading="lazy" />
</picture>

// Image below-fold: tambahkan loading="lazy"
<img src="..." alt="..." loading="lazy" />

// Image di hero (LCP): tambahkan fetchpriority="high" dan loading="eager"
<img src="..." alt="..." fetchPriority="high" loading="eager" />
```

### 4.3 Font Preload

Di `resources/views/app.blade.php`, tambahkan preload untuk font utama:
```html
<head>
  <!-- preload critical font -->
  <link rel="preload" href="/fonts/your-font.woff2" as="font" type="font/woff2" crossorigin>
</head>
```

Verifikasi `font-display: swap` di CSS:
```css
@font-face {
  font-family: 'YourFont';
  font-display: swap;
  ...
}
```

### 4.4 Cek CLS (Cumulative Layout Shift)

CLS paling sering disebabkan oleh:
1. Image tanpa `width` dan `height` → tambahkan dimensi eksplisit
2. Font swap → sudah dihandle `font-display: swap`
3. Banner/widget yang muncul tiba-tiba → pastikan konten critical ada di SSR

```tsx
// Image harus selalu punya width + height atau aspect-ratio
<img
  src="/og-image.png"
  alt="Prokerin"
  width={1200}
  height={630}
  className="w-full h-auto"
/>
```

### 4.5 Commit Sprint 4

```bash
./vendor/bin/pint --test
npm run build
git add public/ resources/js/ resources/views/
git commit -m "perf(landing): image webp, font preload, LCP & CLS optimizations - sprint 4"
```

---

## DEFINISI SELESAI (Definition of Done)

Jalankan checklist ini sebelum closing ticket:

### Automated Checks
```bash
# 1. Tidak ada sisa placeholder
grep -r "lorem\|placeholder\|TODO\|FIXME\|your text\|dummy\|test@test" \
  resources/js/Components/Landing/ resources/js/Pages/Landing/ \
  --include="*.tsx" -il | wc -l
# ASSERT: 0 file

# 2. Tidak ada link # atau href kosong di landing
grep -r 'href="#"' resources/js/Components/Landing/ resources/js/Pages/Landing/ \
  --include="*.tsx" | wc -l
# ASSERT: 0 (kecuali smooth-scroll anchor yang disengaja, annotate dengan comment)

# 3. Build sukses
npm run build
# ASSERT: exit 0

# 4. Lint
./vendor/bin/pint --test
npm run lint 2>/dev/null || true
# ASSERT: exit 0

# 5. Routes OK
php artisan route:list | grep landing
# ASSERT: landing.home, landing.features, landing.pricing muncul

# 6. HTTP smoke
for path in "/" "/features" "/pricing"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000${path}")
  echo "$path → $code"
done
# ASSERT: semua 200
```

### Manual Checks (tandai saat selesai)
```
□ Hero headline: jelas, max 12 kata, ada accent warna
□ Semua CTA mengarah ke URL yang valid (tidak 404)
□ Navbar: semua link aktif benar, hamburger berfungsi di 375px
□ Pricing: 4 tier tampil benar, "Paling Populer" highlighted di Pro
□ FAQ: 7 FAQ, accordion expand/collapse benar
□ Footer: copyright tahun dinamis, tidak ada link sosial dummy
□ Dark mode: semua halaman landing readable
□ Viewport 375px: tidak ada horizontal scroll
□ OG image render di WhatsApp/Twitter saat share URL (test pakai: https://www.opengraph.xyz)
□ robots.txt accessible di /robots.txt
□ sitemap.xml accessible di /sitemap.xml
□ Analytics event ter-log saat CTA diklik (cek di browser console dev mode)
□ Lighthouse mobile Performance ≥ 90
□ Commit history: satu commit per sprint, pesan commit jelas
```

---

## CATATAN PENTING

1. **Scope ketat**: Hanya ubah file landing (Components/Landing, Pages/Landing, LandingController, public assets). Jangan sentuh komponen dashboard, auth, atau Filament.

2. **Shadcn/UI tidak dimodifikasi**: Kalau perlu tombol baru, pakai `<Button>` dari `@/components/ui/button` tanpa override, atau buat komponen baru di `Components/Landing/` saja.

3. **CMS dibatalkan**: Jangan buat migration, jangan buat Filament resource, jangan buat model baru. Semua hardcoded di React — ini keputusan founder yang sudah final untuk saat ini.

4. **Iterasi sprint**: Kalau ada sprint yang blockednya (mis. OG image butuh desainer), skip dan catat di `# TODO` dengan format `// TODO(landing-polish): ...` lalu lanjut ke sprint berikutnya.

5. **Test suite**: Setelah selesai semua sprint, pastikan `php artisan test` masih hijau — jangan ada test yang regresi.