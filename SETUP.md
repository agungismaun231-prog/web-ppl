# Setup Lokal

1. Jalankan MySQL dari XAMPP.
2. Buat database `db_tiket`, lalu import `../database/db_tiket.sql` lewat phpMyAdmin.
3. Pastikan konfigurasi `.env` menunjuk ke MySQL `db_tiket` (kredensial default XAMPP sudah diisi).
4. Dari folder `laravel-app`, jalankan `php artisan migrate`. Ini membuat tabel pendukung Laravel dan pembayaran serta memperlebar kolom password pada tabel akun lama.
5. Jalankan `php artisan serve`, lalu buka URL yang ditampilkan.

Login memakai kolom `email` untuk admin, `username` untuk member, dan `user` untuk operator loket. Password plaintext akan diubah menjadi hash setelah login pertama yang berhasil.

Member dapat memesan kursi dari jadwal, mengirim referensi transfer, dan melihat status transaksi pada menu **Transaksi**. Admin dan operator loket dapat memeriksa bukti transfer lalu mengonfirmasi pembayaran; admin juga dapat menambahkan jadwal baru. Pembayaran saat ini dikonfirmasi secara manual dan belum terhubung ke payment gateway.

Jalankan tes dengan `php artisan test` dari folder `laravel-app`.