<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Saya - Rute Travel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell management-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Riwayat perjalanan</p>
            <h1>Transaksi saya</h1>
            <p class="auth-description">Lihat status pemesanan dan lanjutkan pembayaran tiketmu.</p>
            @if (session('success'))
                <p class="notice notice-success" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
            @endif

            @forelse ($transactions as $transaction)
                <article class="transaction-card">
                    <div class="transaction-heading">
                        <strong>Transaksi #{{ $transaction->id_pesan }}</strong>
                        <span class="status-badge">{{ $transaction->status }}</span>
                    </div>
                    <p class="transaction-route">{{ $transaction->nm_kota_asal }} <span aria-hidden="true">→</span> {{ $transaction->nm_kota_tujuan }}</p>
                    <dl class="transaction-details">
                        <div><dt>Berangkat</dt><dd>{{ \Carbon\Carbon::parse($transaction->tgl_berangkat)->format('d/m/Y') }}, {{ substr($transaction->jam_berangkat, 0, 5) }}</dd></div>
                        <div><dt>Kursi</dt><dd>{{ $transaction->nomor_kursi }}</dd></div>
                        <div><dt>Total</dt><dd>Rp{{ number_format((int) $transaction->harga, 0, ',', '.') }}</dd></div>
                        @if ($transaction->no_resi)
                            <div><dt>No. referensi</dt><dd>{{ $transaction->no_resi }}</dd></div>
                            <div><dt>Status pembayaran</dt><dd>{{ $transaction->status_pembayaran }}</dd></div>
                        @endif
                    </dl>
                    @if ($transaction->status === 'Belum Bayar' && !$transaction->id_pembayaran)
                        <a class="primary-link" href="{{ route('payments.create', $transaction->id_pesan) }}">Bayar sekarang</a>
                    @endif
                </article>
            @empty
                <div class="empty-panel">
                    <h2>Belum ada transaksi</h2>
                    <p>Pilih jadwal yang tersedia untuk mulai memesan tiket.</p>
                    <a class="primary-link" href="{{ route('home') }}">Cari jadwal</a>
                </div>
            @endforelse
            <a class="back-link" href="{{ route('home') }}">Kembali ke jadwal</a>
        </section>
    </main>
</body>
</html>
