@extends('layouts.member')

@section('title', 'Sunday Service Monitoring')

@section('header', 'Sunday Service Monitoring')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <label for="monthFilter" class="font-semibold">Filter by month:</label>
    <select id="monthFilter" class="border p-2 rounded">
        <option value="all">All</option>
        <option value="2025-01">January 2025</option>
        <option value="2025-02">February 2025</option>
    </select>

    <div class="mt-4">
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Date</th>
                    <th class="border p-2">Sermon Title / Service</th>
                    <th class="border p-2">Preacher</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody id="serviceTable">
                <tr class="service-row" data-date="2025-02">
                    <td class="border p-2">Feb 11, 2025</td>
                    <td class="border p-2">The Power of Faith</td>
                    <td class="border p-2">Pastor Enegz</td>
                    <td class="border p-2 status text-green-600 font-bold">Attended</td>
                    <td class="border p-2">
                        <button class="toggle-status bg-green-500 text-white px-3 py-1 rounded">Attended</button>
                        <button class="toggle-status bg-red-500 text-white px-3 py-1 rounded">Missed</button>
                    </td>
                </tr>
                <tr class="service-row" data-date="2025-02">
                    <td class="border p-2">Feb 4, 2025</td>
                    <td class="border p-2">Walking with God</td>
                    <td class="border p-2">Pastor Carl</td>
                    <td class="border p-2 status text-green-600 font-bold">Attended</td>
                    <td class="border p-2">
                        <button class="toggle-status bg-green-500 text-white px-3 py-1 rounded">Attended</button>
                        <button class="toggle-status bg-red-500 text-white px-3 py-1 rounded">Missed</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-4 border rounded bg-gray-100">
        <strong>Attendance Summary:</strong>
        <p>You attended <span id="attendedCount">2</span> out of <span id="totalServices">2</span> services this quarter.</p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const filter = document.getElementById("monthFilter");
    const rows = document.querySelectorAll(".service-row");
    const buttons = document.querySelectorAll(".toggle-status");

    filter.addEventListener("change", function() {
        const selectedMonth = this.value;
        rows.forEach(row => {
            if (selectedMonth === "all" || row.getAttribute("data-date") === selectedMonth) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    buttons.forEach(button => {
        button.addEventListener("click", function() {
            const statusCell = this.parentElement.previousElementSibling;
            if (this.textContent === "Attended") {
                statusCell.textContent = "Attended";
                statusCell.classList.remove("text-red-600");
                statusCell.classList.add("text-green-600");
            } else {
                statusCell.textContent = "Missed";
                statusCell.classList.remove("text-green-600");
                statusCell.classList.add("text-red-600");
            }
            updateAttendanceSummary();
        });
    });

    function updateAttendanceSummary() {
        const totalServices = document.querySelectorAll(".service-row").length;
        const attendedCount = document.querySelectorAll(".status.text-green-600").length;
        document.getElementById("attendedCount").textContent = attendedCount;
        document.getElementById("totalServices").textContent = totalServices;
    }
});
</script>

@endsection
