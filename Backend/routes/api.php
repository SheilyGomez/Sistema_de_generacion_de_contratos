<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractGenerationController;
use App\Http\Controllers\Api\ContractVerificationController;
use App\Http\Controllers\Api\LawyerRequestController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/settings', [AuthController::class, 'updateSettings']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contract-verifications', ContractVerificationController::class)
        ->only(['index', 'show', 'store']);
    Route::get('contract-verifications/{contract_verification}/download', [ContractVerificationController::class, 'download']);

    Route::get('contract-generations/request-reviews', [ContractGenerationController::class, 'myLawyerRequests']);
    Route::apiResource('contract-generations', ContractGenerationController::class)
        ->only(['index', 'show', 'store']);
    Route::get('contract-generations/{contract_generation}/download', [ContractGenerationController::class, 'download']);
    Route::post('contract-generations/{contract_generation}/request-review', [ContractGenerationController::class, 'requestReview']);
});

Route::middleware('auth:sanctum')->prefix('wallet')->group(function () {
    Route::post('/deposit', [WalletController::class, 'deposit']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/transactions', [WalletController::class, 'transactions']);
    Route::get('/balance', [WalletController::class, 'balance']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lawyers', [LawyerRequestController::class, 'lawyers']);

    Route::get('/lawyer-requests', [LawyerRequestController::class, 'index'])
        ->middleware('role:abogado');

    Route::get('/lawyer-requests/{lawyer_request}', [LawyerRequestController::class, 'show']);
    Route::get('/lawyer-requests/{lawyer_request}/download', [LawyerRequestController::class, 'download']);

    Route::post('/lawyer-requests/{lawyer_request}/upload', [LawyerRequestController::class, 'upload'])
        ->middleware('role:abogado');

    Route::post('/lawyer-requests/{lawyer_request}/complete', [LawyerRequestController::class, 'complete'])
        ->middleware('role:abogado');
});
