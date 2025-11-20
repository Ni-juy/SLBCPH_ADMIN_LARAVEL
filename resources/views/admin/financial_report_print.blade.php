<!DOCTYPE html>
<html>
<head>
    <title>Financial Report</title>
    <style>
        /* -------- Page Margins (for DomPDF) -------- */
        @page {
            margin: 140px 30px 80px 30px; /* top, right, bottom, left */
        }

        /* -------- Header & Footer -------- */
        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 12px;
            color: #777777;
        }

        /* -------- General Styles -------- */
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 20px;
            color: #333333;
            background-color: #ffffff;
        }

        h2, h3 {
            text-align: center;
            margin: 10px 0;
        }

        h2 {
            color: #1e3a8a;
            font-size: 20px;
        }

        h3 {
            color: #2563eb;
            font-size: 16px;
        }

        /* -------- Section Labels -------- */
        .section-label {
            font-size: 16px;
            font-weight: bold;
            padding: 8px;
            color: #ffffff;
            margin-top: 20px;
        }

        .income-label { background-color: #10b981; } /* Green */
        .expense-label { background-color: #ef4444; } /* Red */

        /* -------- Tables -------- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #ffffff;
        }

        th, td {
            border: 1px solid #999999;
            padding: 10px;
            font-size: 13px;
        }

        th {
            background-color: #1e40af;
            color: #ffffff;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* -------- Totals Section -------- */
        .totals {
            margin-top: 30px;
            font-weight: bold;
            font-size: 14px;
            text-align: right;
        }

        .totals p { margin: 6px 0; }

        /* -------- Charts -------- */
        .charts {
            margin-top: 40px;
            text-align: center;
        }

        .charts img {
            margin: 10px auto;
            display: block;
            max-width: 100%;
            height: auto;
        }

        .chart-title {
            font-weight: bold;
            margin: 10px 0;
        }

        /* -------- Print-specific Styles -------- */
        @media print {
            header, footer {
                position: fixed;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body class="{{ isset($pdf) ? 'pdf-mode' : '' }}">
    <!-- Header -->
    <header>
        <div style="display: inline-block; vertical-align: middle;">
            <img src="{{ $logo }}" alt="Church Logo" style="height: 70px; width: auto;">
        </div>
        <div style="display: inline-block; margin-left: 10px; vertical-align: middle;">
            <div style="font-size: 20px; font-weight: bold; color: #1e40af;">SHINING LIGHT BAPTIST CHURCH</div>
            <div style="font-size: 16px; color: #1e3a8a; font-weight: bold;">FINANCIAL REPORT</div>
            <div style="font-size: 13px; color: #2563eb;">
                Date Range: {{ date('F j, Y', strtotime($from)) }} to {{ date('F j, Y', strtotime($to)) }}
            </div>
            <div style="font-size: 13px; color: #2563eb;">
                Branch: {{ auth()->user()->branch->name ?? 'N/A' }}
            </div>
        </div>
    </header>

    <!-- Income Table -->
    <div class="section-label income-label">CASH INFLOWS (INCOME)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount (&#8369;)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($income as $item)
                <tr>
                    <td>{{ date('F j, Y', strtotime($item->date)) }}</td>
                    <td>{{ $item->category }}</td>
                  <td style="text-align: right;">
    <span style="float: left;">&#8369;</span>
    {{ number_format($item->amount, 2) }}
</td>

                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Expense Table -->
    <div class="section-label expense-label">CASH OUTFLOWS (EXPENSES)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount (&#8369;)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $item)
                <tr>
                    <td>{{ date('F j, Y', strtotime($item->date)) }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->description }}</td>
  <td style="text-align: right;">
    <span style="float: left;">&#8369;</span>
    {{ number_format($item->amount, 2) }}
</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <p>Total Cash Inflows: &#8369;{{ number_format($totalIncome, 2) }}</p>
        <p>Total Cash Outflows: &#8369;{{ number_format($totalExpenses, 2) }}</p>
        <p>Net Cash Flow: &#8369;{{ number_format($netIncome, 2) }}</p>
    </div>

    <!-- Charts -->
    <div class="charts">
        <h3>Graphical Representation</h3>
        <table style="width: 100%; text-align: center; margin-top: 20px;">
            <tr>
                <td>
                    <div class="chart-title">Income vs Expenses</div>
                    <img src="{{ $incomeExpenseChart }}" alt="Income vs Expenses Chart">
                </td>
                <td>
                    <div class="chart-title">
                        {{ $isSingleMonth ? 'Total Income and Expenses' : 'Monthly Income and Expenses' }}
                    </div>
                    <img src="{{ $incomeExpensesChart }}" alt="Income and Expenses Chart">
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <footer>
        <p>Generated on {{ date('F j, Y') }} by {{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}</p>
    </footer>

    <script>
        window.onload = function () {
            window.print();
            window.onafterprint = function () {
                window.close();
            };
        };
    </script>
</body>
</html>
