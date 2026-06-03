<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Food & Dining', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Transportation', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Salary', 'type' => 'income', 'user_id' => null],
            ['name' => 'Utilities (Bills)', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Shopping', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Freelance Projects', 'type' => 'income', 'user_id' => null],
            ['name' => 'Health & Fitness', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Entertainment', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Rent/Mortgage', 'type' => 'expense', 'user_id' => null],
            ['name' => 'Investments', 'type' => 'income', 'user_id' => null],
        ];

        foreach($categories as $category){
            Category::create($category);
        }
    }
}
