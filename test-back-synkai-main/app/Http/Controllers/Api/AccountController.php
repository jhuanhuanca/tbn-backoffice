<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'document_id' => ['nullable', 'string', 'max:64', Rule::unique('users', 'document_id')->ignore($user->id)],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
            'country_code' => isset($validated['country_code']) ? strtoupper($validated['country_code']) : $user->country_code,
            'document_id' => $validated['document_id'] ?? $user->document_id,
        ]);
        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado.',
            'user' => $user->fresh()->loadMissing('rank', 'sponsor', 'registrationPackage'),
        ]);
    }

    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual no es correcta.',
                'errors' => ['current_password' => ['Contraseña actual incorrecta.']],
            ], 422);
        }

        $user->password = $validated['password'];
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada.']);
    }

    public function getLanding(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $meta = is_array($user->meta) ? $user->meta : [];

        return response()->json([
            'member_code' => $user->member_code,
            'landing' => $meta['landing'] ?? null,
        ]);
    }

    public function updateLanding(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'landing' => ['required', 'array'],
            'landing.tagline' => ['nullable', 'string', 'max:160'],
            'landing.bio' => ['nullable', 'string', 'max:800'],
            'landing.phone' => ['nullable', 'string', 'max:32'],
            'landing.email' => ['nullable', 'email', 'max:255'],
            'landing.whatsapp' => ['nullable', 'string', 'max:32'],
            'landing.videos' => ['nullable', 'array', 'max:12'],
        ]);

        $meta = is_array($user->meta) ? $user->meta : [];
        $meta['landing'] = $validated['landing'];
        $user->meta = $meta;
        $user->save();

        return response()->json(['message' => 'Landing actualizada.']);
    }

    public function getWalletSettings(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $meta = is_array($user->meta) ? $user->meta : [];

        return response()->json([
            'wallet_settings' => $meta['wallet_settings'] ?? null,
        ]);
    }

    public function updateWalletSettings(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'wallet_settings' => ['required', 'array'],
            'wallet_settings.method' => ['nullable', 'string', 'max:16'],
            'wallet_settings.currency' => ['nullable', 'string', 'max:3'],
            'wallet_settings.address' => ['nullable', 'string', 'max:255'],
            'wallet_settings.bank' => ['nullable', 'string', 'max:120'],
            'wallet_settings.holder' => ['nullable', 'string', 'max:120'],
            'wallet_settings.account' => ['nullable', 'string', 'max:64'],
            'wallet_settings.swift' => ['nullable', 'string', 'max:32'],
        ]);

        $meta = is_array($user->meta) ? $user->meta : [];
        $meta['wallet_settings'] = $validated['wallet_settings'];
        $user->meta = $meta;
        $user->save();

        return response()->json(['message' => 'Preferencias de billetera actualizadas.']);
    }
}

