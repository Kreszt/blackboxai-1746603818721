<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\MedicalJournalController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Patient routes
    Route::get('patients/search', [PatientController::class, 'search']);
    Route::apiResource('patients', PatientController::class);

    // Visit routes
    Route::get('visits/queue-summary', [VisitController::class, 'queueSummary']);
    Route::patch('visits/{visit}/status', [VisitController::class, 'updateStatus']);
    Route::apiResource('visits', VisitController::class);

    // Medical Journal routes
    Route::get('medical-journals/by-patient', [MedicalJournalController::class, 'getByPatient']);
    Route::patch('medical-journals/{medical_journal}/status', [MedicalJournalController::class, 'updateStatus']);
    Route::apiResource('medical-journals', MedicalJournalController::class);

    // Prescription routes
    Route::get('prescriptions/by-patient', [PrescriptionController::class, 'getByPatient']);
    Route::patch('prescriptions/{prescription}/revise', [PrescriptionController::class, 'revise']);
    Route::apiResource('prescriptions', PrescriptionController::class);

    // Transaction routes
    Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'receipt']);
    Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus']);
    Route::apiResource('transactions', TransactionController::class);
});
