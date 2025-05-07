<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMedicalJournalRequest;
use App\Http\Requests\UpdateMedicalJournalRequest;
use App\Models\MedicalJournal;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalJournalController extends Controller
{
    /**
     * Display a listing of medical journals.
     */
    public function index(Request $request)
    {
        try {
            $query = MedicalJournal::with(['medicalRecord.patient', 'doctor', 'clinic'])
                ->when($request->date, function ($query, $date) {
                    return $query->whereDate('date', $date);
                })
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    return $query->whereBetween('date', [$request->start_date, $request->end_date]);
                })
                ->when($request->doctor_id, function ($query, $doctorId) {
                    return $query->where('doctor_id', $doctorId);
                })
                ->when($request->clinic_id, function ($query, $clinicId) {
                    return $query->where('clinic_id', $clinicId);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->whereHas('medicalRecord.patient', function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('nomor_rm', 'like', "%{$search}%");
                    });
                });

            $journals = $query->latest('date')
                            ->paginate(10)
                            ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => $journals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created medical journal in storage.
     */
    public function store(CreateMedicalJournalRequest $request)
    {
        try {
            DB::beginTransaction();

            $journal = MedicalJournal::create($request->validated());

            // Load relationships for response
            $journal->load(['medicalRecord.patient', 'doctor', 'clinic']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Catatan medis berhasil dibuat',
                'data' => $journal
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified medical journal.
     */
    public function show(MedicalJournal $medicalJournal)
    {
        try {
            $medicalJournal->load([
                'medicalRecord.patient',
                'visit',
                'doctor',
                'clinic',
                'creator',
                'updater'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $medicalJournal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified medical journal in storage.
     */
    public function update(UpdateMedicalJournalRequest $request, MedicalJournal $medicalJournal)
    {
        try {
            DB::beginTransaction();

            $medicalJournal->update($request->validated());

            // Reload the model with relationships
            $medicalJournal = $medicalJournal->fresh([
                'medicalRecord.patient',
                'doctor',
                'clinic'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Catatan medis berhasil diperbarui',
                'data' => $medicalJournal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified medical journal from storage.
     */
    public function destroy(MedicalJournal $medicalJournal)
    {
        try {
            if (!$medicalJournal->canBeUpdated()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Catatan medis yang sudah selesai atau dirujuk tidak dapat dihapus'
                ], 422);
            }

            DB::beginTransaction();

            $medicalJournal->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Catatan medis berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get medical journals by patient's medical record number.
     */
    public function getByPatient(Request $request)
    {
        try {
            $request->validate([
                'nomor_rm' => 'required|string|exists:medical_records,nomor_rm'
            ], [
                'nomor_rm.required' => 'Nomor RM wajib diisi',
                'nomor_rm.exists' => 'Nomor RM tidak ditemukan'
            ]);

            $medicalRecord = MedicalRecord::where('nomor_rm', $request->nomor_rm)->firstOrFail();

            $journals = MedicalJournal::with(['doctor', 'clinic'])
                ->where('medical_record_id', $medicalRecord->id)
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->latest('date')
                ->paginate(10)
                ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'medical_record' => $medicalRecord->load('patient'),
                    'journals' => $journals
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update medical journal status.
     */
    public function updateStatus(Request $request, MedicalJournal $medicalJournal)
    {
        try {
            $request->validate([
                'status' => ['required', 'string', Rule::in(MedicalJournal::STATUSES)],
                'referral' => ['required_if:status,referred', 'nullable', 'string']
            ], [
                'status.required' => 'Status wajib diisi',
                'status.in' => 'Status tidak valid',
                'referral.required_if' => 'Rujukan wajib diisi saat mengubah status menjadi dirujuk'
            ]);

            if (!$medicalJournal->canBeUpdated()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status catatan medis tidak dapat diubah'
                ], 422);
            }

            DB::beginTransaction();

            $medicalJournal->update([
                'status' => $request->status,
                'referral' => $request->referral
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status catatan medis berhasil diperbarui',
                'data' => $medicalJournal->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status catatan medis',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
