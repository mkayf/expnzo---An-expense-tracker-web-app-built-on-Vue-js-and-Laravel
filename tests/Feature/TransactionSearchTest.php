<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transaction search matches amount type category and date and keeps pagination filtered', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Dining Out',
        'type' => 'expense',
    ]);

    $matchingTransactions = collect(range(1, 21))->map(fn ($index) => Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => $index === 1 ? 123.45 : 50 + $index,
        'transaction_date' => $index === 1 ? '2026-03-16' : '2026-02-01',
    ]));

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 999,
        'transaction_date' => '2026-01-01',
    ]);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=')
        ->assertOk()
        ->assertJsonPath('data.total', 22);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=dining')
        ->assertOk()
        ->assertJsonPath('data.total', 21);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=expense')
        ->assertOk()
        ->assertJsonPath('data.total', 21);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=123.45')
        ->assertOk()
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.items.0.id', $matchingTransactions[0]->id);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=2026-03-16')
        ->assertOk()
        ->assertJsonPath('data.total', 1)
        ->assertJsonPath('data.items.0.id', $matchingTransactions[0]->id);

    $this->actingAs($user)
        ->getJson('/api/transactions?search=expense&page=2&per_page=20')
        ->assertOk()
        ->assertJsonPath('data.total', 21)
        ->assertJsonPath('data.current_page', 2)
        ->assertJsonCount(1, 'data.items');
});
