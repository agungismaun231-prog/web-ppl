-- --------------------------------------------------------
-- Database: db_tiket
-- Aplikasi: Jalanbae / tiket-travel
-- Struktur sesuai dengan kode Laravel aplikasi
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS db_tiket
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_tiket;

DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS keluhan;
DROP TABLE IF EXISTS pesan;
DROP TABLE IF EXISTS jadwal;
DROP TABLE IF EXISTS kendaraan;
DROP TABLE IF EXISTS kota_tujuan;
DROP TABLE IF EXISTS kota_asal;
DROP TABLE IF EXISTS operator_loket;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS admin;

CREATE TABLE admin (
  id_admin INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_admin),
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE member (
  id_member INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_member),
  UNIQUE KEY uq_member_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operator_loket (
  id_opr INT NOT NULL AUTO_INCREMENT,
  user VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_opr),
  UNIQUE KEY uq_operator_user (user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kota_asal (
  id_kota_asal INT NOT NULL AUTO_INCREMENT,
  nm_kota_asal VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_kota_asal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kota_tujuan (
  id_kota_tujuan INT NOT NULL AUTO_INCREMENT,
  nm_kota_tujuan VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_kota_tujuan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kendaraan (
  id_mobil INT NOT NULL AUTO_INCREMENT,
  jenis_mobil VARCHAR(100) NOT NULL,
  nomor_polisi VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_mobil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jadwal (
  id_jadwal INT NOT NULL AUTO_INCREMENT,
  id_kota_asal INT NOT NULL,
  id_kota_tujuan INT NOT NULL,
  harga INT NOT NULL,
  tgl_berangkat DATE NOT NULL,
  jam_berangkat TIME NOT NULL,
  id_mobil INT NOT NULL,
  PRIMARY KEY (id_jadwal),
  KEY idx_jadwal_kota (id_kota_asal, id_kota_tujuan),
  KEY idx_jadwal_tanggal (tgl_berangkat),
  CONSTRAINT fk_jadwal_asal FOREIGN KEY (id_kota_asal) REFERENCES kota_asal (id_kota_asal),
  CONSTRAINT fk_jadwal_tujuan FOREIGN KEY (id_kota_tujuan) REFERENCES kota_tujuan (id_kota_tujuan),
  CONSTRAINT fk_jadwal_mobil FOREIGN KEY (id_mobil) REFERENCES kendaraan (id_mobil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pesan (
  id_pesan INT NOT NULL AUTO_INCREMENT,
  nomor_kursi VARCHAR(10) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Belum Bayar',
  id_jadwal INT NOT NULL,
  id_member INT NOT NULL,
  PRIMARY KEY (id_pesan),
  KEY idx_pesan_jadwal (id_jadwal),
  KEY idx_pesan_member (id_member),
  KEY idx_pesan_status (status),
  UNIQUE KEY uq_pesan_jadwal_kursi (id_jadwal, nomor_kursi),
  CONSTRAINT fk_pesan_jadwal FOREIGN KEY (id_jadwal) REFERENCES jadwal (id_jadwal),
  CONSTRAINT fk_pesan_member FOREIGN KEY (id_member) REFERENCES member (id_member)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE keluhan (
  id_keluhan INT NOT NULL AUTO_INCREMENT,
  id_member INT NOT NULL,
  j_keluhan VARCHAR(50) NOT NULL,
  d_keluhan TEXT NOT NULL,
  PRIMARY KEY (id_keluhan),
  KEY idx_keluhan_member (id_member),
  CONSTRAINT fk_keluhan_member FOREIGN KEY (id_member) REFERENCES member (id_member)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembayaran (
  id_pembayaran BIGINT NOT NULL AUTO_INCREMENT,
  id_pesan INT NOT NULL,
  no_resi VARCHAR(50) NOT NULL,
  no_rek VARCHAR(32) NOT NULL,
  tgl_transfer DATE NOT NULL,
  jam_transfer TIME NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Dalam Proses',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_pembayaran),
  UNIQUE KEY uq_pembayaran_id_pesan (id_pesan),
  UNIQUE KEY uq_pembayaran_no_resi (no_resi),
  KEY idx_pembayaran_status (status),
  CONSTRAINT fk_pembayaran_pesan FOREIGN KEY (id_pesan) REFERENCES pesan (id_pesan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data contoh / master data
-- --------------------------------------------------------

INSERT INTO admin (email, password, nama_lengkap) VALUES
  ('admin@jalanbae.test', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Jalanbae');

INSERT INTO member (username, password, nama) VALUES
  ('member01', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Member Uji'),
  ('member02', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Member Lain');

INSERT INTO operator_loket (user, password, nama) VALUES
  ('loket1', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Loket 1');

INSERT INTO kota_asal (id_kota_asal, nm_kota_asal) VALUES
  (1, 'Bandar Lampung'),
  (2, 'Metro'),
  (3, 'Pringsewu');

INSERT INTO kota_tujuan (id_kota_tujuan, nm_kota_tujuan) VALUES
  (1, 'Metro'),
  (2, 'Pringsewu'),
  (3, 'Bandar Lampung');

INSERT INTO kendaraan (id_mobil, jenis_mobil, nomor_polisi) VALUES
  (1, 'Avanza', 'BE 1234 AB'),
  (2, 'Xenia', 'BE 5678 CD');

INSERT INTO jadwal (id_jadwal, id_kota_asal, id_kota_tujuan, harga, tgl_berangkat, jam_berangkat, id_mobil) VALUES
  (1, 1, 1, 60000, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00', 1),
  (2, 1, 2, 70000, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:15:00', 2),
  (3, 2, 3, 65000, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:30:00', 1);

INSERT INTO pesan (id_pesan, nomor_kursi, status, id_jadwal, id_member) VALUES
  (1, '2', 'Belum Bayar', 1, 1),
  (2, '3', 'Lunas', 1, 2);

INSERT INTO keluhan (id_keluhan, id_member, j_keluhan, d_keluhan) VALUES
  (1, 1, 'Kendaraan terlambat', 'Travel belum tiba pada waktu yang dijadwalkan.');

INSERT INTO pembayaran (id_pembayaran, id_pesan, no_resi, no_rek, tgl_transfer, jam_transfer, status) VALUES
  (1, 2, 'REF-TEST-1', '1234567890123456', CURDATE(), '09:30:00', 'Lunas');

-- --------------------------------------------------------
-- Untuk login, password yang dimasukkan di sini memakai contoh hash default Laravel.
-- Jika di login pertama kali, aplikasi akan otomatis mengubah password plain text
-- menjadi hash jika belum terenkripsi.
-- --------------------------------------------------------
