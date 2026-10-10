<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialSummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-10-09 12:00:00');

    $this->user = User::factory()->create([
        'created_at' => '2026-07-01 00:00:00',
    ]);
    $this->category = Category::create([
        'user_id' => $this->user->id,
        'name' => 'Food',
        'type' => 'expense',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('it returns the unique highest spending day when there are at least three expenses', function () {
    foreach ([
        ['2026-10-02', 50],
        ['2026-10-05', 80],
        ['2026-10-09', 120],
    ] as [$date, $amount]) {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }

    $insight = app(FinancialSummaryService::class)
        ->spendingInsights($this->user)
        ->firstWhere('type', 'highest_spending_day');

    expect($insight['date'])->toBe('2026-10-09')
        ->and($insight['amount'])->toBe(120.0)
        ->and($insight['is_shared'])->toBeFalse();
});

test('it returns a shared highest spending amount without selecting a date for a tie', function () {
    foreach ([
        ['2026-10-02', 100],
        ['2026-10-05', 50],
        ['2026-10-09', 100],
        ['2026-10-09', 50],
        ['2026-10-01', 150],
    ] as [$date, $amount]) {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }

    $insight = app(FinancialSummaryService::class)
        ->spendingInsights($this->user)
        ->firstWhere('type', 'highest_spending_day');

    expect($insight['date'])->toBeNull()
        ->and($insight['amount'])->toBe(150.0)
        ->and($insight['is_shared'])->toBeTrue();
});

test('it does not return a highest spending day with fewer than three expense transactions', function () {
    foreach ([
        ['2026-10-02', 100],
        ['2026-10-09', 150],
    ] as [$date, $amount]) {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }

    $insights = app(FinancialSummaryService::class)->spendingInsights($this->user);

    expect($insights->firstWhere('type', 'highest_spending_day'))->toBeNull();
});

test('it combines same-day transactions before finding the highest spending day', function () {
    foreach ([
        ['2026-10-02', 60],
        ['2026-10-02', 70],
        ['2026-10-02', 80],
        ['2026-10-05', 190],
    ] as [$date, $amount]) {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }

    $insight = app(FinancialSummaryService::class)
        ->spendingInsights($this->user)
        ->firstWhere('type', 'highest_spending_day');

    expect($insight['date'])->toBe('2026-10-02')
        ->and($insight['amount'])->toBe(210.0)
        ->and($insight['is_shared'])->toBeFalse();
});

test('it returns the highest spending day alongside a spending spike', function () {
    foreach ([
        ['2026-07-05', 100],
        ['2026-08-05', 100],
        ['2026-09-05', 100],
        ['2026-10-02', 50],
        ['2026-10-05', 50],
        ['2026-10-09', 50],
    ] as [$date, $amount]) {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => $date,
        ]);
    }

    $insights = app(FinancialSummaryService::class)->spendingInsights($this->user);

    expect($insights->pluck('type')->all())->toContain('spending_spike', 'highest_spending_day');
});
