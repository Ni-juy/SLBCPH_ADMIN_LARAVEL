<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- FontAwesome for Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar {
            background: linear-gradient(to bottom, #1E3A8A, #DC2626, #D97706);
            transition: transform 0.3s ease-in-out;
        }
        .hover-pointer {
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
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
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 min-h-screen text-white sidebar p-4 md:block">
            <button class="text-white hover:bg-red-600 p-2 rounded mb-4 md:hidden" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex flex-col items-center mb-6">
                <img src="https://via.placeholder.com/50" class="rounded-full mb-2" alt="Profile">
                <span class="text-lg font-bold">Member Name</span>
            </div>
            
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="/member/memdashboard" class="flex items-center space-x-2 p-3 rounded bg-red-600">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/member/events" class="flex items-center space-x-2 p-3 rounded hover:bg-blue-500">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="/member/attendance" class="flex items-center space-x-2 p-3 rounded hover:bg-blue-500">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Sunday Service Monitoring</span>
                        </a>
                    </li>
                    <li>
                        <a href="/member/donation" class="flex items-center space-x-2 p-3 rounded hover:bg-blue-500">
                            <i class="fas fa-hand-holding-heart"></i>
                            <span>Track Financial</span>
                        </a>
                    </li>
                    <li>
                        <a href="/member/request" class="flex items-center space-x-2 p-3 rounded hover:bg-blue-500">
                            <i class="fas fa-pray"></i>
                            <span>Prayer Requests</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Overlay -->
        <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 p-6 overflow-x-auto overflow-y-auto">
            <!-- Header -->
            <div class="flex justify-between items-center bg-white p-4 shadow rounded mb-6">
                <button class="md:hidden p-4" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-black"></i>
                </button>
                <div class="text-xl font-bold">
                    @yield('header')
                </div>
                <div class="relative">
                    <button class="flex items-center space-x-2 bg-gray-200 p-2 rounded hover:bg-gray-300" onclick="toggleProfileMenu()">
                        <i class="fas fa-user"></i>
                        <span>Member Name</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <ul id="profileMenu" class="hidden absolute right-0 mt-2 bg-white shadow rounded w-40">
                        <li class="p-2 hover:bg-gray-200 hover-pointer"><a href="/member/memberprofile">Profile</a></li>
                        <li class="p-2 hover:bg-gray-200 hover-pointer"><a href="#">Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Container -->
            @yield('content')
        </div>
    </div>

    <!-- JavaScript for Dropdown and Sidebar -->
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
    </script>
</body>
</html>
