<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories = Category::whereNull('user_id')->orWhere('user_id', $request->user()->id)->select('id', 'user_id', 'name', 'type')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories fetched succesfully',
                'data' => $categories,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error occured while getting categories', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while getting categories, please try again later',
            ], 500);
        }

    }

    public function createCustomCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:5', 'max:50'],
                'type' => ['required', 'string', 'in:income,expense'],
            ]);

            $user = $request->user();
            $category = $user->customCategories()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'New category added successfully'
            ], 201);

        } catch (\Throwable $th) {
            Log::error('Error occured while creating category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while creating categories, please try again later',
            ], 500);
        }
    }

    public function deleteCustomCategory(Request $request){
        try {
            $validated = $request->validate([
                'id' => ['required', 'exists:categories,id']
            ]);

            $user = $request->user();
            $user->customCategories()->where('id', $validated['id'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your custom category has been deleted successfully'
            ], 200);

        } catch (\Throwable $th) {
             Log::error('Error occured while deleting user custom category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while deleting categories, please try again later',
            ], 500);
        }
    }
}
