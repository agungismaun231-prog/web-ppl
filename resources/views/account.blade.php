<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akun - Rute Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell account-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">{{ $account['role_label'] }}</p>
            <h1>Halo, {{ $account['name'] }}</h1>
            <dl class="account-details">
                <div><dt>Nama</dt><dd>{{ $account['name'] }}</dd></div>
                <div><dt>Jenis akun</dt><dd>{{ $account['role_label'] }}</dd></div>
                <div><dt>Email / username</dt><dd>{{ $account['identifier'] }}</dd></div>
            </dl>
            <div class="account-actions">
                <a class="secondary-button" href="{{ route('home') }}">Lihat jadwal</a>
                @if (session('legacy_account.role') === 'member')
                    <a class="secondary-button" href="{{ route('transactions.index') }}">Transaksi saya</a>
                @elseif (session('legacy_account.role') === 'admin')
                    <a class="secondary-button" href="{{ route('schedules.create') }}">Tambah jadwal</a>
                    <a class="secondary-button" href="{{ route('payments.index') }}">Verifikasi pembayaran</a>
                @elseif (session('legacy_account.role') === 'operator_loket')
                    <a class="secondary-button" href="{{ route('payments.index') }}">Verifikasi pembayaran</a>
                @endif
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit">Keluar</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>