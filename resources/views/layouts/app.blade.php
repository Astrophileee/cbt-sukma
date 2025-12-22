<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dropdown-transition {
          overflow: hidden;
          transition: max-height 0.5s ease;
          max-height: 0;
        }

        .dropdown-transition.open {
          max-height: 1000px;
        }
        </style>

</head>
<body class="bg-gray-100 text-gray-900">

    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <div class="flex-1 flex flex-col">
            @include('layouts.partials.header')

            <main class="flex-1 p-6">
                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

</body>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }

    function toggleSidebarDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('open');

    const icon = dropdown.previousElementSibling.querySelector('i.fa-chevron-right');
    icon.classList.toggle('rotate-90');
}



</script>
</html>
