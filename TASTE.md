# 🎨 Frontend Taste Directive (Anti-Slop UI Architecture)

Dokumen ini adalah panduan otomatisasi frontend untuk proyek **SIBOB (Sistem Informasi Barang Milik Negara)**. Panduan ini mengaktifkan dan mengintegrasikan skill **`design-taste-frontend`** dan **`minimalist-ui`** (dari `Leonxlnx/taste-skill`) pada setiap pekerjaan frontend.

---

## ⚡ 1. Aturan Aktivasi Otomatis (Automatic Trigger)

Setiap kali agen AI diminta untuk:
- Membuat, mendesain, atau merevisi halaman **Blade / HTML / CSS / Tailwind**.
- Merancang **Landing Page**, **Halaman Login / Auth**, **Dashboard**, atau **Tabel Aset**.
- Membuat komponen UI baru (kartu aset, modal, formulir, badge status, tombol).

**AGEN AI HARUS SECARA OTOMATIS:**
1. Membaca dan menerapkan instruksi dari `.agents/skills/design-taste-frontend/SKILL.md` dan `.agents/skills/minimalist-ui/SKILL.md`.
2. Menyertakan satu baris **"Design Read"** sebelum menulis kode:
   > *"Reading this as: [Jenis Halaman] for [Target Pengguna], with a [Vibe/Gaya] language, leaning toward [Sistem Desain]."*
3. Menolak seluruh elemen *AI Design Slop* (lihat bagian Banned Elements).

---

## 🎛️ 2. Preset Dials untuk SIBOB (Institutional Utilitarian)

Karena SIBOB adalah sistem inventaris & aset instansi pemerintah (BPOM), gunakan konfigurasi *dial* berikut:

| Dial | Nilai Default | Penjelasan |
|---|---|---|
| **`DESIGN_VARIANCE`** | **6 / 10** | Rapi, asimetris elegan, terstruktur, profesional (bukan seni eksperimental liar). |
| **`MOTION_INTENSITY`** | **3 / 10** | Halus dan tertahan (transisi cepat 150-200ms, hover state subtil, tanpa animasi looping yang mengganggu). |
| **`VISUAL_DENSITY`** | **6 / 10** | Kepadatan informatif tinggi untuk tabel & kartu aset, namun tetap memiliki *whitespace* yang lega. |

---

## 🚫 3. Banned Elements (Anti-Slop Strict Checklist)

Dilarang keras menggunakan *template LLM generik*:
- ❌ **NO AI-Purple/Indigo Gradients:** Hindari gradien ungu-kebiruan khas template AI.
- ❌ **NO Heavy Shadows:** Dilarang menggunakan `shadow-lg`, `shadow-xl`, atau `shadow-2xl`. Gunakan border tipis 1px (`border-stone-200` atau `border-neutral-200/80`) dengan bayangan sangat halus (`shadow-sm` atau *border-only*).
- ❌ **NO Generic Fonts:** Hindari default font generik (Inter/Roboto biasa). Gunakan font modern editorial seperti **Geist**, **SF Pro**, atau aksen serif tajam seperti **Instrument Serif / Newsreader** pada tajuk utama.
- ❌ **NO Emojis in UI / Headings:** Dilarang meletakkan emoji di dalam header atau tombol. Gunakan ikon SVG monokrom yang presisi dan bersih.
- ❌ **NO Giant Pill Containers:** Jangan buat kontainer kartu besar berbentuk kapsul (`rounded-full`). Gunakan sudut tajam dan modern (`rounded-lg` atau `rounded-xl`, radius 8px - 12px).
- ❌ **NO AI Copywriting Clichés:** Hindari kata-kata basi seperti *"Seamless"*, *"Elevate"*, *"Unleash"*, *"Next-Gen"*. Gunakan bahasa Indonesia kedinasan yang lugas, profesional, dan to-the-point.

---

## 🏛️ 4. Desain Sistem & Palet Warna SIBOB (Editorial Forest Pine & Crisp White)

Sesuai preferensi Anda: nuansa **kehijauan elegan (*botanical minimalist*) berpadu dengan putih bersih (*crisp white*)**, memberikan kesan resmi kedinasan, modern, bersih, dan berwibawa:

### Palet Warna Utama:
* **Background Kanvas Utama:** *Soft Pale Sage Tint* (`#F7FAF8` atau `bg-[#F7FAF8]`) — memberikan kontras halus agar kartu putih dapat "pop out" tanpa silau.
* **Permukaan Kartu & Panel:** *Crisp Pure White* (`#FFFFFF`) dengan border halus bernuansa sage lembut (`1px solid #E3ECE6` atau `border-emerald-950/10`).
* **Aksen Primer / Brand / Tombol Utama:** *Deep Forest Pine / Emerald Night* (`#163828` atau `#123324` dengan teks `#FFFFFF`) — tegas, prestisius, tanpa kesan norak.
* **Surface Aksen Sekunder (Soft Green Highlights):** *Muted Herbal Sage* (`#EAF2ED` atau `#DDEAE2`, teks `#225038`) untuk tag, tab menu aktif, dan sorotan ringkasan.
* **Teks Primer:** *Forest Charcoal Off-Black* (`#132219` atau `text-stone-900`) — kontras tinggi dan sangat nyaman dibaca.
* **Teks Sekunder:** *Muted Sage-Gray* (`#62756A` atau `text-stone-500`).

### Aksen Status Aset (Pastel Muted, Ramah Lingkungan & Kedinasan):
* **Kondisi Baik / Aktif:** Latar `#EAF5EE` (Teks Hijau Hutan `#1E5E38`)
* **Kondisi Rusak / Dihapus:** Latar `#FDF0EE` (Teks Merah Bata Teduh `#9C322C`)
* **Perlu Kalibrasi:** Latar `#FCF5E8` (Teks Kuning Amber Teduh `#8E5F13`)
* **Dalam Distribusi:** Latar `#EEF4F8` (Teks Slate Muted `#284B66`)

### Komponen Kartu (Flat Bento Grid):
Gunakan grid asimetris dengan kartu berlatar putih bersih (`#FFFFFF`), border 1px sage halus (`#E3ECE6`), sudut modern `rounded-xl` (12px), dan sentuhan aksen Deep Pine pada tombol tindakan utama.


---

## 💬 5. Contoh Prompt Singkat untuk Pengguna

Setelah file ini aktif, Anda cukup memberikan instruksi singkat, dan AI akan otomatis menghasilkan UI berstandar tinggi:

- *"Buatkan halaman login SIBOB dengan gaya taste-skill."*
- *"Redesain kartu daftar barang BMN agar terlihat clean editorial dan modern."*
- *"Buatkan dashboard ringkasan aset dengan layout bento grid minimalis."*
- *"Poles tampilan form pengajuan distribusi ini."*
