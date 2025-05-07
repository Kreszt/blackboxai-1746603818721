<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index()
    {
        $patients = Patient::latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $patients
        ]);
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(CreatePatientRequest $request)
    {
        try {
            DB::beginTransaction();

            $patient = Patient::create($request->validated());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pasien berhasil ditambahkan',
                'data' => $patient
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        return response()->json([
            'status' => 'success',
            'data' => $patient->load(['creator', 'updater'])
        ]);
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        try {
            DB::beginTransaction();

            $patient->update($request->validated());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data pasien berhasil diperbarui',
                'data' => $patient->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy(Patient $patient)
    {
        try {
            DB::beginTransaction();

            $patient->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data pasien berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data pasien',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search patients by various parameters.
     */
    public function search(Request $request)
    {
        $query = Patient::query();

        // Search by nomor_rm
        if ($request->has('nomor_rm')) {
            $query->where('nomor_rm', 'like', '%' . $request->nomor_rm . '%');
        }

        // Search by nama_lengkap
        if ($request->has('nama')) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama . '%');
        }

        // Search by NIK
        if ($request->has('nik')) {
            $query->where('nik', 'like', '%' . $request->nik . '%');
        }

        $patients = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $patients
        ]);
    }
}
