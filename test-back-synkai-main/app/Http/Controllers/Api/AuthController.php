<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'Debes confirmar tu correo electrónico. Revisa la bandeja de entrada o solicita un nuevo enlace.',
                'code' => 'email_unverified',
                'email' => $user->email,
            ], 403);
        }
        if ($request->filled('country_code')) {
            $user->country_code = strtoupper($request->country_code);
            $user->save();
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->loadMissing('rank', 'sponsor', 'registrationPackage'),
        ]);
    }
}
