@php
    $summary = $report['summary'];
    $comparison = $report['period_comparison'];
    $money = fn ($amount) => $currency . ' ' . number_format((float) $amount, 2);
    $percentage = fn ($value) => $value === null
        ? 'N/A (no previous-period baseline)'
        : (($value > 0 ? '+' : '') . number_format((float) $value, 2) . '%');
    $date = fn ($value) => \Carbon\Carbon::parse($value)->format('M d, Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Expnzo Financial Report</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm 14mm; }
        * { box-sizing: border-box; }
        body { color: #334155; font: 10px Arial, sans-serif; line-height: 1.45; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #328e6e; padding-bottom: 12px; }
        header img { width: 150px; }
        h1 { margin: 0; color: #0f172a; font-size: 24px; text-align: right; }
        .meta { margin: 12px 0 18px; color: #64748b; }
        section { margin: 0 0 18px; page-break-inside: avoid; }
        h2 { margin: 0 0 7px; padding: 6px 8px; background: #225b49; color: #fff; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f8f5; color: #225b49; text-align: left; }
        th, td { padding: 6px 8px; border: 1px solid #dbe3e8; vertical-align: top; }
        .number { text-align: right; white-space: nowrap; }
        .status-exceeded { color: #b91c1c; font-weight: bold; }
        .status-within { color: #28725a; font-weight: bold; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <header>
        <img src="{{ $logoDataUri }}" alt="Expnzo">
        <div>
            <h1>Financial Report</h1>
            <div class="meta">
                {{ $date($report['period']['start_date']) }} – {{ $date($report['period']['end_date']) }}
                &nbsp;|&nbsp; Generated {{ $generatedAt->format('M d, Y, h:i A') }}
                <br>
                Transaction type: {{ $filters['transaction_type_label'] }}
                &nbsp;|&nbsp; Category: {{ $filters['category_name'] }}
            </div>
        </div>
    </header>

    <section>
        <h2>Financial Summary</h2>
        <table>
            <thead><tr><th>Measure</th><th class="number">Amount / Rate</th></tr></thead>
            <tbody>
                <tr><td>Total income</td><td class="number">{{ $money($summary['total_income']) }}</td></tr>
                <tr><td>Total expenses</td><td class="number">{{ $money($summary['total_expenses']) }}</td></tr>
                <tr><td>Net cash flow</td><td class="number">{{ $money($summary['net_cash_flow']) }}</td></tr>
                <tr><td>Savings rate</td><td class="number">{{ $percentage($summary['savings_rate']) }}</td></tr>
                <tr><td>Average monthly expenses</td><td class="number">{{ $money($summary['average_monthly_expenses']) }}</td></tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Previous Period Comparison</h2>
        <p class="muted">
            Current: {{ $date($report['period']['start_date']) }} – {{ $date($report['period']['end_date']) }}
            &nbsp;|&nbsp;
            Previous: {{ $date($comparison['previous_period']['start_date']) }} – {{ $date($comparison['previous_period']['end_date']) }}
        </p>
        <table>
            <thead>
                <tr><th>Measure</th><th class="number">Current</th><th class="number">Previous</th><th class="number">Difference</th><th class="number">Percentage change</th><th>Direction</th></tr>
            </thead>
            <tbody>
                @foreach ([
                    'Total income' => $comparison['income'],
                    'Total expenses' => $comparison['expenses'],
                    'Net cash flow' => $comparison['net_cash_flow'],
                ] as $label => $metric)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="number">{{ $money($metric['current']) }}</td>
                        <td class="number">{{ $money($metric['previous']) }}</td>
                        <td class="number">{{ $money($metric['difference']) }}</td>
                        <td class="number">
                            {{ array_key_exists('percentage_change', $metric) ? $percentage($metric['percentage_change']) : 'N/A' }}
                        </td>
                        <td>{{ ucfirst($metric['direction']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Monthly Cash Flow Statement</h2>
        <table>
            <thead><tr><th>Month</th><th class="number">Income</th><th class="number">Expenses</th><th class="number">Net cash flow</th><th class="number">Savings rate</th><th class="number">Transactions</th></tr></thead>
            <tbody>
                @foreach ($report['monthly'] as $month)
                    <tr>
                        <td>{{ $month['month'] }}</td>
                        <td class="number">{{ $money($month['income']) }}</td>
                        <td class="number">{{ $money($month['expenses']) }}</td>
                        <td class="number">{{ $money($month['net']) }}</td>
                        <td class="number">{{ $percentage($month['savings_rate']) }}</td>
                        <td class="number">{{ number_format($month['transaction_count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @if (($filters['transaction_type'] ?? 'all') !== 'income')
        <section>
            <h2>Budget Performance</h2>
            <table>
                <thead><tr><th>Month</th><th class="number">Budget limit</th><th class="number">Actual spending</th><th class="number">Remaining</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($report['budget_performance'] as $budget)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $budget['period'])->format('M Y') }}</td>
                            <td class="number">{{ $money($budget['budget_limit']) }}</td>
                            <td class="number">{{ $money($budget['actual_spending']) }}</td>
                            <td class="number">{{ $money($budget['remaining_amount']) }}</td>
                            <td class="{{ $budget['status'] === 'exceeded' ? 'status-exceeded' : 'status-within' }}">
                                {{ $budget['status'] === 'exceeded' ? 'Exceeded' : 'Within budget' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">No monthly budgets were set during this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section>
            <h2>Largest Expenses</h2>
            <table>
                <thead><tr><th>Date</th><th>Category</th><th>Note</th><th class="number">Amount</th></tr></thead>
                <tbody>
                    @forelse ($report['largest_expenses'] as $expense)
                        <tr>
                            <td>{{ $date($expense['transaction_date']) }}</td>
                            <td>{{ $expense['category'] ?? 'Uncategorized' }}</td>
                            <td>{{ $expense['note'] ?? '—' }}</td>
                            <td class="number">{{ $money($expense['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No expenses in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</body>
</html>
