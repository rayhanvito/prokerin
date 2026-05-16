# L01 · Prokerin Landing Page — Master Build Prompt

> **Untuk agent yang mengeksekusi ini:** Baca dokumen ini secara penuh sebelum menulis satu baris kode. Dokumen ini adalah sumber kebenaran tunggal untuk semua keputusan teknis, desain, copy, dan arsitektur landing page Prokerin. Jangan berasumsi — semua sudah diputuskan di sini.

---

## Konteks Proyek

**Prokerin** adalah SaaS web app / PWA untuk organisasi mahasiswa Indonesia (BEM, HIMA, UKM) untuk mengelola program kerja (proker) — dari perencanaan, proposal, timeline, task, RAB, eksekusi, dokumentasi, hingga LPJ dan serah terima kepengurusan.

**Target user landing page:** Ketua / pengurus inti BEM dan HIMA, usia 19–23 tahun, aktif di organisasi kampus di Jawa (Surabaya, Malang, Yogyakarta, Bandung).

**Tujuan landing page:**
1. Konversi pengunjung menjadi signup (CTA utama: "Coba Gratis")
2. Komunikasikan product value dalam 5 detik pertama
3. Rangking di Google untuk keyword organisasi mahasiswa Indonesia
4. Tampil cukup premium agar pengurus BEM/HIMA percaya menaruh data organisasi di sini

---

## Keputusan Teknis (Final — Jangan Diubah Tanpa Konfirmasi)

| Aspek | Keputusan |
|-------|-----------|
| Framework | Laravel + Inertia.js + React (TypeScript) — **satu repo dengan app utama** |
| Styling | Tailwind CSS + shadcn/ui (konsisten dengan app) |
| Layout | `LandingLayout` baru — **terpisah dari `AppLayout`** (tidak ada sidebar/header app) |
| File lokasi | `resources/js/Pages/Landing/` untuk semua halaman landing |
| Komponen | `resources/js/Components/Landing/` untuk semua komponen landing |
| Routes | Public routes di `routes/web.php`, **tidak pakai auth middleware** |
| Controller | `LandingController` di `app/Http/Controllers/` — thin, hanya return Inertia views |
| Animasi | Framer Motion (sudah ada di project, jika belum: `npm install framer-motion`) |
| Font | Plus Jakarta Sans (heading) + Inter (body) — load via `@fontsource` atau Google Fonts CDN |
| CMS | **Hardcode dulu** — konten statis di komponen. CMS via Filament menyusul di fase berikutnya. |
| Images | SVG illustrations inline + WebP screenshots (placeholder dulu jika belum ada). Semua punya `alt` text. |

---

## Design System (Wajib Diikuti — Konsisten dengan Dashboard App)

### Color Tokens
```css
/* Wajib pakai CSS variables ini, konsisten dengan app */
--color-primary:       #24695c;   /* Prokerin green — primary brand */
--color-primary-dark:  #1b4c43;   /* Hover states, darker green */
--color-primary-light: #e8f5f2;   /* Light green backgrounds, highlights */
--color-secondary:     #ba895d;   /* Warm brown — accent, warmth */
--color-secondary-light: #f5ede4; /* Light brown background */
--color-bg:            #f5f7fb;   /* Page background */
--color-surface:       #ffffff;   /* Cards, panels */
--color-text:          #242934;   /* Body text */
--color-text-muted:    #59667a;   /* Secondary text, captions */
--color-border:        #e6edef;   /* Dividers, card borders */
--color-success:       #22c55e;
--color-warning:       #f59e0b;
--color-danger:        #ef4444;
```

### Typography
```
Heading font : Plus Jakarta Sans — weight 600, 700, 800
Body font    : Inter — weight 400, 500
```

Scale (px): 12 / 14 / 16 / 18 / 20 / 24 / 30 / 36 / 48 / 60 / 72

### Spacing & Border Radius
- Ikuti Tailwind default scale
- Card border radius: `rounded-2xl` (16px)
- Button border radius: `rounded-xl` (12px)
- Konsisten dengan komponen shadcn/ui yang ada di app

### Tone Visual
- **Clean, modern, trustworthy** — bukan playful/cartoon, bukan korporat kaku
- **Friendly di hero** (warna hangat, ilustrasi, copy santai)
- **Profesional di fitur & pricing** (tabel, icon, copy to-the-point)
- Banyak white space — tidak sesak
- Green sebagai warna dominan, brown sebagai aksen kehangatan

---

## Arsitektur File

```
prokerin/
├── app/
│   └── Http/
│       └── Controllers/
│           └── LandingController.php          ← Buat baru
├── resources/
│   └── js/
│       ├── Layouts/
│       │   └── LandingLayout.tsx              ← Buat baru
│       ├── Pages/
│       │   └── Landing/
│       │       ├── Home.tsx                   ← Halaman utama (/)
│       │       ├── Features.tsx               ← Halaman /features
│       │       └── Pricing.tsx                ← Halaman /pricing
│       └── Components/
│           └── Landing/
│               ├── Navbar.tsx
│               ├── HeroSection.tsx
│               ├── SocialProofBar.tsx
│               ├── ProblemSection.tsx
│               ├── FeatureShowcase.tsx
│               ├── HowItWorksSection.tsx
│               ├── TestimonialsSection.tsx
│               ├── PricingSection.tsx
│               ├── FaqSection.tsx
│               ├── CtaBanner.tsx
│               ├── Footer.tsx
│               ├── DemoVideoModal.tsx
│               └── MobileMenu.tsx
└── routes/
    └── web.php                                ← Tambahkan public routes di sini
```

---

## Routes (tambahkan ke `routes/web.php`)

```php
// Landing Page — Public Routes (no auth middleware)
Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('/features', [LandingController::class, 'features'])->name('landing.features');
Route::get('/pricing', [LandingController::class, 'pricing'])->name('landing.pricing');
```

**Penting:** Route `/` ini akan menggantikan default route Laravel. Pastikan route auth tetap berjalan (login, register, dll.) dan route `/dashboard` tetap terlindungi middleware `auth`.

---

## LandingController.php

```php
<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Landing/Home');
    }

    public function features(): Response
    {
        return Inertia::render('Landing/Features');
    }

    public function pricing(): Response
    {
        return Inertia::render('Landing/Pricing');
    }
}
```

---

## LandingLayout.tsx

Layout ini tidak memiliki sidebar, header app, atau elemen app shell. Murni untuk marketing pages.

```tsx
// resources/js/Layouts/LandingLayout.tsx
import { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import Navbar from '@/Components/Landing/Navbar';
import Footer from '@/Components/Landing/Footer';

interface Props {
    children: ReactNode;
    title?: string;
    description?: string;
    ogImage?: string;
}

export default function LandingLayout({
    children,
    title = 'Prokerin — Kelola Proker Organisasi Tanpa Chaos',
    description = 'Platform manajemen program kerja untuk BEM, HIMA, dan UKM Indonesia. Dari perencanaan, proposal, RAB, sampai LPJ — semua dalam satu tempat.',
    ogImage = '/images/og-prokerin.png',
}: Props) {
    return (
        <>
            <Head>
                <title>{title}</title>
                <meta name="description" content={description} />
                {/* Open Graph */}
                <meta property="og:title" content={title} />
                <meta property="og:description" content={description} />
                <meta property="og:image" content={ogImage} />
                <meta property="og:type" content="website" />
                <meta property="og:locale" content="id_ID" />
                {/* Twitter Card */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={description} />
                <meta name="twitter:image" content={ogImage} />
                {/* Canonical */}
                <link rel="canonical" href="https://prokerin.id" />
                {/* Fonts */}
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-white font-sans">
                <Navbar />
                <main>{children}</main>
                <Footer />
            </div>
        </>
    );
}
```

---

## Copy & Konten (Semua Hardcode — Per Komponen)

### Tone Guidelines
- **Hero & Problem section:** Friendly, relatable, sedikit dramatik — kayak ngobrol sama senior organisasi yang paham pain-nya
- **Feature showcase:** Jelas, manfaat-first, tidak jargon teknis
- **Pricing & FAQ:** Profesional, transparan, tidak ada fine print tersembunyi
- **CTA:** Urgency ringan, tanpa pressure — "Gratis, tidak perlu kartu kredit" selalu disertakan

### Bahasa
- Bahasa Indonesia natural — bukan terjemahan kaku dari Inggris
- Boleh pakai kata campuran yang lazim di kalangan mahasiswa: "proker", "LPJ", "RAB", "ormawa", "rapat"
- Hindari: kata-kata terlalu formal/birokratis, kata Inggris yang ada padanannya

---

## Komponen — Spesifikasi Detail

---

### 1. `Navbar.tsx`

**Behavior:**
- Sticky — `position: fixed`, `top: 0`, `z-50`, full width
- Saat load: background transparan, teks putih (untuk hero yang memiliki background gelap)
- Saat scroll > 50px: background putih dengan shadow (`shadow-sm`), teks berubah gelap
- Transisi smooth: `transition-all duration-300`
- Mobile (< `md`): sembunyikan menu items, tampilkan hamburger button → buka `MobileMenu`

**Items:**
```
Logo Prokerin | Fitur | Harga | Blog | [Masuk] [Coba Gratis →]
```

**Logo:** Text "Prokerin" dengan dot hijau atau icon sederhana. Jika sudah ada SVG logo di project, pakai. Jika belum, buat text-based logo:
```tsx
<span className="font-bold text-xl text-primary">Proker<span className="text-secondary">in</span></span>
```

**CTA Navbar:**
- "Masuk" → `route('login')` — ghost/outline style
- "Coba Gratis →" → `route('register')` — solid primary green, `rounded-xl`

**Link navigasi:**
- "Fitur" → `route('landing.features')`
- "Harga" → `route('landing.pricing')`
- "Blog" → `/blog` (hardcode, belum ada, bisa disabled dulu)

---

### 2. `HeroSection.tsx`

**Layout:** Full viewport height (`min-h-screen`), centered content, dark green gradient background

**Background:**
```css
background: linear-gradient(135deg, #1b4c43 0%, #24695c 50%, #2d7a6a 100%);
```
Tambahkan subtle pattern overlay (dots atau grid dengan opacity 5%) untuk texture.

**Struktur:**
```
[Navbar overlay]

[Badge: "✨ Baru: Absensi QR & Sertifikat Digital"]

[H1: Headline utama]
[Subheadline]
[CTA Buttons]
[Social proof mini]

[Dashboard mockup visual]
```

**Copy:**

**Badge (animated, fade in delay 0ms):**
```
✨ Platform proker terlengkap untuk ormawa Indonesia
```
Style: pill badge, warna `bg-white/10 text-white border border-white/20`, rounded-full

**H1 (animated, fade in delay 100ms):**
```
Kelola Proker Organisasi
Tanpa Chaos, Tanpa Drama
```
Size: `text-5xl md:text-6xl lg:text-7xl`, font-weight: 800, color: white
"Tanpa Chaos, Tanpa Drama" — bisa diberi warna aksen `text-secondary` (#ba895d) untuk emphasis

**Subheadline (animated, fade in delay 200ms):**
```
Dari perencanaan proker, proposal, RAB, absensi rapat, 
sampai LPJ — semua terintegrasi. Dirancang khusus untuk 
BEM, HIMA, dan UKM di Indonesia.
```
Size: `text-lg md:text-xl`, color: `text-white/80`, max-width: 560px

**CTA Buttons (animated, fade in delay 300ms):**
- Primary: `"Mulai Gratis Sekarang →"` → `route('register')`
  - Style: `bg-white text-primary font-semibold px-8 py-4 rounded-xl hover:bg-gray-50 transition shadow-lg`
- Secondary: `"Lihat Demo"` → opens `DemoVideoModal`
  - Style: `border border-white/30 text-white px-8 py-4 rounded-xl hover:bg-white/10 transition`
  - Prefix dengan icon ▶ play button

**Social proof mini (animated, fade in delay 400ms):**
```
🔒 Gratis untuk 1 organisasi · Tidak perlu kartu kredit · Setup dalam 5 menit
```
Size: `text-sm`, color: `text-white/60`

**Dashboard Mockup Visual:**
- Buat SVG ilustrasi dashboard Prokerin yang stylish — bukan screenshot nyata karena belum ada
- Tampilkan card-card yang merepresentasikan: project status, task board, finance summary
- Warna: gunakan warna brand Prokerin dalam mockup
- Animasi: floating animation subtle (CSS keyframe `translateY(-8px)` loop 3s ease-in-out)
- Posisi: di bawah CTA pada mobile, di kanan pada desktop (split layout `lg:grid-cols-2`)
- Drop shadow untuk depth: `shadow-2xl`

**Contoh struktur mockup SVG (simplified — agent wajib buat yang lebih detail dan realistis):**
```tsx
// DashboardMockup.tsx — inline SVG yang menampilkan:
// - Header bar dengan avatar dan notif
// - Sidebar mini dengan menu items
// - 3 stat cards (Proker Aktif, Task Selesai, Anggaran Terpakai)
// - 1 progress bar proker
// - Mini task list
// Semua dengan warna brand Prokerin
```

---

### 3. `SocialProofBar.tsx`

**Posisi:** Tepat di bawah HeroSection, background putih atau `bg-gray-50`

**Copy:**
```
Dipercaya oleh 500+ organisasi mahasiswa di Indonesia
```

**Visual:** Row of university/org name chips atau logo placeholders:
```
BEM Universitas Airlangga  ·  HIMA Teknik ITS  ·  BEM UNESA  ·  BEM Brawijaya  ·  UKM Robotika ITS
```
(Ini placeholder — ganti dengan real logo/nama saat beta launch. Tammpilkan sebagai badge chips atau grayscale logo)

**Style:** Divider section tipis, text-center, `text-sm text-gray-500`, nama org dalam `font-medium text-gray-700`

---

### 4. `ProblemSection.tsx`

**Headline:**
```
7 Masalah yang Bikin Pengurus Ormawa Pusing
```
Size: `text-3xl md:text-4xl`, font-weight: 700, text-center

**Subheadline:**
```
Kamu pasti pernah ngalamin salah satunya. Atau semuanya.
```
Tone: relatable, sedikit humor — ini yang bikin hook kuat

**7 Problem Cards** (grid `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4` — card ke-7 centered atau spanning):

| # | Emoji | Headline | Deskripsi singkat |
|---|-------|----------|-------------------|
| 1 | 📅 | Proker Molor Melulu | Deadline lewat, progress tidak jelas, tidak ada yang bisa ditagih |
| 2 | 📄 | Proposal Bolak-Balik | Revisi ke-5 masih ada yang kurang, format tidak pernah seragam |
| 3 | ✅ | Task Jatuh ke Mana? | Sudah didelegasikan tapi tidak ada yang follow up — hilang begitu saja |
| 4 | 💰 | RAB vs Realisasi Beda Jauh | Laporan keuangan dikerjakan dadakan, angka tidak sinkron |
| 5 | 📁 | Dokumen Nyebar di Mana-Mana | Ada di Drive A, Drive B, WhatsApp, dan laptop yang sudah ganti pemilik |
| 6 | 📋 | LPJ Dikerjain H-1 | Karena tidak ada catatan selama proker berjalan, semua ditulis ulang dari ingatan |
| 7 | 🔄 | Pergantian Pengurus = Reset Total | Ilmu, dokumen, dan konteks hilang saat pengurus lama selesai menjabat |

**Transisi setelah cards:**
```
Prokerin dirancang khusus untuk menyelesaikan semua ini — bukan workaround, tapi solusi permanen.
```
Style: full-width text dengan garis bawah atau divider, warna primary, font-medium

**Card style:**
- Background: `bg-white`, border: `border border-gray-100`, rounded-2xl
- Hover: `hover:border-primary/30 hover:shadow-md transition-all`
- Emoji large (`text-3xl`), headline `font-semibold text-gray-800`, desc `text-sm text-gray-500`
- Padding: `p-6`

---

### 5. `FeatureShowcase.tsx`

**Headline:**
```
Semua yang Kamu Butuhkan, dalam Satu Platform
```

**Layout:** Alternating — Feature 1: text kiri, visual kanan. Feature 2: visual kiri, text kanan. dst.

**6 Features:**

**Feature 1 — Manajemen Proker**
- Icon/color: hijau primary
- Headline: `Buat dan Pantau Proker dengan Mudah`
- Body: `Mulai proker dari template yang sudah tersedia — ada task, timeline, dan draft RAB otomatis. Tidak perlu mulai dari nol setiap semester. Pantau progress semua proker dari satu dashboard.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup card proker dengan status badge, progress bar, nama PIC

**Feature 2 — Proposal Generator**
- Icon/color: biru teal
- Headline: `Proposal Terisi Otomatis dari Data Proker`
- Body: `Isi data proker sekali, Prokerin generate draft proposal secara otomatis. Template sesuai format kampus. Export ke PDF atau Word dalam hitungan detik — tidak ada lagi copy-paste malam-malam.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup dokumen proposal dengan sections terisi

**Feature 3 — RAB & Keuangan**
- Icon/color: kuning/amber
- Headline: `Keuangan Proker Transparan dan Terkontrol`
- Body: `Planning budget, pengajuan, approval, sampai realisasi — semua dalam satu alur. Perbandingan RAB vs realisasi real-time. Tidak ada lagi spreadsheet yang salah versi atau angka yang tidak sinkron.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup tabel RAB dengan kolom budget vs realisasi

**Feature 4 — Absensi QR**
- Icon/color: ungu
- Headline: `Absensi Rapat Semudah Scan QR`
- Body: `Buka kamera, scan QR yang ditampilkan panitia, hadir tercatat. Laporan kehadiran otomatis per rapat dan per proker. Tidak ada lagi titip absen atau rekap manual.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup layar QR code + list kehadiran

**Feature 5 — LPJ Otomatis**
- Icon/color: hijau muda
- Headline: `LPJ Terbentuk dari Data Nyata, Bukan Ingatan`
- Body: `Prokerin mengumpulkan data selama proker berjalan — task selesai, pengeluaran, kehadiran, dokumen. LPJ tinggal generate, bukan nulis ulang dari nol H-1 deadline.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup halaman LPJ dengan sections terisi otomatis

**Feature 6 — Sertifikat Digital**
- Icon/color: oranye/gold
- Headline: `Sertifikat Digital yang Bisa Diverifikasi Online`
- Body: `Terbitkan sertifikat untuk anggota dan peserta event dengan satu klik. Setiap sertifikat punya QR verifikasi unik. Anggota bisa langsung share ke LinkedIn — exposure gratis untuk organisasimu.`
- Link: `Lihat fitur lengkap →`
- Visual: Mockup sertifikat dengan QR code dan nama penerima

**Visual style untuk semua feature:**
- Buat sebagai SVG illustrations inline — bukan screenshot (screenshot belum ada)
- Gunakan warna brand Prokerin dalam ilustrasi
- Ukuran: sekitar 480×320px, rounded-2xl, shadow-lg
- Pada mobile: visual di atas, text di bawah (stacked)

---

### 6. `HowItWorksSection.tsx`

**Background:** `bg-primary` (hijau gelap) — section gelap untuk variasi visual

**Headline (putih):**
```
Mulai dalam 5 Menit
```

**Subheadline (putih/80):**
```
Tidak ada training, tidak ada setup rumit. Langsung produktif.
```

**3 Steps** (horizontal pada desktop, vertical pada mobile):

**Step 1 — Daftarkan Organisasi**
- Number: besar, stylish (outlined atau gradient)
- Icon: 🏛️ atau building icon
- Headline: `Daftarkan Organisasi`
- Body: `Buat akun gratis, isi profil organisasi, setup periode kepengurusan, dan undang anggota inti. Selesai dalam hitungan menit.`

**Step 2 — Buat Proker dari Template**
- Icon: ⚡ atau zap icon
- Headline: `Buat Proker dari Template`
- Body: `Pilih template yang sesuai — sudah ada susunan task, RAB, dan draft proposal. Sesuaikan, lalu launch. Tidak perlu mulai dari nol.`

**Step 3 — Kelola Sampai LPJ**
- Icon: 🎯 atau target icon
- Headline: `Kelola sampai LPJ`
- Body: `Pantau semua progress dari dashboard, kelola keuangan, absensi rapat, dan generate laporan — semua dari satu tempat.`

**CTA setelah steps:**
```
[Mulai Gratis Sekarang →]
```
Button: `bg-white text-primary` di atas background hijau

**Connector visual antar step:** garis putus-putus horizontal (desktop) atau vertikal (mobile) dengan warna `white/20`

---

### 7. `TestimonialsSection.tsx`

**Headline:**
```
Apa Kata Pengurus Ormawa
```

**Subheadline:**
```
Dari yang dulunya chaos, sekarang semua terkendali.
```

**3 Testimonial Cards** (placeholder — isi dengan real testimonial saat beta launch):

**Card 1:**
```
"Proposal yang biasanya makan 3 hari sekarang beres 2 jam. 
LPJ tidak perlu lembur lagi — data sudah terkumpul otomatis 
selama proker berjalan."

— Raihan Fauzi
  Ketua BEM, Universitas Airlangga Surabaya
  ⭐⭐⭐⭐⭐
```

**Card 2:**
```
"Anggaran kami akhirnya transparan. Semua bendahara bisa 
lihat real-time, approval lebih cepat, tidak ada lagi 
debat soal versi spreadsheet yang mana yang benar."

— Aulia Putri Maharani
  Bendahara Umum HIMA Teknik Informatika ITS
  ⭐⭐⭐⭐⭐
```

**Card 3:**
```
"Pergantian pengurus semester ini lancar banget. Semua 
dokumen, proker, dan konteks tersimpan rapi di Prokerin — 
pengurus baru langsung bisa kerja dari hari pertama."

— Dimas Prasetyo
  Sekretaris Jenderal UKM Robotika, Universitas Brawijaya
  ⭐⭐⭐⭐⭐
```

**Catatan implementasi:** Tambahkan `data-placeholder="true"` attribute dan comment di code bahwa ini placeholder dan akan diganti dengan real testimonial sebelum go-live.

**Card style:**
- Background: `bg-white`, border: `border border-gray-100`, rounded-2xl
- Quote marks decoratif besar di kiri atas (`"` dengan warna primary/20)
- Photo placeholder: avatar circle dengan inisial nama
- Nama: `font-semibold`, Jabatan: `text-sm text-gray-500`

---

### 8. `PricingSection.tsx`

**Headline:**
```
Harga Transparan, Tidak Ada Biaya Tersembunyi
```

**Subheadline:**
```
Mulai gratis, upgrade kapan saja. Tidak perlu kartu kredit untuk plan Free.
```

**Toggle:** Bulanan / Tahunan (hemat 20%) — state dengan useState

**4 Pricing Tiers:**

| | Free | Starter | Pro | Campus |
|--|------|---------|-----|--------|
| **Harga** | Rp 0 | Rp 99.000/bln | Rp 299.000/bln | Hubungi kami |
| **Harga tahunan** | - | Rp 79.000/bln | Rp 239.000/bln | Custom |
| **Highlighted** | Tidak | Tidak | **YA** (most popular badge) | Tidak |
| **Organisasi** | 1 | 1 | 1 | Unlimited |
| **Anggota** | 20 | 50 | Unlimited | Unlimited |
| **Proker Aktif** | 3 | 10 | Unlimited | Unlimited |
| **Penyimpanan** | 500 MB | 5 GB | 20 GB | Custom |
| **MVP M01–M13** | ✅ | ✅ | ✅ | ✅ |
| **Rapat (M14)** | ❌ | ✅ | ✅ | ✅ |
| **Absensi QR (M15)** | ❌ | ✅ | ✅ | ✅ |
| **Sertifikat (M16)** | ❌ | ❌ | ✅ | ✅ |
| **WhatsApp (M17)** | ❌ | ❌ | ✅ | ✅ |
| **AI Assistant (M23)** | ❌ | ❌ | ✅ | ✅ |
| **Campus Dashboard** | ❌ | ❌ | ❌ | ✅ |
| **Support** | Komunitas | Email | Email + Chat | Dedicated |
| **CTA** | Mulai Gratis | Coba 14 Hari | Coba 14 Hari | Hubungi Kami |

**"Pro" card styling:**
- Border: `border-2 border-primary`
- Badge: `"Paling Populer"` — absolute positioned pill di atas card, background primary
- Background: `bg-primary/5`
- CTA button: filled primary

**Lainnya:**
- CTA "Mulai Gratis" → `route('register')`
- CTA "Coba 14 Hari" → `route('register')` (dengan param plan)
- CTA "Hubungi Kami" → `mailto:halo@prokerin.id`

---

### 9. `FaqSection.tsx`

**Headline:**
```
Pertanyaan yang Sering Ditanyakan
```

**Implementasi:** Accordion komponen (gunakan shadcn/ui `Accordion` jika sudah ada, atau buat dari scratch dengan useState)

**10 FAQ Items:**

**Q1: Apakah Prokerin benar-benar gratis?**
A: Ya, plan Free kami benar-benar gratis tanpa batas waktu — untuk 1 organisasi dengan hingga 20 anggota dan 3 proker aktif. Tidak perlu kartu kredit untuk mulai. Upgrade ke Starter atau Pro hanya jika kamu butuh lebih banyak kapasitas atau fitur tambahan.

**Q2: Apakah data organisasi kami aman?**
A: Sangat aman. Data setiap organisasi sepenuhnya terisolasi — tidak ada organisasi lain yang bisa melihat data kamu. Semua file disimpan di cloud storage terenkripsi. Kami tidak menjual atau membagikan data ke pihak manapun.

**Q3: Bisa diakses dari HP?**
A: Bisa. Prokerin adalah Progressive Web App (PWA) — bisa diakses dari browser HP langsung tanpa perlu install dari App Store atau Play Store. Cukup buka prokerin.id dari Chrome atau Safari, lalu "Add to Home Screen."

**Q4: Apakah ada masa percobaan untuk Starter dan Pro?**
A: Ya, semua plan berbayar memiliki masa percobaan 14 hari gratis. Kamu bisa eksplorasi semua fitur tanpa ditagih. Batalkan kapan saja sebelum 14 hari jika tidak cocok.

**Q5: Bagaimana dengan organisasi yang sudah punya Google Drive dan Notion?**
A: Prokerin tidak menggantikan Google Drive untuk storage umum — kamu bisa tetap pakai Drive untuk file-file lain. Yang Prokerin gantikan adalah: spreadsheet keuangan, template proposal, grup WhatsApp tracking tugas, dan dokumen LPJ yang dibuat dari nol. Semuanya terpusat dan terhubung satu sama lain.

**Q6: Apakah bisa digunakan lebih dari satu organisasi?**
A: Saat ini satu akun bisa join di banyak organisasi, tapi setiap organisasi memiliki subscription sendiri. Jika kamu pengurus di dua organisasi berbeda, masing-masing memiliki workspace terpisah.

**Q7: Bagaimana proses serah terima ke pengurus baru?**
A: Sangat mudah. Owner lama cukup mengubah role anggota — semua data, proker, dokumen, dan history tetap tersimpan di Prokerin. Pengurus baru langsung bisa akses tanpa proses migrasi manual.

**Q8: Apakah ada versi desktop / native app?**
A: Prokerin berjalan sepenuhnya di browser — tidak ada native app terpisah. Ini disengaja agar tidak perlu update manual dan semua platform (Windows, Mac, Android, iOS) bisa akses dengan pengalaman yang sama.

**Q9: Bagaimana cara mengajukan demo untuk BEM kampus kami?**
A: Kirim email ke halo@prokerin.id atau klik tombol "Lihat Demo" di halaman ini. Kami bisa mengatur sesi demo online 30 menit khusus untuk pengurus inti organisasimu.

**Q10: Apakah ada diskon untuk kampus atau banyak organisasi?**
A: Ya. Untuk universitas yang ingin mengelola banyak BEM/HIMA/UKM sekaligus, kami memiliki plan Campus dengan harga custom. Hubungi kami di halo@prokerin.id untuk mendiskusikan kebutuhanmu.

---

### 10. `CtaBanner.tsx`

**Background:** `bg-primary` gradient (`from-primary to-primary-dark`)
**Layout:** Full-width, centered content, padding besar atas bawah

**Copy:**
```
Headline: "Siap Kelola Organisasi Tanpa Chaos?"
Sub: "Gratis untuk 1 organisasi. Tidak perlu kartu kredit. Setup dalam 5 menit."
```

**CTA Buttons:**
- Primary: `"Daftar Sekarang — Gratis →"` → `route('register')`
  - Style: `bg-white text-primary font-semibold px-8 py-4 rounded-xl`
- Secondary: `"Lihat Demo"` → DemoVideoModal
  - Style: `border border-white/30 text-white px-8 py-4 rounded-xl`

**Tambahan:**
```
✓ Gratis selamanya untuk 1 organisasi
✓ Tidak ada kartu kredit
✓ Cancel kapan saja
```
Style: row of 3 checkmarks, `text-white/70 text-sm`

---

### 11. `Footer.tsx`

**Layout:** 4 kolom pada desktop, stacked pada mobile
**Background:** `bg-gray-900`, text: `text-gray-400`

**Kolom 1 — Brand:**
```
[Logo Prokerin]
Platform manajemen proker untuk BEM, HIMA, dan UKM Indonesia.

[Instagram] [LinkedIn] [Twitter/X]
```

**Kolom 2 — Produk:**
```
Fitur
Harga
Marketplace (coming soon)
Changelog
```

**Kolom 3 — Resources:**
```
Blog
Prokerin Academy (coming soon)
Dokumentasi API
Status Layanan
```

**Kolom 4 — Perusahaan:**
```
Tentang Kami
Kontak
Kebijakan Privasi
Syarat & Ketentuan
```

**Bottom bar:**
```
© 2026 Prokerin. All rights reserved.  |  Made with ❤️ for ormawa Indonesia
```

---

### 12. `DemoVideoModal.tsx`

**Trigger:** Button "Lihat Demo" di Hero dan CtaBanner

**Implementasi:**
- Modal overlay dengan backdrop blur (`backdrop-blur-sm bg-black/60`)
- Close on backdrop click, close on Escape key
- Konten: YouTube embed iframe (URL placeholder: `https://youtube.com/embed/DEMO_VIDEO_ID`)
- Ukuran: 16:9 aspect ratio, max-width 800px
- Animasi: scale dari 0.9 → 1 saat buka (Framer Motion atau CSS transition)

**Catatan:** Jika video belum ada, tampilkan placeholder dengan text:
```
Demo video akan segera tersedia.
Ingin melihat demo langsung? Hubungi kami.
[Hubungi Kami →]
```

---

### 13. `MobileMenu.tsx`

**Trigger:** Hamburger button di Navbar pada mobile

**Implementasi:**
- Full-screen overlay, slide in dari kanan atau fade in
- Background: putih
- Items sama dengan Navbar desktop
- Close button (X) di pojok kanan atas
- Animasi: Framer Motion `AnimatePresence` + `motion.div`

---

## Halaman — Spesifikasi

---

### `Home.tsx` (`/`)

```tsx
import LandingLayout from '@/Layouts/LandingLayout';
import HeroSection from '@/Components/Landing/HeroSection';
import SocialProofBar from '@/Components/Landing/SocialProofBar';
import ProblemSection from '@/Components/Landing/ProblemSection';
import FeatureShowcase from '@/Components/Landing/FeatureShowcase';
import HowItWorksSection from '@/Components/Landing/HowItWorksSection';
import TestimonialsSection from '@/Components/Landing/TestimonialsSection';
import PricingSection from '@/Components/Landing/PricingSection';
import FaqSection from '@/Components/Landing/FaqSection';
import CtaBanner from '@/Components/Landing/CtaBanner';

export default function Home() {
    return (
        <LandingLayout
            title="Prokerin — Kelola Proker Organisasi Tanpa Chaos | Platform Ormawa Indonesia"
            description="Platform manajemen program kerja untuk BEM, HIMA, dan UKM Indonesia. Proposal otomatis, RAB terintegrasi, absensi QR, LPJ dari data nyata. Coba gratis."
        >
            <HeroSection />
            <SocialProofBar />
            <ProblemSection />
            <FeatureShowcase />
            <HowItWorksSection />
            <TestimonialsSection />
            <PricingSection />
            <FaqSection />
            <CtaBanner />
        </LandingLayout>
    );
}
```

**SEO meta tambahan untuk Home:**
```html
<!-- Schema.org structured data — tambahkan di Head -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "Prokerin",
  "description": "Platform manajemen program kerja untuk organisasi mahasiswa Indonesia",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  }
}
</script>
```

---

### `Features.tsx` (`/features`)

**Title:** `"Semua Fitur Prokerin — Platform Proker Lengkap untuk Ormawa | Prokerin"`

**Description:** `"Lihat semua fitur Prokerin: manajemen proker, proposal otomatis, RAB & keuangan, absensi QR, LPJ otomatis, sertifikat digital. Dirancang untuk BEM, HIMA, dan UKM Indonesia."`

**Struktur halaman:**

1. **Page Hero** (lebih kecil dari homepage hero):
   ```
   Background: light green gradient (bg-primary/5)
   H1: "Semua yang Kamu Butuhkan untuk Kelola Organisasi"
   Sub: "Fitur-fitur Prokerin dirancang berdasarkan alur kerja nyata BEM dan HIMA Indonesia — bukan adaptasi dari tools Barat."
   ```

2. **Feature Categories** (dengan section anchor `#perencanaan`, `#keuangan`, dll.):
   - **Perencanaan & Eksekusi:** M04 Proker, M05 Template, M06 Timeline & Task
   - **Dokumen & Proposal:** M08 Proposal, M09 Dokumen, M10 LPJ
   - **Keuangan:** M07 RAB & Finance
   - **Operasional:** M14 Rapat, M15 Absensi QR
   - **Pasca Proker:** M16 Sertifikat, M11 Dashboard

3. **Per Feature** — layout card besar dengan:
   - Icon + badge kategori
   - H2: nama fitur
   - Deskripsi 3–4 kalimat
   - Bullet list manfaat (3–4 poin)
   - Visual mockup (SVG placeholder)
   - Link "Pelajari lebih lanjut →" (bisa ke section anchors di halaman ini)

4. **Coming Soon Section:**
   ```
   Fitur dalam pengembangan: WhatsApp Reminder, AI Assistant, Campus Dashboard
   ```

5. **CTA Bottom:** sama dengan `CtaBanner`

---

### `Pricing.tsx` (`/pricing`)

**Title:** `"Harga Prokerin — Mulai Gratis, Upgrade Kapan Saja | Prokerin"`

**Description:** `"Lihat harga Prokerin: Free Rp0, Starter Rp99.000/bulan, Pro Rp299.000/bulan, Campus custom. Coba 14 hari gratis untuk semua plan berbayar."`

**Struktur halaman:**

1. **Page Hero:**
   ```
   H1: "Harga yang Adil untuk Semua Skala Organisasi"
   Sub: "Mulai gratis selamanya. Upgrade hanya jika kamu butuh lebih."
   Toggle: Bulanan / Tahunan
   ```

2. **Pricing Cards** — sama seperti `PricingSection` di homepage tapi lebih detail (tampilkan full feature list per tier)

3. **Feature Comparison Table** — tabel lengkap semua fitur vs semua tier (scroll horizontal pada mobile)

4. **FAQ Pricing** — 5 pertanyaan khusus seputar harga:
   - "Kapan saya ditagih?"
   - "Bagaimana cara upgrade/downgrade?"
   - "Apakah ada refund?"
   - "Bisa bayar tahunan?"
   - "Ada diskon untuk kampus?"

5. **CTA Bottom:** `CtaBanner`

---

## Animasi & Interaksi

### Prinsip Animasi
- Subtle dan purposeful — tidak berlebihan
- Gunakan Framer Motion untuk entrance animations
- `initial={{ opacity: 0, y: 20 }}` → `animate={{ opacity: 1, y: 0 }}` adalah pola standar
- `viewport={{ once: true }}` untuk scroll-triggered animations — hanya play sekali
- Duration: 0.4–0.6s, ease: `easeOut`
- Stagger antar cards: 0.1s delay per item

### Elemen yang harus dianimasi:
- Hero: badge, H1, sub, CTA — staggered fade in dari bawah
- Dashboard mockup: floating animation (CSS keyframe)
- Problem cards: fade in saat masuk viewport, staggered
- Feature sections: slide in dari kiri/kanan bergantian
- How it works steps: sequential reveal
- Pricing cards: scale up saat hover
- FAQ accordion: smooth expand/collapse

### Yang TIDAK perlu dianimasi:
- Navbar (selalu visible)
- Footer
- Teks paragraf biasa

---

## SEO — Implementasi

### Per Halaman

**Home (`/`):**
```html
<title>Prokerin — Kelola Proker Organisasi Tanpa Chaos | Platform Ormawa Indonesia</title>
<meta name="description" content="Platform manajemen program kerja untuk BEM, HIMA, dan UKM Indonesia. Proposal otomatis, RAB terintegrasi, absensi QR, LPJ dari data nyata. Coba gratis.">
<meta name="keywords" content="aplikasi manajemen organisasi mahasiswa, software BEM, aplikasi BEM, manajemen proker mahasiswa, aplikasi absensi organisasi, template proposal mahasiswa">
```

**Features (`/features`):**
```html
<title>Semua Fitur Prokerin — Platform Proker Lengkap untuk Ormawa | Prokerin</title>
<meta name="description" content="Lihat semua fitur Prokerin: manajemen proker, proposal otomatis, RAB & keuangan, absensi QR, LPJ otomatis, sertifikat digital. Dirancang untuk BEM, HIMA, dan UKM Indonesia.">
```

**Pricing (`/pricing`):**
```html
<title>Harga Prokerin — Mulai Gratis, Upgrade Kapan Saja | Prokerin</title>
<meta name="description" content="Lihat harga Prokerin: Free Rp0, Starter Rp99.000/bulan, Pro Rp299.000/bulan, Campus custom. Coba 14 hari gratis untuk semua plan berbayar.">
```

### Heading Hierarchy (wajib diikuti untuk SEO)
- `<h1>`: hanya satu per halaman — headline utama
- `<h2>`: nama section atau kategori fitur
- `<h3>`: nama sub-fitur atau pertanyaan FAQ
- Jangan skip level (h1 → h3 langsung)

### Image Alt Text
- Setiap `<img>` atau `<Image>` WAJIB ada `alt` attribute yang deskriptif
- Format: `alt="Dashboard Prokerin menampilkan manajemen program kerja BEM"`
- Bukan: `alt="screenshot"` atau `alt=""`

### Internal Linking
- Navbar dan footer menghubungkan semua halaman publik
- Hero section link ke `/features` dan `/pricing`
- Feature showcase items link ke `/features#[section]`

---

## Aksesibilitas (a11y)

- Semua button dan link punya `aria-label` yang deskriptif jika teks tidak cukup jelas
- Modal (`DemoVideoModal`, `MobileMenu`) harus trap focus saat terbuka
- FAQ accordion gunakan ARIA `role="button"`, `aria-expanded`, `aria-controls`
- Color contrast minimum 4.5:1 untuk teks normal, 3:1 untuk teks besar
- Skip-to-content link di awal halaman: `<a href="#main-content" className="sr-only focus:not-sr-only">Skip to content</a>`
- Semua interaksi accessible via keyboard

---

## Performance

- Lazy load semua gambar/visual di bawah fold: `loading="lazy"`
- Framer Motion: gunakan `LazyMotion` dengan `domAnimation` features untuk code splitting
- Tidak ada blocking scripts — semua JS di-load async
- Font: sudah pakai `display=swap` dari Google Fonts — tidak blocking render
- SVG illustrations: inline di JSX (tidak perlu network request)

---

## Checklist Sebelum Commit

```
[x] LandingLayout.tsx dibuat dan tidak mengimpor AppLayout atau elemen sidebar
[x] LandingController.php dibuat dengan 3 method (home, features, pricing)
[x] Routes ditambahkan di web.php — tidak ada conflict dengan existing routes
[x] Navbar sticky behavior berfungsi (transparan → putih saat scroll)
[x] MobileMenu buka dan tutup dengan benar di viewport mobile
[x] DemoVideoModal terbuka dan bisa ditutup (X button + backdrop click + Escape)
[x] Semua CTA "Mulai Gratis" dan "Coba Gratis" link ke route('register')
[x] Semua CTA "Masuk" link ke route('login')
[x] Semua <img> punya alt text non-kosong
[x] Semua external link punya rel="noopener noreferrer" dan target="_blank"
[x] FAQ accordion expand/collapse berfungsi
[x] Pricing toggle Bulanan/Tahunan mengupdate harga yang ditampilkan
[x] Tidak ada console error atau TypeScript error
[x] Mobile responsive: cek di 375px, 768px, 1280px
[x] npm run build berhasil tanpa error
[x] php artisan test tetap hijau (tidak ada test yang rusak karena route baru)
```

Catatan verifikasi 2026-05-16:
- Browser smoke: `/`, `/features`, `/pricing`, mobile menu, demo modal, FAQ accordion, pricing toggle.
- Responsive smoke: 375px, 768px, 1280px tanpa horizontal overflow.
- Build: `npm run build` berhasil.
- Test: `PATH=/opt/homebrew/bin:/opt/homebrew/sbin:$PATH php artisan test` → 202 passed, 897 assertions.

---

## Urutan Build yang Disarankan

Ikuti urutan ini untuk memastikan tidak ada dependency yang hilang:

```
1. LandingController.php
2. Route entries di web.php
3. LandingLayout.tsx
4. Navbar.tsx (tanpa MobileMenu dulu)
5. Footer.tsx
6. Home.tsx (skeleton dengan layout)
7. HeroSection.tsx
8. SocialProofBar.tsx
9. ProblemSection.tsx
10. FeatureShowcase.tsx
11. HowItWorksSection.tsx
12. TestimonialsSection.tsx
13. PricingSection.tsx (dengan toggle)
14. FaqSection.tsx
15. CtaBanner.tsx
16. DemoVideoModal.tsx
17. MobileMenu.tsx + integrasi ke Navbar
18. Features.tsx (halaman /features)
19. Pricing.tsx (halaman /pricing)
20. Final: review checklist, npm run build, php artisan test
```

---

## Commit Convention

```bash
feat(landing): add LandingLayout and LandingController
feat(landing): add Navbar with scroll behavior and MobileMenu
feat(landing): add HeroSection with dashboard mockup
feat(landing): add SocialProofBar and ProblemSection
feat(landing): add FeatureShowcase with alternating layout
feat(landing): add HowItWorksSection and TestimonialsSection
feat(landing): add PricingSection with monthly/yearly toggle
feat(landing): add FaqSection and CtaBanner
feat(landing): add DemoVideoModal
feat(landing): add Features and Pricing pages
feat(landing): add SEO metadata and structured data
chore(landing): verify build and test suite green
```

---

## Catatan Penting untuk Agent

1. **Jangan sentuh `AppLayout.tsx`** — landing page punya layout sendiri
2. **Jangan pakai sidebar atau header app** di halaman landing apapun
3. **Jangan tambahkan auth middleware** ke landing routes
4. **Jangan bikin REST API endpoint** — ini Inertia, data hardcode di komponen
5. **Jika ragu soal route name**, cek `routes/web.php` yang sudah ada — jangan assume
6. **TypeScript strict** — tidak boleh ada `any` type
7. **Gunakan `cn()` helper** untuk semua conditional class joining — jangan string concatenation manual
8. **Semua teks Indonesia** — tidak ada placeholder "Lorem ipsum" di final output
9. **Komponen Landing tidak boleh diimport oleh komponen App** — unidirectional dependency
10. **Jika `framer-motion` belum terinstall**, jalankan `npm install framer-motion` dulu sebelum build

---

_Dokumen ini dibuat pada 2026-05-16. Update jika ada perubahan keputusan teknis atau desain sebelum agent mengeksekusi._
