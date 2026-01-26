<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Ujian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; }
        .meta { font-size: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Laporan Nilai Ujian</div>
            <div class="meta">Nama: {{ $user->name ?? '-' }}</div>
            <div class="meta">Email: {{ $user->email ?? '-' }}</div>
        </div>
        <div class="meta">
            Periode:
            @if($startDate || $endDate)
                {{ optional($startDate)->format('d M Y') ?? 'Awal' }} - {{ optional($endDate)->format('d M Y') ?? 'Sekarang' }}
            @else
                Semua waktu
            @endif
            <br>
            Dicetak: {{ now()->format('d M Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:26%;">Exam</th>
                <th style="width:15%;">Jurusan</th>
                <th style="width:15%;">Waktu Mulai</th>
                <th style="width:15%;">Submit</th>
                <th style="width:10%;" class="text-right">Skor</th>
                <th style="width:15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attempts as $index => $attempt)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $attempt->exam->title ?? '-' }}</div>
                        <div class="muted">Access: {{ $attempt->exam->access_code ?? '-' }}</div>
                    </td>
                    <td>{{ $attempt->exam->jurusan ?? '-' }}</td>
                    <td>{{ optional($attempt->started_at)->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ optional($attempt->submitted_at)->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="text-right">
                        {{ $attempt->score_final ?? 0 }}
                        <div class="muted">Raw: {{ $attempt->score_raw }}</div>
                    </td>
                    <td>{{ ucfirst($attempt->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:16px;">Tidak ada ujian pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
