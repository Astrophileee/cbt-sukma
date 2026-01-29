<div id="overlay" onclick="closeSidebar()" class="fixed inset-0 bg-[rgba(0,0,0,0.75)] z-30 hidden lg:hidden"></div>



<!-- Sidebar -->
<aside id="sidebar" class="fixed z-40 top-0 left-0 w-64 min-h-screen bg-white border-r border-yellow-100 shadow-sm transform -translate-x-full transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto">
    <div class="p-4 border-b border-yellow-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-x flex items-center justify-center">
                <img src="{{ asset('logo-sukma.png') }}" alt="Logo Sukma" class="w-6 h-6 object-contain" />
            </div>
            <div>
                <h1 class="font-bold text-sm text-gray-900">CBT-Sukma</h1>
                <p class="text-xs text-gray-500">PT. Sukma Karya Sejati</p>
            </div>
        </div>
    </div>
    <nav class="mt-3 px-2 space-y-1 text-sm text-gray-700">
        <!-- Single link -->
        <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
            <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
            Dashboard
        </a>
    @hasrole('admin')
    <a href="{{ route('users.index') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Manajemen User
    </a>
    <a href="{{ route('students.index') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Manajemen Siswa
    </a>
    @endhasrole
    @hasrole('guru')
    <a href="{{ route('questions.index') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Bank Soal
    </a>
    <a href="{{ route('exams.index') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Kelola Ujian
    </a>
    <a href="{{ route('exams.grade.index') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Penilaian Essay
    </a>
    @endhasrole
    @hasrole('siswa')
    <a href="{{ route('exams.join.form') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Ikuti Ujian
    </a>
    <a href="{{ route('exams.attempts.mine') }}" class="group flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-yellow-50 transition">
        <i class="fas fa-home w-5 h-5 pt-1 text-gray-400 group-hover:text-yellow-600"></i>
        Nilai Saya
    </a>
    @endhasrole
    </nav>
</aside>
