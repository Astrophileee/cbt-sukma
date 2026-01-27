@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Dashboard</h1>
    </div>

    <div class="space-y-8">
        @if($adminStats)
            <section>
                <h2 class="text-lg font-semibold mb-3">Statistik Admin</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
                    @php
                        $adminCards = [
                            ['label' => 'Total User', 'value' => $adminStats['users'], 'icon' => 'user'],
                            ['label' => 'Total Murid', 'value' => $adminStats['students'], 'icon' => 'graduation'],
                            ['label' => 'Total Soal', 'value' => $adminStats['questions'], 'icon' => 'question'],
                            ['label' => 'Total Ujian', 'value' => $adminStats['exams'], 'icon' => 'exam'],
                            ['label' => 'Total Attempt', 'value' => $adminStats['attempts'], 'icon' => 'clock'],
                        ];
                    @endphp
                    @foreach($adminCards as $card)
                        <div class="bg-white/90 rounded-lg border border-yellow-100/80 shadow-sm hover:shadow-md transition p-4 flex items-center gap-3">
                            @include('partials.dashboard-icon', ['type' => $card['icon']])
                            <div>
                                <div class="text-2xl font-bold">{{ $card['value'] }}</div>
                                <div class="text-sm text-gray-600">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($teacherStats)
            <section>
                <h2 class="text-lg font-semibold mb-3">Statistik Guru</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $teacherCards = [
                            ['label' => 'Soal Yang Saya Buat', 'value' => $teacherStats['questions'], 'icon' => 'question'],
                            ['label' => 'Ujian Yang Saya Buat', 'value' => $teacherStats['exams'], 'icon' => 'exam'],
                            ['label' => 'Attempt di Ujian Saya', 'value' => $teacherStats['exam_attempts'], 'icon' => 'clock'],
                        ];
                    @endphp
                    @foreach($teacherCards as $card)
                        <div class="bg-white/90 rounded-lg border border-yellow-100/80 shadow-sm hover:shadow-md transition p-4 flex items-center gap-3">
                            @include('partials.dashboard-icon', ['type' => $card['icon']])
                            <div>
                                <div class="text-2xl font-bold">{{ $card['value'] }}</div>
                                <div class="text-sm text-gray-600">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($studentStats)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Statistik Siswa</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $studentCards = [
                            ['label' => 'Total Attempt', 'value' => $studentStats['total_attempts'], 'icon' => 'clock'],
                            ['label' => 'Selesai', 'value' => $studentStats['completed_attempts'], 'icon' => 'check'],
                        ];
                    @endphp
                    @foreach($studentCards as $card)
                        <div class="bg-white/90 rounded-lg border border-yellow-100/80 shadow-sm hover:shadow-md transition p-4 flex items-center gap-3">
                            @include('partials.dashboard-icon', ['type' => $card['icon']])
                            <div>
                                <div class="text-2xl font-bold">{{ $card['value'] }}</div>
                                <div class="text-sm text-gray-600">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-1">
                    <div class="bg-white/90 rounded-lg border border-yellow-100/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-semibold text-gray-800">Jadwal Ujian (Mendatang)</div>
                            @include('partials.dashboard-icon', ['type' => 'calendar'])
                        </div>
                        <div class="space-y-3">
                            @forelse($upcomingExams as $exam)
                                <div class="border border-yellow-100 rounded-md p-3 flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $exam->title }}</div>
                                        <div class="text-xs text-gray-600">{{ $exam->jurusan }} • {{ $exam->selection_mode }} • {{ $exam->total_questions }} soal</div>
                                        <div class="text-xs text-gray-500 mt-1">Mulai: {{ $exam->start_at ? $exam->start_at->format('d/m/Y H:i') : 'Tanpa jadwal' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">Belum ada jadwal ujian mendatang untuk jurusan Anda.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- <div class="bg-white rounded-lg shadow p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-semibold text-gray-800">Jadwal Ujian (Sudah Terlewat)</div>
                            @include('partials.dashboard-icon', ['type' => 'history'])
                        </div>
                        <div class="space-y-3">
                            @forelse($pastExams as $exam)
                                <div class="border border-gray-200 rounded-md p-3 flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $exam->title }}</div>
                                        <div class="text-xs text-gray-600">{{ $exam->jurusan }} • {{ $exam->selection_mode }} • {{ $exam->total_questions }} soal</div>
                                        <div class="text-xs text-gray-500 mt-1">Mulai: {{ $exam->start_at ? $exam->start_at->format('d/m/Y H:i') : '-' }}</div>
                                    </div>
                                    <div class="text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded">Selesai</div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">Belum ada riwayat ujian untuk jurusan Anda.</div>
                            @endforelse
                        </div>
                    </div> --}}
                </div>

                {{-- <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="font-semibold text-gray-800">Daftar Ujian</div>
                        @include('partials.dashboard-icon', ['type' => 'list'])
                    </div>
                    <div class="space-y-3">
                        @forelse($upcomingExams->merge($pastExams)->sortBy('start_at') as $exam)
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between border border-gray-200 rounded-lg p-3 gap-2">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $exam->title }}</div>
                                    <div class="text-xs text-gray-600">{{ $exam->jurusan }} • {{ $exam->selection_mode }} • {{ $exam->total_questions }} soal</div>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-700">
                                    <div class="flex items-center gap-1">
                                        @include('partials.dashboard-icon', ['type' => 'calendar-mini'])
                                        <span>{{ $exam->start_at ? $exam->start_at->format('d/m/Y H:i') : 'Tanpa jadwal' }}</span>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs {{ $exam->start_at && $exam->start_at->isPast() ? 'bg-gray-100 text-gray-700' : 'bg-green-50 text-green-700' }}">
                                        {{ $exam->start_at && $exam->start_at->isPast() ? 'Selesai' : 'Akan datang' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Tidak ada ujian yang relevan untuk jurusan Anda.</div>
                        @endforelse
                    </div>
                </div> --}}
            </section>
        @endif
    </div>

@vite(['resources/js/app.js'])

@endsection
