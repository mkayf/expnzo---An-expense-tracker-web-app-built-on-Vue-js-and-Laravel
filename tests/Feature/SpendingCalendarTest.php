<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('spending calendar uses the requested month instead of the current month', function () {
    Carbon::setTestNow('2026-11-15');

    try {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 800,
            'transaction_date' => '2026-10-18',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 1300,
            'transaction_date' => '2026-10-28',
        ]);

        $this->actingAs($user)
            ->getJson('/api/dashboard/spending-calendar?month=2026-10')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'transaction_date' => '2026-10-18',
                'amount' => 800,
            ])
            ->assertJsonFragment([
                'transaction_date' => '2026-10-28',
                'amount' => 1300,
            ]);
    } finally {
        Carbon::setTestNow();
    }
});
