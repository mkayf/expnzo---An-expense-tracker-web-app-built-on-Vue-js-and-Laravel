<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialSummaryService
{
    public function statsSummary($user)
    {
        // Balance calculation:
        $total_incomes = (float) ($user?->transactions()->where('type', 'income')->sum('amount') ?? 0);
        $total_expenses = (float) ($user?->transactions()->where('type', 'expense')->sum('amount') ?? 0);

        $total_balance = $total_incomes - $total_expenses;

        $current_date = Carbon::now();

        $last_month_income = (float) ($user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0);

        $last_month_expense = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->subMonthNoOverflow()->startOfMonth(), $current_date->copy()->subMonthNoOverflow()->endOfMonth()])->sum('amount') ?? 0);

        $last_month_balance = $last_month_income - $last_month_expense;

        $current_month_transactions = $user?->transactions()->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->count();

        $current_month_income = (float) ($user?->transactions()->where('type', 'income')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->sum('amount') ?? 0);

        $current_month_expense = (float) ($user?->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$current_date->copy()->startOfMonth(), $current_date->copy()->endOfMonth()])->sum('amount') ?? 0);

        $current_month_balance = $current_month_income - $current_month_expense;

        $balancePercentage = null;
        $balanceDirection = null;

        if ($last_month_balance != 0) {
            $balancePercentage = round((($current_month_balance - $last_month_balance) / $last_month_balance) * 100, 2);

            if ($balancePercentage == 0) {
                $balanceDirection = 'neutral';
            } else if ($balancePercentage > 0) {
                $balanceDirection = "up";
            } else {
                $balanceDirection = 'down';
            }
        }

        // Income calculation:
        $incomePercentage = null;
        $incomeDirection = null;

        if ($last_month_income != 0) {
            $incomePercentage = round((($current_month_income - $last_month_income) / $last_month_income) * 100, 2);

            if ($incomePercentage == 0) {
                $incomeDirection = 'neutral';
            } else if ($incomePercentage > 0) {
                $incomeDirection = 'up';
            } else {
                $incomeDirection = 'down';
            }
        }

        $daily_income = $user->transactions()
            ->where('type', 'income')
            ->whereBetween(
                'transaction_date',
                [
                    $current_date->copy()->startOfMonth(),
                    $current_date->copy()->endOfMonth()
                ]
            )
            ->selectRaw('transaction_date, SUM(amount) as amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $income_dates = [];
        $income_amounts = [];

        foreach ($daily_income as $income) {
            $income_dates[] = $income['transaction_date'];
            $income_amounts[] = (float) $income['amount'];
        }

        // Expense calculation:
        $expensePercentage = null;
        $expenseDirection = null;

        if ($last_month_expense != 0) {
            $expensePercentage = round(
                (($current_month_expense - $last_month_expense) / $last_month_expense) * 100,
                2
            );

            if ($expensePercentage == 0) {
                $expenseDirection = 'neutral';
            } else if ($expensePercentage > 0) {
                $expenseDirection = 'up';
            } else {
                $expenseDirection = 'down';
            }
        }

        $daily_expense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween(
                'transaction_date',
                [
                    $current_date->copy()->startOfMonth(),
                    $current_date->copy()->endOfMonth()
                ]
            )
            ->selectRaw('transaction_date, SUM(amount) as amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $expense_dates = [];
        $expense_amounts = [];

        foreach ($daily_expense as $expense) {
            $expense_dates[] = $expense['transaction_date'];
            $expense_amounts[] = (float) $expense['amount'];
        }

        // Budget calculation:
        $period = $current_date->copy()->format('Y-m');
        $budget = (float) ($user?->budgets()->where('period', $period)->value('limit_amount') ?? 0);
        $budgetUsePercentage = null;
        $overBudgetPercentage = null;
        $budgetDirection = null;
        $usedBudget = 0;
        $remainingBudget = 0;

        if ($budget) {
            $budgetUsePercentage = round(($current_month_expense / $budget) * 100, 2);

            if ($budgetUsePercentage == 0) {
                $budgetDirection = 'neutral';
            } else if ($budgetUsePercentage > 100) {
                $budgetDirection = 'over';
                $overBudgetPercentage = round($budgetUsePercentage - 100, 2);
            } else if ($budgetUsePercentage > 75) {
                $budgetDirection = 'up';
            } else if ($budgetUsePercentage <= 75) {
                $budgetDirection = 'down';
            }

            $usedBudget = $current_month_expense;
            $remainingBudget = $budget - $usedBudget;
        }


        return [
            'balance' => [
                'amount' => $total_balance,
                'current_month_transactions' => $current_month_transactions,
                'trend' => [
                    'direction' => $balanceDirection,
                    'percentage' => abs($balancePercentage)
                ],
                'chart_data' => [
                    'total_incomes' => $total_incomes,
                    'total_expense' => $total_expenses
                ]
            ],
            'income' => [
                'amount' => $current_month_income,
                'trend' => [
                    'direction' => $incomeDirection,
                    'percentage' => abs($incomePercentage)
                ],
                'chart_data' => [
                    'dates' => $income_dates,
                    'amounts' => $income_amounts
                ]
            ],
            'expense' => [
                'amount' => $current_month_expense,
                'trend' => [
                    'direction' => $expenseDirection,
                    'percentage' => abs($expensePercentage)
                ],
                'chart_data' => [
                    'dates' => $expense_dates,
                    'amounts' => $expense_amounts
                ]
            ],
            'budget' => [
                'amount' => $budget,
                'trend' => [
                    'direction' => $budgetDirection,
                    'percentage' => abs($budgetUsePercentage),
                    'over_budget_percentage' => abs($overBudgetPercentage)
                ],
                'chart_data' => [
                    'used_budget' => $usedBudget,
                    'remaining_budget' => $remainingBudget
                ]
            ]
        ];

    }

    public function incomeVsExpense($user, $months = 6)
    {
        $start_date = Carbon::now()->subMonthsNoOverflow($months - 1)->startOfMonth();

        $end_date = Carbon::now()->endOfMonth();

        // Get the existing months data:
        $existing_months_data = $user?->transactions()
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->where('transaction_date', '>=', $start_date)
            ->where('transaction_date', '<=', $end_date)
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        // generate months when there was no transactions:
        $data = collect(range(0, $months - 1))->map(function (int $i) use ($start_date, $existing_months_data) {
            $date = $start_date->copy()->addMonths($i);
            $key = $date->format('Y-m');
            $row = $existing_months_data->get($key);

            return [
                'month' => $date->format('M'),
                'income' => (float) ($row->income ?? 0),
                'expense' => (float) ($row->expense ?? 0)
            ];
        });

        return $data;
    }

    public function expenseByCategories($user, $months)
    {
        $current_date = Carbon::now();

        if ($months == 0) {
            $from = $current_date->copy()->startOfMonth();
            $to = $current_date->copy()->endOfMonth();
        } else if ($months == 1) {
            $from = $current_date->copy()->subMonthNoOverflow()->startOfMonth();
            $to = $current_date->copy()->subMonthNoOverflow()->endOfMonth();
        } else {
            $from = $current_date->copy()->subMonthsNoOverflow($months - 1)->startOfMonth();
            $to = $current_date->copy()->endOfMonth();
        }

        $data = $user?->transactions()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name')
            ->selectRaw('SUM(transactions.amount) AS amount')
            ->where('transactions.type', 'expense')
            ->where('categories.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->groupBy('categories.name')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'amount' => (float) $item->amount
                ];
            });

        if ($data->count() > 7) {
            $top = $data->take(7);
            $otherCategoriesAmount = $data->slice(7)->sum('amount');

            $data = $top->push([
                'name' => 'Other',
                'amount' => $otherCategoriesAmount
            ]);
        }

        return $data->values();

    }

    public function recentTransactions($user)
    {
        $transactions = $user->transactions()->with(['category:id,name'])->latest('transaction_date')->latest('id')->take(5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'amount' => (float) $item->amount,
                'note' => $item->note,
                'transaction_date' => $item->transaction_date,
                'category' => $item->category,
            ];
        });

        return $transactions;
    }

    public function spendingInsights($user)
    {
        $today = Carbon::today();
        $current_month = $today->copy()->startOfMonth();
        $current_month_key = $current_month->format('Y-m');
        $previous_month_keys = collect(range(3, 1))
            ->map(fn(int $monthsAgo) => $current_month->copy()->subMonthsNoOverflow($monthsAgo)->format('Y-m'));

        $comparison_periods = collect([
            $current_month_key => [
                $current_month,
                $today->copy()->endOfDay()
            ]
        ]);

        $average_transaction_size_insights = collect();
        if ($user) {
            $current_month_expense_totals = $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$current_month, $today->copy()->endOfDay()])
                ->selectRaw('COUNT(*) as transaction_count, SUM(amount) as total_amount')
                ->first();
            $current_month_expense_count = (int) $current_month_expense_totals->transaction_count;

            if ($current_month_expense_count > 0) {
                $current_month_average = (float) $current_month_expense_totals->total_amount / $current_month_expense_count;
                $previous_month = $current_month->copy()->subMonthNoOverflow();
                $previous_month_expense_totals = $user->transactions()
                    ->where('type', 'expense')
                    ->whereBetween('transaction_date', [
                        $previous_month->copy()->startOfMonth(),
                        $previous_month->copy()->endOfMonth()
                    ])
                    ->selectRaw('COUNT(*) as transaction_count, SUM(amount) as total_amount')
                    ->first();
                $previous_month_expense_count = (int) $previous_month_expense_totals->transaction_count;
                $insight = [
                    'type' => 'average_transaction_size',
                    'severity' => 'neutral',
                    'current_month_average' => round($current_month_average, 2)
                ];

                if ($previous_month_expense_count > 0) {
                    $previous_month_average = (float) $previous_month_expense_totals->total_amount / $previous_month_expense_count;
                    $previous_month_average = round($previous_month_average, 2);
                    $insight['previous_month_average'] = $previous_month_average;
                    $insight['comparison_direction'] = match (round($current_month_average, 2) <=> $previous_month_average) {
                        1 => 'increased',
                        -1 => 'decreased',
                        default => 'unchanged'
                    };
                }

                $average_transaction_size_insights->push($insight);
            }
        }

        $daily_expenses = $user?->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$current_month, $today->copy()->endOfDay()])
            ->selectRaw('DATE(transaction_date) as spending_date')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount) as amount')
            ->groupBy('spending_date')
            ->get();

        $small_purchases_impact_insights = collect();
        $total_expenses = (float) ($daily_expenses?->sum('amount') ?? 0);
        if ($user && $total_expenses > 0) {
            $small_purchase_threshold = 1000;
            $small_purchase_totals = $user->transactions()
                ->where('type', 'expense')
                ->where('amount', '<=', $small_purchase_threshold)
                ->whereBetween('transaction_date', [$current_month, $today->copy()->endOfDay()])
                ->selectRaw('COUNT(*) as transaction_count, SUM(amount) as total_amount')
                ->first();
            $small_transaction_count = (int) $small_purchase_totals->transaction_count;
            $small_purchases_total = (float) ($small_purchase_totals->total_amount ?? 0);
            $spending_percentage = $small_purchases_total / $total_expenses * 100;

            if ($small_transaction_count >= 5 && $spending_percentage >= 15) {
                $small_purchases_impact_insights->push([
                    'type' => 'small_purchases_impact',
                    'severity' => 'neutral',
                    'small_purchases_total' => $small_purchases_total,
                    'transaction_count' => $small_transaction_count,
                    'spending_percentage' => (int) round($spending_percentage)
                ]);
            }
        }

        $highest_spending_day_insights = collect();
        if (
            $daily_expenses
            && $daily_expenses->sum('transaction_count') >= 3
            && $daily_expenses->count() >= 2
        ) {
            $highest_daily_amount = $daily_expenses->max(fn($day) => (float) $day->amount);
            $highest_spending_days = $daily_expenses->filter(function ($day) use ($highest_daily_amount) {
                return (float) $day->amount === $highest_daily_amount;
            })->values();
            $highest_spending_day = $highest_spending_days->first();

            $highest_spending_day_insights->push([
                'type' => 'highest_spending_day',
                'severity' => 'neutral',
                'amount' => $highest_daily_amount,
                'date' => $highest_spending_days->count() === 1 ? $highest_spending_day->spending_date : null,
                'is_shared' => $highest_spending_days->count() > 1
            ]);
        }

        $most_frequent_category_insights = collect();
        if ($user) {
            $category_transaction_counts = $user->transactions()
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->select('categories.id as category_id', 'categories.name')
                ->selectRaw('COUNT(transactions.id) as transaction_count')
                ->where('transactions.type', 'expense')
                ->where('categories.type', 'expense')
                ->whereBetween('transactions.transaction_date', [$current_month, $today->copy()->endOfDay()])
                ->groupBy('categories.id', 'categories.name')
                ->get();

            if ($category_transaction_counts->sum('transaction_count') >= 3) {
                $highest_category_count = (int) $category_transaction_counts->max('transaction_count');
                $most_frequent_categories = $category_transaction_counts
                    ->filter(fn($category) => (int) $category->transaction_count === $highest_category_count)
                    ->pluck('name')
                    ->values()
                    ->all();

                $most_frequent_category_insights->push([
                    'type' => 'most_frequent_category',
                    'severity' => 'neutral',
                    'categories' => $most_frequent_categories,
                    'transaction_count' => $highest_category_count
                ]);
            }
        }

        foreach ($previous_month_keys as $month) {
            $month_start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $month_end_day = min($today->day, $month_start->daysInMonth);
            $comparison_periods->put($month, [
                $month_start,
                $month_start->copy()->day($month_end_day)->endOfDay()
            ]);
        }

        $history_start = $comparison_periods->get($previous_month_keys->first())[0];

        if (!$user || !$user->created_at || Carbon::parse($user->created_at)->gt($history_start)) {
            return $highest_spending_day_insights
                ->concat($most_frequent_category_insights)
                ->concat($average_transaction_size_insights)
                ->concat($small_purchases_impact_insights)
                ->values();
        }

        $month_expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', transactions.transaction_date)"
            : "DATE_FORMAT(transactions.transaction_date, '%Y-%m')";

        $category_expenses = $user?->transactions()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.id as category_id', 'categories.name')
            ->selectRaw("$month_expression as month")
            ->selectRaw('SUM(transactions.amount) AS amount')
            ->where('transactions.type', 'expense')
            ->where('categories.type', 'expense')
            ->where(function ($query) use ($comparison_periods) {
                foreach ($comparison_periods as [$from, $to]) {
                    $query->orWhereBetween('transactions.transaction_date', [$from, $to]);
                }
            })
            ->groupBy('categories.id', 'categories.name', 'month')
            ->get()
            ->groupBy('category_id');

        $spending_spike_insights = $category_expenses
            ->map(function ($months) use ($previous_month_keys, $current_month_key) {
                $previous_month_expenses = $previous_month_keys
                    ->map(function (string $month) use ($months) {
                        $expense = $months->firstWhere('month', $month);

                        return [
                            'month' => $month,
                            'amount' => (float) ($expense->amount ?? 0)
                        ];
                    })
                    ->values();
                $current_month_expense = $months->firstWhere('month', $current_month_key);
                $category = $months->first();
                $average_previous_month_expense = $previous_month_keys
                    ->map(function (string $month) use ($months) {
                        $expense = $months->firstWhere('month', $month);

                        return $expense ? (float) $expense->amount : null;
                    })
                    ->filter(fn($amount) => $amount !== null)
                    ->avg();
                $increase_percentage = $average_previous_month_expense > 0
                    ? (($current_month_expense->amount ?? 0) - $average_previous_month_expense) / $average_previous_month_expense * 100
                    : null;

                return [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'current_month_expense' => (float) ($current_month_expense->amount ?? 0),
                    'previous_3_months' => $previous_month_expenses,
                    'average_previous_3_months' => round($average_previous_month_expense ?? 0, 2),
                    'increase_percentage' => $increase_percentage,
                ];
            })
            ->filter(function ($insight) {
                return $insight['increase_percentage'] !== null && $insight['increase_percentage'] >= 30;
            })
            ->map(function ($insight) {
                $insight['type'] = 'spending_spike';
                $insight['severity'] = 'warning';

                return $insight;
            })
            ->sortByDesc('increase_percentage')
            ->pipe(function ($insights) {
                $highest_spike = $insights->first();

                if ($highest_spike === null) {
                    return $insights;
                }

                return $insights
                    ->filter(function ($insight) use ($highest_spike) {
                        return $insight['increase_percentage'] === $highest_spike['increase_percentage'];
                    })
                    ->take(2);
            })
            ->map(function ($insight) {
                $insight['increase_percentage'] = round($insight['increase_percentage'], 2);

                return $insight;
            })
            ->values();

        return $spending_spike_insights
            ->concat($highest_spending_day_insights)
            ->concat($most_frequent_category_insights)
            ->concat($average_transaction_size_insights)
            ->concat($small_purchases_impact_insights)
            ->values();
    }

    public function spendingCalendar($user, $month)
    {
        
    }


}