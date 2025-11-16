@extends('layouts.admin')

@section('title', 'Church Service')
@section('header', 'Church Service Schedule')

@section('content')
{{-- ── SweetAlert Flash Messages ───────────────────── --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        });
    </script>
@endif

{{-- ── Main Card ───────────────────────────────────── --}}
<div class="w-full max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 relative z-10">
    <h2 class="text-3xl font-bold text-center text-blue-700 mb-6">Set Church Service Schedule</h2>

    <form action="{{ route('churchservice.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Title Field --}}
        <div>
            <label for="title" class="block text-lg lg:text-xl font-semibold text-gray-700 mb-1">Service Title</label>
            <input type="text" name="title" id="title"
                   value="{{ old('title', $service->title ?? '') }}"
                   class="w-full border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none rounded-lg p-2.5">
        </div>

        {{-- Day of Week --}}
        <div >
            <label for="day_of_week" class="block text-lg lg:text-xl font-semibold text-gray-700 mb-1">Day of Week</label>
            <select name="day_of_week" id="day_of_week"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    required>
               @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
    <option value="{{ $day }}"
        {{ (old('day_of_week', $service->day_of_week ?? '') == $day) ? 'selected' : '' }}>
        {{ $day }}
    </option>
@endforeach

            </select>
        </div>

        {{-- Time Range --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="start_time" class="block text-lg lg:text-xl font-semibold text-gray-700 mb-1">Start Time</label>
                <input type="time" name="start_time" id="start_time"
                       value="{{ old('start_time', isset($service->start_time) ? substr($service->start_time, 0, 5) : '') }}"
                       class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none rounded-lg p-2.5"
                       required>
            </div>
            <div>
                <label for="end_time" class="block text-lg lg:text-xl font-semibold text-gray-700 mb-1">End Time</label>
                <input type="time" name="end_time" id="end_time"
                       value="{{ old('end_time', isset($service->end_time) ? substr($service->end_time, 0, 5) : '') }}"
                       class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none rounded-lg p-2.5"
                       required>
            </div>
        </div>

        {{-- QR Code Section --}}
        @if (isset($qrUrl))
<div class="mt-8 border-t pt-6 text-center">
    <p class="text-lg lg:text-xl text-gray-600 mb-3">Permanent Attendance QR Code</p>

    {{-- Centered QR and Button --}}
    <div class="flex justify-center">
        <div class="flex flex-col items-center">
            {{-- Clickable QR Image --}}
            <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrUrl) }}"
               download="church_service_qr.png"
               class="transform transition hover:scale-105 duration-200 mb-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrUrl) }}"
                     alt="QR Code" class="shadow-md rounded">
            </a>

            {{-- Download Button BELOW the QR --}}
            <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrUrl) }}"
               download="church_service_qr.png"
               class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow-md transition text-base lg:text-lg">
                Download QR Code
            </a>
        </div>
    </div>
</div>
        @endif

        {{-- Submit Button --}}
        <div class="pt-4">
<button type="submit"
        class="save-schedule-btn w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg shadow transition-all duration-300 cursor-pointer hover:bg-blue-700 text-base lg:text-lg">
    Save Schedule
</button>

<style>
.save-schedule-btn:hover {
    transform: scale(1.05);  /* 5% zoom */
}
</style>

        </div>
    </form>
</div>
@endsection
