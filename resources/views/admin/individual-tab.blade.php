<div x-data="fundExpenseForm()" x-init="init()">
    <h2 class="text-xl lg:text-3xl font-semibold mb-4">USE FUND (Expense)</h2>
    <form @submit.prevent="submitFundExpense">

        <!-- Date -->
        <div class="mb-4">
            <label class="block text-md md:text-base lg:text-lg font-medium text-gray-600">Date:</label>
            <input 
                type="date" 
                class="mt-1 block w-full p-2 border border-gray-300 rounded" 
                x-model="fundExpense.date" 
                :max="today" 
                required
            >
        </div>

        <!-- Taken From: Allocation (Fund) -->
        <div class="mb-4">
            <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Taken From (Fund Allocation):</label>
            <select x-model="fundExpense.taken_from" required class="border p-2 rounded w-full">
                <option value="">Select Fund</option>
                <template x-for="fund in funds" :key="fund.allocation_id">
                    <option 
                        :value="fund.allocation_id" 
                        x-text="fund.partition.category + ' (₱' + parseFloat(fund.remaining_balance).toFixed(2) + ')'">
                    </option>
                </template>
            </select>
        </div>

        <!-- Amount -->
       <div class="mb-4">
    <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Amount:</label>

    <div class="relative mt-1">
        <!-- Peso prefix -->
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700">₱</span>

        <!-- Input -->
        <input type="number" step="0.01" 
               x-model="fundExpense.amount" 
               required
                    @focus="if ($el.value == 0) $el.value = ''"
               @blur="if ($el.value === '') $el.value = 0"
               class="block w-full p-2 pl-7 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-md md:text-base lg:text-lg font-medium text-gray-700">Description:</label>
            <textarea class="mt-1 block w-full p-2 border border-gray-300 rounded" x-model="fundExpense.description" rows="3" required></textarea>
        </div>

        <!-- Submit -->
<button type="submit" class="fund-btn px-4 py-2 bg-blue-600 text-white rounded text-md md:text-base lg:text-lg transition-all duration-300 cursor-pointer">
    Submit Fund Expense
</button>

<style>
.fund-btn:hover {
    transform: scale(1.05);       /* 10% zoom */
    background-color: #3b82f6;   /* Tailwind blue-500 for hover */
}
</style>
    </form>
</div>

<script>
    function fundExpenseForm() {
        return {
            fundExpense: {
                date: '',
                taken_from: '',
                amount: 0,
                description: '',
            },
            funds: @json($funds), // This contains the funds data with partition_id and category
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

                if (enteredAmount > availableBalance) {
                    Swal.fire({
                        title: 'Insufficient Funds',
                        text: `Entered amount exceeds the available fund. Only ₱${availableBalance.toFixed(2)} is available.`,
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                    return;
                }

                // SweetAlert2 confirmation for submission
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to deduct an expense amount of ₱${enteredAmount.toFixed(2)} from the fund: ${selectedFund.partition.category}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Proceed with form submission
                        fetch('/fund-expenses', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(this.fundExpense)
                        })
                        .then(response => response.json())
                       .then(data => {
    if (data.success) {
        Swal.fire('Success!', 'Fund Expense recorded successfully!', 'success').then(() => {
            // Reset form (optional)
            this.resetForm();
            // Full page refresh
            window.location.reload();
        });
    } else {
        Swal.fire('Error!', data.message || 'An error occurred.', 'error');
    }
})
.catch(error => {
    Swal.fire('Error!', 'There was an error submitting the form.', 'error');
});

                    } else {
                        // SweetAlert2 for cancellation
                        Swal.fire({
                            title: 'Cancelled!',
                            text: 'The fund expense submission has been cancelled.',
                            icon: 'info',
                            confirmButtonText: 'Ok',
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
                };
            }
        }
    }
</script>
