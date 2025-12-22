<header class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <button onclick="toggleSidebar()" class="p-2 text-gray-700 lg:hidden">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <div></div> {{-- kosongin title --}}

    {{-- Foto profil dan dropdown --}}
    <div class="relative">
        <button onclick="toggleDropdown()" class="focus:outline-none">
            <p>{{ Auth::user()->name }}</p>
        </button>

        {{-- Dropdown --}}
        <div id="dropdownMenu" class="absolute right-0 mt-2 w-64 bg-white border rounded-lg shadow-lg hidden z-50">
            <div class="flex items-center gap-3 px-4 py-3 border-b">
                <div>
                    <div class="font-semibold text-sm text-gray-900">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-sign-out-alt text-sm"></i> Log out
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('dropdownMenu');
        const button = event.target.closest('button');

        if (!dropdown.contains(event.target) && (!button || !button.closest('.relative'))) {
            dropdown.classList.add('hidden');
        }
    });
</script>
