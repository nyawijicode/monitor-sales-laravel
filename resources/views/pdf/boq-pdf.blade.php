<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>BOQ - {{ $boq->boq_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h3 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 11px;
            margin: 2px 0;
        }

        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table {
            border: 1px solid #000;
        }

        .info-table td {
            padding: 4px 8px;
            border: 1px solid #000;
        }

        .info-table td:first-child {
            width: 25%;
            font-weight: bold;
        }

        .items-table {
            border: 1px solid #000;
            margin-top: 10px;
        }

        .items-table th,
        .items-table td {
            padding: 5px;
            border: 1px solid #000;
            text-align: center;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            font-size: 10px;
        }

        .items-table td.left {
            text-align: left;
        }

        .items-table td.right {
            text-align: right;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .signatures {
            margin-top: 30px;
            text-align: center;
        }

        .signatures .date {
            text-align: right;
            margin-bottom: 20px;
            margin-right: 50px;
        }

        .signature-row {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .signature-cell {
            display: table-cell;
            width: 25%;
            /* Changed from 33% to 25% for 4 columns */
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .signature-cell .title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .signature-cell .name {
            font-weight: bold;
            margin-top: 5px;
            text-decoration: underline;
        }

        .signature-cell .position {
            font-size: 10px;
            color: #666;
        }

        .signature-img {
            height: 50px;
            margin: 10px 0;
        }

        .signature-img img {
            max-height: 45px;
            max-width: 120px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <table style="width: 100%; border: none; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 10px;">
        <tr>
            @if($company && $company->logo)
                <td style="width: 100px; vertical-align: middle; text-align: center; border: none; padding: 0;">
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo"
                        style="max-width: 100px; max-height: 80px; width: auto; height: auto; object-fit: contain; display: block;">
                </td>
            @endif
            <td style="vertical-align: middle; text-align: center; border: none; padding: 0;">
                <h3 style="margin: 0; padding: 0;">SURAT PENAWARAN</h3>
                <p style="font-weight: bold; font-size: 16px; margin: 5px 0; padding: 0;">
                    {{ strtoupper($company->name ?? 'PERUSAHAAN') }}
                </p>
                <p style="margin: 2px 0; padding: 0;">No BOQ: {{ $boq->boq_number }}</p>
                @if($company && $company->address)
                    <p style="margin: 2px 0; padding: 0; font-size: 10px;">{{ $company->address }}</p>
                @endif
            </td>
        </tr>
    </table>

    <!-- Info Table -->
    <table class="info-table">
        <tr>
            <td>Nomor Visit</td>
            <td>{{ $boq->visit->visit_number }}</td>
            <td>Email Sales</td>
            <td>{{ $boq->user->email ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>{{ \Carbon\Carbon::parse($boq->visit->visit_date)->format('d/m/Y') }}</td>
            <td>Kontak Sales</td>
            <td>{{ $boq->user->userInfo->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td colspan="3">{{ $boq->visit->customer->nama_instansi }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td colspan="3">{{ $boq->visit->customer->alamat }}</td>
        </tr>
        <tr>
            <td>Wilayah</td>
            <td colspan="3">
                {{ $boq->visit->customer->city->province->name ?? '' }} -
                {{ $boq->visit->customer->city->name ?? '' }}
            </td>
        </tr>
        <tr>
            <td>Sales</td>
            <td colspan="3">{{ $boq->user->name }}</td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 20%;">NAMA BARANG/JASA</th>
                <th style="width: 20%;">SPESIFIKASI</th>
                <th style="width: 10%;">FOTO</th>
                <th style="width: 6%;">QTY</th>
                <th style="width: 13%;">HARGA SATUAN</th>
                <th style="width: 13%;">HARGA PENAWARAN</th>
                <th style="width: 14%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boq->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="left">{{ $item->nama_barang }}</td>
                    <td class="left">{{ $item->spesifikasi ?? '-' }}</td>
                    <td style="text-align: center; padding: 3px;">
                        @if($item->foto)
                            <img src="{{ public_path('storage/' . $item->foto) }}" alt="Foto"
                                style="max-width: 60px; max-height: 60px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->qty }}</td>
                    <td class="right">Rp {{ number_format($item->harga_barang, 0, ',', '.') }}</td>
                    <td class="right">
                        @if($item->harga_penawaran)
                            Rp {{ number_format($item->harga_penawaran, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" style="text-align: right; padding-right: 10px;">TOTAL</td>
                <td class="right">Rp {{ number_format($boq->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Signatures (only for internal version) -->
    @if($type === 'internal' && $boq->persetujuan)
        <div class="signatures">
            <div class="date">
                {{ $location }}, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
            </div>

            <div class="signature-row">
                {{-- Pengaju (Creator) --}}
                <div class="signature-cell">
                    <div class="title">Pengaju</div>

                    @if($boq->user->userInfo && $boq->user->userInfo->signature)
                        <div class="signature-img">
                            <img src="{{ public_path('storage/' . $boq->user->userInfo->signature) }}" alt="TTD">
                        </div>
                    @else
                        <div class="signature-img"></div>
                    @endif

                    <div class="name">{{ $boq->user->name }}</div>
                    <div class="position">{{ $boq->user->userInfo->position->name ?? 'Staff' }}</div>
                </div>

                {{-- Approvers --}}
                @foreach($boq->persetujuan->approvers->sortBy('sort_order') as $approver)
                    <div class="signature-cell">
                        <div class="title">Yang Menyetujui</div>

                        @if($approver->user->userInfo && $approver->user->userInfo->signature)
                            <div class="signature-img">
                                <img src="{{ public_path('storage/' . $approver->user->userInfo->signature) }}" alt="TTD">
                            </div>
                        @else
                            <div class="signature-img"></div>
                        @endif

                        <div class="name">{{ $approver->user->name }}</div>
                        <div class="position">{{ $approver->user->userInfo->position->name ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</body>

</html>