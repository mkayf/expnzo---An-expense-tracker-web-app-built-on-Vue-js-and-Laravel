<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function transactionQuery(User $user, ?string $search = null): HasMany
    {
        $query = $user->transactions();

        if ($search !== null && trim($search) !== '') {
            $search = trim($search);
            $like = '%'.mb_strtolower($search).'%';

            $query->where(function ($query) use ($search, $like) {
                $query->whereRaw('LOWER(type) LIKE ?', [$like])
                    ->orWhereRaw('CAST(amount AS CHAR) LIKE ?', ['%'.$search.'%'])
                    ->orWhereRaw('LOWER(transaction_date) LIKE ?', [$like])
                    ->orWhereHas('category', function ($categoryQuery) use ($like) {
                        $categoryQuery->whereRaw('LOWER(name) LIKE ?', [$like]);
                    });

                if (is_numeric($search)) {
                    $query->orWhere('amount', $search);
                }
            });
        }

        return $query;
    }

    public function listTransactions(
        User $user,
        $page,
        $per_page,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null
    )
    {
        $offset = ($page - 1) * $per_page;
        $query = $this->transactionQuery($user, $search);

        $total = (clone $query)->count();
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'category.name') {
            $query->orderBy(
                Category::query()
                    ->select('name')
                    ->whereColumn('categories.id', 'transactions.category_id'),
                $direction
            );
        } elseif (in_array($sortBy, ['amount', 'type', 'transaction_date'], true)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->orderByDesc('transaction_date');
        }

        $records = $query->with(['category:id,name,type'])
            ->orderByDesc('id')
            ->skip($offset)
            ->take($per_page)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'amount' => (float) $item->amount,
                    'note' => $item->note,
                    'transaction_date' => $item->transaction_date,
                    'category' => $item->category,
                ];
            });

        return [
            'items' => $records,
            'total' => $total,
            'per_page' => (int) $per_page,
            'current_page' => (int) $page,
            'last_page' => (int) ceil($total / $per_page)
        ];
    }

    public function showTransaction(User $user, $id)
    {
        $transaction = $user->transactions()->findOrFail($id);
        $transaction = [
            'id' => $transaction->id,
            'category_id' => $transaction->category_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'note' => $transaction->note,
            'transaction_date' => $transaction->transaction_date,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];

        return $transaction;
    }

    public function makeTransaction(User $user, array $data)
    {
        return $user->transactions()->create($data);
    }

    public function updateTransaction(User $user, array $data)
    {
        return $user->transactions()->where('id', $data['id'])->update($data);
    }

    public function deleteTransaction(User $user, $id)
    {
        return $user->transactions()->findOrFail($id)->delete();
    }

    public function deleteTransactions(User $user, array $ids): int
    {
        return DB::transaction(function () use ($user, $ids) {
            $transactions = $user->transactions()->whereIn('id', $ids);
            $foundIds = (clone $transactions)->lockForUpdate()->pluck('id');

            if ($foundIds->count() !== count($ids)) {
                throw (new ModelNotFoundException)->setModel(
                    Transaction::class,
                    array_values(array_diff($ids, $foundIds->all()))
                );
            }

            return $transactions->delete();
        });
    }
}