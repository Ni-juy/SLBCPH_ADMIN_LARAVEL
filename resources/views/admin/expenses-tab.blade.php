<div x-data="expenseDisplay()" x-init="fetchExpenses()" class="space-y-6">

    <!-- Header + Buttons -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl lg:text-3xl font-semibold text-black">Expenses</h3>
        <div class="flex space-x-2">
            <!-- Add Expense Button -->
            <button @click="showInputForm = !showInputForm"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-300">
                <span x-text="showInputForm ? 'Hide Input Form' : 'Add Expense'"></span>
            </button>
        </div>
    </div>

    <!-- Input Form (Hidden by default) -->
    <div x-show="showInputForm" x-cloak class="mb-6 p-4 bg-white border border-gray-300 rounded-lg shadow-md"
        x-data="fundExpenseForm()" x-init="init()">
        <h4 class="text-lg lg:text-xl font-semibold mb-4">USE FUND (Expense)</h4>
        <form @submit.prevent="submitFundExpense">

            <!-- Date -->
            <div class="mb-4">
                <label class="block text-md md:text-base lg:text-lg font-medium text-gray-600">Date:</label>
                <input type="date" class="mt-1 block  p-2 border border-gray-300 rounded" x-model="fundExpense.date"
                    :max="today" required>
            </div>

            <!-- Taken From: Allocation (Fund) -->
            <div class="mb-4">
                <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Taken From (Fund
                    Allocation):</label>
                <select x-model="fundExpense.taken_from" required class="border p-2 rounded w-full">
                    <option value="">Select Fund</option>
                    <template x-for="fund in funds" :key="fund.allocation_id">
                        <option :value="fund.allocation_id"
                            x-text="fund.partition.category + ' (₱' + parseFloat(fund.remaining_balance).toFixed(2) + ')'">
                        </option>
                    </template>
                </select>
            </div>

            <!-- Amount -->
            <div class="mb-4">
                <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Amount:</label>
                <div class="relative mt-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700">₱</span>
                    <input type="number" step="0.01" x-model="fundExpense.amount" required
                        @focus="if ($el.value == 0) $el.value = ''" @blur="if ($el.value === '') $el.value = 0"
                        class="block w-full p-2 pl-7 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Description:</label>
                <textarea class="mt-1 block w-full p-2 border border-gray-300 rounded" x-model="fundExpense.description"
                    rows="3" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Upload Image
                    (Optional):</label>
                <input type="file" accept="image/*" @change="fundExpense.image = $event.target.files[0]"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <div class="mt-4">
                    <button type="submit"
                        class="fund-btn px-4 py-2 bg-blue-600  hover:bg-blue-700 text-white rounded-lg shadow flex-1 sm:flex-none transition-all duration-300 cursor-pointer ">
                        Submit Fund Expense
                    </button>

                </div>

                <!-- Batch Upload Button inside slip -->
                <div class="mt-4">
                    <button type="button"
                        onclick="document.getElementById('expenseUploadModal').classList.remove('hidden'); document.getElementById('expenseUploadModal').classList.add('flex')"
                        class="fund-btn px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition flex-1 sm:flex-none">
                        📁 Batch Upload Expenses
                    </button>
                </div>
            </div>

            <style>
                .fund-btn:hover {
                    transform: scale(1.05);
                }
            </style>
        </form>
    </div>

    <!-- ========================== -->
    <!-- Batch Upload Modal -->
    <div id="expenseUploadModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[9999]">
        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg relative z-70">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">📁 Batch Upload Expenses</h2>
            <p class="text-sm text-gray-600 mb-4">
                Upload your Excel file using the template provided. Only allocations in your branch will be accepted.
            </p>

            <form id="expenseUploadForm" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Excel File (.xlsx)</label>
                    <input type="file" id="expenseBatchFileInput" name="file" accept=".xlsx,.xls"
                        class="w-full border p-2 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <!-- Buttons Container -->
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <button type="button" onclick="confirmExpenseBatchUpload()"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">
                        📤 Upload Excel
                    </button>

                    <button type="button" onclick="confirmExpenseDownloadTemplate()"
                        class="w-full sm:w-auto text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg border border-gray-300 shadow transition">
                        📥 Download Excel Template
                    </button>
                </div>
            </form>

            <!-- Close Button -->
            <button type="button" onclick="document.getElementById('expenseUploadModal').classList.add('hidden')"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Month Filter -->
    <div class="mb-4 relative w-48" id="dropdown">
        <label for="monthFilter" class="block text-md lg:text-lg font-medium text-gray-700 mb-1">Filter by
            Month:</label>
        <div class="relative">
            <select id="monthFilter"
                class="appearance-none w-full p-2 pr-8 border border-gray-300 rounded shadow-sm focus:ring focus:ring-red-200 focus:border-red-400 text-sm"
                x-model="selectedMonth" @change="filterExpenses">
                <option value="all">All Months</option>
                <template x-for="(month, index) in months" :key="index">
                    <option :value="index + 1" x-text="month"></option>
                </template>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Detailed Expenses Table -->
    <div>
        <h4 class="text-lg lg:text-xl font-semibold mb-2 text-gray-700">Detailed Expenses</h4>
        <div class="overflow-x-auto rounded-lg shadow">
            <table class=" min-w-full bg-white border border-gray-300 text-base lg:text-lg">
                <thead class="bg-red-600 text-white text-center">
                    <tr>
                        <th class="border px-4 py-2 font-semibold">Date</th>
                        <th class="border px-4 py-2 font-semibold">Taken From</th>
                        <th class="border px-4 py-2 font-semibold">Description</th>
                        <th class="border px-4 py-2 font-semibold">Amount</th>
                        <th class="border px-4 py-2 font-semibold">Image</th>
                    </tr>
                </thead>
                <tbody class="text-center text-gray-700">
                    <template x-for="expense in paginatedExpenses" :key="expense.id">
                        <tr class="border">
                            <td class="border px-4 py-2" x-text="formatDate(expense.date)"></td>
                            <td class="border px-4 py-2" x-text="expense.allocation?.partition?.category || 'N/A'"></td>
                            <td class="border px-4 py-2" x-text="expense.description"></td>
                            <td class="border px-4 py-2 text-right"
                                x-text="'₱' + parseFloat(expense.amount).toFixed(2)"></td>
                            <td class="border px-4 py-2">
                                <template x-if="expense.image">
                                    <img :src="expense.image" alt="Expense Image"
                                        class="w-16 h-16 object-cover rounded">
                                </template>
                                <template x-if="!expense.image">
                                    N/A
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center items-center gap-2 mt-4 ">

            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-3 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50 cursor-pointer hover:bg-gray-400">
                Previous
            </button>

            <template x-for="page in getTotalPages()" :key="page">
                <button @click="goToPage(page)"
                    :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                    class="px-3 py-2 rounded cursor-pointer hover:bg-blue-500 hover:text-white">
                    <span x-text="page"></span>
                </button>
            </template>

            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === getTotalPages()"
                class="px-3 py-2 bg-gray-300 text-gray-700 rounded disabled:opacity-50 cursor-pointer hover:bg-gray-400">
                Next
            </button>

            <span class="ml-4 text-sm text-gray-600">
                Page <span x-text="currentPage"></span> of <span x-text="getTotalPages()"></span>
            </span>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
        <h4 class="text-lg lg:text-xl font-semibold mb-2 text-gray-700">Summary by Category</h4>
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="min-w-full bg-white border border-gray-300 text-md lg:text-lg">
                <thead class="bg-gray-800 text-white text-center">
                    <tr>
                        <th class="border px-4 py-2 font-semibold">Category</th>
                        <th class="border px-4 py-2 font-semibold">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="text-center text-gray-700">
                    <template x-for="(total, category) in categorySummary" :key="category">
                        <tr>
                            <td class="border px-4 py-2" x-text="category"></td>
                            <td class="border px-4 py-2" x-text="'₱' + total.toFixed(2)"></td>
                        </tr>
                    </template>
                    <tr class="bg-red-100 text-red-800 font-bold border-t-2 border-red-400">
                        <td class="border px-4 py-2 text-center">Total:</td>
                        <td class="border px-4 py-2 text-right" x-text="'₱' + totalExpenses.toFixed(2)"></td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
<script>



    function fundExpenseForm() {
        return {
            fundExpense: {
                date: '',
                taken_from: '',
                amount: 0,
                description: '',
                image: ""
            },
            funds: @json($funds ?? []), // This contains the funds data with partition_id and category
            today: '',

            init() {
                // Set today's date in the format YYYY-MM-DD
                const now = new Date();
                this.today = now.toISOString().split('T')[0];
            },

            submitFundExpense() {
                const selectedFund = this.funds.find(fund => fund.allocation_id == this.fundExpense.taken_from);

                if (!selectedFund) {
                    Swal.fire({
                        title: 'Invalid Selection',
                        text: 'Please select a valid fund.',
                        icon: 'warning',
                        confirmButtonText: 'Okay'
                    });
                    return;
                }

                const enteredAmount = parseFloat(this.fundExpense.amount);
                const availableBalance = parseFloat(selectedFund.remaining_balance);

                if (enteredAmount <= availableBalance) {
                    // No excess: Proceed normally
                    this.confirmAndSubmit([{ allocation_id: selectedFund.allocation_id, amount: enteredAmount }]);
                    return;
                }

                // Excess exists: Show manual selection popup
                const excess = enteredAmount - availableBalance;
                const otherFunds = this.funds.filter(fund => fund.allocation_id != selectedFund.allocation_id);

                if (otherFunds.length === 0) {
                    Swal.fire({
                        title: 'Insufficient Funds',
                        text: `Entered amount exceeds the available fund. No other funds available.`,
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                    return;
                }

                // Build popup HTML
                let popupHtml = `<p>Amount exceeds by ₱${excess.toFixed(2)}. Select funds to deduct the excess from:</p>`;
                popupHtml += `<div style="max-height: 300px; overflow-y: auto;">`;
                otherFunds.forEach(fund => {
                    popupHtml += `
                    <div style="margin-bottom: 10px;">
                        <label>
                            <input type="checkbox" class="fund-checkbox" data-id="${fund.allocation_id}" data-balance="${fund.remaining_balance}">
                            ${fund.partition.category} (Available: ₱${parseFloat(fund.remaining_balance).toFixed(2)})
                        </label>
                        <input type="number" step="0.01" min="0" max="${fund.remaining_balance}" class="fund-amount" data-id="${fund.allocation_id}" placeholder="Amount" style="margin-left: 10px; width: 100px;" disabled>
                    </div>
                `;
                });
                popupHtml += `</div>`;
                popupHtml += `<p id="total-error" style="color: red; display: none;">Total selected amounts must equal ₱${excess.toFixed(2)}.</p>`;

                Swal.fire({
                    title: 'Allocate Excess to Funds',
                    html: popupHtml,
                    showCancelButton: true,
                    confirmButtonText: 'Confirm Allocation',
                    preConfirm: () => {
                        const selectedAllocations = [];
                        let totalSelected = 0;

                        document.querySelectorAll('.fund-checkbox:checked').forEach(checkbox => {
                            const id = checkbox.dataset.id;
                            const amountInput = document.querySelector(`.fund-amount[data-id="${id}"]`);
                            const amount = parseFloat(amountInput.value) || 0;
                            const balance = parseFloat(checkbox.dataset.balance);

                            if (amount > balance) {
                                Swal.showValidationMessage(`Amount for ${otherFunds.find(f => f.allocation_id == id).partition.category} exceeds available balance.`);
                                return false;
                            }

                            selectedAllocations.push({ allocation_id: id, amount });
                            totalSelected += amount;
                        });

                        if (Math.abs(totalSelected - excess) > 0.01) { // Allow small floating-point tolerance
                            document.getElementById('total-error').style.display = 'block';
                            return false;
                        }

                        return selectedAllocations;
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        const excessAllocations = result.value;
                        const allAllocations = [
                            { allocation_id: selectedFund.allocation_id, amount: availableBalance },
                            ...excessAllocations
                        ];
                        this.confirmAndSubmit(allAllocations);
                    }
                });

                // Enable/disable amount inputs based on checkboxes
                document.addEventListener('change', function (e) {
                    if (e.target.classList.contains('fund-checkbox')) {
                        const amountInput = document.querySelector(`.fund-amount[data-id="${e.target.dataset.id}"]`);
                        amountInput.disabled = !e.target.checked;
                        if (!e.target.checked) amountInput.value = '';
                    }
                });
            },

            confirmAndSubmit(allocations) {
                const enteredAmount = parseFloat(this.fundExpense.amount);
                let popupText = `You are about to deduct ₱${enteredAmount.toFixed(2)} from the following funds:\n`;
                allocations.forEach(alloc => {
                    const fund = this.funds.find(f => f.allocation_id == alloc.allocation_id);
                    popupText += `- ${fund.partition.category}: ₱${alloc.amount.toFixed(2)}\n`;
                });

                Swal.fire({
                    title: 'Confirm Expense Allocation',
                    html: `<pre>${popupText}</pre>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel',
                }).then(async (result) => {
                    if (result.isConfirmed) {

                        let image = "";

                        if (this.fundExpense.image !== "") {

                            try {
                                const formData = new FormData();
                                formData.append('image', this.fundExpense.image);

                                const uploadResult = await fetch('/upload-image', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });

                                if (!uploadResult.ok) {
                                    Swal.fire('Error!', 'There was an error uploading the image.', 'error');
                                    return;
                                }

                                const data = await uploadResult.json();
                                console.log('Image upload response:', data);
                                if (data.success) {

                                    image = data.image;
                                } else {
                                    Swal.fire('Error!', data.message || 'Image upload failed.', 'error');
                                    return;
                                }


                            } catch (error) {
                                console.error('Image upload error:', error);
                                Swal.fire('Error!', `There was an error uploading the image: ${error.message}`, 'error');
                                return;
                            }
                        }

                        const payload = {
                            date: this.fundExpense.date,
                            allocations: allocations,
                            description: this.fundExpense.description,
                            image: image ?? ''
                        };

                        fetch('/fund-expenses', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(payload)
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Fund Expense submission response:', data);
                                if (data.success) {
                                    Swal.fire('Success!', 'Fund Expense recorded successfully!', 'success').then(() => {
                                        this.resetForm();
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', data.message || 'An error occurred.', 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error!', 'There was an error submitting the form.', 'error');
                            });
                    }
                });
            },

            resetForm() {
                this.fundExpense = {
                    date: '',
                    taken_from: '',
                    amount: 0,
                    description: '',
                    image: ""
                };
            }

        }
    }



    function expenseDisplay() {
        return {
            // UI state
            showInputForm: false,
            showBatchUpload: false,

            // Expenses + filters
            expenses: [],
            filteredExpenses: [],
            selectedMonth: 'all',
            months: [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],

            // Pagination
            currentPage: 1,
            itemsPerPage: 5,
            paginatedExpenses: [],

            categorySummary: {},
            totalExpenses: 0,

            // 🔹 Fetch Expenses
          fetchExpenses() {
            fetch('/fund-expenses/list')
                .then(res => res.json())
                .then(data => {
                    // Sort latest created first
                    this.expenses = data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    this.filteredExpenses = [...this.expenses];
                    this.currentPage = 1; // Reset to page 1
                    this.calculateSummary();
                    this.paginateExpenses();
                });
        },

        // 🔹 Filter by month
        filterExpenses() {
            if (this.selectedMonth === 'all') {
                this.filteredExpenses = [...this.expenses];
            } else {
                this.filteredExpenses = this.expenses
                    .filter(exp => new Date(exp.date).getMonth() + 1 === parseInt(this.selectedMonth))
                    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at)); // latest created first
            }
            this.currentPage = 1; // Reset to page 1 after filter
            this.paginateExpenses();
        },


            // 🔹 Paginate expenses
            paginateExpenses() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                this.paginatedExpenses = this.filteredExpenses.slice(start, end);
            },

            // 🔹 Get total pages
            getTotalPages() {
                return Math.ceil(this.filteredExpenses.length / this.itemsPerPage);
            },

            // 🔹 Go to page
            goToPage(page) {
                const totalPages = this.getTotalPages();
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    this.paginateExpenses();
                }
            },

            // 🔹 Category summary
            calculateSummary() {
                const summary = {};
                let total = 0;

                this.expenses.forEach(exp => {
                    const category = exp.allocation?.partition?.category || 'Unknown';
                    const amount = parseFloat(exp.amount) || 0;
                    summary[category] = (summary[category] || 0) + amount;
                    total += amount;
                });

                this.categorySummary = summary;
                this.totalExpenses = total;
            },

            // 🔹 Format date for table
            formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            },


        }
    }




</script>
<style>
    #monthFilter {
        -webkit-appearance: auto;
        -moz-appearance: auto;
        appearance: none;
        background-image: initial !important;
    }
</style>