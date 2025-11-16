<!DOCTYPE html>
<html>
<head>
    <title>Donations Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tfoot {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            padding-right: 20px;
        }
        .branch-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 5px 0;
            color: #1e40af;
        }
        canvas {
            display: block;
            margin: 40px auto;
            max-width: 500px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- 🔷 Header with logo + church name -->
    <div style="text-align:center; margin-bottom: 20px;">
        <div style="display:inline-block; vertical-align:middle;">
            <img src="{{ $logo }}" alt="Logo" style="height:80px; width:auto;">
        </div>
        <div style="display:inline-block; text-align:left; margin-left:15px; vertical-align:middle;">
            <div style="font-size:24px; font-weight:bold; color:#1e40af;">
                SHINING LIGHT BAPTIST CHURCH
            </div>
            <div style="font-size:18px; font-weight:bold; color:#1e3a8a;">
                DONATIONS REPORT
            </div>
            <div style="font-size:14px; color:#2563eb;">
                From: {{ $from }} &nbsp;  To: {{ $to }}
            </div>
            <div style="font-size:14px; color:#2563eb;">
                Member Name: {{ $memberName }}
            </div>
        </div>
    </div>

    <!-- 🔹 Branch-wise tables -->
  @php
    $branches = $donations->groupBy('branch_name');
    $overallTotal = $donations->sum('amount');
@endphp

@foreach ($branches as $branchName => $branchDonations)
    <div class="branch-title">Branch: {{ $branchName ?? 'N/A' }}</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($branchDonations as $donation)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($donation->date)->format('M d, Y') }}</td>
                    <td>{{ $donation->category }}</td>
                    <td>{{ number_format($donation->amount, 2) }} PHP</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="total">Branch Total:</td>
                <td class="total">{{ number_format($branchDonations->sum('amount'), 2) }} PHP</td>
            </tr>
        </tfoot>
    </table>
@endforeach

{{-- 🔹 Only show overall total if there are 2+ branches --}}
@if ($branches->count() > 1)
    <h2 style="text-align:right; color:#1e40af;">
        Overall Total: {{ number_format($overallTotal, 2) }} PHP
    </h2>
@endif

<!-- 🔹 Pie chart -->
@if ($branches->count() > 1)
    <canvas id="donationPie"></canvas>
    <script>
        const ctx = document.getElementById('donationPie').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($branches->keys()->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($branches->map->sum('amount')->values()->toArray()) !!},
                    backgroundColor: [
                        '#007BFF', '#28A745', '#FFC107', '#DC3545', '#6F42C1', '#17A2B8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
@endif


   
    <!-- 🔹 Pie chart -->
    <canvas id="donationPie"></canvas>
    <script>
        const ctx = document.getElementById('donationPie').getContext('2d');
        const donationPie = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($branches->keys()->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($branches->map->sum('amount')->values()->toArray()) !!},
                    backgroundColor: [
                        '#007BFF', '#28A745', '#FFC107', '#DC3545', '#6F42C1', '#17A2B8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
