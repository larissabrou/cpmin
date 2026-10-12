<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function showChange(Request $request): View
    {
        return view('auth.change-password', [
            'forced' => $request->user()->must_change_password,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)],
        ];

        if (! $user->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Votre mot de passe a été mis à jour.');
    }
}
