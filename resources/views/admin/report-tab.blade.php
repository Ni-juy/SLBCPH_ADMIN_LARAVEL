<div x-data="financialReport" x-init="init()">
  <h3 class="text-xl lg:text-3xl font-semibold mb-4">
    Financial Report <span x-text="currentYear"></span>
  </h3>

  <!-- Date Filter for PDF Export and Print -->
  <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0 mb-4">
    <input type="date" class="border p-2 rounded w-full sm:w-auto" x-model="fromDate" :max="today">
    <input type="date" class="border p-2 rounded w-full sm:w-auto" x-model="toDate" :max="today">
<button @click="confirmExport()" 
        class="export-btn bg-blue-600 text-white px-4 py-2 rounded w-full text-base sm:w-auto transition-all duration-300 cursor-pointer">
    Export PDF
</button>

<button @click="printReport()" 
        class="print-btn bg-green-600 text-white px-4 py-2 rounded w-full text-base sm:w-auto transition-all duration-300 cursor-pointer">
    Print Report
</button>

<style>
.export-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
    background-color: #3b82f6;    /* Tailwind blue-500 */
}

.print-btn:hover {
    transform: scale(1.05);       /* 5% zoom */
    background-color: #16a34a;    /* Tailwind green-700 */
}
</style>

  </div>

  <!-- Income Summary -->
  <div class="overflow-x-auto mb-6">
    <h4 class="font-semibold mb-2 text-lg">Income Summary</h4>
    <table class="min-w-full border border-gray-300 bg-white text-md">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">Category</th>
          <th class="border px-4 py-2">Total</th>
        </tr>
      </thead>
      <tbody>
        <template x-for="(row, idx) in incomeSummary" :key="'income-' + idx">
          <tr>
            <td class="border px-4 py-2 text-center" x-text="row.category"></td>
            <td class="border px-4 py-2 text-right text-green-600"
                x-text="'₱' + formatMoney(row.total)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  <!-- Expense Summary -->
  <div class="overflow-x-auto mb-6">
    <h4 class="font-semibold mb-2 text-lg">Expenses Summary</h4>
    <table class="min-w-full border border-gray-300 bg-white text-md">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">Category</th>
          <th class="border px-4 py-2">Total</th>
        </tr>
      </thead>
      <tbody>
        <template x-for="(row, idx) in expensesSummary" :key="'expense-' + idx">
          <tr>
            <td class="border px-4 py-2 text-center" x-text="row.category"></td>
            <td class="border px-4 py-2 text-right text-red-600"
                x-text="'₱' + formatMoney(row.total)"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  <!-- Totals -->
  <div class="mt-4 p-4 bg-white border rounded shadow-md text-md lg:text-lg ">
    <p class="font-semibold">Total Income:
      <span class="text-green-600" x-text="'₱' + formatMoney(totalIncome)"></span>
    </p>
    <p class="font-semibold">Total Expenses:
      <span class="text-red-600" x-text="'₱' + formatMoney(totalExpenses)"></span>
    </p>
    <p class="font-semibold">Net Income:
      <span x-text="'₱' + formatMoney(netIncome)"></span>
    </p>
  </div>

  <script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('financialReport', () => ({
      fromDate: '',   // <-- no pre-fill
      toDate: '',
      today: '',
      currentYear: '',
      incomeSummary: [],
      expensesSummary: [],
      totalIncome: 0,
      totalExpenses: 0,
      netIncome: 0,

      formatMoney(value) {
        return new Intl.NumberFormat('en-PH', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value || 0);
      },

      async fetchSummary(from, to) {
        try {
          const res = await fetch(`/api/financial-report-data?from=${from}&to=${to}`);
          const data = await res.json();
          if (data.error) {
            alert('Error: ' + data.error);
            return;
          }
          this.incomeSummary   = data.income_summary || [];
          this.expensesSummary = data.expenses_summary || [];
          this.totalIncome     = data.total_income || 0;
          this.totalExpenses   = data.total_expenses || 0;
          this.netIncome       = data.net_income || 0;
        } catch (e) {
          console.error(e);
          alert('Failed to fetch summary.');
        }
      },

      async confirmExport() {
        if (!this.fromDate || !this.toDate) {
          alert("Please select both dates.");
          return;
        }
        if (new Date(this.toDate) < new Date(this.fromDate)) {
          alert("The 'To' date cannot be earlier than the 'From' date.");
          return;
        }

        const confirmed = await Swal.fire({
          title: 'Are you sure?',
          text: "You are about to export the financial report as PDF.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, export it!',
          cancelButtonText: 'Cancel',
        });

        if (confirmed.isConfirmed) {
          await this.exportPDF();
        } else {
          Swal.fire('Export cancelled', '', 'info');
        }
      },

      async exportPDF() {
        try {
          const url = `/download-financial-report-pdf?from=${this.fromDate}&to=${this.toDate}`;
          const response = await fetch(url);
          if (!response.ok) {
            const errorData = await response.json();
            alert("Failed to download PDF: " + (errorData.error || "Unknown error"));
            return;
          }
          const blob = await response.blob();
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.download = 'financial_report.pdf';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        } catch (err) {
          alert("An error occurred while downloading the PDF.");
          console.error(err);
        }
      },

     printReport() {
  if (!this.fromDate || !this.toDate) {
    alert("Please select both 'From' and 'To' dates to print.");
    return;
  }
  if (new Date(this.toDate) < new Date(this.fromDate)) {
    alert("The 'To' date cannot be earlier than the 'From' date.");
    return;
  }

  const url = `/financial-report-print?from=${this.fromDate}&to=${this.toDate}`;
  const printWindow = window.open(url, '_blank');

  if (printWindow) {
    printWindow.focus();

    // ✅ Wait for the window to finish loading, then print
    printWindow.addEventListener('load', () => {
      printWindow.print();
    });
  } else {
    alert("Pop-up blocked! Please allow pop-ups for this site to print.");
  }
}
,

      init() {
        const now = new Date();
        this.today = now.toISOString().split('T')[0];
        this.currentYear = now.getFullYear();

        // ✅ overview for the current year only
        const firstDay = `${this.currentYear}-01-01`;
        const lastDay  = `${this.currentYear}-12-31`;

        this.fetchSummary(firstDay, lastDay);

        // ✅ Do NOT prefill export/print filters
        this.fromDate = '';
        this.toDate = '';
      }
    }));
  });
  </script>
</div>
