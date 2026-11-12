<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'asal' => ['nullable', 'integer', 'min:1'],
            'tujuan' => ['nullable', 'integer', 'min:1'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $origins = DB::table('kota_asal')
            ->select('id_kota_asal as id', 'nm_kota_asal as nama')
            ->orderBy('nama')
            ->get();
        $destinations = DB::table('kota_tujuan')
            ->select('id_kota_tujuan as id', 'nm_kota_tujuan as nama')
            ->orderBy('nama')
            ->get();

        $bookings = DB::table('pesan')
            ->select('id_jadwal')
            ->selectRaw('COUNT(*) as kursi_terisi')
            ->whereIn('status', ['Lunas', 'Dalam Proses', 'Belum Bayar'])
            ->groupBy('id_jadwal');

        $scheduleQuery = DB::table('jadwal as j')
            ->join('kota_asal as a', 'a.id_kota_asal', '=', 'j.id_kota_asal')
            ->join('kota_tujuan as t', 't.id_kota_tujuan', '=', 'j.id_kota_tujuan')
            ->leftJoin('kendaraan as k', 'k.id_mobil', '=', 'j.id_mobil')
            ->leftJoinSub($bookings, 'p', 'p.id_jadwal', '=', 'j.id_jadwal')
            ->whereDate('j.tgl_berangkat', '>=', today()->toDateString())
            ->select([
                'j.id_jadwal', 'j.harga', 'j.tgl_berangkat', 'j.jam_berangkat',
                'a.nm_kota_asal', 't.nm_kota_tujuan', 'k.jenis_mobil',
            ])
            ->selectRaw('CASE WHEN 5 - COALESCE(p.kursi_terisi, 0) > 0 THEN 5 - COALESCE(p.kursi_terisi, 0) ELSE 0 END as kursi_tersedia');

        if (! empty($filters['asal'])) {
            $scheduleQuery->where('j.id_kota_asal', $filters['asal']);
        }
        if (! empty($filters['tujuan'])) {
            $scheduleQuery->where('j.id_kota_tujuan', $filters['tujuan']);
        }
        if (! empty($filters['tanggal'])) {
            $scheduleQuery->whereDate('j.tgl_berangkat', $filters['tanggal']);
        }

        $schedules = $scheduleQuery
            ->orderBy('j.tgl_berangkat')
            ->orderBy('j.jam_berangkat')
            ->limit(50)
            ->get();
        $occupiedSeats = DB::table('pesan')
            ->whereIn('id_jadwal', $schedules->pluck('id_jadwal'))
            ->whereIn('status', ['Lunas', 'Dalam Proses', 'Belum Bayar'])
            ->get(['id_jadwal', 'nomor_kursi'])
            ->groupBy('id_jadwal');

        foreach ($schedules as $schedule) {
            $occupied = ($occupiedSeats->get($schedule->id_jadwal) ?? collect())
                ->pluck('nomor_kursi')
                ->map(fn ($seat) => (int) $seat)
                ->all();
            $schedule->available_seats = array_values(array_diff(range(1, 5), $occupied));
        }

        return view('home', [
            'origins' => $origins,
            'destinations' => $destinations,
            'activeScheduleCount' => DB::table('jadwal')
                ->whereDate('tgl_berangkat', '>=', today()->toDateString())
                ->count(),
            'schedules' => $schedules,
            'filters' => $filters,
        ]);
    }
}
