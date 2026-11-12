<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account['role'] === 'member', 403);

        $transactions = DB::table('pesan as p')
            ->join('jadwal as j', 'j.id_jadwal', '=', 'p.id_jadwal')
            ->join('kota_asal as a', 'a.id_kota_asal', '=', 'j.id_kota_asal')
            ->join('kota_tujuan as t', 't.id_kota_tujuan', '=', 'j.id_kota_tujuan')
            ->leftJoin('pembayaran as b', 'b.id_pesan', '=', 'p.id_pesan')
            ->where('p.id_member', $account['id'])
            ->select([
                'p.id_pesan', 'p.nomor_kursi', 'p.status', 'j.harga',
                'j.tgl_berangkat', 'j.jam_berangkat',
                'a.nm_kota_asal', 't.nm_kota_tujuan',
                'b.id_pembayaran', 'b.no_resi', 'b.status as status_pembayaran',
            ])
            ->orderByDesc('p.id_pesan')
            ->get();

        return view('transactions.index', compact('transactions'));
    }

    public function store(Request $request)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account['role'] === 'member', 403);

        $data = $request->validate([
            'id_jadwal' => ['required', 'integer', 'min:1'],
            'nomor_kursi' => ['required', 'integer', 'between:1,5'],
        ]);

        $transactionId = DB::transaction(function () use ($data, $account) {
            $schedule = DB::table('jadwal')
                ->where('id_jadwal', $data['id_jadwal'])
                ->lockForUpdate()
                ->first();

            if (! $schedule || $schedule->tgl_berangkat < today()->toDateString()) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Jadwal tidak tersedia untuk dipesan.',
                ]);
            }

            $seatTaken = DB::table('pesan')
                ->where('id_jadwal', $schedule->id_jadwal)
                ->where('nomor_kursi', (string) $data['nomor_kursi'])
                ->whereIn('status', ['Lunas', 'Dalam Proses', 'Belum Bayar'])
                ->exists();

            if ($seatTaken) {
                throw ValidationException::withMessages([
                    'nomor_kursi' => 'Kursi tersebut sudah dipesan. Silakan pilih kursi lain.',
                ]);
            }

            return DB::table('pesan')->insertGetId([
                'nomor_kursi' => (string) $data['nomor_kursi'],
                'status' => 'Belum Bayar',
                'id_jadwal' => $schedule->id_jadwal,
                'id_member' => $account['id'],
            ], 'id_pesan');
        });

        return redirect()->route('transactions.index')
            ->with('success', "Transaksi #{$transactionId} berhasil dibuat. Silakan lakukan pembayaran.");
    }
}
