<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        return view('account', [
            'account' => $request->session()->get('legacy_account'),
        ]);
    }
}