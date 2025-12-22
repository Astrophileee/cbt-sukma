@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4">Penilaian Essay</h1>
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Exam</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Peserta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Skor</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attempts as $attempt)
                    <tr>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="font-semibold">{{ $attempt->exam->title ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Access Code: <span class="font-mono">{{ $attempt->exam->access_code ?? '-' }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $attempt->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ ucfirst($attempt->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $attempt->score_final ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('exams.grade.show', $attempt) }}" class="text-blue-600 hover:text-blue-800 text-sm">Nilai / Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">Belum ada attempt yang perlu dinilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
