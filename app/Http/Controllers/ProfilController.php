<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function edit()
    {
        return view('profil.password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (! Hash::check($request->password_lama, $request->user()->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
