# Panduan Desain UI PT Technodrives

Panduan ini membuat tampilan sistem Cash Advance & Settlement terlihat satu keluarga dengan website resmi PT Technodrives di https://www.technodrives.co.id/. Semua warna, font, dan ukuran komponen di bawah ini diambil dari file CSS asli website tersebut (`assets/css/style.css` dan `assets/css/theme-default.css`), bukan ditebak dari screenshot.

Satu hal yang perlu diluruskan sejak awal: warna utama website resmi itu **oranye**, bukan biru. Biru tua memang ada dan kuat, tetapi oranye adalah warna aksinya: tombol, hover, label seksi, dan garis submenu semuanya oranye.

## Cara memakai panduan ini

Ada dua cara, dan keduanya boleh dipakai bersamaan:

1. Salin blok prompt ringkas di bagian "Prompt siap tempel untuk Antigravity" di akhir dokumen ini, lalu tempel ke Antigravity bersama dokumen desain sistem.
2. Simpan seluruh dokumen ini sebagai acuan. Kalau hasil generate Antigravity melenceng (misalnya tombolnya jadi biru atau radiusnya kotak), tunjuk kembali ke angka persis di bagian komponen.

Aturan urutan: dokumen desain sistem mengatur perilaku aplikasi (role, status, rumus saldo, aturan bisnis), dokumen ini mengatur tampilannya. Kalau keduanya bertabrakan soal tampilan, dokumen ini yang menang. Kalau bertabrakan soal perilaku, dokumen desain sistem yang menang.

## Logo resmi PT Technodrives

![Logo resmi PT Technodrives](../logo-technodrives.png)

File logo: `workspace/goals/cash-advance-settlement-pt-technodrive/files/logo-technodrives.png` (PNG transparan, 161 x 42 piksel, sekitar 10 KB). Unduh file itu dan letakkan di folder aset proyek, misalnya `public/assets/images/logo.png`, lalu panggil dari header aplikasi.

### Anatomi logo

Logo terdiri dari dua bagian yang menyatu dalam satu file:

- Monogram TD di sisi kiri: huruf D biru tua tebal dengan aksen swoosh oranye-kuning yang menyapu di depannya, memberi kesan gerak dan energi listrik.
- Wordmark di sisi kanan: tulisan TECHNODRIVES huruf kapital semua, biru tua, dengan jarak huruf rapat dan tegas.

Warna logo selaras dengan palet website: biru tua untuk badan huruf dan oranye untuk aksennya. Oranye pada logo terasa sedikit lebih kuning (sekitar keluarga `#F2782B` bila diambil sampel pikselnya) dibanding oranye tombol website (`#FF3B00` dan saudaranya), jadi jangan menyamakan keduanya secara paksa. Logo dibiarkan dengan warna aslinya.

### Aturan pakai logo

- Tampilkan logo apa adanya di header atau sidebar atas aplikasi, sama seperti website resmi menaruhnya di kiri atas.
- Jangan mengubah warnanya: jangan dibuat putih, hitam, oranye semua, atau diberi filter dan gradasi.
- Jangan ditarik atau dipipihkan. Kunci rasio aspeknya (161:42, kira-kira 3,8 banding 1) dan atur hanya lebar atau tingginya saja.
- Beri ruang kosong di sekeliling logo minimal setinggi setengah tinggi logo, dan jangan menempelkan teks, tombol, atau badge ke logo.
- Pakai di atas background terang (putih atau abu terang `#F4F4F4`), karena wordmark-nya biru tua dan akan hilang di atas navy. Konsekuensinya untuk aplikasi: bila sidebar memakai navy `#050A1E`, letakkan logo di dalam blok putih atau area terang di puncak sidebar, atau letakkan logo di header konten yang terang, bukan langsung di atas navy.
- Jangan menggambar ulang logo dengan font lain dan jangan memisahkan monogram dari wordmark-nya, kecuali monogram saja untuk favicon.

## Warna: keluarga oranye dan navy, sesuai aslinya

Website resmi tidak memakai satu hex oranye yang rapi. File CSS-nya memakai tiga penulisan yang hampir sama secara bergantian, dan ketiganya memang muncul puluhan kali:

| Peran | Nilai persis dari CSS | Keterangan |
| --- | --- | --- |
| Oranye utama | `#FF3B00` | Paling sering untuk background tombol dan aksen (91 kemunculan) |
| Oranye utama (varian tulis) | `#FF3C00` | Dipakai untuk teks aksen, hover menu, dan label seksi (55 kemunculan) |
| Oranye utama (varian tulis) | `#FF3D00` | Dipakai untuk hover dan fokus form (29 kemunculan) |
| Navy utama | `#050A1E` | Warna heading, header lengket, footer, dan lapisan hover tombol (59 kemunculan) |
| Navy sekunder | `#161A2B` | Latar pill label di hero, panel gelap sekunder, latar breadcrumb |
| Abu teks isi | `#7A7A7A` | Warna teks body di seluruh website |
| Abu latar terang | `#F4F4F4` | Latar kartu fitur dan seksi terang |
| Putih | `#FFFFFF` / `#FFF` | Latar kartu layanan dan teks di atas gelap |
| Kuning aksen | `#FFC704` | Hanya aksen kecil: tombol kembali ke atas dan satu gradasi |
| Biru aksen | `#00AFF5` | Hanya muncul sedikit, bukan warna utama |

Perlakukan `#FF3B00`, `#FF3C00`, dan `#FF3D00` sebagai satu warna brand yang sama. Di kode aplikasi, kunci satu nilai saja sebagai token, yaitu `#FF3C00`, dan jangan menyebar ketiganya, supaya tampilan aplikasi justru lebih konsisten daripada website sumbernya. Nilai lain di tabel dibiarkan persis apa adanya.

Catatan gradasi: website memakai gradasi oranye ke navy (misalnya `linear-gradient(270deg, #FF3B00 0%, #05091D 100%)`) dan satu gradasi oranye-kuning pada tombol sekunder. Untuk aplikasi internal, gradasi cukup dipakai di band header dan halaman login. Tabel, form, dan kartu angka tetap memakai warna datar supaya angka mudah dibaca.

## Token siap tempel

Blok variabel CSS ini adalah terjemahan langsung dari nilai di atas. Tempel di file CSS utama aplikasi.

```css
:root {
  --td-orange: #FF3C00;
  --td-orange-alt: #FF3B00;
  --td-orange-hover-text: #FF3D00;
  --td-navy: #050A1E;
  --td-navy-2: #161A2B;
  --td-body: #7A7A7A;
  --td-surface: #F4F4F4;
  --td-card-border: #E1E1E1;
  --td-input-border: rgba(122, 122, 122, 0.5);
  --td-yellow: #FFC704;
  --td-blue-accent: #00AFF5;
  --td-font-head: "Poppins", sans-serif;
  --td-font-body: "Fira Sans", sans-serif;
  --td-radius-pill: 30px;
  --td-radius-card: 15px;
  --td-radius-input: 15px;
  --td-radius-label: 20px;
  --td-transition: 0.5s;
}
```

Bila proyek memakai Tailwind, pakai konfigurasi setara ini. Namanya sengaja dibuat pendek supaya kelasnya enak dibaca di Blade.

```js
// tailwind.config.js (potongan extend)
export default {
  theme: {
    extend: {
      colors: {
        brand: "#FF3C00",
        brandalt: "#FF3B00",
        navy: "#050A1E",
        navy2: "#161A2B",
        bodygrey: "#7A7A7A",
        surface: "#F4F4F4",
        cardborder: "#E1E1E1",
        accent: "#FFC704",
        skyaccent: "#00AFF5",
      },
      fontFamily: {
        head: ["Poppins", "sans-serif"],
        body: ["Fira Sans", "sans-serif"],
      },
      borderRadius: {
        pill: "30px",
        card: "15px",
        input: "15px",
        label: "20px",
      },
      transitionDuration: {
        brand: "500ms",
      },
    },
  },
};
```

## Tipografi: Poppins untuk judul, Fira Sans untuk isi

Website memuat kedua font ini lewat satu baris Google Fonts di `theme-default.css`:

```css
@import url('https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
```

Aturan pakainya di website, dan yang diteruskan ke aplikasi:

| Elemen | Font | Ukuran dan tinggi baris | Bobot | Warna |
| --- | --- | --- | --- | --- |
| Isi teks (body) | Fira Sans | 16px / 26px | 400 | `#7A7A7A` |
| Judul umum (h1 sampai h6) | Poppins | dasar 42px / 55px | 700 | `#050A1E` |
| h1 | Poppins | 48px | 700 | `#050A1E` |
| h2 | Poppins | 35px | 700 | `#050A1E` |
| h3 | Poppins | 30px | 700 | `#050A1E` |
| h4 | Poppins | 24px | 700 | `#050A1E` |
| h5 | Poppins | 20px | 700 | `#050A1E` |
| h6 | Poppins | 18px | 700 | `#050A1E` |
| Judul hero | Poppins | 55px / 70px | 700 | Putih di atas gelap |
| Paragraf hero | Fira Sans | 19px / 30px | 400 | Putih, opacity 0,702 |
| Tautan | Open Sans pada aturan tautan, Fira Sans pada menu | 16px | 400 | Hover menjadi `#FF3C00` |

Diterjemahkan ke aplikasi: judul halaman dan judul kartu memakai Poppins 600 sampai 700 warna navy, angka saldo dan nominal memakai Poppins 600 supaya tegas, dan semua teks isi, label form, serta isi tabel memakai Fira Sans 16px warna `#7A7A7A` di atas terang dan navy untuk teks yang harus dibaca cepat. Bobot yang benar-benar dipakai website berkisar 400, 500, 600, dan 700, jadi jangan memakai bobot di luar itu.

## Layout dan navigasi: gelap di atas, terang di isi

Pola website resmi yang paling terasa ada tiga:

- Header menempel (sticky) berwarna navy `#050A1E` dengan bayangan tipis `0 2px 5px -2px rgba(0, 0, 0, 0.1)`. Menu di dalamnya putih 16px Fira Sans, berubah oranye `#FF3C00` saat hover, dan submenu tampil putih dengan garis oranye 3px di tepi atas dan bawahnya.
- Hero adalah band gelap besar bergambar latar, dengan pill label kecil, judul Poppins putih 55px, dan satu kata atau frasa di dalam judul yang diwarnai oranye.
- Isi bergantian antara seksi terang (putih dan `#F4F4F4`) dan seksi gelap navy, dengan jarak antarbagian yang lega.

Adaptasi untuk aplikasi internal, yang dipakai harian dari HP:

- Sidebar di desktop dan top bar di HP memakai navy `#050A1E`. Item menu aktif ditandai teks atau pip oranye `#FF3C00`, bukan blok biru. Logo resmi ditaruh di blok terang di puncak sidebar mengikuti aturan pakai logo.
- Area konten selalu terang: putih untuk kartu utama dan `#F4F4F4` untuk latar halaman. Angka tidak pernah ditaruh di atas navy kecuali di band ringkasan.
- Band ringkasan di dashboard meniru hero: latar navy, pill label oranye, judul Poppins putih, dan angka saldo terbesar diwarnai putih dengan label oranye. Ini satu-satunya tempat gradasi oranye ke navy boleh muncul di dalam aplikasi.
- Lebar konten mengikuti pola website yang longgar: beri margin samping yang terasa (website memakai 50px di desktop) dan jangan menempelkan kartu ke tepi layar. Di HP, margin menyusut tetapi urutan tumpukan tetap sama: band ringkasan, antrean tugas, lalu daftar.

## Komponen: angka persis dari CSS asli

### Tombol utama

Ini gaya `.solutek-btn a`, tombol yang dipakai di seluruh website:

```css
.btn-brand {
  display: inline-block;
  background: #FF3B00;
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 600;
  text-transform: uppercase;
  padding: 16px 28px;
  border-radius: 30px;
  position: relative;
  z-index: 1;
  transition: 0.5s;
}
```

Efek hover khasnya bukan sekadar ganti warna: sebuah lapisan navy `#050A1E` menyapu masuk dari samping (kelas `.solutek-hover-btn`, skala X dari 0 menjadi 1 selama 0,5 detik) dan teks tetap terbaca di atasnya. Pada beberapa varian, hover justru mengubah teks menjadi `#FF3D00` di atas putih. Untuk aplikasi, pakai perilaku pertama untuk tombol utama (Ajukan CA, Setujui, Cairkan, Simpan Pengeluaran) dan sederhanakan di HP menjadi perubahan warna datar ke navy, karena sapuan lapisan sulit terasa di layar sentuh.

Varian tombol lain yang memang ada di website:

| Varian | Gaya persis | Dipakai di aplikasi untuk |
| --- | --- | --- |
| Utama | Latar `#FF3B00`, teks putih, radius 30px | Aksi utama setiap halaman |
| Gelap | Latar `#050A1E`, teks putih, Poppins 500, 18px/22px | Aksi sekunder kuat, misalnya Ekspor Laporan |
| Pucat | Latar `#F5EBE8`, teks `#FF3D00`, padding 16px 55px | Aksi ringan di kartu terang, misalnya Lihat Detail |

### Pill label seksi

Setiap seksi di website dibuka label kecil berbentuk pil. Ini gaya `h5.section-sub-title`, dan versi hero-nya identik dengan latar lebih gelap:

```css
.pill-label {
  font-family: "Poppins";
  font-size: 16px;
  font-weight: 600;
  color: #FF3C00;
  background-color: rgba(255, 60, 0, 0.031); /* versi terang */
  border: 1px solid rgba(122, 122, 122, 0.15);
  border-radius: 20px;
  padding: 6px 29px;
  display: inline-block;
  line-height: 25px;
}
```

Di atas latar gelap, versi hero memakai latar `#161A2B` dan border 2px `#40465D`, dengan teks oranye yang sama. Di aplikasi, pill ini dipakai persis untuk label di atas judul halaman, misalnya "Pengajuan", "Settlement", atau "Laporan", satu pill per halaman, bukan per kartu.

### Kartu

Dua pola kartu dari website yang diteruskan:

| Pola | Gaya persis | Dipakai di aplikasi untuk |
| --- | --- | --- |
| Kartu fitur | Latar `#F4F4F4`, border 1px `#E1E1E1`, radius 15px, padding 36px 0 32px | Kartu ringkasan jumlah CA per status |
| Kartu layanan | Latar putih, radius 15px, padding 45px 35px 42px | Kartu detail, buku saldo, dan panel form |

Hover kartu fitur mengubah border menjadi oranye (`#FF4208` pada aturan hover-nya, masih keluarga oranye yang sama). Untuk aplikasi, hover cukup berupa border oranye dan kenaikan bayangan tipis; jangan memakai animasi gambar latar seperti di website, karena kartu aplikasi berisi angka yang harus tetap diam dan terbaca.

### Form

Ini gaya `.form_box input` dan saudaranya, diteruskan apa adanya ke semua form aplikasi:

```css
.input-brand {
  border: 1px solid rgba(122, 122, 122, 0.5);
  height: 60px;
  border-radius: 15px;
  padding: 0 23px;
  width: 100%;
  font-family: "Fira Sans";
  font-size: 16px;
  line-height: 26px;
  color: #7A7A7A;
  transition: 0.5s;
}
.input-brand:focus {
  border-color: #FF3D00;
  outline: 0;
  box-shadow: 0 0 4px rgba(255, 61, 0, 0.5);
}
```

Textarea mengikuti pola yang sama dengan radius lebih besar: border yang sama, padding 17px 23px, tinggi 150px, radius 25px. Pesan error validasi tampil di bawah field dengan warna oranye brand untuk ikon dan garisnya, tetapi teks error memakai navy supaya tetap terbaca sebagai peringatan, bukan hiasan. Field jumlah uang memakai input yang sama, dengan awalan "Rp" di dalam kotak dan angka rata kanan.

## Badge status CA: adaptasi yang dinyatakan terbuka

Website resmi tidak punya badge status transaksi, jadi pemetaan di bawah ini adalah adaptasi yang dibuat untuk aplikasi ini, hanya memakai warna dari palet brand di atas. Pemetaan ini menggantikan saran badge lama di dokumen desain sistem.

| Status CA | Gaya badge | Alasan pemetaan |
| --- | --- | --- |
| `PENDING_APPROVAL` | Latar `#FFC704`, teks navy `#050A1E` | Kuning memang aksen "perhatian" di website, dipakai kecil saja |
| `REJECTED` | Latar `#FF3C00`, teks putih | Oranye brand adalah warna paling keras di palet, dipakai untuk penolakan |
| `ACTIVE` | Latar `#050A1E`, teks putih | Navy untuk status berjalan yang stabil dan utama |
| `NEED_SETTLEMENT` | Latar putih, border 2px `#FF3C00`, teks `#FF3C00` | Versi garis luar dari pill label, menandakan tindakan dibutuhkan |
| `PENDING_VERIFICATION` | Latar `#161A2B`, teks putih | Navy sekunder, membedakan dari `ACTIVE` tanpa warna baru |
| `CLOSED` | Latar `#F4F4F4`, border 1px `#E1E1E1`, teks `#7A7A7A` | Abu netral untuk arsip yang sudah selesai dan tidak menuntut aksi |

Satu pengecualian yang disengaja: jangan memakai hijau untuk `CLOSED`. Hijau tidak ada di palet brand PT Technodrives, dan status selesai lebih jelas dibaca dari label teksnya daripada dari warna semantik pinjaman. Semua badge selalu menampilkan teks status yang ramah (misalnya "Menunggu Persetujuan"), tidak pernah warna saja, dan bentuknya mengikuti radius label 20px dengan padding pill yang diperkecil proporsional.

## Tabel data: adaptasi, karena website resmi tidak punya tabel

Website resmi adalah situs profil dan tidak memiliki tabel data, jadi tidak ada gaya tabel yang bisa disalin. Gaya berikut adalah adaptasi dari palet dan pola border yang sama, dan harus diperlakukan sebagai keputusan aplikasi, bukan fakta dari website:

- Kepala tabel: latar navy `#050A1E`, teks putih Poppins 600, huruf kapital kecil untuk nama kolom.
- Baris isi: Fira Sans 16px, latar putih dan `#F4F4F4` bergantian, garis antarbaris 1px `#E9E9E9` (warna border yang memang dipakai website untuk input sidebar).
- Kolom angka rata kanan, kolom status memakai badge di atas, dan baris yang bisa diklik mendapat hover border atau latar oranye sangat tipis.
- Di HP, tabel daftar CA berubah menjadi tumpukan kartu layanan (putih, radius 15px), bukan tabel yang diperas, mengikuti prinsip mobile-first di dokumen desain sistem.

## Radius, bayangan, gerak, dan ikon

Nilai yang dominan di CSS asli, diteruskan sebagai skala aplikasi:

| Aspek | Nilai dari website | Pemakaian di aplikasi |
| --- | --- | --- |
| Radius pil dan tombol | 30px | Tombol, pill label besar, tab aktif |
| Radius kartu | 15px | Semua kartu dan panel |
| Radius label kecil | 20px | Badge status dan pill label |
| Radius input | 15px, textarea 25px | Semua field form |
| Bayangan header | `0 2px 5px -2px rgba(0, 0, 0, 0.1)` | Top bar dan sidebar saat menggulir |
| Bayangan submenu | `0px 1px 3px rgba(0, 0, 0, 0.3)` | Dropdown dan menu konteks |
| Bayangan fokus form | `0 0 4px rgba(255, 61, 0, 0.5)` | Field yang sedang aktif saja |
| Transisi | 0,5 detik hampir di semua elemen | Hover, fokus, dan buka tutup panel |
| Durasi lain | 0,3 sampai 0,8 detik pada animasi masuk | Maksimal untuk transisi halaman, jangan lebih |

Ikon di website berasal dari tiga set yang dimuat bersamaan: Font Awesome (`assets/css/all.min.css`), Flaticon (`assets/css/flaticon.css`), dan Bootstrap Icons 1.9.1 dari CDN. Untuk aplikasi, cukup pakai Bootstrap Icons saja agar konsisten dan ringan, dengan gaya garis yang sama seperti ikon chevron pada menu website. Jangan mencampur ikon emoji atau set ikon lain ke dalam aplikasi.

## Yang boleh dan tidak boleh

Panduan ini menggantikan saran warna di dokumen desain sistem sebelumnya, yang menyarankan navy sebagai warna utama dengan hijau dan kuning semantik. Saran lama itu dibuat sebelum CSS asli website dibaca. Mulai sekarang:

- Pakai oranye `#FF3C00` untuk semua aksi utama, tautan aktif, dan penanda fokus. Ini warna brand, bukan warna peringatan.
- Pakai navy `#050A1E` untuk struktur: sidebar, header, judul, dan band ringkasan. Navy adalah panggungnya, oranye adalah aktornya.
- Jangan memperkenalkan biru muda sebagai warna utama. `#00AFF5` hanya muncul sedikit di website dan tidak dipakai sama sekali di aplikasi ini, supaya tidak ada dua warna aksi yang bersaing.
- Jangan memakai hijau, ungu, atau merah semantik untuk badge dan tombol. Satu-satunya warna di luar palet yang boleh muncul adalah warna foto struk dan foto profil pengguna itu sendiri.
- Jangan memakai font selain Poppins dan Fira Sans, dan jangan memakai bobot di luar 400, 500, 600, dan 700.
- Jangan mengecilkan radius tombol menjadi kotak. Bentuk pil 30px adalah ciri paling mudah dikenali dari website ini.
- Jangan menaruh teks abu `#7A7A7A` di atas navy untuk informasi penting. Di atas gelap, teks penting selalu putih, dan abu hanya untuk teks sekunder.

## Prompt siap tempel untuk Antigravity

Salin blok ini persis ke Antigravity bersama dokumen desain sistem:

```text
Terapkan identitas visual website resmi PT Technodrives (www.technodrives.co.id) ke seluruh UI aplikasi ini.

Logo: pakai file logo resmi PT Technodrives (PNG transparan 161x42, monogram TD biru tua dengan swoosh oranye dan wordmark TECHNODRIVES biru tua) apa adanya di header, di atas background terang, tanpa mengubah warna, tanpa stretch, dengan ruang kosong di sekelilingnya.

Warna: oranye brand #FF3C00 (varian tulis resmi #FF3B00 dan #FF3D00 adalah warna yang sama, kunci hanya #FF3C00 di kode), navy #050A1E, navy sekunder #161A2B, teks isi #7A7A7A, latar terang #F4F4F4, border kartu #E1E1E1, kuning aksen #FFC704 hanya untuk badge PENDING_APPROVAL. Tidak ada hijau, ungu, atau biru muda sebagai warna UI.

Font: Poppins (700 untuk judul, 600 untuk angka dan tombol) dan Fira Sans (400/500 untuk isi, label, dan tabel), dimuat dari Google Fonts. Isi teks 16px/26px.

Komponen: tombol utama latar #FF3B00, teks putih kapital 15px bobot 600, padding 16px 28px, radius 30px, hover menjadi navy #050A1E, transisi 0.5s. Pill label di atas judul halaman: teks #FF3C00 Poppins 600 16px, radius 20px, padding 6px 29px, border 1px rgba(122,122,122,0.15). Kartu putih atau #F4F4F4, radius 15px, border 1px #E1E1E1. Input tinggi 60px, radius 15px, padding 0 23px, border 1px rgba(122,122,122,0.5), fokus border #FF3D00 dengan box-shadow 0 0 4px rgba(255,61,0,0.5). Sidebar dan top bar navy #050A1E, menu aktif oranye. Kepala tabel navy dengan teks putih. Badge status mengikuti tabel badge di dokumen panduan desain UI. Ikon hanya Bootstrap Icons. Mobile-first, angka saldo selalu terbaca tanpa zoom.
```

## Checklist sebelum menganggap tampilan selesai

- Logo resmi tampil apa adanya di header, di atas background terang, tidak terdistorsi.
- Tidak ada tombol utama berwarna selain oranye brand dan navy.
- Semua judul memakai Poppins warna navy, semua isi memakai Fira Sans.
- Semua tombol berbentuk pil radius 30px dan semua kartu radius 15px.
- Fokus form menunjukkan border oranye dan bayangan oranye tipis.
- Keenam badge status mengikuti tabel badge dan selalu berteks.
- Tidak ada hijau, ungu, atau biru muda yang muncul sebagai warna UI.
- Dashboard memiliki satu band navy bergaya hero, dan sisanya terang.

## Sumber dan batas kejujuran

- Warna, font, dan ukuran diambil dari `assets/css/style.css` dan `assets/css/theme-default.css` milik website resmi PT Technodrives, dibaca pada 9 November 2026. File `style.css` itu sendiri adalah stylesheet tema bernama Solutek yang dipakai website tersebut, jadi yang disalin di sini adalah tampilan yang benar-benar tampil di website PT, apa pun nama temanya.
- Akses HTTPS ke website sempat menampilkan halaman pemeriksaan bot, sehingga file CSS dibaca lewat alamat HTTP pada domain yang sama. Isi file dan nilai warnanya sama, dan tidak ada nilai yang diambil dari halaman pemeriksaan tersebut.
- Logo diambil dari file logo resmi yang tampil di header website. Warna oranye logo digambarkan sebagai keluarga oranye-kuning berdasarkan sampel piksel, bukan dari kode CSS, karena logo adalah berkas gambar.
- Dua bagian adalah adaptasi yang dinyatakan terbuka di tempatnya masing-masing: badge status CA dan gaya tabel data. Website resmi tidak memiliki keduanya, jadi keduanya dibuat dari palet brand, bukan disalin.
