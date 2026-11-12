<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Pembayaran - Rute Travel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell management-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Pengelolaan transaksi</p>
            <h1>Verifikasi pembayaran</h1>
            <p class="auth-description">Periksa referensi transfer dan konfirmasi pembayaran yang sudah diterima.</p>
            @if (session('success'))
                <p class="notice notice-success" role="status">{{ session('success') }}</p>
            @endif
            @forelse ($payments as $payment)
                <article class="transaction-card">
                    <div class="transaction-heading">
                        <strong>Transaksi #{{ $payment->id_pesan }}</strong>
                        <span class="status-badge">{{ $payment->status_pembayaran }}</span>
                    </div>
                    <p class="transaction-route">{{ $payment->nm_kota_asal }} <span aria-hidden="true">→</span> {{ $payment->nm_kota_tujuan }}</p>
                    <dl class="transaction-details">
                        <div><dt>Pemesan</dt><dd>{{ $payment->nama_member }}</dd></div>
                        <div><dt>Kursi / total</dt><dd>{{ $payment->nomor_kursi }} · Rp{{ number_format((int) $payment->harga, 0, ',', '.') }}</dd></div>
                        <div><dt>Referensi transfer</dt><dd>{{ $payment->no_resi }}</dd></div>
                        <div><dt>Rekening pengirim</dt><dd>{{ $payment->no_rek }}</dd></div>
                        <div><dt>Waktu transfer</dt><dd>{{ \Carbon\Carbon::parse($payment->tgl_transfer)->format('d/m/Y') }} {{ substr($payment->jam_transfer, 0, 5) }}</dd></div>
                    </dl>
                    @if ($payment->status_pembayaran === 'Dalam Proses')
                        <form method="post" action="{{ route('payments.confirm', $payment->id_pembayaran) }}" onsubmit="return confirm('Konfirmasi pembayaran ini sebagai lunas?')">
                            @csrf
                            <button type="submit">Konfirmasi lunas</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="empty-panel">
                    <h2>Belum ada pembayaran</h2>
                    <p>Bukti pembayaran yang dikirim member akan muncul di sini.</p>
                </div>
            @endforelse
            <a class="back-link" href="{{ route('home') }}">Kembali ke beranda</a>
        </section>
    </main>
</body>
</html>
