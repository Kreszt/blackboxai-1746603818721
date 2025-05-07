<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        try {
            $query = Transaction::with(['patient', 'items.reference'])
                ->when($request->date, function ($query, $date) {
                    return $query->whereDate('visit_date', $date);
                })
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    return $query->dateBetween($request->start_date, $request->end_date);
                })
                ->when($request->patient_id, function ($query, $patientId) {
                    return $query->where('patient_id', $patientId);
                })
                ->when($request->payment_method, function ($query, $method) {
                    return $query->paymentMethod($method);
                })
                ->when($request->status, function ($query, $status) {
                    return $query->status($status);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->search($search);
                });

            $transactions = $query->latest()
                                ->paginate(10)
                                ->withQueryString();

            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(CreateTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::create($request->safe()->except('items'));

            // Create transaction items
            foreach ($request->items as $item) {
                $transaction->items()->create($item);
            }

            // Calculate totals
            $transaction->calculateTotals();
            $transaction->save();

            // Load relationships for response
            $transaction->load(['patient', 'items.reference']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        try {
            $transaction->load([
                'patient',
                'items.reference',
                'creator',
                'updater'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            $transaction->update($request->safe()->except(['items', 'items_to_remove']));

            // Handle items to remove
            if ($request->items_to_remove) {
                TransactionItem::whereIn('id', $request->items_to_remove)
                    ->where('transaction_id', $transaction->id)
                    ->delete();
            }

            // Update or create items
            foreach ($request->items as $item) {
                if (isset($item['id'])) {
                    $transaction->items()->where('id', $item['id'])->update($item);
                } else {
                    $transaction->items()->create($item);
                }
            }

            // Recalculate totals
            $transaction->calculateTotals();
            $transaction->save();

            // Reload the model with relationships
            $transaction = $transaction->fresh(['patient', 'items.reference']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diperbarui',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction)
    {
        try {
            if (!$transaction->canBeUpdated()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi yang sudah selesai tidak dapat dihapus'
                ], 422);
            }

            DB::beginTransaction();

            $transaction->items()->delete();
            $transaction->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update transaction status.
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        try {
            $request->validate([
                'status' => ['required', Rule::in(Transaction::STATUSES)],
                'payment_method' => [
                    Rule::requiredIf($request->status === 'paid'),
                    Rule::in(Transaction::PAYMENT_METHODS)
                ]
            ], [
                'status.required' => 'Status wajib diisi',
                'status.in' => 'Status tidak valid',
                'payment_method.required_if' => 'Metode pembayaran wajib diisi untuk transaksi yang dibayar',
                'payment_method.in' => 'Metode pembayaran tidak valid'
            ]);

            if (!$transaction->canBeUpdated()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status transaksi tidak dapat diubah'
                ], 422);
            }

            DB::beginTransaction();

            $transaction->update([
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'is_completed' => in_array($request->status, ['paid', 'canceled'])
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status transaksi berhasil diperbarui',
                'data' => $transaction->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF receipt.
     */
    public function receipt(Transaction $transaction)
    {
        try {
            $transaction->load(['patient', 'items.reference']);

            $pdf = PDF::loadView('transactions.receipt', [
                'transaction' => $transaction
            ]);

            // Configure for thermal printer (80mm width)
            $pdf->setPaper([0, 0, 226.77, 850], 'portrait');

            return $pdf->stream("receipt-{$transaction->transaction_number}.pdf");

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat struk',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
