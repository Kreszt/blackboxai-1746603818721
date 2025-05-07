<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;

class VisitViewController extends Controller
{
    /**
     * Display the visits listing page.
     */
    public function index()
    {
        $clinics = Clinic::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();
        $statuses = Visit::VISIT_STATUSES;
        
        return view('visits.index', compact('clinics', 'doctors', 'statuses'));
    }

    /**
     * Show the form for creating a new visit.
     */
    public function create()
    {
        $clinics = Clinic::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();
        $visitTypes = Visit::VISIT_TYPES;
        
        return view('visits.create', compact('clinics', 'doctors', 'visitTypes'));
    }

    /**
     * Display the specified visit.
     */
    public function show(Visit $visit)
    {
        $visit->load(['patient', 'clinic', 'doctor', 'creator', 'updater']);
        
        return view('visits.show', compact('visit'));
    }

    /**
     * Show the form for editing the specified visit.
     */
    public function edit(Visit $visit)
    {
        $visit->load(['patient', 'clinic', 'doctor']);
        $clinics = Clinic::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();
        $visitTypes = Visit::VISIT_TYPES;
        $statuses = Visit::VISIT_STATUSES;
        
        return view('visits.edit', compact('visit', 'clinics', 'doctors', 'visitTypes', 'statuses'));
    }

    /**
     * Display the queue summary page.
     */
    public function queueSummary()
    {
        $clinics = Clinic::where('is_active', true)->get();
        
        return view('visits.queue-summary', compact('clinics'));
    }

    /**
     * Get doctors by clinic for AJAX request.
     */
    public function getDoctorsByClinic(Request $request)
    {
        $doctors = Doctor::where('clinic_id', $request->clinic_id)
            ->where('is_active', true)
            ->get();
        
        return response()->json($doctors);
    }
}
