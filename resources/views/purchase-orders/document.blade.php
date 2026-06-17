<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $po->po_number }} · Dokumen PO</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1e293b; margin: 0; padding: 40px; font-size: 13px; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0f172a; padding-bottom: 16px; }
        .logo { width: 40px; height: 40px; background: #f59e0b; color: #0f172a; font-weight: bold; display: inline-grid; place-items: center; border-radius: 8px; }
        h1 { font-size: 22px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th { background: #0f172a; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .right { text-align: right; }
        .total { font-weight: bold; font-size: 15px; }
        .grid { display: flex; gap: 48px; margin-top: 24px; }
        .muted { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        .sign { margin-top: 60px; display: flex; justify-content: space-between; }
        .sign div { text-align: center; width: 200px; }
        .line { border-top: 1px solid #94a3b8; margin-top: 56px; padding-top: 4px; }
        @media print { .noprint { display: none; } body { padding: 20px; } }
    </style>
</head>
<body onload="window.print && setTimeout(()=>{}, 200)">
    <div class="noprint" style="margin-bottom:16px;">
        <button onclick="window.print()" style="background:#0f172a;color:#fff;border:0;padding:8px 16px;border-radius:8px;cursor:pointer;">Cetak / Simpan PDF</button>
    </div>

    <div class="head">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="logo">MJ</span>
                <div><strong style="font-size:16px;">CV. Mugi Jaya</strong><br><span class="muted">Aluminium · Kaca · Bekasi</span></div>
            </div>
        </div>
        <div style="text-align:right;">
            <h1>PURCHASE ORDER</h1>
            <div class="muted">{{ $po->po_number }}</div>
            <div style="margin-top:4px;">Status: <strong>{{ strtoupper($po->status) }}</strong></div>
        </div>
    </div>

    <div class="grid">
        <div>
            <div class="muted">Supplier</div>
            <strong>{{ $po->supplier->name }}</strong><br>
            {{ $po->supplier->address }}<br>
            {{ $po->supplier->city }} · {{ $po->supplier->contact_phone }}
        </div>
        <div>
            <div class="muted">Detail</div>
            Dibuat: {{ $po->created_at->format('d M Y') }}<br>
            Dibutuhkan: {{ $po->needed_date?->format('d M Y') ?? '—' }}<br>
            Dibuat oleh: {{ $po->creator->full_name }}
        </div>
    </div>

    <table>
        <thead><tr><th>No</th><th>SKU</th><th>Material</th><th class="right">Qty</th><th class="right">Harga Satuan</th><th class="right">Subtotal</th></tr></thead>
        <tbody>
            @foreach($po->items as $it)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $it->material->sku }}</td>
                    <td>{{ $it->material->name }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }} {{ $it->material->unit }}</td>
                    <td class="right">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><td colspan="5" class="right total">TOTAL ESTIMASI</td><td class="right total">Rp {{ number_format($po->total_estimated, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <div class="sign">
        <div><div class="line">Dibuat oleh<br><strong>{{ $po->creator->full_name }}</strong></div></div>
        <div><div class="line">Disetujui oleh<br><strong>{{ $po->approver?->full_name ?? '—' }}</strong></div></div>
    </div>
</body>
</html>
