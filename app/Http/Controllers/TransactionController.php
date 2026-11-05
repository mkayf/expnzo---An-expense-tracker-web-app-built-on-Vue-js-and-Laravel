<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }

    public function index(Request $request)
    {
        $validation = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|nullable|string|max:255',
            'sort_by' => ['sometimes', 'nullable', 'string', Rule::in(['amount', 'type', 'category.name', 'transaction_date'])],
            'sort_direction' => ['sometimes', 'required_with:sort_by', Rule::in(['asc', 'desc'])],
        ]);

        $page = $validation['page'] ?? 1;
        $per_page = $validation['per_page'] ?? 20;

        try {
            $transactions = $this->transactionService->listTransactions(
                $request->user(),
                $page,
                $per_page,
                $validation['sort_by'] ?? null,
                $validation['sort_direction'] ?? null,
                $validation['search'] ?? null
            );

            if ($transactions) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transactions fetched successfully',
                    'data' => $transactions
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while listing transcations', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting transactions, please try again'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $validation = $request->validate([
            'columns' => 'required|array|min:1',
            'columns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(TransactionsExport::COLUMNS))],
            'search' => 'sometimes|nullable|string|max:255',
        ]);

        $transactions = $this->transactionService->transactionQuery(
            $request->user(),
            $validation['search'] ?? null
        );

        return Excel::download(
            new TransactionsExport($transactions, $validation['columns']),
            'transactions.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPdf(Request $request)
    {
        $pdfColumns = array_diff_key(TransactionsExport::COLUMNS, ['note' => true]);
        $validation = $request->validate([
            'columns' => 'required|array|min:1',
            'columns.*' => ['required', 'string', 'distinct', Rule::in(array_keys($pdfColumns))],
            'search' => 'sometimes|nullable|string|max:255',
        ]);

        $records = $this->transactionService
            ->transactionQuery($request->user(), $validation['search'] ?? null)
            ->with('category:id,name,type')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $columns = array_map(fn(string $column) => [
            'prop' => $column,
            'label' => TransactionsExport::COLUMNS[$column],
        ], $validation['columns']);
        $rows = $records->map(fn($transaction) => array_map(
            fn(string $column) => match ($column) {
                'category.name' => $transaction->category?->name,
                'created_at', 'updated_at' => $transaction->{$column}?->format('Y-m-d H:i:s'),
                default => $transaction->{$column},
            },
            $validation['columns']
        ));

        $logoPath = resource_path('js/src/assets/logo/expnzo-light-logo.png');
        $logo = file_get_contents($logoPath);
        if ($logo === false) {
            throw new RuntimeException('Unable to load the report logo.');
        }

        return Pdf::view('pdf.transactions', [
            'columns' => $columns,
            'rows' => $rows,
            'recordCount' => $records->count(),
            'incomeTotal' => $records->where('type', 'income')->sum(fn($transaction) => (float) $transaction->amount),
            'expenseTotal' => $records->where('type', 'expense')->sum(fn($transaction) => (float) $transaction->amount),
            'currency' => $request->user()->preferences?->currency_iso ?? 'USD',
            'currencySymbol' => $request->user()->preferences?->currency ?? null,
            'search' => $validation['search'] ?? null,
            'generatedAt' => now(),
            'logoDataUri' => 'data:image/png;base64,' . base64_encode($logo),
        ])
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->newHeadless()->waitUntilNetworkIdle();
            })
            ->format('a4')
            ->landscape()
            ->margins(12, 10, 14, 10)
            ->download('transactions.pdf');
    }

    public function show(Request $request, $id)
    {
        try {
            $transaction = $this->transactionService->showTransaction($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Transaction fetched succesfully',
                'data' => $transaction
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        } catch (\Throwable $th) {
            Log::error('Error occured while fetching single transcation', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting transaction, please try again'
            ], 500);
        }
    }

    public function store(TransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->makeTransaction($request->user(), $request->validated());

            if ($transaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction created successfully'
                ], 201);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while creating transaction', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while making transaction, please try again'
            ], 500);
        }
    }

    public function update(TransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->updateTransaction($request->user(), $request->validated());

            if ($transaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction updated succesfully'
                ], 200);
            }
        } catch (\Throwable $th) {
            Log::error('Error occured while updating transcation', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating transaction, please try again'
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $this->transactionService->deleteTransaction($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        } catch (\Throwable $th) {
            Log::error('Error occurred while deleting transaction', [
                'transaction_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting transaction, please try again',
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $validation = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'required|integer|min:1|distinct',
        ]);

        try {
            $deletedCount = $this->transactionService->deleteTransactions(
                $request->user(),
                $validation['ids']
            );

            return response()->json([
                'success' => true,
                'message' => $deletedCount . ' transactions deleted successfully',
                'deleted_count' => $deletedCount,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'One or more transactions were not found',
            ], 404);
        } catch (\Throwable $th) {
            Log::error('Error occurred while bulk deleting transactions', [
                'user_id' => $request->user()->id,
                'transaction_ids' => $validation['ids'],
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting transactions, please try again',
            ], 500);
        }
    }
}
