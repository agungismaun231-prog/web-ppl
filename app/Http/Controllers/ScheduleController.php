<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function create(Request $request)
    {
        $this->authorizeManager($request);

        return view('schedules.create', [
            'origins' => DB::table('kota_asal')->orderBy('nm_kota_asal')->get(),
            'destinations' => DB::table('kota_tujuan')->orderBy('nm_kota_tujuan')->get(),
            'vehicles' => DB::table('kendaraan')->orderBy('jenis_mobil')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            'id_kota_asal' => ['required', 'integer', 'exists:kota_asal,id_kota_asal'],
            'id_kota_tujuan' => ['required', 'integer', 'exists:kota_tujuan,id_kota_tujuan'],
            'harga' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'tgl_berangkat' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jam_berangkat' => ['required', 'date_format:H:i'],
            'id_mobil' => ['required', 'integer', 'exists:kendaraan,id_mobil'],
        ]);

        $origin = DB::table('kota_asal')->where('id_kota_asal', $data['id_kota_asal'])->value('nm_kota_asal');
        $destination = DB::table('kota_tujuan')->where('id_kota_tujuan', $data['id_kota_tujuan'])->value('nm_kota_tujuan');
        if ($origin === $destination) {
            throw ValidationException::withMessages([
                'id_kota_tujuan' => 'Kota asal dan tujuan harus berbeda.',
            ]);
        }

        $data['jam_berangkat'] .= ':00';
        DB::table('jadwal')->insert($data);

        return redirect()->route('home')->with('success', 'Jadwal keberangkatan baru berhasil ditambahkan.');
    }

    private function authorizeManager(Request $request): void
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account && $account['role'] === 'admin', 403);
    }
}
