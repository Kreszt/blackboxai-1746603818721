<?php

use App\Http\Controllers\PatientViewController;
use App\Http\Controllers\VisitViewController;
use App\Http\Controllers\MedicalJournalViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('medical-journals.index');
});

// Patient Routes
Route::get('/patients', [PatientViewController::class, 'index'])->name('patients.index');
Route::get('/patients/create', [PatientViewController::class, 'create'])->name('patients.create');
Route::get('/patients/{patient}/edit', [PatientViewController::class, 'edit'])->name('patients.edit');

// Visit Routes
Route::get('/visits', [VisitViewController::class, 'index'])->name('visits.index');
Route::get('/visits/create', [VisitViewController::class, 'create'])->name('visits.create');
Route::get('/visits/queue-summary', [VisitViewController::class, 'queueSummary'])->name('visits.queue-summary');
Route::get('/visits/{visit}', [VisitViewController::class, 'show'])->name('visits.show');
Route::get('/visits/{visit}/edit', [VisitViewController::class, 'edit'])->name('visits.edit');

// Medical Journal Routes
Route::get('/medical-journals', [MedicalJournalViewController::class, 'index'])->name('medical-journals.index');

// AJAX Routes
Route::get('/visits/doctors-by-clinic/{clinic}', [VisitViewController::class, 'getDoctorsByClinic'])
    ->name('visits.doctors-by-clinic');
Route::get('/medical-journals/clinics', [MedicalJournalViewController::class, 'getClinics'])
    ->name('medical-journals.clinics');
Route::get('/medical-journals/doctors', [MedicalJournalViewController::class, 'getDoctors'])
    ->name('medical-journals.doctors');
