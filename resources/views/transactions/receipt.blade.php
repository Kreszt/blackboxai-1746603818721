<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $transaction->transaction_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 14px;
            font-weight: bold;
        }
        .receipt-number {
            margin: 10px 0;
            font-size: 11px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .patient-info {
            margin-bottom: 15px;
        }
        .patient-info p {
            margin: 3px 0;
        }
        .items {
            width: 100%;
            margin: 15px 0;
        }
        .items th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
        }
        .items td {
            padding: 3px 0;
            vertical-align: top;
        }
        .amount {
            text-align: right;
        }
        .total {
            margin-top: 10px;
            text-align: right;
        }
        .total-line {
            margin: 3px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-name">KLINIK PRATAMA</div>
        <div>Jl. Contoh No. 123, Jakarta</div>
        <div>Telp: (021) 123-4567</div>
    </div>

    <div class="receipt-number">
        No: {{ $transaction->transaction_number }}<br>
        Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}
    </div>

    <div class="divider"></div>

    <div class="patient-info">
        <p>Pasien: {{ $transaction->patient->nama_lengkap }}</p>
        <p>No. RM: {{ $transaction->patient->nomor_rm }}</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th width="45%">Item</th>
                <th width="15%">Qty</th>
                <th width="20%">Harga</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>
                    {{ $item->description }}
                    @if($item->notes)
                        <br><small>{{ $item->notes }}</small>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td class="amount">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="amount">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="total">
        <div class="total-line">
            <strong>Total:</strong> 
            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
        </div>
        @if($transaction->discount > 0)
        <div class="total-line">
            <strong>Diskon:</strong> 
            Rp {{ number_format($transaction->discount, 0, ',', '.') }}
        </div>
        @endif
        <div class="total-line">
            <strong>Total Bayar:</strong> 
            Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}
        </div>
        <div class="total-line">
            <strong>Metode Pembayaran:</strong> 
            {{ strtoupper($transaction->payment_method) }}
        </div>
    </div>

    <div class="divider"></div>

    <div class="footer">
        Terima kasih atas kunjungan Anda<br>
        Semoga lekas sembuh
    </div>
</body>
</html>
