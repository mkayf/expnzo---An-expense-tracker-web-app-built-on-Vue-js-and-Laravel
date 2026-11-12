<?php

namespace App\Http\Controllers;

use App\Exports\FinancialReportExport;
use App\Models\Category;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
    }

    public function financialReport(Request $request)
    {
        $filters = $this->validatedFilters($request);

        try {
            $data = $this->reportService->financialReport(
                $request->user(),
                $filters
            );

            return response()->json([
                'success' => true,
                'message' => 'Financial report fetched successfully',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error occurred while fetching financial report', [
                'user_id' => $request->user()->id,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting the financial report',
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $user = $request->user();
        $report = $this->reportService->financialReport($user, $filters);
        $currency = $user->preferences?->currency
            ?? $user->preferences?->currency_iso
            ?? 'USD';

        return Excel::download(
            new FinancialReportExport($report, $currency, $this->filterLabels($user, $filters)),
            $this->exportFilename($filters, 'xlsx'),
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $user = $request->user();
        $report = $this->reportService->financialReport($user, $filters);
        $logoPath = resource_path('js/src/assets/logo/expnzo-light-logo.png');
        if (! is_file($logoPath) || ! is_readable($logoPath)) {
            throw new RuntimeException('Unable to load the report logo.');
        }

        $logo = file_get_contents($logoPath);
        if ($logo === false) {
            throw new RuntimeException('Unable to load the report logo.');
        }

        return Pdf::view('pdf.financial-report', [
            'report' => $report,
            'filters' => $this->filterLabels($user, $filters),
            'currency' => $user->preferences?->currency
                ?? $user->preferences?->currency_iso
                ?? 'USD',
            'generatedAt' => now(),
            'logoDataUri' => 'data:image/png;base64,' . base64_encode($logo),
        ])
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->newHeadless()->waitUntilNetworkIdle();
            })
            ->format('a4')
            ->landscape()
            ->margins(12, 10, 14, 10)
            ->download($this->exportFilename($filters, 'pdf'));
    }

    private function validatedFilters(Request $request): array
    {
        $transactionType = $request->input('transaction_type', 'all');
        $categoryRule = Rule::exists('categories', 'id')->where(function ($query) use ($request, $transactionType) {
            $query->where(function ($ownershipQuery) use ($request) {
                $ownershipQuery->whereNull('user_id')
                    ->orWhere('user_id', $request->user()->id);
            });

            if (in_array($transactionType, ['income', 'expense'], true)) {
                $query->where('type', $transactionType);
            }
        });

        $validated = $request->validate([
            'months' => ['sometimes', 'required_without:start_date', 'integer', 'in:3,6,12'],
            'start_date' => ['sometimes', 'required_with:end_date', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'required_with:start_date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'transaction_type' => ['sometimes', 'in:all,income,expense'],
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                $categoryRule,
            ],
        ]);

        return [
            ...$validated,
            'transaction_type' => $validated['transaction_type'] ?? 'all',
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
        ];
    }

    private function exportFilename(array $filters, string $extension): string
    {
        $period = isset($filters['start_date'])
            ? $filters['start_date'] . '-to-' . $filters['end_date']
            : $filters['months'] . '-months';

        return 'financial-report-' . $period . '.' . $extension;
    }

    private function filterLabels(User $user, array $filters): array
    {
        $categoryId = $filters['category_id'] ?? null;

        return [
            'transaction_type' => $filters['transaction_type'],
            'transaction_type_label' => match ($filters['transaction_type']) {
                'income' => 'Income',
                'expense' => 'Expenses',
                default => 'All Transactions',
            },
            'category_name' => $categoryId === null
                ? 'All Categories'
                : Category::query()
                    ->whereKey($categoryId)
                    ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
                    ->value('name'),
        ];
    }
}
