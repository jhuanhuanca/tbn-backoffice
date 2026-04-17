<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Rank;
use App\Models\User;
use App\Services\MemberCodeService;
use Illuminate\Http\Request;
class AuthRegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'document_id' => ['required', 'string', 'max:64', 'unique:users,document_id'],
            'phone' => ['required', 'string', 'max:32'],
            'birth_date' => ['required', 'date', 'before:today'],
            'sponsor_referral_code' => ['nullable', 'string', 'max:32'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'registration_package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'preferred_payment_method' => ['nullable', 'string', 'max:32'],
            'preferred_binary_leg' => ['nullable', 'string', 'in:left,right,auto'],
        ]);

        $sponsorId = null;
        if (! empty($validated['sponsor_referral_code'])) {
            $sponsor = MemberCodeService::findUserBySponsorCode($validated['sponsor_referral_code']);
            if (! $sponsor) {
                return response()->json([
                    'message' => 'El código de patrocinador no es válido.',
                    'errors' => ['sponsor_referral_code' => ['Código de patrocinador no encontrado.']],
                ], 422);
            }
            $sponsorId = $sponsor->id;
        }

        $packageId = null;
        if (! empty($validated['registration_package_id'])) {
            $exists = Package::query()
                ->where('id', $validated['registration_package_id'])
                ->where('estado', 'activo')
                ->exists();
            if (! $exists) {
                return response()->json([
                    'message' => 'El paquete de inscripción no es válido.',
                    'errors' => ['registration_package_id' => ['Paquete no disponible.']],
                ], 422);
            }
            $packageId = (int) $validated['registration_package_id'];
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'document_id' => $validated['document_id'],
            'phone' => $validated['phone'],
            'birth_date' => $validated['birth_date'],
            'sponsor_id' => $sponsorId,
            'preferred_binary_leg' => $validated['preferred_binary_leg'] ?? null,
            'account_type' => 'member',
            'rank_id' => Rank::query()->where('slug', 'activo')->value('id'),
            // Regla: hasta pagar el paquete de activación, el socio queda pendiente.
            'account_status' => 'pending',
            'country_code' => isset($validated['country_code']) ? strtoupper($validated['country_code']) : null,
            'registration_package_id' => $packageId,
            'preferred_payment_method' => $validated['preferred_payment_method'] ?? null,
        ]);

        $user->loadMissing('rank', 'sponsor', 'registrationPackage');

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Cuenta creada. Revisa tu correo y confirma el enlace antes de iniciar sesión.',
            'requires_email_verification' => true,
            'email' => $user->email,
        ], 201);
    }
}
