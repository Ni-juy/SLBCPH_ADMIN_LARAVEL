<h3 class="text-xl lg:text-3xl font-semibold mb-6">Financial Overview</h3>

<!-- Year Filter -->
<div class="mb-6">
    <label for="yearFilterChart" class="text-md md:text-base lg:text-lg ">Select Year:</label>
    <select id="yearFilterChart" class="w-20 p-2 border rounded">
        <option value="2025">2025</option>
        <option value="2024">2024</option>
    </select>
</div>

<!-- Income & Expenses Tables -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Income Table Card -->
    <div class="p-4 bg-gray-100 border rounded shadow">
        <h5 class="income-heading text-lg md:text-lg lg:text-xl font-semibold mb-3">Income ()</h5>
        <table class="w-full bg-white border border-gray-300  text-md md:text-base lg:text-lg">
            <thead class="bg-green-500 text-white text-md md:text-base lg:text-lg">
                <tr>
                    <th class="border px-4 py-2 font-semibold">Month</th>
                    <th class="border px-4 py-2 font-semibold">Amount</th>
                </tr>
            </thead >
            <tbody class="income-table-body"></tbody>
        </table>
    </div>

    <!-- Expenses Table Card -->
    <div class="p-4 bg-gray-100 border rounded shadow">
        <h5 class="expense-heading text-lg md:text-lg lg:text-xl font-semibold mb-3">Expenses ()</h5>
        <table class="w-full bg-white border border-gray-30 text-md md:text-base lg:text-lg">
            <thead class="bg-red-500 text-white text-md md:text-base lg:text-lg">
                <tr>
                    <th class="border px-4 py-2">Month</th>
                    <th class="border px-4 py-2">Amount</th>
                </tr>
            </thead>
            <tbody class="expense-table-body text-center"></tbody>
        </table>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 gap-4 mt-4">
    <!-- Bar Chart Full Width -->
    <div class="p-4 bg-gray-100 border rounded shadow h-auto flex flex-col justify-center ">
        <h5 class="text-lg lg:text-xl font-semibold mb-3 text-center">Income & Expenses by Month</h5>
        <div class="flex-1">
            <canvas id="incomeExpenseChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Pie Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Income Pie Chart -->
        <div class="p-4 bg-gray-100 border rounded shadow h-auto flex flex-col justify-center items-center">
            <h5 class="text-md md:text-base lg:text-xl font-semibold mb-3">Income by Category</h5>
            <canvas id="incomeCategoryChart" class="w-full max-w-[300px] h-[300px]"></canvas>
        </div>

        <!-- Expenses Pie Chart -->
        <div class="p-4 bg-gray-100 border rounded shadow h-auto flex flex-col justify-center items-center">
            <h5 class="text-md md:text-base lg:text-xl font-semibold mb-3">Expenses by Category</h5>
            <canvas id="expenseCategoryChart" class="w-full max-w-[300px] h-[300px]"></canvas>
        </div>
    </div>
</div>



<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        // Chart Instances
        const incomeExpenseChart = new Chart(document.getElementById('incomeExpenseChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Income', data: Array(12).fill(0), backgroundColor: 'rgba(0, 128, 0, 0.6)' },
                    { label: 'Expenses', data: Array(12).fill(0), backgroundColor: 'rgba(255, 0, 0, 0.6)' }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        const incomeCategoryChart = new Chart(document.getElementById('incomeCategoryChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#e91e63', '#9c27b0', '#795548', '#03a9f4']
                }]
            },
            options: { responsive: true }
        });

        const expenseCategoryChart = new Chart(document.getElementById('expenseCategoryChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#ff9800', '#e91e63', '#9c27b0', '#795548', '#03a9f4']
                }]
            },
            options: { responsive: true }
        });

            
     


        const yearSelect = document.getElementById('yearFilterChart');
        const incomeTable = document.querySelector('.income-table-body');
        const expenseTable = document.querySelector('.expense-table-body');
        const incomeHeading = document.querySelector('.income-heading');
        const expenseHeading = document.querySelector('.expense-heading');



        function updateTable(tbody, dataArray) {
            tbody.innerHTML = '';
            dataArray.forEach((amount, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td class="border px-4 py-2 text-center">${months[index]}</td>
                        <td class="border px-4 py-2 text-right">₱${amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                    </tr>
                `;
            });
        }

        function fetchAndUpdateCharts(year) {
            fetch(`/chart-data/${year}`)
                .then(res => res.json())
                .then(data => {
                    const income = Array(12).fill(0);
                    const expenses = Array(12).fill(0);

                    for (let month in data.income) {
                        income[month - 1] = data.income[month];
                    }
                    for (let month in data.expenses) {
                        expenses[month - 1] = data.expenses[month];
                    }

                    // Update Bar Chart
                    incomeExpenseChart.data.datasets[0].data = income;
                    incomeExpenseChart.data.datasets[1].data = expenses;
                    incomeExpenseChart.update();

                    // Update Tables (showing all months)
                    updateTable(incomeTable, income);
                    updateTable(expenseTable, expenses);
                    incomeHeading.textContent = `Income (${year})`;
                    expenseHeading.textContent = `Expenses (${year})`;

                    // Update Pie Chart for Income Categories
                    const incomeCat = data.category?.income || {};
                    const incomeCatLabels = [];
                    const incomeCatData = [];

                    for (const [label, amount] of Object.entries(incomeCat)) {
                        incomeCatLabels.push(label);
                        incomeCatData.push(amount);
                    }

                    incomeCategoryChart.data.labels = incomeCatLabels;
                    incomeCategoryChart.data.datasets[0].data = incomeCatData;
                    incomeCategoryChart.update();

                    // Update Pie Chart for Expenses Categories
                    const expenseCat = data.category?.expenses || {};
                    const expenseCatLabels = [];
                    const expenseCatData = [];

                    for (const [label, amount] of Object.entries(expenseCat)) {
                        expenseCatLabels.push(label);
                        expenseCatData.push(amount);
                    }

                    expenseCategoryChart.data.labels = expenseCatLabels;
                    expenseCategoryChart.data.datasets[0].data = expenseCatData;
                    expenseCategoryChart.update();
                });
        }

        // Initial Load
        fetchAndUpdateCharts(yearSelect.value);

        // On Year Change
        yearSelect.addEventListener('change', function () {
            fetchAndUpdateCharts(this.value);
        });
    });


</script>
