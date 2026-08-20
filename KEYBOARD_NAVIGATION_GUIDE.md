# ⌨️ Panduan Keyboard Navigation - Form Analis

## 🎯 Konsep Navigasi

Form analis menggunakan **column-based navigation** dimana:
- **Enter** dari kolom "Hasil" → langsung ke "Hasil" baris berikutnya
- **Tab** untuk pindah horizontal (antar kolom)
- **Ctrl + Arrow** untuk navigasi vertikal yang lebih presisi

---

## 📋 Keyboard Shortcuts Lengkap

### 🔵 Dari Kolom "HASIL"

| Tombol | Aksi |
|--------|------|
| **Enter** | Pindah ke **Hasil** di baris berikutnya ⬇️ |
| **Tab** | Pindah ke **Keterangan** di baris yang sama ➡️ |
| **Ctrl + ↓** | Pindah ke **Hasil** di baris bawah |
| **Ctrl + ↑** | Pindah ke **Hasil** di baris atas |

**Ideal untuk:** Cepat mengisi semua hasil parameter berurutan

---

### 🟢 Dari Kolom "KETERANGAN"

| Tombol | Aksi |
|--------|------|
| **Enter** | New line / Line break (untuk keterangan multi-line) |
| **Ctrl + Enter** | Pindah ke **Keterangan** di baris berikutnya ⬇️ |
| **Tab** | Pindah ke **Hasil** di baris berikutnya ⬇️➡️ |
| **Shift + Tab** | Kembali ke **Hasil** di baris yang sama ⬅️ |
| **Ctrl + ↓** | Pindah ke **Keterangan** di baris bawah |
| **Ctrl + ↑** | Pindah ke **Keterangan** di baris atas |

**Ideal untuk:** Mengisi keterangan dengan detail multi-line

---

## 🎬 Workflow Rekomendasi

### Skenario 1: Fokus Input Hasil Cepat
**Tujuan:** Isi semua hasil parameter dulu, keterangan belakangan

```
1. Klik "Hasil" parameter pertama
2. Ketik nilai
3. Tekan Enter → Langsung ke "Hasil" berikutnya
4. Ulangi sampai semua hasil terisi
5. Kembali ke atas untuk isi keterangan (jika perlu)
```

**Shortcut:** Enter → Enter → Enter (super cepat!)

---

### Skenario 2: Lengkap Per Parameter
**Tujuan:** Isi hasil dan keterangan langsung per parameter

```
1. Klik "Hasil" parameter pertama
2. Ketik nilai
3. Tekan Tab → Pindah ke "Keterangan"
4. Ketik keterangan (bisa multi-line dengan Enter biasa)
5. Tekan Tab → Pindah ke "Hasil" parameter berikutnya
6. Ulangi
```

**Shortcut:** Tab → Tab → Tab (lengkap berurutan)

---

### Skenario 3: Edit Kolom Tertentu Saja
**Tujuan:** Hanya update hasil saja atau keterangan saja untuk beberapa parameter

**Untuk Hasil:**
```
1. Klik "Hasil" parameter tertentu
2. Edit nilai
3. Ctrl + ↓ → Langsung ke "Hasil" di bawahnya
4. Edit nilai
5. Ulangi
```

**Untuk Keterangan:**
```
1. Klik "Keterangan" parameter tertentu
2. Edit keterangan
3. Ctrl + Enter → Langsung ke "Keterangan" di bawahnya
4. Edit keterangan
5. Ulangi
```

---

## 🔄 Pola Navigasi Umum

### Pattern 1: Vertical (Atas-Bawah)
```
Hasil Parameter 1
    ↓ [Enter]
Hasil Parameter 2
    ↓ [Enter]
Hasil Parameter 3
    ↓ [Enter]
...
```

### Pattern 2: Horizontal + Vertical
```
Hasil P1 → [Tab] → Keterangan P1
                        ↓ [Tab]
                  Hasil P2 → [Tab] → Keterangan P2
                                        ↓ [Tab]
                                  Hasil P3
```

### Pattern 3: Skip to Column
```
Hasil P1
    ↓ [Ctrl + ↓]
Hasil P2
    ↓ [Ctrl + ↓]
Hasil P3
```

---

## 📝 Tips & Trik Navigasi

### 1. **Speed Entry Mode**
Untuk input cepat puluhan parameter:
- Gunakan **Enter** terus menerus
- Fokus hanya di kolom "Hasil"
- Keterangan bisa diisi belakangan

### 2. **Review Mode**
Untuk cek dan edit:
- Gunakan **Ctrl + Arrow** untuk loncat-loncat
- Lebih presisi tanpa perlu klik mouse

### 3. **Dropdown Parameter**
Untuk parameter dengan dropdown (option):
- Buka dropdown dengan **Space** atau **Arrow Down**
- Pilih dengan **Arrow Up/Down**
- Confirm dengan **Enter** → langsung pindah ke parameter berikutnya

### 4. **Multi-line Keterangan**
Kalau butuh keterangan panjang:
- Enter biasa untuk new line
- Ctrl + Enter untuk pindah parameter (jangan lupa!)

### 5. **Quick Jump**
Loncat cepat antar section:
- Klik parameter pertama di group baru
- Lalu gunakan keyboard dari sana

---

## 🚫 Yang TIDAK Bisa

### ❌ Arrow biasa tanpa Ctrl
- Arrow Up/Down biasa digunakan TinyMCE untuk navigate dalam text
- Harus pakai **Ctrl + Arrow** untuk pindah antar row

### ❌ Escape untuk cancel
- Tidak ada mode edit/view
- Semua langsung auto-save

### ❌ Shift + Enter untuk pindah
- Shift + Enter di keterangan = new line (bukan pindah)
- Gunakan Tab atau Ctrl + Enter

---

## 🎯 Quick Reference Card

**Print/Save ini untuk referensi cepat:**

```
┌─────────────────────────────────────────────────┐
│  HASIL COLUMN          │  KETERANGAN COLUMN     │
├─────────────────────────────────────────────────┤
│  Enter    → Hasil ⬇️   │  Enter    → New Line   │
│  Tab      → Ket ➡️     │  Ctrl+Ent → Ket ⬇️     │
│  Ctrl+↓   → Hasil ⬇️   │  Tab      → Hasil ⬇️➡️ │
│  Ctrl+↑   → Hasil ⬆️   │  Shift+Tab→ Hasil ⬅️  │
└─────────────────────────────────────────────────┘

FORMATTING SHORTCUTS:
Ctrl + B         = Bold
Ctrl + I         = Italic
Ctrl + Shift + = = Superscript (pangkat)
Ctrl + =         = Subscript

SPECIAL:
Ω button         = Simbol (±, °, ≤, ≥, µ, etc)
```

---

## 🏆 Pro Tips

1. **Muscle Memory**
   - Latih pola: Enter-Enter-Enter untuk hasil
   - Latih pola: Tab-Tab-Tab untuk lengkap

2. **Eyes on Data**
   - Dengan keyboard navigation, mata bisa fokus ke data
   - Tidak perlu lihat mouse/tombol

3. **Consistency**
   - Gunakan satu pola untuk satu jenis input
   - Jangan mix-and-match kalau tidak perlu

4. **Speed vs Accuracy**
   - Untuk hasil numerik: gunakan Enter (cepat)
   - Untuk keterangan detail: gunakan Tab (lengkap)

---

## 🆘 Troubleshooting

### Navigasi Tidak Jalan?
**Cek:**
1. Apakah ada popup/modal terbuka?
2. Apakah cursor benar-benar di input field?
3. Refresh halaman dan coba lagi

### Stuck di Satu Field?
**Solusi:**
- Klik field lain dengan mouse
- Tab beberapa kali sampai keluar
- Refresh jika perlu

### Arrow Tidak Pindah Row?
**Normal!** Arrow tanpa Ctrl untuk navigate dalam text.
Gunakan **Ctrl + Arrow** untuk pindah row.

---

## 📱 Mobile/Tablet

Keyboard navigation **tidak tersedia** di touch device.
Gunakan:
- Tap untuk pindah field
- Touch keyboard bawaan device

---

**Happy Typing! ⌨️✨**

