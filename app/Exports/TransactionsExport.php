<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    public const COLUMNS = [
        'id' => 'Transaction ID',
        'amount' => 'Amount',
        'type' => 'Type',
        'category.name' => 'Category',
        'note' => 'Note',
        'transaction_date' => 'Transaction Date',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ];

    /**
     * @param  list<string>  $columns
     */
    public function __construct(
        private readonly HasMany $transactions,
        private readonly array $columns,
    ) {
    }

    public function query(): HasMany
    {
        return $this->transactions
            ->with('category:id,name,type')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return array_map(fn (string $column) => self::COLUMNS[$column], $this->columns);
    }

    public function map(mixed $transaction): array
    {
        return array_map(
            fn (string $column) => match ($column) {
                'category.name' => $transaction->category?->name,
                default => $transaction->{$column},
            },
            $this->columns
        );
    }
}
