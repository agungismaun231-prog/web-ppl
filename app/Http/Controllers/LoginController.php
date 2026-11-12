<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    private const ACCOUNT_TYPES = [
        'admin' => [
            'table' => 'admin', 'id' => 'id_admin', 'login' => 'email', 'name' => 'nama_lengkap',
            'label' => 'Administrator',
        ],
        'member' => [
            'table' => 'member', 'id' => 'id_member', 'login' => 'username', 'name' => 'nama',
            'label' => 'Member',
        ],
        'operator_loket' => [
            'table' => 'operator_loket', 'id' => 'id_opr', 'login' => 'user', 'name' => 'nama',
            'label' => 'Operator loket',
        ],
    ];

    public function create(Request $request)
    {
        if ($request->session()->has('legacy_account')) {
            return redirect()->route('account');
        }

        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'role' => ['required', 'in:admin,member,operator_loket'],
            'identifier' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $type = self::ACCOUNT_TYPES[$credentials['role']];
        $account = DB::table($type['table'])
            ->where($type['login'], $credentials['identifier'])
            ->first();

        if (!$account || !$this->passwordMatches($credentials['password'], $account->password)) {
            return back()
                ->withErrors(['credentials' => 'Peran, identitas, atau password tidak sesuai.'])
                ->onlyInput('role', 'identifier');
        }

        if (password_get_info($account->password)['algo'] === null) {
            DB::table($type['table'])
                ->where($type['id'], $account->{$type['id']})
                ->update(['password' => Hash::make($credentials['password'])]);
        }

        $request->session()->regenerate();
        $request->session()->put('legacy_account', [
            'id' => $account->{$type['id']},
            'role' => $credentials['role'],
            'role_label' => $type['label'],
            'name' => $account->{$type['name']},
            'identifier' => $credentials['identifier'],
        ]);

        return redirect()->intended(route('account'));
    }

    public function destroy(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function passwordMatches(string $provided, string $stored): bool
    {
        if (password_get_info($stored)['algo'] !== null) {
            return Hash::check($provided, $stored);
        }

        return hash_equals($stored, $provided);
    }
}