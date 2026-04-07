<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/signin?verified=1');
})->middleware(['signed'])->name('verification.verify');
