<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromArray, ShouldAutoSize, WithStyles
{
    private array $rows = [];
    private array $sectionRows = [];
    private array $headerRows = [];
    private array $currencyCells = [];
    private array $percentageCells = [];

    public function __construct(
        private readonly array $report,
        private readonly string $currency,
        private readonly array $filters
    ) {
        $this->addRow(['Financial Report'], true);
        $this->addRow([
            'Report period',
            $this->formatDate($report['period']['start_date']),
            'to',
            $this->formatDate($report['period']['end_date']),
        ]);
        $this->addRow([
            'Compared period',
            $this->formatDate($report['period_comparison']['previous_period']['start_date']),
            'to',
            $this->formatDate($report['period_comparison']['previous_period']['end_date']),
        ]);
        $this->addRow(['Transaction type', $filters['transaction_type_label']]);
        $this->addRow(['Category', $filters['category_name']]);
        $this->addRow([]);

        $this->addRow(['Financial Summary'], true);
        $this->addRow(['Metric', 'Amount (' . $currency . ')'], false, true);
        $summary = $report['summary'];
        $this->addRow(['Total income', $this->numericAmount($summary['total_income'])], false, false, ['B']);
        $this->addRow(['Total expenses', $this->numericAmount($summary['total_expenses'])], false, false, ['B']);
        $this->addRow(['Net cash flow', $this->numericAmount($summary['net_cash_flow'])], false, false, ['B']);
        $this->addRow([
            'Savings rate',
            $this->formatPercentage($summary['savings_rate'], 'N/A (not available for selected filters)'),
        ], false, false, [], ['B']);
        $this->addRow(['Average monthly expenses', $this->numericAmount($summary['average_monthly_expenses'])], false, false, ['B']);
        $this->addRow([]);

        $this->addRow(['Previous Period Comparison'], true);
        $this->addRow([
            'Metric',
            'Current (' . $currency . ')',
            'Previous (' . $currency . ')',
            'Difference (' . $currency . ')',
            'Percentage change',
            'Direction',
        ], false, true);
        foreach ([
            'Total income' => $report['period_comparison']['income'],
            'Total expenses' => $report['period_comparison']['expenses'],
            'Net cash flow' => $report['period_comparison']['net_cash_flow'],
        ] as $label => $metric) {
            $this->addRow([
                $label,
                $this->numericAmount($metric['current']),
                $this->numericAmount($metric['previous']),
                $this->numericAmount($metric['difference']),
                array_key_exists('percentage_change', $metric)
                    ? $this->formatPercentage($metric['percentage_change'], 'N/A (no previous-period baseline)')
                    : 'N/A',
                ucfirst($metric['direction']),
            ], false, false, ['B', 'C', 'D'], ['E']);
        }
        $this->addRow([]);

        $this->addRow(['Monthly Cash Flow'], true);
        $this->addRow([
            'Month',
            'Income (' . $currency . ')',
            'Expenses (' . $currency . ')',
            'Net cash flow (' . $currency . ')',
            'Savings rate',
            'Transaction count',
        ], false, true);
        foreach ($report['monthly'] as $month) {
            $this->addRow([
                $month['month'],
                $this->numericAmount($month['income']),
                $this->numericAmount($month['expenses']),
                $this->numericAmount($month['net']),
                $this->formatPercentage($month['savings_rate']),
                $month['transaction_count'],
            ], false, false, ['B', 'C', 'D'], ['E']);
        }
        $this->addRow([]);

        if (($filters['transaction_type'] ?? 'all') !== 'income') {
            $this->addRow(['Budget Performance'], true);
            $this->addRow([
                'Month',
                'Budget limit (' . $currency . ')',
                'Actual spending (' . $currency . ')',
                'Remaining amount (' . $currency . ')',
                'Status',
            ], false, true);
            foreach ($report['budget_performance'] as $budget) {
                $this->addRow([
                    Carbon::createFromFormat('Y-m', $budget['period'])->format('M Y'),
                    $this->numericAmount($budget['budget_limit']),
                    $this->numericAmount($budget['actual_spending']),
                    $this->numericAmount($budget['remaining_amount']),
                    $budget['status'] === 'exceeded' ? 'Exceeded' : 'Within budget',
                ], false, false, ['B', 'C', 'D']);
            }
            if ($report['budget_performance'] === []) {
                $this->addRow(['No monthly budgets were set during this period.']);
            }
            $this->addRow([]);

            $this->addRow(['Largest Expenses'], true);
            $this->addRow(['Date', 'Category', 'Note', 'Amount (' . $currency . ')'], false, true);
            foreach ($report['largest_expenses'] as $expense) {
                $this->addRow([
                    $this->formatDate($expense['transaction_date']),
                    $expense['category'] ?? 'Uncategorized',
                    $expense['note'] ?? '',
                    $this->numericAmount($expense['amount']),
                ], false, false, ['D']);
            }
            if ($report['largest_expenses']->isEmpty()) {
                $this->addRow(['No expenses in this period.']);
            }
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];
        foreach ($this->sectionRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '225B49']],
            ];
        }
        foreach ($this->headerRows as $row) {
            $styles[$row] = ['font' => ['bold' => true]];
        }
        foreach ($this->currencyCells as $cell) {
            $styles[$cell] = ['numberFormat' => ['formatCode' => '#,##0.00;[Red]-#,##0.00']];
        }
        foreach ($this->percentageCells as $cell) {
            $styles[$cell] = ['numberFormat' => ['formatCode' => '0.00%;[Red]-0.00%']];
        }

        return $styles;
    }

    private function addRow(
        array $row,
        bool $section = false,
        bool $header = false,
        array $currencyColumns = [],
        array $percentageColumns = []
    ): void
    {
        $this->rows[] = array_pad($row, 6, '');
        $rowNumber = count($this->rows);

        if ($section) {
            $this->sectionRows[] = $rowNumber;
        } elseif ($header) {
            $this->headerRows[] = $rowNumber;
        }

        foreach ($currencyColumns as $column) {
            $this->currencyCells[] = $column . $rowNumber;
        }
        foreach ($percentageColumns as $column) {
            $this->percentageCells[] = $column . $rowNumber;
        }
    }

    private function numericAmount(float|int $amount): float
    {
        return (float) $amount;
    }

    private function formatDate(string $date): string
    {
        return Carbon::parse($date)->format('M d, Y');
    }

    private function formatPercentage(float|int|null $value, string $nullMessage = 'N/A'): float|string
    {
        if ($value === null) {
            return $nullMessage;
        }

        return (float) $value / 100;
    }
}
