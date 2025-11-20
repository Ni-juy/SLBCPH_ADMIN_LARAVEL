@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
    <!-- Notification -->
    <div id="alertBox" class="bg-yellow-200 text-yellow-800 p-3 rounded flex justify-between items-center text-base md:text-base lg:text-lg font-semibold" >
        <span>Welcome Admin! You can remove this message.</span>
        <button onclick="closeAlert()" class="hover-pointer text-red-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Cards Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6 ">
    @php
        $cards = [
            ['title' => 'Total Members', 'value' => $totalMembers, 'icon' => 'users', 'color' => 'red-400', 'textColor' => 'black-600'], 
            ['title' => 'New Members', 'value' => $newMembers, 'icon' => 'user-plus', 'color' => 'red-400', 'textColor' => 'black-600'],
            ['title' => 'Pending Prayer Requests', 'value' => $pendingPrayerRequests, 'icon' => 'pray', 'color' => 'red-400', 'textColor' => 'black-600'],
            ['title' => 'Current Fund', 'value' => '₱' . number_format($totalCurrentFund, 2), 'icon' => 'coins', 'color' => 'red-400', 'textColor' => 'black-600'],
            ['title' => 'Upcoming Events', 'value' => $upcomingEvents, 'icon' => 'calendar-day', 'color' => 'red-400', 'textColor' => 'black-600'],
            ['title' => 'Total Faith Shared', 'value' => $totalFaithShared, 'icon' => 'hands-praying', 'color' => 'red-400', 'textColor' => 'black-600'],
            ['title' => 'Total Tracks Given', 'value' => $totalTracksGiven, 'icon' => 'file-alt', 'color' => 'red-400', 'textColor' => 'black-600'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="card bg-white px-4 py-3 rounded-2xl shadow-md border-t-4 border-{{ $card['color'] }} 
                    flex flex-col justify-center h-24 transition-all duration-500 cursor-pointer hover:border-t-green-500">
            <p class="text-lg md:text-base lg:text-xl font-medium text-black-500">{{ $card['title'] }}</p>
            <p class="text-lg md:text-base lg:text-xl font-semibold text-{{ $card['textColor'] }} flex items-center mt-1">
                <i class="fas fa-{{ $card['icon'] }} mr-2"></i> {{ $card['value'] }}
            </p>
        </div>
    @endforeach
</div>

<style>
/* Custom 1% zoom on hover */
.card:hover {
    transform: scale(1.01);
}
</style>


 <!-- Charts Section -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 mt-10 ">
    <!-- Weekly New Members -->
    <div class="bg-white p-4 rounded-2xl shadow-md ">
        <h3 class="text-lg md:text-base lg:text-xl font-semibold text-black-600 mb-2 flex justify-center">Weekly New Members</h3>
        <div class="w-full h-48 ">
            <canvas id="membersBarChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Gender Pie Chart -->
    <div class="bg-white p-4 rounded-2xl shadow-md ">
        <h3 class="text-lg md:text-base lg:text-xl font-semibold text-black-600 mb-2 flex justify-center ">Member Gender Distribution</h3>
        <div class="w-full h-48 flex justify-center">
            <canvas id="genderPieChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Member Status Chart -->
    <div class="bg-white p-4 rounded-2xl shadow-md">
        <h3 class="text-lg md:text-base lg:text-xl font-semibold text-black-600 mb-2 flex justify-center">Member Status</h3>
        <div class="w-full h-48 flex justify-center">
            <canvas id="memberStatusChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Event Type Frequency -->
    <div class="bg-white p-4 rounded-2xl shadow-md">
        <h3 class="text-lg md:text-base lg:text-xl font-semibold text-black-600 mb-2 flex justify-center">Event Type Frequency</h3>
        <div class="w-full h-48">
            <canvas id="eventTypeChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Prayer Request Types (spans 2 columns) -->
  <!-- Prayer Request Types -->
<div class="bg-white p-4 rounded-2xl shadow-md md:col-span-2 w-full max-w-3xl mx-auto">
    <h3 class="text-lg md:text-base lg:text-xl font-semibold text-black-600 mb-2 flex justify-center">Prayer Request Types</h3>
    <div class="w-full h-48 flex justify-center">
        <canvas id="prayerTypeChart" width="400" height="200"></canvas>
    </div>
</div>

</div>



    <!-- JavaScript -->
    <script>
        function closeAlert() {
            document.getElementById('alertBox').remove();
        }

        new Chart(document.getElementById("membersBarChart"), {
            type: 'bar',
            data: {
                labels: @json($weeklyNewMembers->pluck('date')),
                datasets: [{
                    label: 'New Members',
                    data: @json($weeklyNewMembers->pluck('count')),
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        new Chart(document.getElementById("genderPieChart"), {
            type: 'pie',
            data: {
                labels: @json($genderDistribution->keys()),
                datasets: [{
                    label: 'Gender Distribution',
                    data: @json($genderDistribution->values()),
                    backgroundColor: ['#10B981', '#EF4444', '#FBBF24']
                }]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById("memberStatusChart"), {
            type: 'doughnut',
            data: {
                labels: @json($memberStatusCounts->keys()),
                datasets: [{
                    data: @json($memberStatusCounts->values()),
                    backgroundColor: ['#60A5FA ','#34D399' ,'#F87171']
                }]
            }
        });

        new Chart(document.getElementById("eventTypeChart"), {
            type: 'bar',
            data: {
                labels: @json($eventTypeCounts->keys()),
                datasets: [{
                    label: 'Events',
                    data: @json($eventTypeCounts->values()),
                    backgroundColor: '#F59E0B'
                }]
            }
        });

        new Chart(document.getElementById("prayerTypeChart"), {
            type: 'doughnut',
            data: {
                labels: @json($prayerTypeCounts->keys()),
                datasets: [{
                    data: @json($prayerTypeCounts->values()),
                    backgroundColor: ['#4ADE80',' #FFFF00','#60A5FA ']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    </script>
@endsection