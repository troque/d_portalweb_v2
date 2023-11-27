<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\UserConfirmationController;
use App\Http\Controllers\Auth\TokenVerifiedController;
use App\Http\Controllers\Auth\CodeAccessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\ProcessDetailsController;
use App\Http\Controllers\Auth\NotificationsController;
use App\Http\Controllers\Auth\DownloadFileController;
use App\Http\Controllers\Auth\ResendPasswordController;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::get('reset-password/{token}/{userId}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');

    Route::get('user-confirmation', [UserConfirmationController::class, 'create'])
        ->name('user.confirmation');

    Route::get('token-verified', [TokenVerifiedController::class, 'create'])
        ->name('token.verified');

    Route::get('resend', [ResendPasswordController::class, 'create'])
        ->name('resend');

    Route::post('resend.password', [ResendPasswordController::class, 'store'])
        ->name('resend.password');
});

Route::middleware('web')->group(function () {

    // Ruta del codigo de acceso get
    Route::get('code-access', [CodeAccessController::class, 'create'])->name('code-access');

    // Ruta del codigo de acceso post
    Route::post('code-access', [CodeAccessController::class, 'store'])->name('code-access');
});


Route::middleware('auth')->group(function () {

    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Rutas detalle del proceso disciplinario
    Route::get('process-details/{uuid}', [ProcessDetailsController::class, 'show'])
        ->name('process-details');

    // Ruta notificaciones
    Route::get('notifications', [NotificationsController::class, 'show'])
        ->name('notifications');

    // Ruta descarga archivo compartido
    Route::get('download/{uuid}', [DownloadFileController::class, 'create'])
        ->name('download.file');

    // Ruta descarga archivo compartido
    Route::get('download-actuacion/{uuid}', [DownloadFileController::class, 'descargarDocumentoActuaciones'])
    ->name('download.file.actuacion');
});
