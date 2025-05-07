<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request)
    {
        try {
            $query = Prescription::with(['journal.medicalRecord.patient', 'journal.doctor', 'journal.clinic', 'items.medication'])
                ->when($request->date, function ($query, $date) {
                    return $query->whereDate('created_at', $date);
                })
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    return $query->dateBetween($request->start_date, $request->end_date);
                })
                ->when($request->doctor_id, function ($query, $doctorId) {
                    return $query->byDoctor($doctorId);
                })
                ->when($request->clinic_id, function ($query, $clinicId) {
                    return $query->byClinic($clinicId);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->status($status);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->search($search);
                });

            $prescriptions = $query->latest()
                                 ->paginate(10)
                                 ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => $prescriptions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(CreatePrescriptionRequest $request)
    {
        try {
            DB::beginTransaction();

            $prescription = Prescription::create($request->safe()->except('items'));

            // Create prescription items
            foreach ($request->items as $item) {
                $prescription->items()->create($item);
            }

            // Load relationships for response
            $prescription->load(['journal.medicalRecord.patient', 'journal.doctor', 'journal.clinic', 'items.medication']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Resep berhasil dibuat',
                'data' => $prescription
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription)
    {
        try {
            $prescription->load([
                'journal.medicalRecord.patient',
                'journal.doctor',
                'journal.clinic',
                'items.medication',
                'creator',
                'updater',
                'reviser'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $prescription
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified prescription in storage.
     */
    public function update(UpdatePrescriptionRequest $request, Prescription $prescription)
    {
        try {
            DB::beginTransaction();

            $prescription->update($request->safe()->except(['items', 'items_to_remove']));

            // Handle items to remove
            if ($request->items_to_remove) {
                PrescriptionItem::whereIn('id', $request->items_to_remove)
                    ->where('prescription_id', $prescription->id)
                    ->delete();
            }

            // Update or create items
            foreach ($request->items as $item) {
                if (isset($item['id'])) {
                    $prescription->items()->where('id', $item['id'])->update($item);
                } else {
                    $prescription->items()->create($item);
                }
            }

            // Reload the model with relationships
            $prescription = $prescription->fresh([
                'journal.medicalRecord.patient',
                'journal.doctor',
                'journal.clinic',
                'items.medication'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Resep berhasil diperbarui',
                'data' => $prescription
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified prescription from storage.
     */
    public function destroy(Prescription $prescription)
    {
        try {
            if (!$prescription->canBeUpdated()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resep yang sudah final atau direvisi tidak dapat dihapus'
                ], 422);
            }

            DB::beginTransaction();

            $prescription->items()->delete();
            $prescription->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Resep berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescriptions by patient.
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

            $prescriptions = Prescription::with([
                    'journal.medicalRecord.patient',
                    'journal.doctor',
                    'journal.clinic',
                    'items.medication'
                ])
                ->whereHas('journal.medicalRecord', function ($query) use ($request) {
                    $query->where('nomor_rm', $request->nomor_rm);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => $prescriptions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revise the specified prescription.
     */
    public function revise(Request $request, Prescription $prescription)
    {
        try {
            $request->validate([
                'revised_reason' => 'required|string'
            ], [
                'revised_reason.required' => 'Alasan revisi wajib diisi'
            ]);

            if (!$prescription->canBeRevised()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resep tidak dapat direvisi'
                ], 422);
            }

            DB::beginTransaction();

            $prescription->update([
                'status' => 'revised',
                'revised_reason' => $request->revised_reason,
                'revised_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Resep berhasil direvisi',
                'data' => $prescription->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal merevisi resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
