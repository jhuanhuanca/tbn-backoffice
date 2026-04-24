<?php

use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminLeadershipController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminPackageController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminReconciliationController;
use App\Http\Controllers\Api\Admin\AdminWithdrawalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthRegisterController;
use App\Http\Controllers\Api\AuthRegisterPreferenteController;
use App\Http\Controllers\Api\BinaryPlacementController;
use App\Http\Controllers\Api\BinaryPlacementSelfController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\PublicLandingController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackageCatalogController;
use App\Http\Controllers\Api\ProductCatalogController;
use App\Http\Controllers\Api\SponsorLookupController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\MlmBonusCalculatorController;
use App\Http\Controllers\ProspectosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthRegisterController::class, 'register']);
Route::post('/register/preferred-customer', [AuthRegisterPreferenteController::class, 'register']);

Route::post('/email/resend-verification', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);
    $user = \App\Models\User::query()->where('email', $request->email)->first();
    if ($user && ! $user->hasVerifiedEmail()) {
        $user->sendEmailVerificationNotification();
    }

    return response()->json([
        'message' => 'Si el correo existe y aún no está verificado, te enviamos un enlace.',
    ]);
});

Route::get('/public/sponsors/{code}', [SponsorLookupController::class, 'show'])
    ->where('code', '[A-Za-z0-9._-]+');

Route::get('/packages', [PackageCatalogController::class, 'index']);

Route::apiResource('prospectos', ProspectosController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/me/binary-placement', [BinaryPlacementSelfController::class, 'store']);

    Route::get('/me', [MeController::class, 'profile']);
    Route::get('/me/dashboard', [MeController::class, 'dashboard']);
    Route::get('/me/referrals', [MeController::class, 'referrals']);
    Route::get('/me/unilevel-tree', [MeController::class, 'unilevelTree']);
    Route::get('/me/commissions', [MeController::class, 'commissions']);
    Route::get('/me/notifications', [MeController::class, 'notifications']);
    Route::delete('/me/notifications', [MeController::class, 'dismissNotifications']);
    Route::get('/me/binary-tree', [MeController::class, 'binaryTree']);
    Route::get('/me/binary-tree/children', [MeController::class, 'binaryTreeChildren']);
    Route::get('/me/binary-tree/search', [MeController::class, 'binaryTreeSearch']);
    Route::get('/me/founder', [MeController::class, 'founder']);
    Route::post('/me/founder/purchase', [MeController::class, 'founderPurchase']);
    Route::put('/me/profile', [AccountController::class, 'updateProfile']);
    Route::put('/me/password', [AccountController::class, 'changePassword']);
    Route::get('/me/landing', [AccountController::class, 'getLanding']);
    Route::put('/me/landing', [AccountController::class, 'updateLanding']);
    Route::get('/me/wallet-settings', [AccountController::class, 'getWalletSettings']);
    Route::put('/me/wallet-settings', [AccountController::class, 'updateWalletSettings']);
    Route::get('/support/tickets', [SupportTicketController::class, 'index']);
    Route::post('/support/tickets', [SupportTicketController::class, 'store']);

    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);

    Route::get('/products', [ProductCatalogController::class, 'index']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);

    // Calculadoras (JSON) para integrar fórmulas de bonos.
    Route::post('/mlm/bonus/calculate', [MlmBonusCalculatorController::class, 'calculate']);

    Route::middleware('mlm.admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::post('/orders/{order}/confirm-payment', [AdminOrderController::class, 'confirmPayment']);
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve']);
        Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);
        Route::post('/binary-placement', [BinaryPlacementController::class, 'store']);
        Route::get('/reconciliation/period-closures', [AdminReconciliationController::class, 'periodClosures']);
        Route::get('/reconciliation/commission-summary', [AdminReconciliationController::class, 'commissionSummary']);
        Route::get('/leadership/{monthKey}', [AdminLeadershipController::class, 'show'])
            ->where('monthKey', '[0-9]{4}-[0-9]{2}');

        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::put('/products/{product}', [AdminProductController::class, 'update']);
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
        Route::get('/packages', [AdminPackageController::class, 'index']);
        Route::post('/packages', [AdminPackageController::class, 'store']);
        Route::put('/packages/{package}', [AdminPackageController::class, 'update']);
        Route::delete('/packages/{package}', [AdminPackageController::class, 'destroy']);
    });
});

Route::get('/public/landing/{memberCode}', [PublicLandingController::class, 'show']);

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Sesión cerrada correctamente',
    ]);
})->middleware('auth:sanctum');
