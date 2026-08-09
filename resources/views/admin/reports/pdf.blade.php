<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1A2848;
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #1A2848;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
            color: #1A2848;
        }

        .header p {
            margin: 0;
            font-size: 10px;
            color: #64748b;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 18px;
            font-size: 10px;
        }

        .meta-table td {
            padding: 2px 0;
            color: #475569;
        }

        .meta-table td.label {
            width: 110px;
            color: #94a3b8;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            width: 20%;
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        .summary-table .value {
            font-size: 15px;
            font-weight: bold;
            color: #1A2848;
            display: block;
            margin-bottom: 2px;
        }

        .summary-table .label {
            font-size: 8.5px;
            color: #94a3b8;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1A2848;
            margin: 22px 0 8px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.data th {
            background: #1A2848;
            color: #ffffff;
            font-size: 9.5px;
            text-align: left;
            padding: 6px 8px;
        }

        table.data td {
            font-size: 10px;
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        table.data tr:nth-child(even) td {
            background: #f8fafc;
        }

        .status-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .status-grid td {
            width: 25%;
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
        }

        .status-grid .dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-grid .value {
            font-size: 13px;
            font-weight: bold;
            color: #1A2848;
        }

        .status-grid .label {
            font-size: 8.5px;
            color: #94a3b8;
        }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Laporan Peminjaman Barang</h1>
        <p>Sistem Peminjaman Barang Kampus &mdash; INSTIKI</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Rentang Waktu</td>
            <td>: {{ $rangeLabel }}</td>
            <td class="label">Kategori</td>
            <td>: {{ $categoryLabel }}</td>
        </tr>
        <tr>
            <td class="label">Dicetak pada</td>
            <td colspan="3">: {{ $printedAt }}</td>
        </tr>
    </table>

    {{-- Ringkasan --}}
    <table class="summary-table">
        <tr>
            <td>
                <span class="value">{{ $summary['total_transactions'] }}</span>
                <span class="label">Total Pengajuan</span>
            </td>
            <td>
                <span class="value">{{ $summary['total_unit'] }}</span>
                <span class="label">Total Unit Dipinjam</span>
            </td>
            <td>
                <span class="value">{{ $summary['total_peminjam'] }}</span>
                <span class="label">Peminjam Aktif</span>
            </td>
            <td>
                <span class="value">{{ $summary['avg_duration'] }} hari</span>
                <span class="label">Rata-rata Durasi</span>
            </td>
            <td>
                <span class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</span>
                <span class="label">Total Pendapatan</span>
            </td>
        </tr>
    </table>

    {{-- Breakdown Status --}}
    <div class="section-title">Status Pengajuan pada Rentang Ini</div>
    <table class="status-grid">
        <tr>
            <td>
                <span class="dot" style="background:#F59E0B;"></span>
                <span class="value">{{ $statusBreakdown['pending'] }}</span><br>
                <span class="label">Tertunda</span>
            </td>
            <td>
                <span class="dot" style="background:#10B981;"></span>
                <span class="value">{{ $statusBreakdown['booked'] }}</span><br>
                <span class="label">Disetujui / Dipinjam</span>
            </td>
            <td>
                <span class="dot" style="background:#3B82F6;"></span>
                <span class="value">{{ $statusBreakdown['completed'] }}</span><br>
                <span class="label">Selesai</span>
            </td>
            <td>
                <span class="dot" style="background:#DC2626;"></span>
                <span class="value">{{ $statusBreakdown['rejected'] }}</span><br>
                <span class="label">Ditolak</span>
            </td>
        </tr>
    </table>

    {{-- Laporan Per Barang --}}
    <div class="section-title">Laporan Per Barang</div>
    <table class="data">
        <thead>
            <tr>
                <th>Barang</th>
                <th>Kategori</th>
                <th>Unit</th>
                <th>Frekuensi</th>
                <th>Durasi Rata-rata</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($itemRows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['total_unit'] }}</td>
                    <td>{{ $row['frequency'] }}x</td>
                    <td>{{ $row['avg_duration'] }} hari</td>
                    <td>{{ $row['total_revenue'] > 0 ? 'Rp ' . number_format($row['total_revenue'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Laporan Per Peminjam --}}
    <div class="section-title">Laporan Per Peminjam</div>
    <table class="data">
        <thead>
            <tr>
                <th>Nama</th>
                <th>NIM/NIDN</th>
                <th>Pengajuan</th>
                <th>Unit</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($borrowerRows as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['nim_nidn'] ?? '-' }}</td>
                    <td>{{ $row['total_requests'] }}x</td>
                    <td>{{ $row['total_unit'] }}</td>
                    <td>{{ $row['total_fine'] > 0 ? 'Rp ' . number_format($row['total_fine'], 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tidak ada data pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem peminjaman barang kampus INSTIKI pada {{ $printedAt }}.
    </div>

</body>

</html>