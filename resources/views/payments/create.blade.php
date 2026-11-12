<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran - Rute Travel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Transaksi #{{ $booking->id_pesan }}</p>
            <h1>Konfirmasi pembayaran</h1>
            <p class="auth-description">{{ $booking->nm_kota_asal }} ke {{ $booking->nm_kota_tujuan }} · Kursi {{ $booking->nomor_kursi }} · Rp{{ number_format((int) $booking->harga, 0, ',', '.') }}</p>
            <p class="payment-instructions">Silakan transfer sesuai total tiket melalui rekening resmi travel, lalu isi detail transfer di bawah. Pembayaran akan berstatus dalam proses hingga diverifikasi petugas.</p>
            @if ($errors->any())
                <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
            @endif
            <form method="post" action="{{ route('payments.store', $booking->id_pesan) }}">
                @csrf
                <label for="no_resi">Nomor referensi / bukti transfer</label>
                <input id="no_resi" name="no_resi" value="{{ old('no_resi') }}" maxlength="50" required>
                <label for="no_rek">Nomor rekening pengirim</label>
                <input id="no_rek" name="no_rek" value="{{ old('no_rek') }}" inputmode="numeric" pattern="[0-9]+" maxlength="32" required>
                <label for="tgl_transfer">Tanggal transfer</label>
                <input id="tgl_transfer" type="date" name="tgl_transfer" value="{{ old('tgl_transfer', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
                <label for="jam_transfer">Jam transfer</label>
                <input id="jam_transfer" type="time" name="jam_transfer" value="{{ old('jam_transfer') }}" required>
                <button type="submit">Kirim bukti pembayaran</button>
            </form>
            <a class="back-link" href="{{ route('transactions.index') }}">Kembali ke transaksi</a>
        </section>
    </main>
</body>
</html>
