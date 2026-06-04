# INVENDOR - Sistem Manajemen Gudang

## Kelompok 7:
1. Adam Adelwan
2. Ahmad Ghozi Al Khifari
3. Nabila Rambu Anarki
4. Syarifah Aisyah
5. Tiara Nazwa Andira A

## Tentang Aplikasi
INVENDOR adalah aplikasi berbasis web yang dibuat untuk mempermudah pengelolaan barang di dalam gudang. Aplikasi ini membantu mencatat data barang, memantau jumlah stok, dan melihat riwayat barang keluar-masuk secara rapi dan otomatis.

## Fungsi Utama Aplikasi:
* **Halaman Dashboard & Peringatan Stok:** Menampilkan ringkasan kondisi gudang saat ini. Sistem akan otomatis memunculkan peringatan berwarna jingga jika ada barang yang stoknya sudah mau habis (kurang dari 5 buah).
* **Mengelola Data Barang & Supplier (Khusus Admin):** Mempermudah Admin untuk menambah, mengubah, atau menghapus data barang, kategori, serta data supplier (pemasok barang).
* **Mencatat Riwayat Otomatis:** Setiap kali ada barang masuk atau keluar, sistem akan langsung mencatat waktunya secara otomatis agar tidak ada barang yang hilang tanpa kejelasan.
* **Cetak Struk Riwayat:** Membantu Admin maupun Staf untuk mencetak laporan riwayat keluar-masuk barang secara instan ke dalam bentuk cetakan fisik atau file PDF.

## Panduan Instalasi & Dependensi:
1. Nyalakan modul **Apache** dan **MySQL** pada XAMPP Control Panel.
2. Ekstrak folder proyek ini dan pindahkan ke dalam direktori `C:/xampp/htdocs/`.
3. Buka browser, masuk ke `localhost/phpmyadmin`, lalu buat database baru bernama `invendor`.
4. Import file database `invendor.sql` ke dalam database tersebut.
5. Akses aplikasi melalui URL: `http://localhost/invendor` di browser Anda.