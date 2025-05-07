<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalJournal;
use Illuminate\Http\Request;

class MedicalJournalViewController extends Controller
{
    /**
     * Display the medical journals listing page.
     */
    public function index()
    {
        return view('medical-journals.index');
    }

    /**
     * Get clinics for dropdown.
     */
    public function getClinics()
    {
        $clinics = Clinic::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => $clinics
        ]);
    }

    /**
     * Get doctors for dropdown.
     */
    public function getDoctors(Request $request)
    {
        $query = Doctor::where('is_active', true);

        if ($request->clinic_id) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $doctors = $query->orderBy('name')
            ->get(['id', 'name', 'clinic_id']);

        return response()->json([
            'status' => 'success',
            'data' => $doctors
        ]);
    }
}
