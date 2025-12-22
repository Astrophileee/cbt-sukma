<div id="overlay" onclick="closeSidebar()" class="fixed inset-0 bg-[rgba(0,0,0,0.75)] z-30 hidden lg:hidden"></div>



<!-- Sidebar -->
<aside id="sidebar" class="fixed z-40 top-0 left-0 w-64 min-h-screen bg-white border-r border-gray-200 transform -translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto">
    <div class="p-4 flex items-center gap-2">
    <div>
        <h1 class="font-bold text-sm">CBT-Sukma Karya Sejati</h1>
        <p class="text-xs text-gray-500">Staff</p>
    </div>
    </div>
    <nav class="mt-4 space-y-2 text-sm">
    <!-- Single link -->
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Dashboard
    </a>
    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Manajemen User
    </a>
    <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Manajemen Siswa
    </a>
    <a href="{{ route('questions.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Bank Soal
    </a>
    <a href="{{ route('exams.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Kelola Ujian
    </a>
    <a href="{{ route('exams.join.form') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Ikuti Ujian
    </a>
    <a href="{{ route('exams.attempts.mine') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Nilai Saya
    </a>
    <a href="{{ route('exams.grade.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-600"></i>
        Penilaian Essay
    </a>
    </nav>
</aside>
