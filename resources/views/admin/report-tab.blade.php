<div x-data="financialReport" x-init="init()">
  <h3 class="text-xl lg:text-3xl font-semibold mb-4">
    Financial Report <span x-text="currentYear"></span>
  </h3>


  <div class="flex flex-col lg:flex-row lg:space-x-6 lg:space-y-0">

    <!-- Report Generation Section -->
    <div class="flex-1 p-4 bg-blue-50 border rounded shadow-sm hover:bg-blue-100 transition-colors">
      <h4 class="font-semibold mb-2 text-lg">Generate and View Reports</h4>
      <p class="text-sm text-gray-600 mb-4">Select a date range to filter the financial data, then export as PDF or
        print the report.</p>
      <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
        <input type="date" class="border p-2 rounded w-full sm:w-auto" x-model="fromDate" :max="today"
          title="Select the start date for the report">
        <input type="date" class="border p-2 rounded w-full sm:w-auto" x-model="toDate" :max="today"
          title="Select the end date for the report">
        <button @click="confirmExport()"
          class="export-btn bg-blue-600 text-white px-4 py-2 rounded w-full text-base sm:w-auto transition-all duration-300 cursor-pointer"
          title="Download the financial report as a PDF file based on the selected date range">
          Export PDF
        </button>
        <button @click="printReport()"
          class="print-btn bg-green-600 text-white px-4 py-2 rounded w-full text-base sm:w-auto transition-all duration-300 cursor-pointer"
          title="Open and print the financial report in a new window based on the selected date range">
          Print Report
        </button>
      </div>
    </div>

    <!-- Admin Upload Section -->
    <div class="flex-1 p-4 bg-yellow-50 border rounded shadow-sm hover:bg-blue-100 transition-colors">
      <h4 class="font-semibold mb-2 text-lg">Monthly Branch Report</h4>
      <p class="text-sm text-gray-600 mb-4">Upload a PDF file that will be made available for members to view in the
        information section.</p>
      <input type="file" id="pdfInput" accept="application/pdf" class="hidden" @change="uploadPdf">
      <button @click="document.getElementById('pdfInput').click()"
        class="print-btn2 bg-yellow-600 text-white px-4 py-2 rounded w-full text-base sm:w-auto transition-all duration-300 cursor-pointer"
        title="Select and upload a PDF file to update the transparency record for members">
        Upload PDF
      </button>
    </div>

  </div>


  <style>
    .export-btn:hover {
      transform: scale(1.05);
      /* 5% zoom */
      background-color: #3b82f6;
      /* Tailwind blue-500 */
    }

    .print-btn:hover {
      transform: scale(1.05);
      background-color: #16a34a;
    }

    .print-btn2:hover {
      transform: scale(1.05);
      background-color: #7B4F0F;
    }
  </style>

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
            <td class="border px-4 py-2 text-green-600">
              <div class="flex justify-between w-full">
                <span>₱</span>
                <span x-text="formatMoney(row.total)"></span>
              </div>
            </td>
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
            <td class="border px-4 py-2 text-red-600">
              <div class="flex justify-between w-full">
                <span>₱</span>
                <span x-text="formatMoney(row.total)"></span>
              </div>
            </td>
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
              Swal.fire('Error', data.error, 'error');
              return;
            }
            this.incomeSummary = data.income_summary || [];
            this.expensesSummary = data.expenses_summary || [];
            this.totalIncome = data.total_income || 0;
            this.totalExpenses = data.total_expenses || 0;
            this.netIncome = data.net_income || 0;
          } catch (e) {
            console.error(e);
            Swal.fire('Failed to fetch summary.', '', 'error');
          }
        },

        async confirmExport() {
          if (!this.fromDate || !this.toDate) {
            Swal.fire('Missing Dates', 'Please select both dates.', 'warning');
            return;
          }
          if (new Date(this.toDate) < new Date(this.fromDate)) {
            Swal.fire('Invalid Date Range', "The 'To' date cannot be earlier than the 'From' date.", 'warning');
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
              Swal.fire(
                'Failed to download PDF',
                errorData.error || 'Unknown error',
                'error'
              );
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
            console.error(err);
            Swal.fire('Error', 'An error occurred while downloading the PDF.', 'error');
          }
        },

        async uploadPdf() {
          const fileInput = document.getElementById('pdfInput');
          const file = fileInput.files[0];

          if (!file) {
            Swal.fire({
              icon: 'error',
              title: 'No File Selected',
              text: 'Please choose a PDF to upload.'
            });
            return;
          }

          if (file.type !== "application/pdf") {
            Swal.fire({
              icon: 'error',
              title: 'Invalid File',
              text: 'Only PDF files are allowed.'
            });
            fileInput.value = '';
            return;
          }

          const formData = new FormData();
          formData.append('pdf', file);

          await fetch('/upload-pdf', {
            method: 'POST',
            body: formData,
            headers: {
              "X-CSRF-TOKEN": '{{ csrf_token() }}'
            }
          })
            .then(res => res.json())
            .then(async (data) => {

              if (data.success) {
                // Swal.fire({
                //   icon: 'success',
                //   title: 'PDF Uploaded',
                //   text: 'Your PDF has been successfully uploaded!'
                // });

                const res2 = await fetch('/update-transparency', {
                  method: 'POST',
                  body: JSON.stringify({
                    pdf_link: data.pdf
                  }),
                  headers: {
                    'Content-Type': 'application/json',
                    "X-CSRF-TOKEN": '{{ csrf_token() }}'
                  }
                })
                  .then(res2 => res2.json())
                  .then(data2 => {
                    console.log(data2);
                    if (data2.success) {
                      Swal.fire({
                        icon: 'success',
                        title: 'Transparency Updated',
                        text: 'Transparency record updated successfully.'
                      });
                    } else {
                      Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: data2.message || 'Please try again later.'
                      });
                    }
                  });


              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Upload Failed',
                  text: data.message || 'Please try again later.'
                });
              }
            })
            .catch(err => {
              Swal.fire({
                icon: 'error',
                title: 'Upload Error',
                text: 'Something went wrong. Try again later.'
              });
            });

          fileInput.value = '';
        },

        printReport() {
          if (!this.fromDate || !this.toDate) {
            Swal.fire("Missing Dates", "Please select both 'From' and 'To' dates to print.", 'warning');
            return;
          }
          if (new Date(this.toDate) < new Date(this.fromDate)) {
            Swal.fire("Invalid Date Range", "The 'To' date cannot be earlier than the 'From' date.", 'warning');
            return;
          }

          const url = `/financial-report-print?from=${this.fromDate}&to=${this.toDate}`;
          const printWindow = window.open(url, '_blank');

          if (printWindow) {
            printWindow.focus();

            printWindow.addEventListener('load', () => {
              printWindow.print();
            });
          } else {
            Swal.fire("Pop-up Blocked", "Please allow pop-ups for this site to print.", 'error');
          }
        },

        init() {
          const now = new Date();
          this.today = now.toISOString().split('T')[0];
          this.currentYear = now.getFullYear();

          const firstDay = `${this.currentYear}-01-01`;
          const lastDay = `${this.currentYear}-12-31`;

          this.fetchSummary(firstDay, lastDay);

          this.fromDate = '';
          this.toDate = '';
        }
      }));

    });



  </script>

</div>