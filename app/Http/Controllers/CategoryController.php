<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

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
            Log::info('Error occured while getting categories', ['error' => $th->getMessage()]);

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
            Log::info('Error occured while creating category', ['error' => $th->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Somethin went wrong while creating categories, please try again later',
            ], 500);
        }

    }
}
