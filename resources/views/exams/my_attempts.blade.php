@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4">Nilai Saya</h1>

    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <form action="{{ route('exams.attempts.report') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Dari tanggal</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('start_date')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sampai tanggal</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('end_date')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Generate PDF</button>
                <span class="text-xs text-gray-500">Kosongkan tanggal untuk semua attempt.</span>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Exam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attempts as $attempt)
                    <tr>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="font-semibold">{{ $attempt->exam->title ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Access Code: <span class="font-mono">{{ $attempt->exam->access_code ?? '-' }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ ucfirst($attempt->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            {{ $attempt->score_final ?? 0 }} /
                            <span class="text-xs text-gray-500">raw: {{ $attempt->score_raw }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            <a href="{{ route('exams.attempt.show', $attempt) }}" class="text-blue-600 hover:text-blue-800 text-sm">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 text-sm">Belum ada attempt.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
