<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ session('legacy_account.role') === 'admin' ? 'Keluhan Member' : 'Keluhan Saya' }} - Jalanbae</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell management-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">J</span><span>jalan<span class="brand-light">bae</span></span></a>
        <section class="auth-panel">
            @if (session('legacy_account.role') === 'admin')
                <p class="eyebrow">Layanan member</p>
                <h1>Keluhan member</h1>
                <p class="auth-description">Baca masukan dan kendala yang disampaikan member.</p>
            @else
                <p class="eyebrow">Bantuan perjalanan</p>
                <h1>Keluhan saya</h1>
                <p class="auth-description">Sampaikan kendala atau masukan kepada admin.</p>
            @endif

            @if (session('success'))
                <p class="notice notice-success" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
                <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
            @endif

            @if (session('legacy_account.role') === 'member')
                <form method="post" action="{{ route('complaints.store') }}">
                    @csrf
                    <label for="judul">Judul keluhan</label>
                    <input id="judul" name="judul" type="text" value="{{ old('judul') }}" maxlength="20" required>
                    <label for="isi_keluhan">Detail keluhan</label>
                    <textarea id="isi_keluhan" name="isi_keluhan" rows="5" maxlength="200" required>{{ old('isi_keluhan') }}</textarea>
                    <button type="submit">Kirim keluhan</button>
                </form>
                <h2>Riwayat keluhan</h2>
            @endif

            @forelse ($complaints as $complaint)
                <article class="transaction-card">
                    <div class="transaction-heading">
                        <strong>{{ $complaint->judul }}</strong>
                        @if (session('legacy_account.role') === 'admin')
                            <span class="status-badge">{{ $complaint->nama_member }}</span>
                        @endif
                    </div>
                    <p>{{ $complaint->isi_keluhan }}</p>
                </article>
            @empty
                <div class="empty-panel">
                    <h2>Belum ada keluhan</h2>
                    <p>{{ session('legacy_account.role') === 'admin' ? 'Keluhan dari member akan muncul di sini.' : 'Keluhan yang kamu kirim akan tercatat di sini.' }}</p>
                </div>
            @endforelse
            <a class="back-link" href="{{ route('home') }}">Kembali ke beranda</a>
        </section>
    </main>
</body>
</html>
