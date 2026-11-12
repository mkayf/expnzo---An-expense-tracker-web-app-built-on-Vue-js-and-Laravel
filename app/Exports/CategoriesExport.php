<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromQuery, WithHeadings, WithMapping
{
    public const COLUMNS = [
        'id' => 'Category ID',
        'name' => 'Category',
        'type' => 'Type',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ];

    /**
     * @param  list<string>  $columns
     */
    public function __construct(
        private readonly HasMany $categories,
        private readonly array $columns,
    ) {
    }

    public function query(): HasMany
    {
        return $this->categories
            ->orderBy('name')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return array_map(fn (string $column) => self::COLUMNS[$column], $this->columns);
    }

    public function map(mixed $category): array
    {
        return array_map(
            fn (string $column) => $category->{$column},
            $this->columns
        );
    }
}
