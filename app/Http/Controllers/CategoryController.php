<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class CategoryController extends Controller
{
    public function export(Request $request)
    {
        $validated = $request->validate([
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(CategoriesExport::COLUMNS))],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $categories = $request->user()->customCategories();
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';
            $categories->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(type) LIKE ?', [$like]);
            });
        }

        return Excel::download(
            new CategoriesExport($categories, $validated['columns']),
            'categories.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(CategoriesExport::COLUMNS))],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $categories = $request->user()->customCategories();
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';
            $categories->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(type) LIKE ?', [$like]);
            });
        }

        $records = $categories
            ->orderBy('name')
            ->orderBy('id')
            ->get();
        $columns = array_map(fn (string $column) => [
            'prop' => $column,
            'label' => CategoriesExport::COLUMNS[$column],
        ], $validated['columns']);
        $rows = $records->map(fn (Category $category) => array_map(
            fn (string $column) => match ($column) {
                'created_at', 'updated_at' => $category->{$column}?->format('Y-m-d H:i:s'),
                default => $category->{$column},
            },
            $validated['columns']
        ));

        $logoPath = resource_path('js/src/assets/logo/expnzo-light-logo.png');
        $logo = file_get_contents($logoPath);
        if ($logo === false) {
            throw new RuntimeException('Unable to load the report logo.');
        }

        return Pdf::view('pdf.categories', [
            'columns' => $columns,
            'rows' => $rows,
            'recordCount' => $records->count(),
            'expenseCategoryCount' => $records->where('type', 'expense')->count(),
            'incomeCategoryCount' => $records->where('type', 'income')->count(),
            'search' => $validated['search'] ?? null,
            'generatedAt' => now(),
            'logoDataUri' => 'data:image/png;base64,' . base64_encode($logo),
        ])
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->newHeadless()->waitUntilNetworkIdle();
            })
            ->format('a4')
            ->landscape()
            ->margins(12, 10, 14, 10)
            ->download('categories.pdf');
    }

    public function list(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_by' => ['sometimes', 'nullable', 'string', Rule::in(['name', 'type', 'created_at'])],
            'sort_direction' => ['sometimes', 'required_with:sort_by', Rule::in(['asc', 'desc'])],
        ]);

        $user = $request->user();
        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 20;

        try {
            $query = $user->customCategories();

            if (!empty($validated['search'])) {
                $search = mb_strtolower(trim($validated['search']));
                $like = '%' . $search . '%';
                $query->where(function ($query) use ($like) {
                    $query->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$like]);
                });
            }

            $total = (clone $query)->count();
            $sortBy = $validated['sort_by'] ?? 'name';
            $sortDirection = $validated['sort_direction'] ?? 'asc';
            $categories = $query
                ->orderBy($sortBy, $sortDirection)
                ->orderBy('id')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get(['id', 'name', 'type', 'created_at'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'created_at' => $category->created_at,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Categories fetched successfully',
                'data' => [
                    'items' => $categories,
                    'total' => $total,
                    'per_page' => (int) $perPage,
                    'current_page' => (int) $page,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('Error occurred while listing categories', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while getting categories, please try again',
            ], 500);
        }
    }

    public function index(Request $request)
    {   
        $validated = $request->validate([
            'type' => ['required', 'in:expense,income']
        ]);

        try {
            $categories = Category::where('type', $validated['type'])
            ->where(function($query) use($request) {
                $query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories fetched succesfully',
                'data' => $categories,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error occured while getting categories', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while getting categories, please try again',
            ], 500);
        }

    }

    public function createCustomCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:50'],
            'type' => ['required', 'string', 'in:income,expense'],
        ]);

        try {
            $user = $request->user();
            $category = $user->customCategories()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'New category added successfully',
                'category' => $category
            ], 201);

        } catch (\Throwable $th) {
            Log::error('Error occured while creating category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while creating categories, please try again',
            ], 500);
        }
    }

    public function deleteCustomCategory(Request $request, int $id)
    {
        try {
            $category = $request->user()->customCategories()->findOrFail($id);
            if ($category->transactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This category is used by transactions and cannot be deleted',
                ], 409);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your custom category has been deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Custom category not found',
            ], 404);
        } catch (\Throwable $th) {
            Log::error('Error occured while deleting user custom category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while deleting categories, please try again',
            ], 500);
        }
    }

    public function updateCustomCategory(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:50'],
            'type' => ['required', 'string', Rule::in(['income', 'expense'])],
        ]);

        try {
            $category = $request->user()->customCategories()->findOrFail($id);
            if ($category->type !== $validated['type'] && $category->transactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The category type cannot be changed while it is used by transactions',
                ], 409);
            }

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Custom category not found',
            ], 404);
        } catch (\Throwable $th) {
            Log::error('Error occurred while updating user custom category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the category, please try again',
            ], 500);
        }
    }

    public function bulkDeleteCustomCategories(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $categories = $request->user()->customCategories()
                    ->whereIn('id', $validated['ids'])
                    ->lockForUpdate()
                    ->get();

                if ($categories->count() !== count($validated['ids'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more selected custom categories were not found',
                    ], 404);
                }

                $categoryIds = $categories->modelKeys();
                if (Category::query()->whereIn('id', $categoryIds)->whereHas('transactions')->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected categories used by transactions cannot be deleted',
                    ], 409);
                }

                $deletedCount = $request->user()->customCategories()
                    ->whereIn('id', $categoryIds)
                    ->delete();

                return response()->json([
                    'success' => true,
                    'message' => "{$deletedCount} categories deleted successfully",
                    'deleted_count' => $deletedCount,
                ]);
            });
        } catch (\Throwable $th) {
            Log::error('Error occurred while bulk deleting user custom categories', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting categories, please try again',
            ], 500);
        }
    }
}
