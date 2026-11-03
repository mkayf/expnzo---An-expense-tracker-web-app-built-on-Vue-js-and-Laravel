<?php

use App\Exports\TransactionsExport;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

test('transaction export downloads selected columns for all matching user transactions', function () {
    Excel::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Dining Out',
        'type' => 'expense',
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 42.50,
        'note' => 'Dinner',
        'transaction_date' => '2026-03-16',
    ]);
    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 120,
        'note' => 'Refund',
        'transaction_date' => '2026-03-17',
    ]);
    Transaction::create([
        'user_id' => $otherUser->id,
        'type' => 'expense',
        'amount' => 42.50,
        'note' => 'Private',
        'transaction_date' => '2026-03-16',
    ]);

    $this->actingAs($user)
        ->get('/api/transactions/export?columns[]=type&columns[]=note&search=expense')
        ->assertOk();

    Excel::assertDownloaded('transactions.xlsx', function (TransactionsExport $export) {
        return $export->headings() === ['Type', 'Note']
            && $export->query()->count() === 1
            && $export->query()->first()->note === 'Dinner';
    });
});

test('transaction export requires at least one valid column', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/transactions/export?columns[]=password')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('columns.0');
});
