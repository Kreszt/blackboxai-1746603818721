<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitController extends Controller
{
    /**
     * Display a listing of visits.
     */
    public function index(Request $request)
    {
        try {
            $query = Visit::with(['patient', 'clinic', 'doctor'])
                ->when($request->date, function ($query, $date) {
                    return $query->whereDate('visit_date', $date);
                })
                ->when($request->clinic_id, function ($query, $clinicId) {
                    return $query->where('clinic_id', $clinicId);
                })
                ->when($request->doctor_id, function ($query, $doctorId) {
                    return $query->where('doctor_id', $doctorId);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->whereHas('patient', function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('nomor_rm', 'like', "%{$search}%");
                    });
                });

            // Default sort by visit_date desc and queue_order asc
            $visits = $query->orderBy('visit_date', 'desc')
                           ->orderBy('queue_order', 'asc')
                           ->paginate(10)
                           ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => $visits
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created visit in storage.
     */
    public function store(CreateVisitRequest $request)
    {
        try {
            DB::beginTransaction();

            $visit = Visit::create($request->validated());

            // Load relationships for response
            $visit->load(['patient', 'clinic', 'doctor']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kunjungan berhasil dibuat',
                'data' => $visit
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified visit.
     */
    public function show(Visit $visit)
    {
        try {
            $visit->load(['patient', 'clinic', 'doctor', 'creator', 'updater']);

            return response()->json([
                'status' => 'success',
                'data' => $visit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified visit in storage.
     */
    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        try {
            DB::beginTransaction();

            $visit->update($request->validated());

            // Reload the model with relationships
            $visit = $visit->fresh(['patient', 'clinic', 'doctor']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kunjungan berhasil diperbarui',
                'data' => $visit
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified visit from storage.
     */
    public function destroy(Visit $visit)
    {
        try {
            if (!$visit->canBeCancelled()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kunjungan tidak dapat dihapus karena status tidak valid'
                ], 422);
            }

            DB::beginTransaction();

            // Instead of deleting, we'll mark it as cancelled
            $visit->update(['status' => 'cancelled']);
            $visit->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kunjungan berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update visit status.
     */
    public function updateStatus(Request $request, Visit $visit)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(Visit::VISIT_STATUSES)]
        ], [
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status tidak valid'
        ]);

        try {
            DB::beginTransaction();

            if (!$visit->updateStatus($request->status)) {
                throw new \Exception('Perubahan status tidak valid');
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status kunjungan berhasil diperbarui',
                'data' => $visit->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status kunjungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get queue summary for today.
     */
    public function queueSummary(Request $request)
    {
        try {
            $date = $request->date ?? now()->toDateString();
            $clinicId = $request->clinic_id;

            $summary = Visit::where('visit_date', $date)
                ->when($clinicId, function ($query, $clinicId) {
                    return $query->where('clinic_id', $clinicId);
                })
                ->selectRaw('
                    status,
                    COUNT(*) as total,
                    MIN(queue_order) as min_queue,
                    MAX(queue_order) as max_queue
                ')
                ->groupBy('status')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil ringkasan antrian',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
