<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function create(Request $request, int $transaction)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account['role'] === 'member', 403);

        $booking = $this->bookingQuery($transaction, $account['id'])->first();
        abort_unless($booking, 404);
        abort_if($booking->status !== 'Belum Bayar' || $booking->id_pembayaran, 403);

        return view('payments.create', compact('booking'));
    }

    public function store(Request $request, int $transaction)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account['role'] === 'member', 403);

        $data = $request->validate([
            'no_resi' => ['required', 'string', 'max:50', 'unique:pembayaran,no_resi'],
            'no_rek' => ['required', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'tgl_transfer' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'jam_transfer' => ['required', 'date_format:H:i'],
        ]);

        DB::transaction(function () use ($transaction, $account, $data) {
            $booking = DB::table('pesan')
                ->where('id_pesan', $transaction)
                ->where('id_member', $account['id'])
                ->lockForUpdate()
                ->first();

            if (! $booking || $booking->status !== 'Belum Bayar') {
                throw ValidationException::withMessages([
                    'no_resi' => 'Transaksi tidak ditemukan atau sudah dibayarkan.',
                ]);
            }

            $alreadySubmitted = DB::table('pembayaran')
                ->where('id_pesan', $transaction)
                ->exists();
            if ($alreadySubmitted) {
                throw ValidationException::withMessages([
                    'no_resi' => 'Bukti pembayaran untuk transaksi ini sudah dikirim.',
                ]);
            }

            DB::table('pembayaran')->insert([
                'id_pesan' => $booking->id_pesan,
                'no_resi' => $data['no_resi'],
                'no_rek' => $data['no_rek'],
                'tgl_transfer' => $data['tgl_transfer'],
                'jam_transfer' => $data['jam_transfer'].':00',
                'status' => 'Dalam Proses',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pesan')
                ->where('id_pesan', $booking->id_pesan)
                ->update(['status' => 'Dalam Proses']);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi.');
    }

    public function index(Request $request)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless(in_array($account['role'], ['admin', 'operator_loket'], true), 403);

        $payments = DB::table('pembayaran as b')
            ->join('pesan as p', 'p.id_pesan', '=', 'b.id_pesan')
            ->join('member as m', 'm.id_member', '=', 'p.id_member')
            ->join('jadwal as j', 'j.id_jadwal', '=', 'p.id_jadwal')
            ->join('kota_asal as a', 'a.id_kota_asal', '=', 'j.id_kota_asal')
            ->join('kota_tujuan as t', 't.id_kota_tujuan', '=', 'j.id_kota_tujuan')
            ->select([
                'b.id_pembayaran', 'b.no_resi', 'b.no_rek', 'b.tgl_transfer',
                'b.jam_transfer', 'b.status as status_pembayaran',
                'p.id_pesan', 'p.nomor_kursi', 'p.status as status_transaksi',
                'm.nama as nama_member', 'j.harga', 'j.tgl_berangkat',
                'a.nm_kota_asal', 't.nm_kota_tujuan',
            ])
            ->orderByRaw("CASE WHEN b.status = 'Dalam Proses' THEN 0 ELSE 1 END")
            ->orderByDesc('b.id_pembayaran')
            ->get();

        return view('payments.index', compact('payments'));
    }

    public function confirm(Request $request, int $payment)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless(in_array($account['role'], ['admin', 'operator_loket'], true), 403);

        DB::transaction(function () use ($payment) {
            $record = DB::table('pembayaran')
                ->where('id_pembayaran', $payment)
                ->lockForUpdate()
                ->first();

            abort_unless($record, 404);
            abort_if($record->status !== 'Dalam Proses', 409, 'Pembayaran ini sudah diverifikasi.');

            DB::table('pembayaran')
                ->where('id_pembayaran', $payment)
                ->update(['status' => 'Lunas', 'updated_at' => now()]);
            DB::table('pesan')
                ->where('id_pesan', $record->id_pesan)
                ->update(['status' => 'Lunas']);
        });

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    private function bookingQuery(int $transaction, int $memberId)
    {
        return DB::table('pesan as p')
            ->join('jadwal as j', 'j.id_jadwal', '=', 'p.id_jadwal')
            ->join('kota_asal as a', 'a.id_kota_asal', '=', 'j.id_kota_asal')
            ->join('kota_tujuan as t', 't.id_kota_tujuan', '=', 'j.id_kota_tujuan')
            ->leftJoin('pembayaran as b', 'b.id_pesan', '=', 'p.id_pesan')
            ->where('p.id_pesan', $transaction)
            ->where('p.id_member', $memberId)
            ->select([
                'p.id_pesan', 'p.nomor_kursi', 'p.status', 'j.harga',
                'j.tgl_berangkat', 'j.jam_berangkat',
                'a.nm_kota_asal', 't.nm_kota_tujuan',
                'b.id_pembayaran',
            ]);
    }
}
