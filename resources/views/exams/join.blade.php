@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto bg-white shadow rounded-lg p-6">
        <h1 class="text-xl font-bold mb-4">Masuk Ujian</h1>
        <form action="{{ route('exams.join') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Access Code *</label>
                <input type="text" name="access_code" value="{{ old('access_code') }}" required class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 text-sm uppercase">
                @error('access_code')
                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-black text-white rounded-md text-sm hover:bg-gray-800">Mulai</button>
            </div>
        </form>
    </div>

    @if (session('success') || session('error'))
        <div id="flash-message"
            data-type="{{ session('success') ? 'success' : 'error' }}"
            data-message="{{ session('success') ?? session('error') }}">
        </div>
    @endif
@endsection
