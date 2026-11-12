<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Jadwal - Rute Travel</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <main class="auth-shell">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span><span>rute<span class="brand-light">travel</span></span></a>
        <section class="auth-panel">
            <p class="eyebrow">Administrator</p>
            <h1>Tambah jadwal baru</h1>
            <p class="auth-description">Lengkapi rute, kendaraan, tarif, dan waktu keberangkatan.</p>
            @if ($errors->any())
                <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
            @endif
            <form method="post" action="{{ route('schedules.store') }}">
                @csrf
                <label for="id_kota_asal">Kota asal</label>
                <select id="id_kota_asal" name="id_kota_asal" required>
                    <option value="">Pilih kota asal</option>
                    @foreach ($origins as $city)
                        <option value="{{ $city->id_kota_asal }}" @selected(old('id_kota_asal') == $city->id_kota_asal)>{{ $city->nm_kota_asal }}</option>
                    @endforeach
                </select>
                <label for="id_kota_tujuan">Kota tujuan</label>
                <select id="id_kota_tujuan" name="id_kota_tujuan" required>
                    <option value="">Pilih kota tujuan</option>
                    @foreach ($destinations as $city)
                        <option value="{{ $city->id_kota_tujuan }}" @selected(old('id_kota_tujuan') == $city->id_kota_tujuan)>{{ $city->nm_kota_tujuan }}</option>
                    @endforeach
                </select>
                <label for="id_mobil">Kendaraan</label>
                <select id="id_mobil" name="id_mobil" required>
                    <option value="">Pilih kendaraan</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id_mobil }}" @selected(old('id_mobil') == $vehicle->id_mobil)>{{ $vehicle->jenis_mobil }} · {{ $vehicle->nomor_polisi }}</option>
                    @endforeach
                </select>
                <label for="harga">Harga tiket (Rp)</label>
                <input id="harga" type="number" name="harga" value="{{ old('harga') }}" min="1" step="1" required>
                <label for="tgl_berangkat">Tanggal berangkat</label>
                <input id="tgl_berangkat" type="date" name="tgl_berangkat" value="{{ old('tgl_berangkat') }}" min="{{ today()->toDateString() }}" required>
                <label for="jam_berangkat">Jam berangkat</label>
                <input id="jam_berangkat" type="time" name="jam_berangkat" value="{{ old('jam_berangkat') }}" required>
                <button type="submit">Simpan jadwal</button>
            </form>
            <a class="back-link" href="{{ route('home') }}">Kembali ke beranda</a>
        </section>
    </main>
</body>
</html>
