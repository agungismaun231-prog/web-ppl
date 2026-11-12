<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Rute Travel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Portal perjalanan</p>
            <h1>Masuk ke akun</h1>
            <p class="auth-description">Gunakan akun yang sudah terdaftar pada sistem Rute Travel.</p>
            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <label for="role">Jenis akun</label>
                <select id="role" name="role" required>
                    <option value="">Pilih jenis akun</option>
                    <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                    <option value="member" @selected(old('role') === 'member')>Member</option>
                    <option value="operator_loket" @selected(old('role') === 'operator_loket')>Operator loket</option>
                </select>
                <label for="identifier">Email / username</label>
                <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" autocomplete="username" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @if ($errors->any())
                    <p class="form-error" role="alert">{{ $errors->first('credentials') ?: $errors->first() }}</p>
                @endif
                <button type="submit">Masuk</button>
            </form>
            <a class="back-link" href="{{ route('home') }}">Kembali ke jadwal</a>
        </section>
    </main>
</body>
</html>