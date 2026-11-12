<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cari jadwal travel dan pesan perjalanan Anda dengan mudah.">
    <title>Rute Travel - Cari Perjalanan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}" aria-label="Rute Travel, beranda">
            <span class="brand-mark" aria-hidden="true">R</span>
            <span>rute<span class="brand-light">travel</span></span>
        </a>
        <nav class="navigation" aria-label="Navigasi utama">
            <a class="nav-active" href="{{ route('home') }}">Cari tiket</a>
            <a href="#jadwal">Jadwal</a>
            @if (session('legacy_account.role') === 'member')
                <a href="{{ route('transactions.index') }}">Transaksi</a>
            @elseif (session('legacy_account.role') === 'admin')
                <a href="{{ route('schedules.create') }}">Tambah jadwal</a>
            @elseif (session('legacy_account.role') === 'operator_loket')
                <a href="{{ route('payments.index') }}">Pembayaran</a>
            @endif
            <a href="#tentang">Tentang</a>
        </nav>
        @if (session()->has('legacy_account'))
            <a class="contact-link" href="{{ route('account') }}">Akun saya <span aria-hidden="true">↗</span></a>
        @else
            <a class="contact-link" href="{{ route('login') }}">Masuk <span aria-hidden="true">↗</span></a>
        @endif
    </header>

    <main>
        @if (session('success'))
            <p class="notice notice-success" role="status">{{ session('success') }}</p>
        @endif
        <section class="intro" aria-labelledby="page-title">
            <div class="intro-copy">
                <p class="eyebrow"><span class="eyebrow-dot"></span> Perjalanan antarkota, lebih mudah</p>
                <h1 id="page-title">Berangkat nyaman.<br><span>Tiba dengan tenang.</span></h1>
                <p class="intro-description">Temukan jadwal travel yang pas untuk perjalananmu di Lampung.</p>
            </div>
            <div class="intro-photo" role="img" aria-label="Bus travel melaju di jalan antarkota">
                <span class="photo-caption">Lampung, lebih dekat</span>
            </div>
        </section>

        <section class="search-section" aria-label="Pencarian jadwal">
            <form class="search-form" action="{{ route('home') }}" method="get">
                <label class="field">
                    <span class="field-label">Kota asal</span>
                    <select name="asal">
                        <option value="">Pilih kota asal</option>
                        @foreach ($origins as $city)
                            <option value="{{ $city->id }}" @selected((string) ($filters['asal'] ?? '') === (string) $city->id)>{{ $city->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="field-divider" aria-hidden="true"></span>
                <label class="field">
                    <span class="field-label">Kota tujuan</span>
                    <select name="tujuan">
                        <option value="">Pilih kota tujuan</option>
                        @foreach ($destinations as $city)
                            <option value="{{ $city->id }}" @selected((string) ($filters['tujuan'] ?? '') === (string) $city->id)>{{ $city->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <span class="field-divider" aria-hidden="true"></span>
                <label class="field date-field">
                    <span class="field-label">Tanggal berangkat</span>
                    <input type="date" name="tanggal" min="{{ today()->toDateString() }}" value="{{ $filters['tanggal'] ?? '' }}">
                </label>
                <button class="search-button" type="submit"><span aria-hidden="true">⌕</span> Cari jadwal</button>
            </form>
        </section>

        <section class="schedule-section" id="jadwal" aria-labelledby="schedule-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow section-eyebrow">Pilih perjalananmu</p>
                    <h2 id="schedule-title">Jadwal keberangkatan</h2>
                </div>
                <p class="result-count">{{ $schedules->count() }} perjalanan tersedia</p>
            </div>

            @if ($schedules->isNotEmpty())
                <div class="schedule-list">
                    @foreach ($schedules as $schedule)
                        <article class="schedule-row">
                            <div class="departure-time">
                                <strong>{{ substr($schedule->jam_berangkat, 0, 5) }}</strong>
                                <span>{{ \Carbon\Carbon::parse($schedule->tgl_berangkat)->format('d/m/Y') }}</span>
                            </div>
                            <div class="route">
                                <div class="route-city"><span class="route-dot origin-dot"></span><strong>{{ $schedule->nm_kota_asal }}</strong></div>
                                <span class="route-line" aria-hidden="true"></span>
                                <div class="route-city"><span class="route-dot destination-dot"></span><strong>{{ $schedule->nm_kota_tujuan }}</strong></div>
                            </div>
                            <div class="vehicle">
                                <span class="vehicle-label">Kendaraan</span>
                                <strong>{{ $schedule->jenis_mobil ?: 'Travel' }}</strong>
                            </div>
                            <div class="availability {{ (int) $schedule->kursi_tersedia === 0 ? 'sold-out' : '' }}">
                                <strong>{{ (int) $schedule->kursi_tersedia }}</strong>
                                <span>kursi tersisa</span>
                            </div>
                            <div class="fare">
                                <span>Mulai dari</span>
                                <strong>Rp{{ number_format((int) $schedule->harga, 0, ',', '.') }}</strong>
                            </div>
                            @if (session('legacy_account.role') === 'member' && $schedule->available_seats)
                                <form class="booking-form" method="post" action="{{ route('transactions.store') }}">
                                    @csrf
                                    <input type="hidden" name="id_jadwal" value="{{ $schedule->id_jadwal }}">
                                    <label for="seat-{{ $schedule->id_jadwal }}">Kursi</label>
                                    <select id="seat-{{ $schedule->id_jadwal }}" name="nomor_kursi" required>
                                        @foreach ($schedule->available_seats as $seat)
                                            <option value="{{ $seat }}">{{ $seat }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit">Pesan tiket</button>
                                </form>
                            @elseif (!session()->has('legacy_account') && (int) $schedule->kursi_tersedia > 0)
                                <a class="book-link" href="{{ route('login') }}">Masuk untuk pesan</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <span class="empty-icon" aria-hidden="true">↗</span>
                    <div>
                        <h3>{{ $activeScheduleCount > 0 ? 'Jadwal belum ditemukan' : 'Jadwal baru segera hadir' }}</h3>
                        <p>{{ $activeScheduleCount > 0 ? 'Coba ubah kota atau tanggal perjalanan untuk melihat jadwal lain.' : 'Belum ada jadwal keberangkatan mendatang. Silakan cek kembali setelah jadwal diperbarui.' }}</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="trust-strip" id="tentang" aria-label="Informasi layanan">
            <div><span class="trust-number">01</span><strong>Rute pilihan</strong><span>Menjangkau berbagai kota di Lampung</span></div>
            <div><span class="trust-number">02</span><strong>Jadwal jelas</strong><span>Informasi keberangkatan dalam satu tempat</span></div>
            <div><span class="trust-number">03</span><strong>Perjalanan nyaman</strong><span>Kendaraan travel untuk perjalananmu</span></div>
        </section>
    </main>

    <footer class="footer">
        <a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <span>&copy; {{ date('Y') }} Rute Travel. Perjalanan dimulai dari sini.</span>
    </footer>
</body>
</html>