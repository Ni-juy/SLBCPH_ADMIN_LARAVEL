<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .swal2-container {
            z-index: 100000 !important;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            font-size: 16px;
            color: #000000;
        }

        .sidebar {
            background: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            width: 256px;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            z-index: 600;
            color: black;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 256px;
            padding: 1rem;
            overflow-y: auto;
            height: 100vh;
            flex-grow: 1;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 500;
        }

        .overlay.show {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-200 text-black">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar p-4 shadow-md flex flex-col justify-between">
        <div>
            <button class="text-[#fa2223] hover:bg-[#f7881c] p-2 rounded mb-4 md:hidden" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>

            <!-- Church Logo and Name (Responsive for desktop and mobile) -->
            <div class="flex flex-col items-center mb-6 mt-2 md:mt-4">
                <div class="w-16 h-16 md:w-20 md:h-20 overflow-hidden mb-1">
                    <img src="{{ asset('images/clogo.png') }}" class="w-full h-full object-contain" alt="Church Logo" />
                </div>
                <div class="text-center mt-1 md:mt-2 leading-tight">
                    <p class="text-md lg:text-2xl font-bold text-gray-900">Shining Light Baptist Church</p>
                    @if (Auth::user()?->branch)
                        <p class="text-sm md:text-lg text-gray-700 mt-1">Branch: {{ Auth::user()->branch->name }}</p>
                    @endif
                </div>
            </div>

            <nav>
                <ul class="space-y-2 text-md lg:text-lg font-semibold text-black-800">
                    <li><a href="/admin/dashboard" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/dashboard') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-chart-bar"></i><span>Dashboard</span></a></li>
                    <li><a href="/admin/financialtracking" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/financialtracking') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-coins"></i><span>Financial Manager</span></a></li>
                    <li><a href="/admin/faithtracks" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/faithtracks') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-book-open"></i><span>Faith / Tracks</span></a></li>
                    <li><a href="/admin/manageevent" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/manageevent') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-calendar-alt"></i><span>Manage Event</span></a></li>
                    <li><a href="/admin/prayerrequest" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/prayerrequest') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-pray"></i><span>Manage Prayer Requests</span></a></li>
                    <li><a href="/admin/memberdetails" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/memberdetails') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-users"></i><span>Manage Members</span></a></li>
                    <li><a href="/admin/sundayservice" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/sundayservice') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-church"></i><span>Attendance Monitoring</span></a></li>
                    <li><a href="/admin/churchservice" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/churchservice') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-clock"></i><span>Church Service</span></a></li>
                     <li><a href="/admin/visitors" class="flex items-center space-x-2 p-3 rounded {{ request()->is('admin/visitors') ? 'bg-red-600 text-white' : 'hover:bg-gray-200' }}"><i class="fas fa-user-friends"></i><span>List of Visitors</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

<!-- Main Content Wrapper -->
<div class="main-content">
    <div class="flex justify-between items-center flex-wrap gap-2 bg-[#CC0000] text-white p-4 shadow rounded mb-6">

        <!-- Sidebar Toggle (Mobile Only) -->
        <button class="md:hidden p-2" onclick="toggleSidebar()">
            <i class="fas fa-bars text-white text-xl"></i>
        </button>

        <!-- Header Title (Only Desktop) -->
        <div class="text-base lg:text-2xl font-bold whitespace-nowrap hidden md:block">
            @yield('header')
            <!-- @if(Auth::check() && Auth::user()->branch)
                | {{ Auth::user()->branch->name }}
            @endif -->
        </div>

        <!-- Right Section: DateTime + Profile -->
        <div class="flex items-center gap-3 ml-auto">
            <!-- DateTime (Always Visible but flexible) -->
            <div id="dateTime" class="text-sm md:text-base lg:text-base whitespace-nowrap"></div>

            <!-- Profile Button -->
            <div class="relative">
                <button class="flex items-center bg-sky-500 p-2 pr-3 rounded hover:bg-[#003366]" onclick="toggleProfileMenu()">
                    <!-- Image -->
                    <div class="w-8 h-8 rounded-full overflow-hidden border border-white">
                        <img src="{{ Auth::user()?->profile_image ? asset('storage/' . Auth::user()->profile_image) : asset('images/placeholder.png') }}"
                             class="w-full h-full object-cover" alt="Admin Profile Image">
                    </div>

                    <!-- Name (Only Desktop) -->
                    <span class="text-sm font-medium hidden md:inline">{{ Auth::user()?->first_name ?? 'Admin' }}</span>
                    <i class="fas fa-caret-down ml-1"></i>
                </button>

                <!-- Dropdown -->
                <ul id="profileMenu" class="hidden absolute right-0 mt-2 bg-sky-500 shadow rounded w-40 z-50">
                    <li class="p-2 hover:bg-[#003366]"><a href="/admin/adminprofile">Profile</a></li>
                    <li class="p-2 hover:bg-[#003366]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div>
        @yield('content')
    </div>
</div>


<script>
    function toggleProfileMenu() {
        document.getElementById('profileMenu').classList.toggle('hidden');
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    }

    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const date = now.toLocaleDateString(undefined, options);
        document.getElementById('dateTime').textContent = `${date} | ${time}`;
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

</body>

</html>