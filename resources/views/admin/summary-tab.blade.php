<div x-data="financialSummary()" x-init="fetchData()" class="p-1">

    <h3 class="text-xl lg:text-3xl font-semibold mb-4">Financial Summary</h3>

    <!-- Year Filter -->
    <div class="mb-4">
    <label class="text-base text-gray-700 lg:text-lg font-semibold block mb-1">Select Year:</label>
    <div class="relative inline-block w-48">
        <select 
            x-model="year" 
            @change="fetchData"
            class="appearance-none pr-8 p-2 border rounded w-full bg-white"
        >
            <template x-for="y in years">
                <option :value="y" x-text="y"></option>
            </template>
        </select>
        <!-- Custom Dropdown Arrow
        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div> -->
    </div>
</div>

    <!-- Income Table -->
<div class="mb-6 overflow-x-auto">
    <h4 class="text-lg lg:text-2xl font-semibold mb-2">Cash Income</h4>
    <table class="min-w-full bg-white border ">
        <thead class="bg-blue-600 text-white text-md lg:text-lg">
            <tr>
                <th class="border px-2 py-1 text-center">MONTH</th>
                <template x-for="(category, index) in incomeCategories" :key="index">
                    <th class="border px-2 py-1 " x-text="category"></th>
                </template>
            </tr>
        </thead>
        <tbody>
            <template x-for="(entries, month) in incomeData" :key="month">
                <tr>
                    <td class="border px-2 py-1 text-base lg:text-lg text-center" x-text="monthNames[parseInt(month) - 1]"></td>
                    <template x-for="category in incomeCategories" :key="category">
                        <td class="border px-2 py-1 text-base lg:text-lg text-right " x-text="formatCurrency(entries[category] || 0)"></td>
                    </template>
                </tr>
            </template>
        </tbody>
    </table>
</div>


   <!-- Expense Table -->
<div class="mb-6">
    <h4 class="text-lg lg:text-2xl font-semibold mb-2">Expenses</h4>
    <table class="min-w-full bg-white border text-base lg:text-lg">
        <thead class="bg-red-600 text-white">
            <tr>
                <th class="border px-2 py-1">MONTH</th>
                <template x-for="cat in expenseCategories" :key="cat">
                    <th class="border px-2 py-1 " x-text="cat"></th>
                </template>
            </tr>
        </thead>
        <tbody >
            <template x-for="(entries, month) in expenseData" :key="month">
                <tr>
                    <td class="border px-2 py-1 text-center" x-text="monthNames[parseInt(month) - 1]"></td>
                    <template x-for="category in expenseCategories" :key="category">
                        <td class="border px-2 py-1 text-right" x-text="formatCurrency(entries[category] || 0)"></td>
                    </template>
                </tr>
            </template>
        </tbody>
    </table>
</div>


    <!-- Fund Summary -->
    <div class="mb-6 p-4 border rounded bg-gray-50">
        <h4 class="text-lg lg:text-2xl font-semibold mb-2">Current Year Fund</h4>
        <table class="w-full border text-base lg:text-lg text-center">
            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="border px-2 py-1">Category</th>
                    <th class="border px-2 py-1">Amount</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="f in funds">
                    <tr>
                        <td class="border px-2 py-1" x-text="f.category"></td>
                        <td class="border px-2 py-1 text-right" x-text="formatCurrency(f.total)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Net Income -->
    <div class="mb-6 p-4 border rounded bg-gray-50">
        <h4 class="text-lg lg:text-2xl font-semibold mb-2">Net Income</h4>
        <table class="w-full border text-base lg:text-lg text-center">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="border px-2 py-1">Category</th>
                    <th class="border px-2 py-1">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="n in netIncome">
                    <tr>
                        <td class="border px-2 py-1" x-text="n.category"></td>
                        <td class="border px-2 py-1 text-right" x-text="formatCurrency(n.amount)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Combined Fund -->
    <div class="mb-6 p-4 border rounded bg-gray-50">
        <h4 class="text-lg lg:text-2xl font-semibold mb-2">Combined Fund</h4>
        <table class="w-full border text-md lg:text-lg text-center">
            <thead class="bg-blue-700 text-white">
                <tr>
                    <th class="border px-2 py-1">Year</th>
                    <th class="border px-2 py-1">Total</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="c in combined">
                    <tr>
                        <td class="border px-2 py-1" x-text="c.year"></td>
                        <td class="border px-2 py-1 text-right" x-text="formatCurrency(c.total)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-6">
   <button 
    @click="fetchData" 
    class="refresh-btn bg-blue-600 text-white px-3 py-2 rounded text-lg lg:text-xl flex items-center justify-center gap-2 mx-auto transition-all duration-300 cursor-pointer"
    :disabled="loading"
>
    <template x-if="loading">
        <span class="animate-spin border-2 border-white border-t-transparent rounded-full w-5 h-5"></span>
    </template>
    <span x-text="loading ? 'Refreshing…' : '🔄 Refresh Summary'"></span>
</button>

<style>
.refresh-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
    background-color: #3b82f6;    /* Tailwind blue-500 for hover */
}
</style>

</div>

</div>

<script>
      function financialSummary() {
    return {
        year: new Date().getFullYear(),
        years: [new Date().getFullYear(), new Date().getFullYear() - 1],
        incomeData: {},
        incomeCategories: [],
        expenseData: {},
        expenseCategories: [],
        funds: [],
        netIncome: [],
        combined: [],
        loading: false,   
        monthNames: [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ],

        async fetchData() {
            this.loading = true; // 👈 start loading
            try {
                const res = await fetch(`/financial-summary/data/${this.year}`);
                const data = await res.json();
                console.log('Fetched data:', JSON.stringify(data, null, 2));

                this.funds = data.funds || [];
                this.netIncome = data.netIncome || [];
                this.combined = data.combined || [];

                // Transform Income
                this.incomeData = {};
                const income = data.income;
                this.incomeCategories = [...new Set(Object.values(income).flatMap(m => m.map(i => i.category)))];
                for (const [month, entries] of Object.entries(income)) {
                    this.incomeData[month] = {};
                    entries.forEach(e => this.incomeData[month][e.category] = parseFloat(e.total));
                }

                // Transform Expenses
                this.expenseData = {};
                const expenses = data.expenses;
                this.expenseCategories = [...new Set(Object.values(expenses).flatMap(m => m.map(e => e.category)))];
                for (const [month, entries] of Object.entries(expenses)) {
                    this.expenseData[month] = {};
                    entries.forEach(e => this.expenseData[month][e.category] = parseFloat(e.total));
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                this.loading = false; // 👈 stop loading
            }
        },

        formatCurrency(value) {
            return '₱' + parseFloat(value).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        }
    }
}

</script>

