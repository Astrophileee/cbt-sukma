@php
    $size = $size ?? 18;
    $map = [
        'user' => ['fa-solid fa-user', 'text-blue-600'],
        'graduation' => ['fa-solid fa-user-graduate', 'text-green-600'],
        'question' => ['fa-regular fa-circle-question', 'text-indigo-600'],
        'exam' => ['fa-solid fa-file-lines', 'text-orange-600'],
        'clock' => ['fa-regular fa-clock', 'text-purple-600'],
        'check' => ['fa-solid fa-check', 'text-emerald-600'],
        'score' => ['fa-solid fa-chart-line', 'text-amber-600'],
        'calendar' => ['fa-regular fa-calendar-days', 'text-blue-600'],
        'calendar-mini' => ['fa-regular fa-calendar', 'text-gray-600'],
        'history' => ['fa-solid fa-clock-rotate-left', 'text-gray-600'],
        'list' => ['fa-solid fa-list-check', 'text-indigo-600'],
    ];
    [$iconClass, $colorClass] = $map[$type ?? 'info'] ?? ['fa-regular fa-circle-info', 'text-gray-500'];
@endphp
<i class="{{ $iconClass }} {{ $colorClass }}" style="font-size: {{ $size }}px;"></i>
