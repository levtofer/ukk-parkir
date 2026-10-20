# Aplikasi Parkir (Paket 2)

Aplikasi parkir berbasis web untuk Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak, dibuat dengan **PHP native** dan **MySQL**.

Aplikasi memiliki tiga level pengguna (Admin, Petugas, Owner) dan mendukung parkir **member** (mendapat diskon) maupun **non-member**. Biaya dihitung otomatis per jam, struk dapat dicetak, dan owner dapat melihat rekap pendapatan per rentang tanggal.

- Nama: Hasan Asykari
- Kelas: XII RPL 1
- Database: `db_parkir`

---

## Kebutuhan

* XAMPP atau Laragon (Apache + MySQL/MariaDB)
* PHP dengan ekstensi `mysqli`
* Browser modern (misalnya Google Chrome)

---

## Cara Menjalankan

1. **Letakkan folder proyek** di `htdocs/` (XAMPP) atau `www/` (Laragon).
2. **Jalankan** Apache dan MySQL.
3. **Buat database** bernama `db_parkir`, lalu **import** file `xii_rpl1_hasan_asykari_parkir.sql` (lewat phpMyAdmin).
4. **Periksa konfigurasi** koneksi di `config/koneksi.php` (host, username, password, nama database). Bawaan: `localhost`, `root`, tanpa password.
5. **Buka** aplikasi di browser:

```text
http://localhost/<nama-folder-proyek>/
```

Ganti `<nama-folder-proyek>` dengan nama folder yang dipakai di server lokal. `BASE_URL` ditentukan otomatis sehingga nama folder bebas.

---

## Akun Bawaan

| Role    | Username   | Password     |
| ------- | ---------- | ------------ |
| Admin   | `admin`    | `admin123`   |
| Petugas | `petugas1` | `petugas123` |
| Owner   | `owner`    | `owner123`   |

Kata sandi disimpan dalam bentuk hash (`password_hash`).

---

## Data Awal

Database hanya berisi tiga akun di atas. Sebelum petugas dapat melakukan check-in, Admin perlu membuat:

1. **Area Parkir**: nama area, kapasitas, dan peruntukan (Member, Non-member, atau Campuran).
2. **Tarif Parkir**: jenis kendaraan, tarif per jam, dan diskon member (0 sampai 100%).
3. **Member** (opsional): hanya jika ada parkir member. Area member harus berperuntukan Member atau Campuran.

Jika hanya melayani non-member, member tidak perlu dibuat. Area untuk non-member harus berperuntukan Non-member atau Campuran.

---

## Fitur Berdasarkan Hak Akses

| Fitur                              | Admin | Petugas | Owner |
| ---------------------------------- | :---: | :-----: | :---: |
| Login dan Logout                   |   V   |    V    |   V   |
| CRUD User (petugas)                |   V   |         |       |
| CRUD Tarif Parkir                  |   V   |         |       |
| CRUD Area Parkir                   |   V   |         |       |
| CRUD Member                        |   V   |         |       |
| Akses Log Aktivitas                |   V   |         |       |
| Transaksi (check-in dan check-out) |       |    V    |       |
| Cetak struk parkir                 |       |    V    |       |
| Rekap transaksi per rentang tanggal|       |         |   V   |

Catatan: form tambah user hanya membuat akun dengan role `petugas`.

---

## Struktur Folder

```text
admin/        area, tarif, user, member, log (CRUD dan log aktivitas)
petugas/      transaksi (index, checkin, checkout) dan struk.php
owner/        rekap.php
includes/     auth.php, helper.php, sidebar.php, footer.php
config/       koneksi.php
assets/       css, gambar, dan aset lain
login.php     halaman login
logout.php    logout dengan konfirmasi
index.php     pengarah ke login atau dashboard sesuai role
*.sql         struktur database dan akun bawaan
```

---

## Struktur Database

| Tabel              | Fungsi                                                                                  |
| ------------------ | --------------------------------------------------------------------------------------- |
| `tb_user`          | Akun pengguna dan role (`admin`, `petugas`, `owner`)                                    |
| `tb_area_parkir`   | Area, kapasitas, peruntukan, dan jumlah terisi                                          |
| `tb_tarif`         | Tarif per jam dan diskon member per jenis kendaraan                                     |
| `tb_member`        | Data member beserta area dan status (`aktif` atau `suspended`)                          |
| `tb_parkir`        | Kendaraan yang sedang parkir dan riwayat kunjungan                                      |
| `tb_transaksi`     | Hasil akhir check-out: waktu keluar, durasi, biaya, dan snapshot data untuk struk       |
| `tb_log_aktivitas` | Riwayat aktivitas pengguna                                                              |

### Pemetaan terhadap skema soal

| Skema soal         | Aplikasi           | Keterangan                                                                                  |
| ------------------ | ------------------ | ------------------------------------------------------------------------------------------- |
| `tb_kendaraan`     | `tb_member`        | CRUD Kendaraan diwujudkan sebagai CRUD Member. Data kendaraan dicatat di `tb_parkir`.       |
| `tb_transaksi`     | `tb_parkir`        | Menyimpan kendaraan yang sedang parkir dan riwayat kunjungan.                               |
| (tambahan)         | `tb_transaksi`     | Hasil final check-out dalam bentuk snapshot.                                                |
| `tb_tarif`, `tb_user`, `tb_area_parkir`, `tb_log_aktivitas` | sama | Dengan tambahan kolom (diskon member, peruntukan area, hash password). |

Nilai `tipe_parkir` dan peruntukan area memakai `non_member` (dengan garis bawah). Kendaraan non-member disimpan dengan `id_member = NULL`.

---

## Aturan Sistem

* Nama area dan jenis kendaraan pada tarif harus unik, tanpa membedakan huruf besar atau kecil.
* Kapasitas area berupa bilangan bulat 1 sampai 1.000.000.
* Area tidak dapat dihapus jika masih memiliki member atau kendaraan yang belum check-out.
* Kapasitas area tidak dapat diturunkan di bawah jumlah kendaraan yang sedang parkir.
* Peruntukan area yang punya member tidak dapat diubah ke Non-member. Peruntukan area yang dipakai kendaraan non-member tidak dapat diubah ke Member.
* Member tidak dapat dihapus jika kendaraannya belum check-out. Riwayat parkir tetap tersimpan saat member dihapus.
* Member `suspended` tidak dapat check-in, tetapi kendaraan yang sudah di dalam tetap dapat check-out.
* Tarif tidak dapat diubah atau dihapus selama dipakai kendaraan yang sedang parkir.
* Petugas tidak dapat dinonaktifkan jika masih memiliki kendaraan yang belum check-out.
* Plat nomor dinormalisasi (huruf besar, tanpa spasi), 3 sampai 15 karakter huruf atau angka, dan tidak boleh sedang parkir dua kali.
* Member dan non-member memakai kapasitas area yang sama. Check-in ditolak jika area penuh.
* Check-in dan check-out berjalan dalam transaksi database agar data tetap konsisten, dan check-out ganda ditolak.

---

## Perhitungan Biaya

```text
durasi          = ceil(lama parkir dalam jam), minimal 1 jam
tarif dikenakan = tarif dasar x (1 - diskon / 100), dibulatkan ke rupiah   (member)
tarif dikenakan = tarif dasar                                              (non-member)
biaya total     = durasi x tarif dikenakan
```

Data tarif, diskon, nama member, area, dan petugas disimpan sebagai **snapshot** di `tb_transaksi`, sehingga struk lama tidak berubah walaupun data master diubah.

---

## Alur Singkat

**Check-in**: petugas memilih member atau non-member, mengisi plat, warna, dan jenis kendaraan. Area member mengikuti data member, sedangkan area non-member dipilih petugas. Sistem memeriksa kapasitas, plat ganda, tarif, dan status member, lalu menyimpan kendaraan dengan status `masuk`.

**Check-out**: petugas memilih kendaraan, memeriksa halaman konfirmasi (durasi, tarif, diskon, biaya), lalu mengonfirmasi. Sistem menyimpan transaksi, mengubah status menjadi `keluar`, memperbarui jumlah terisi, dan membuka struk untuk dicetak.

**Rekap Owner**: pilih rentang tanggal (bawaan bulan ini). Tanggal tidak valid kembali ke bulan ini, dan tanggal awal yang lebih besar dari akhir ditukar.

---

## Batasan yang Diketahui

* Dua check-in bersamaan dengan plat yang sama pada area berbeda masih dapat lolos jika terjadi pada waktu yang sama. Kapasitas tiap area tetap diperiksa.
* Daftar user dan tarif belum memakai paginasi.
* Log aktivitas dibatasi pada 200 data terbaru.
* Akun admin dan owner baru belum dapat dibuat dari aplikasi (hanya petugas).

---

## Dokumentasi

ERD, diagram, flowchart dan pseudocode, dokumentasi fungsi dan modul, debugging, hasil pengujian black box, dan laporan evaluasi singkat ada di file laporan (`LAPORAN_UKK_Aplikasi_Parkir_Hasan_Asykari.docx`).
