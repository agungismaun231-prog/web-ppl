<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless(in_array($account['role'], ['member', 'admin'], true), 403);

        $complaintsQuery = DB::table('keluhan as k')
            ->join('member as m', 'm.id_member', '=', 'k.id_member')
            ->select([
                'k.id_keluhan',
                'k.j_keluhan as judul',
                'k.d_keluhan as isi_keluhan',
                'm.nama as nama_member',
            ])
            ->orderByDesc('k.id_keluhan');

        if ($account['role'] === 'member') {
            $complaintsQuery->where('k.id_member', $account['id']);
        }

        $complaints = $complaintsQuery->get();

        return view('complaints.index', compact('complaints'));
    }

    public function store(Request $request)
    {
        $account = $request->session()->get('legacy_account');
        abort_unless($account['role'] === 'member', 403);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:20'],
            'isi_keluhan' => ['required', 'string', 'max:200'],
        ]);

        DB::table('keluhan')->insert([
            'id_member' => $account['id'],
            'j_keluhan' => $data['judul'],
            'd_keluhan' => $data['isi_keluhan'],
        ]);

        return redirect()->route('complaints.index')
            ->with('success', 'Keluhan berhasil dikirim kepada admin.');
    }
}
