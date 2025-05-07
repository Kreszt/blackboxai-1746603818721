<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientViewController extends Controller
{
    /**
     * Display the patients listing page.
     */
    public function index()
    {
        return view('patients.index');
    }

    /**
     * Display the patient creation page.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Display the patient edit page.
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', [
            'patientId' => $patient->id
        ]);
    }
}
