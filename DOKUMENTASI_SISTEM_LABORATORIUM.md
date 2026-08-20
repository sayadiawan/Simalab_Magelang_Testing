# Panduan Lengkap Sistem Laboratorium
## Magelang Labkes - Panduan Penggunaan untuk Semua Pengguna

---

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Master Data](#master-data)
3. [Permohonan Uji (Pemeriksaan)](#permohonan-uji-pemeriksaan)
4. [Penambahan Sample](#penambahan-sample)
5. [Permohonan Uji Klinik](#permohonan-uji-klinik)
6. [Menambah Parameter di Panel Pemeriksaan Klinik](#menambah-parameter-di-panel-pemeriksaan-klinik)
7. [Cara Mengubah Harga](#cara-mengubah-harga)
8. [Master Data Klinik (Parameter & Paket)](#master-data-klinik)
9. [Cara Edit & Hapus Data](#cara-edit--hapus-data)
10. [Input Hasil Pemeriksaan Klinik](#input-hasil-pemeriksaan-klinik)
11. [Alur Kerja Lengkap](#alur-kerja-lengkap)
12. [Tips Penggunaan](#tips-penggunaan)
13. [Troubleshooting (Mengatasi Masalah)](#troubleshooting)

---

## Pendahuluan

Sistem Laboratorium Magelang Labkes adalah aplikasi manajemen laboratorium yang mengelola proses pemeriksaan mulai dari permohonan uji, pengambilan sample, hingga hasil pemeriksaan. Panduan ini dirancang untuk **semua pengguna**, termasuk yang baru pertama kali menggunakan sistem — setiap langkah dijelaskan secara rinci dan jelas.

### Prasyarat
- User harus sudah **login** ke sistem
- User harus memiliki **hak akses** ke menu yang digunakan (jika menu tidak muncul, hubungi admin)
- Master data dasar harus sudah disetup oleh admin

### Mengenal Tampilan Layar
| Bagian Layar | Fungsinya |
|---|---|
| **Sidebar (sebelah kiri)** | Daftar menu utama — klik untuk membuka halaman |
| **Area Konten (tengah)** | Tempat data dan form ditampilkan |
| **Header (atas)** | Menampilkan nama pengguna dan tombol logout |

> ✅ **Tips Penting:** Selalu klik tombol **Simpan** setelah mengisi form. Jika berpindah halaman sebelum menyimpan, data yang sudah diisi akan hilang.

---

## Master Data

Master data adalah data referensi yang harus disiapkan sebelum melakukan proses pemeriksaan. Berikut adalah master data yang perlu disetup:

### 1. Customer (Pelanggan)

**Route:** `/elits-customer`

**Fungsi:** Mengelola data pelanggan yang akan melakukan pemeriksaan.

**Langkah-langkah:**
1. Akses menu **Customer** di sidebar
2. Klik tombol **Tambah Customer Baru**
3. Isi form dengan data:
   - **Nama Customer** (wajib)
   - **Alamat Customer** (wajib)
   - **Email Customer** (opsional)
   - **Contact Person** (wajib)
   - **Kecamatan** (wajib)
   - **Kategori Customer** (opsional)
4. Klik **Simpan**

**Catatan:**
- Customer dapat ditambahkan langsung saat membuat Permohonan Uji jika belum terdaftar
- Data customer digunakan untuk semua jenis permohonan uji

---

### 2. Sample Type (Jenis Sample)

**Route:** `/elits-sample-type`

**Fungsi:** Mengelola jenis-jenis sample yang dapat diperiksa di laboratorium.

**Langkah-langkah:**
1. Akses menu **Sample Type** di sidebar
2. Klik tombol **Tambah Sample Type Baru**
3. Isi form dengan data:
   - **Nama Sample Type** (wajib)
   - **Kode Sample Type** (wajib, unik)
   - **Deskripsi** (opsional)
   - **Kategori** (opsional)
4. Klik **Simpan**

**Contoh Sample Type:**
- Air Minum
- Air Bersih
- Makanan
- Minuman
- Darah
- Urine
- dll

---

### 3. Laboratorium

**Route:** `/elits-laboratorium`

**Fungsi:** Mengelola data laboratorium yang tersedia.

**Langkah-langkah:**
1. Akses menu **Laboratorium** di sidebar
2. Klik tombol **Tambah Laboratorium Baru**
3. Isi form dengan data:
   - **Nama Laboratorium** (wajib)
   - **Kode Laboratorium** (wajib, unik)
   - **Deskripsi** (opsional)
4. Klik **Simpan**

**Contoh Laboratorium:**
- KMA (Kimia)
- FKA (Fisika)
- MBI (Mikrobiologi)
- KLI (Klinik)

**Catatan:**
- Kode laboratorium digunakan untuk mengelompokkan metode pemeriksaan
- Setiap sample dapat memiliki metode dari beberapa laboratorium

---

### 4. Method (Metode Pemeriksaan)

**Route:** `/elits-method`

**Fungsi:** Mengelola metode-metode pemeriksaan yang tersedia.

**Langkah-langkah:**
1. Akses menu **Method** di sidebar
2. Klik tombol **Tambah Method Baru**
3. Isi form dengan data:
   - **Nama Method** (wajib)
   - **Parameter Method** (wajib)
   - **Nama Report Method** (wajib)
   - **Laboratorium** (wajib, pilih dari daftar laboratorium)
   - **Unit** (wajib)
   - **Harga Bahan** (opsional)
   - **Harga Sarana** (opsional)
   - **Harga Jasa** (opsional)
   - **Harga Total Method** (wajib)
   - **Jenis Parameter Kimia** (opsional)
   - **Berhubungan Kesehatan** (opsional)
   - **Status Ready** (opsional)
   - **Is Option** (opsional)
4. Klik **Simpan**

**Catatan:**
- Setiap method harus dikaitkan dengan laboratorium
- Harga total method digunakan untuk perhitungan biaya
- Method dapat digunakan dalam paket atau secara individual

---

### 5. Packet (Paket Pemeriksaan)

**Route:** `/elits-packet`

**Fungsi:** Mengelola paket-paket pemeriksaan yang terdiri dari beberapa method.

**Langkah-langkah:**
1. Akses menu **Packet** di sidebar
2. Klik tombol **Tambah Packet Baru**
3. Isi form dengan data:
   - **Nama Packet** (wajib)
   - **Kode Packet** (wajib, unik)
   - **Sample Type** (wajib, pilih jenis sample)
   - **Harga Packet** (wajib)
   - **Deskripsi** (opsional)
4. Tambahkan **Method** ke dalam packet:
   - Pilih method dari daftar
   - Setiap method harus dari laboratorium yang sesuai
5. Klik **Simpan**

**Catatan:**
- Packet dapat berisi beberapa method dari laboratorium yang berbeda
- Harga packet biasanya lebih murah dari total harga method individual
- Packet memudahkan pemilihan pemeriksaan yang sering dilakukan bersamaan

---

### 6. Unit (Satuan)

**Route:** `/elits-unit`

**Fungsi:** Mengelola satuan-satuan yang digunakan dalam hasil pemeriksaan.

**Langkah-langkah:**
1. Akses menu **Unit** di sidebar
2. Klik tombol **Tambah Unit Baru**
3. Isi form dengan data:
   - **Nama Unit** (wajib)
   - **Kode Unit** (wajib, unik)
   - **Simbol Unit** (opsional)
4. Klik **Simpan**

**Contoh Unit:**
- mg/L
- CFU/mL
- NTU
- pH
- dll

---

### 7. Container (Wadah Sample)

**Route:** `/elits-container`

**Fungsi:** Mengelola jenis-jenis wadah yang digunakan untuk menyimpan sample.

**Langkah-langkah:**
1. Akses menu **Container** di sidebar
2. Klik tombol **Tambah Container Baru**
3. Isi form dengan data:
   - **Nama Container** (wajib)
   - **Kode Container** (wajib, unik)
   - **Deskripsi** (opsional)
4. Klik **Simpan**

**Contoh Container:**
- Botol Plastik
- Botol Kaca
- Tabung Reaksi
- dll

---

### 8. Pasien (Untuk Permohonan Uji Klinik)

**Route:** `/elits-pasien`

**Fungsi:** Mengelola data pasien untuk pemeriksaan klinik.

**Langkah-langkah:**
1. Akses menu **Pasien** di sidebar
2. Klik tombol **Tambah Pasien Baru**
3. Isi form dengan data:
   - **No. Pasien** (wajib, unik)
   - **Nama Pasien** (wajib)
   - **Tanggal Lahir** (wajib)
   - **Jenis Kelamin** (wajib)
   - **Alamat** (wajib)
   - **No. Telepon** (opsional)
   - **No. KTP** (opsional)
   - **No. BPJS** (opsional)
4. Klik **Simpan**

**Catatan:**
- Pasien dapat ditambahkan langsung saat membuat Permohonan Uji Klinik
- Data pasien dapat dicari berdasarkan nama, no. pasien, atau no. BPJS

---

## Permohonan Uji (Pemeriksaan)

Permohonan Uji adalah proses awal untuk pemeriksaan non-klinik (kesmas). Proses ini meliputi pendaftaran permohonan dan pengaturan informasi dasar.

### Langkah-langkah Membuat Permohonan Uji

**Route:** `/elits-permohonan-uji/create`

1. **Akses Menu Permohonan Uji**
   - Klik menu **Permohonan Uji** di sidebar
   - Klik tombol **Tambah Permohonan Uji Baru**

2. **Isi Data Dasar Permohonan**
   - **Tanggal Permohonan** (otomatis, dapat diubah)
   - **Customer** (wajib)
     - Pilih customer dari daftar, atau
     - Klik "Tambah Customer Baru" untuk menambah customer baru
   - **Nama Personil** (wajib)
     - Nama petugas yang menangani permohonan

3. **Pengaturan Sample**
   - **Mode Pengiriman Sample:**
     - **Sample Dibawa Sendiri** (pengambil_sample = 0)
       - Isi **Tanggal Penerimaan Sample**
       - Isi **Kondisi Sample** (Baik/Rusak/Lainnya)
       - Jika kondisi "Lainnya", isi keterangan
     - **Sample Diambil Lab** (pengambil_sample = 1)
       - Isi **Tanggal Sampling**
       - Isi **Paket Sampling** (opsional)

4. **Informasi Pembayaran**
   - **Total Harga** (akan dihitung otomatis setelah menambah sample)
   - **Uang Muka** (wajib)
   - **Kekurangan Pembayaran** (otomatis)
   - **Status Pembayaran** (Lunas/Belum Lunas)

5. **Simpan Permohonan Uji**
   - Klik tombol **Simpan**
   - Sistem akan generate **Kode Permohonan Uji** dengan format: `PU.NK/YYYYMMDD/XXXX`
   - Setelah disimpan, akan redirect ke halaman detail permohonan uji

### Informasi Penting

- **Kode Permohonan Uji** dibuat otomatis dengan format: `PU.NK/[TANGGAL]/[URUTAN]`
- Setelah permohonan uji dibuat, status awal adalah **REGISTER**
- Permohonan uji harus memiliki minimal 1 sample untuk dapat diproses
- Total harga akan dihitung dari jumlah sample dan method yang dipilih

---

## Penambahan Sample

Setelah Permohonan Uji dibuat, langkah selanjutnya adalah menambahkan sample ke dalam permohonan uji tersebut.

### Langkah-langkah Menambahkan Sample

**Route:** `/elits-sample/create/{permohonan_uji_id}`

1. **Akses Halaman Tambah Sample**
   - Dari halaman detail Permohonan Uji, klik tombol **Tambah Sample**
   - Atau akses langsung melalui menu **Sample** > **Tambah Sample Baru**

2. **Pilih Jenis Sample**
   - Pilih **Sample Type** dari daftar
   - Setiap sample type memiliki method yang tersedia

3. **Pilih Metode Pemeriksaan**
   Ada dua cara untuk memilih metode:
   
   **A. Menggunakan Paket**
   - Pilih **Packet** yang sesuai dengan sample type
   - Sistem akan otomatis memilih semua method dalam packet
   - Dapat menambah method tambahan di luar packet
   
   **B. Memilih Method Individual**
   - Pilih method satu per satu dari daftar
   - Method dikelompokkan berdasarkan laboratorium
   - Dapat memilih method dari beberapa laboratorium sekaligus

4. **Informasi Sample**
   - **Titik Pengambilan** (opsional)
     - Lokasi pengambilan sample
   - **Tanggal Sampling** (wajib)
     - Tanggal dan waktu pengambilan sample
   - **Tanggal Pengiriman** (opsional)
     - Tanggal sample dikirim ke lab
   - **Catatan Sample** (opsional)
     - Keterangan tambahan tentang sample

5. **Informasi Biaya**
   - **Biaya Sample** (otomatis dihitung)
     - Jika menggunakan packet: harga packet + method tambahan
     - Jika individual: total harga semua method
   - **Biaya Sampling** (jika sample diambil lab)
     - Default: Rp 20.000
     - Dapat disesuaikan

6. **Simpan Sample**
   - Klik tombol **Simpan**
   - Sistem akan:
     - Generate kode sample untuk setiap laboratorium
     - Membuat record sample untuk setiap laboratorium yang memiliki method
     - Menghitung total biaya

### Catatan Penting

- **Multiple Sample Support:**
  - Dapat menambahkan beberapa sample sekaligus dalam satu form
  - Setiap sample dapat memiliki jenis dan method yang berbeda
  - Sistem akan membuat sample terpisah untuk setiap kombinasi sample type + laboratorium

- **Kode Sample:**
  - Format: `S.[LAB_CODE]-[TANGGAL]-[URUTAN]`
  - Contoh: `S.KMA-20240115-0001`
  - Setiap laboratorium memiliki kode sample sendiri

- **Group ID:**
  - Sample yang dibuat bersamaan akan memiliki group_id yang sama
  - Memudahkan tracking sample yang terkait

- **Biaya:**
  - Biaya sample dihitung per laboratorium
  - Jika sample memiliki method dari 2 laboratorium, akan dibuat 2 sample dengan biaya terpisah

### Penerimaan Sample

Setelah sample ditambahkan, sample perlu diterima di laboratorium:

**Route:** `/elits-penerimaan-sample/create/{sample_id}/{laboratorium_id}`

1. **Akses Halaman Penerimaan Sample**
   - Dari halaman detail sample, klik **Terima Sample**
   - Atau akses melalui menu **Penerimaan Sample**

2. **Isi Data Penerimaan**
   - **Wadah** (wajib)
     - Pilih jenis wadah dari daftar
     - Atau isi "Lainnya" jika tidak ada di daftar
   - **Pengawet** (wajib)
     - Jenis pengawet yang digunakan
   - **Volume** (wajib)
     - Volume sample dalam wadah
   - **Unit** (wajib)
     - Satuan volume (mL, L, dll)
   - **Kondisi Sample** (wajib)
     - Baik/Rusak/Tidak Sesuai
   - **Validasi Sample** (wajib)
     - Layak/Tidak Layak untuk diperiksa

3. **Simpan Penerimaan**
   - Klik tombol **Simpan**
   - Sample akan berstatus **DITERIMA** dan siap untuk pemeriksaan

---

## Permohonan Uji Klinik

Permohonan Uji Klinik adalah proses untuk pemeriksaan klinik (pemeriksaan pasien). Proses ini berbeda dengan Permohonan Uji biasa karena menggunakan data pasien dan parameter klinik.

### Langkah-langkah Membuat Permohonan Uji Klinik

**Route:** `/elits-permohonan-uji-klinik-2/create`

#### Step 1: Pilih Tipe Dokter

1. **Akses Menu Permohonan Uji Klinik**
   - Klik menu **Permohonan Uji Klinik** di sidebar
   - Klik tombol **Tambah Permohonan Uji Klinik Baru**

2. **Pilih Tipe Dokter**
   Ada dua pilihan:
   
   **A. Dokter Lab**
   - Untuk pemeriksaan laboratorium internal
   - Tidak memerlukan rujukan dari dokter luar
   - Tidak memerlukan diagnosa
   
   **B. Dokter Rujukan**
   - Untuk pemeriksaan berdasarkan rujukan dari dokter pengirim
   - Memerlukan diagnosa
   - Memerlukan informasi dokter pengirim

3. **Lanjut ke Step 2**
   - Klik tombol **Lanjut** setelah memilih tipe dokter

#### Step 2: Data Pasien

1. **Cari atau Tambah Pasien**
   
   **A. Cari Pasien yang Sudah Ada:**
   - Klik tombol **Cari Pasien**
   - Masukkan:
     - Nama Pasien, atau
     - No. Pasien, atau
     - No. BPJS
   - Klik **Cari**
   - Pilih pasien dari hasil pencarian
   
   **B. Tambah Pasien Baru:**
   - Klik tombol **Tambah Pasien Baru**
   - Isi form:
     - **No. Pasien** (wajib, unik)
     - **Nama Pasien** (wajib)
     - **Tanggal Lahir** (wajib)
     - **Jenis Kelamin** (wajib)
     - **Alamat** (wajib)
     - **No. Telepon** (opsional)
     - **No. KTP** (opsional)
     - **No. BPJS** (opsional)
   - Klik **Simpan**

2. **Verifikasi Data Pasien**
   - Pastikan data pasien yang ditampilkan sudah benar
   - Data pasien akan otomatis terisi di form permohonan

3. **Lanjut ke Step 3**
   - Klik tombol **Lanjut** setelah memastikan data pasien

#### Step 3: Informasi Permohonan

1. **Informasi Dasar Permohonan**
   - **No. SAMPLE** (otomatis)
     - Format: `[KODE]/[TANGGAL]/[URUTAN]`
   - **TGL. REGISTER** (otomatis)
     - Tanggal dan waktu registrasi
   - **Petugas Registrasi** (opsional)
     - Pilih petugas yang menerima permohonan
   - **MODE PENGAMBILAN SAMPEL** (wajib)
     - Diambil di Lab
     - Dibawa Pelanggan Sendiri
     - Diambil Di Lokasi/Rumah
   - **BIAYA PENGAMBILAN SAMPEL** (jika mode "Diambil Di Lokasi/Rumah")
     - Default: Rp 20.000
     - Dapat disesuaikan

2. **Data Pasien (Otomatis dari Step 2)**
   - **Nama Pasien**
   - **Tanggal Lahir**
   - **Jenis Kelamin**
   - **Umur** (otomatis dihitung)
   - **Alamat**

3. **Informasi Pemeriksaan**
   - **Tanggal Pengambilan** (wajib)
     - Tanggal dan waktu pengambilan sample
   - **Nama Pengirim** (opsional)
     - Nama dokter atau petugas yang mengirim
   - **Diagnosa** (wajib jika Dokter Rujukan)
     - Diagnosa dari dokter pengirim
   - **Dokter** (opsional)
     - Pilih dokter yang menangani

4. **Informasi Tambahan**
   - **Lapangan** (opsional)
     - Centang jika pemeriksaan dilakukan di lapangan
   - **Catatan** (opsional)
     - Keterangan tambahan

5. **Simpan Permohonan Uji Klinik**
   - Klik tombol **Simpan**
   - Sistem akan:
     - Generate kode permohonan uji klinik
     - Menyimpan data pasien (jika baru)
     - Set status menjadi **PARAMETER** (menunggu penambahan parameter)

### Penambahan Parameter Pemeriksaan

Setelah Permohonan Uji Klinik dibuat, langkah selanjutnya adalah menambahkan parameter pemeriksaan.

**Route:** `/elits-permohonan-uji-klinik-2/create-permohonan-uji-klinik-parameter/{id}`

1. **Akses Halaman Parameter**
   - Dari halaman detail Permohonan Uji Klinik, klik **Tambah Parameter**
   - Atau akses melalui menu **Parameter Permohonan Uji Klinik**

2. **Pilih Parameter**
   Ada dua jenis parameter:
   
   **A. Parameter Paket**
   - Pilih dari daftar paket klinik yang tersedia
   - Paket berisi beberapa parameter sekaligus
   - Lebih ekonomis dibanding individual
   
   **B. Parameter Individual**
   - Pilih parameter satu per satu
   - Dapat memilih parameter dari berbagai jenis
   - Lebih fleksibel

3. **Informasi Parameter**
   - **Jenis Parameter** (wajib)
     - Pilih dari daftar parameter klinik
   - **Tipe Parameter** (wajib)
     - **P** = Paket
     - **I** = Individual
   - **Satuan Parameter** (jika paket)
     - Pilih satuan untuk paket
   - **Harga Parameter** (otomatis)
     - Harga akan dihitung berdasarkan jenis dan tipe

4. **Simpan Parameter**
   - Klik tombol **Simpan**
   - Sistem akan:
     - Menyimpan parameter ke permohonan uji klinik
     - Menghitung total harga
     - Mengubah status menjadi **ANALIS** (siap untuk analisis)

### Catatan Penting

- **Status Permohonan Uji Klinik:**
  - **PARAMETER**: Menunggu penambahan parameter
  - **ANALIS**: Siap untuk analisis
  - **VERIFIKASI**: Menunggu verifikasi hasil
  - **SELESAI**: Pemeriksaan selesai

- **Parameter Klinik:**
  - Parameter klinik berbeda dengan method pemeriksaan biasa
  - Parameter klinik disesuaikan dengan kebutuhan pemeriksaan medis
  - Setiap parameter memiliki harga tersendiri

- **Integrasi Satu Sehat:**
  - Sistem dapat terintegrasi dengan Satu Sehat (jika dikonfigurasi)
  - Data pasien dan hasil dapat dikirim ke Satu Sehat
  - Memerlukan konfigurasi khusus

---

## Alur Kerja Lengkap

Berikut adalah alur kerja lengkap dari awal hingga akhir:

### Alur 1: Permohonan Uji (Non-Klinik)

```
1. Setup Master Data
   ├── Customer
   ├── Sample Type
   ├── Laboratorium
   ├── Method
   ├── Packet
   ├── Unit
   └── Container

2. Buat Permohonan Uji
   ├── Pilih/Tambah Customer
   ├── Isi Data Dasar
   ├── Tentukan Mode Pengiriman Sample
   └── Simpan Permohonan Uji

3. Tambah Sample
   ├── Pilih Sample Type
   ├── Pilih Method (Paket atau Individual)
   ├── Isi Informasi Sample
   └── Simpan Sample

4. Penerimaan Sample
   ├── Terima Sample di Laboratorium
   ├── Isi Data Penerimaan
   └── Validasi Sample

5. Pemeriksaan
   ├── Input Hasil Pemeriksaan
   ├── Verifikasi Hasil
   ├── Pengesahan Hasil
   └── Release Hasil
```

### Alur 2: Permohonan Uji Klinik

```
1. Setup Master Data
   ├── Pasien (atau tambah saat membuat permohonan)
   ├── Parameter Klinik
   ├── Paket Klinik
   └── Dokter (jika diperlukan)

2. Buat Permohonan Uji Klinik
   ├── Pilih Tipe Dokter
   ├── Cari/Tambah Pasien
   ├── Isi Informasi Permohonan
   └── Simpan Permohonan

3. Tambah Parameter
   ├── Pilih Parameter (Paket atau Individual)
   ├── Tentukan Harga
   └── Simpan Parameter

4. Pengambilan Sample
   ├── Ambil Sample dari Pasien
   ├── Catat Data Pengambilan
   └── Kirim ke Laboratorium

5. Analisis
   ├── Input Hasil Analisis
   ├── Verifikasi Hasil
   ├── Validasi Dokter
   └── Release Hasil
```

---

## Menambah Parameter di Panel Pemeriksaan Klinik

Parameter adalah jenis-jenis tes yang akan dilakukan pada pasien (contoh: Gula Darah, Kolesterol, Hemoglobin). Anda **wajib** menambahkan parameter sebelum pemeriksaan dapat dilanjutkan.

> ⚠️ **Perhatian:** Parameter baru bisa ditambahkan setelah permohonan tersimpan dan statusnya **PARAMETER**.

### Langkah-langkah:

1. **Buka daftar Permohonan Uji Klinik** di sidebar
2. Cari nama pasien → klik baris datanya untuk buka halaman detail
3. Klik tombol **"Tambah Parameter"** atau **"+ Parameter"**
4. **Pilih cara penambahan:**

   | Cara | Keterangan | Kapan Digunakan |
   |---|---|---|
   | **A. Paket** | Memilih satu paket berisi beberapa parameter | Jika ada paket yang sesuai |
   | **B. Individual** | Memilih parameter satu per satu | Jika hanya butuh 1-2 tes tertentu |

5. **Cara A (Paket):**
   - Pilih **Tipe Parameter: P (Paket)**
   - Pilih nama paket dari dropdown (contoh: "Paket Darah Lengkap")
   - Harga otomatis terisi → klik **Simpan**

6. **Cara B (Individual):**
   - Pilih **Tipe Parameter: I (Individual)**
   - Pilih nama parameter dari dropdown (contoh: "Gula Darah Puasa")
   - Harga otomatis terisi → klik **Tambahkan**
   - Ulangi untuk setiap parameter yang diperlukan → klik **Simpan**

7. Status permohonan berubah otomatis dari **PARAMETER** → **ANALIS**

### Arti Status Permohonan Klinik:

| Status | Artinya | Tindakan Selanjutnya |
|---|---|---|
| **PARAMETER** | Permohonan baru, belum ada parameter | Tambahkan parameter pemeriksaan |
| **ANALIS** | Siap dianalisis | Petugas lab input hasil pemeriksaan |
| **VERIFIKASI** | Menunggu verifikasi dokter | Dokter/kepala lab verifikasi hasil |
| **SELESAI** | Pemeriksaan selesai | Cetak hasil untuk diserahkan ke pasien |

### Cara Hapus Parameter yang Salah:

1. Buka halaman detail Permohonan Uji Klinik
2. Di daftar parameter, klik tombol **Hapus** (🗑️) di baris yang salah
3. Konfirmasi penghapusan → parameter dihapus, harga total diperbarui otomatis
4. Tambahkan ulang parameter yang benar

---

## Cara Mengubah Harga

> ⚠️ **Catatan:** Perubahan harga hanya berlaku untuk permohonan **baru**. Permohonan yang sudah ada tidak terpengaruh.

### A. Mengubah Harga Method (Non-Klinik)

1. Di sidebar klik **Master Data** → **Method**
2. Cari method menggunakan kotak pencarian
3. Klik tombol **Edit** (✏️) di baris method yang bersangkutan
4. Ubah kolom **Harga Total Method** (ini yang paling penting)
   - Kolom opsional lain: Harga Bahan, Harga Sarana, Harga Jasa
5. Klik **Simpan** → muncul pesan sukses

### B. Mengubah Harga Paket (Non-Klinik)

1. Di sidebar klik **Master Data** → **Packet**
2. Cari nama paket
3. Klik **Edit** → ubah kolom **Harga Packet**
4. Klik **Simpan**

### C. Mengubah Harga Parameter Klinik

1. Di sidebar klik **Master Data** → **Jenis Parameter Klinik**
2. Cari parameter → klik **Edit**
3. Ubah kolom **Harga** atau **Tarif**
4. Klik **Simpan**

### D. Mengubah Harga Paket Parameter Klinik

1. Di sidebar klik **Master Data** → **Paket Parameter Klinik**
2. Cari paket → klik **Edit**
3. Ubah kolom **Harga Paket**
4. Klik **Simpan**

---

## Master Data Klinik

### Menambah Jenis Parameter Klinik Baru

1. Di sidebar klik **Master Data** → **Jenis Parameter Klinik**
2. Klik **"+ Tambah Baru"**
3. Isi form:

   | Kolom | Contoh | Wajib? |
   |---|---|---|
   | Nama Parameter | `Gula Darah Puasa` | Ya |
   | Singkatan/Kode | `GDP` | Tidak |
   | Satuan | `mg/dL` | Tidak |
   | Nilai Normal Min | `70` | Tidak |
   | Nilai Normal Max | `100` | Tidak |
   | Harga | `25000` | Ya |
   | Laboratorium | `Klinik (KLI)` | Ya |

4. Klik **Simpan**

### Membuat Paket Parameter Klinik

1. Di sidebar klik **Master Data** → **Paket Parameter Klinik**
2. Klik **"+ Tambah Paket Baru"**
3. Isi Nama Paket, Kode Paket, Harga Paket, dan Deskripsi
4. Tambahkan parameter ke paket satu per satu
5. Klik **Simpan Paket**

---

## Cara Edit & Hapus Data

### Cara Mengedit Data

1. Buka menu yang berisi data yang ingin diedit
2. Cari baris data menggunakan kotak pencarian
3. Klik tombol **Edit** (✏️)
4. Ubah kolom yang diperlukan
5. Klik **Simpan** / **Update**

> ⚠️ **Jangan ubah kode unik** (kode customer, kode method, dll.) jika data sudah digunakan di permohonan uji aktif.

### Cara Menghapus Data

1. Cari data yang ingin dihapus
2. Klik tombol **Hapus** (🗑️)
3. Baca kotak konfirmasi dengan teliti
4. Klik **Ya, Hapus** jika yakin, atau **Batal** jika tidak jadi

> 🚫 **Data yang TIDAK boleh dihapus sembarangan:**
> - Method yang masih digunakan di paket aktif
> - Customer yang masih punya permohonan aktif
> - Parameter klinik yang sudah ada hasil pemeriksaannya
> - Pasien yang masih punya permohonan aktif

---

## Input Hasil Pemeriksaan Klinik

> 📋 Langkah ini dilakukan oleh analis/petugas laboratorium. Status permohonan harus **ANALIS**.

1. Dari menu **Permohonan Uji Klinik**, cari pasien dengan status **ANALIS**
2. Buka halaman detail pasien
3. Klik tombol **"Input Hasil"** atau **"Isi Hasil Pemeriksaan"**
4. Isi nilai hasil untuk setiap parameter (contoh: Gula Darah Puasa = `95`)
5. Klik **Simpan**
6. Klik **"Kirim ke Verifikasi"** → status berubah menjadi **VERIFIKASI**

---

## Tips Penggunaan

### Master Data
- Periksa ulang isian sebelum klik Simpan, terutama nama dan kode yang harus unik
- Koordinasikan dengan kepala laboratorium sebelum mengubah harga
- Jangan hapus data yang masih digunakan — nonaktifkan dulu jika perlu

### Permohonan Uji
- Permohonan harus disimpan terlebih dahulu sebelum bisa menambahkan sample
- Total harga dihitung otomatis dari method/paket yang dipilih — jangan diubah manual kecuali ada alasan khusus
- Segera update status pembayaran ke "Lunas" setelah pelanggan membayar

### Permohonan Uji Klinik
- Nama, tanggal lahir, dan jenis kelamin pasien harus benar — berpengaruh pada nilai normal
- Periksa total harga sebelum menyimpan permohonan
- Jangan lewatkan verifikasi dokter sebelum hasil diserahkan ke pasien

---

## Troubleshooting

| Masalah | Kemungkinan Penyebab & Solusi |
|---|---|
| Tidak bisa login | Cek Caps Lock • Ketik ulang username/password • Hubungi admin untuk reset password |
| Menu tidak muncul | Akun tidak punya izin → Hubungi admin untuk meminta akses |
| Customer tidak muncul di dropdown | Belum ditambahkan → Tambahkan di menu Customer • Atau sudah dihapus → Hubungi admin |
| Method tidak muncul saat memilih sample type | Belum dikaitkan dengan sample type/lab → Hubungi admin |
| Data tidak bisa disimpan | Ada kolom wajib yang kosong (berwarna merah/berbintang) → Lengkapi dulu |
| Parameter klinik tidak bisa ditambahkan | Status permohonan bukan PARAMETER/ANALIS • Atau akun tidak punya akses |
| Harga tidak berubah setelah diedit | Pastikan tombol Simpan sudah diklik • Refresh halaman • Harga baru hanya untuk transaksi baru |
| Halaman loading lama | Periksa koneksi internet • Refresh halaman (F5) • Logout-login kembali |
| Data terhapus ingin dikembalikan | Segera hubungi admin — data biasanya masih bisa dipulihkan |

---

## Kontak dan Support

Untuk pertanyaan atau bantuan lebih lanjut, silakan hubungi:
- **Email**: support@magelang-labkes.com
- **Telepon**: (0293) xxxxxx
- **Alamat**: Laboratorium Kesehatan Magelang

**Saat menghubungi, sampaikan:** nama menu yang bermasalah, langkah yang sudah dilakukan, dan pesan error yang muncul di layar.

---

## Changelog

### Versi 2.0 (Mei 2026)
- Penambahan panduan menambah parameter di panel pemeriksaan klinik (langkah spesifik)
- Penambahan panduan cara mengubah harga (Method, Paket, Parameter Klinik)
- Penambahan panduan cara edit & hapus data
- Penambahan panduan input hasil pemeriksaan klinik
- Penambahan panduan Master Data Klinik (Jenis Parameter, Paket Parameter)
- Perbaikan Troubleshooting: tabel lebih lengkap dan mudah dibaca
- Perbaikan Tips: lebih spesifik dan praktis
- Penyesuaian bahasa agar lebih mudah dipahami semua pengguna

### Versi 1.0 (2024-01-15)
- Dokumentasi awal
- Alur kerja Permohonan Uji
- Alur kerja Permohonan Uji Klinik
- Master data setup

---

**Panduan ini dibuat untuk membantu semua pengguna memahami cara menggunakan sistem laboratorium. Dokumentasi diperbarui secara berkala sesuai perkembangan sistem.**

