<?php

use App\Exports\CategoriesExport;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Facades\Pdf;

uses(RefreshDatabase::class);

test('category export downloads selected columns for matching user categories', function () {
    Excel::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Category::create([
        'user_id' => $user->id,
        'name' => 'Dining Out',
        'type' => 'expense',
    ]);
    Category::create([
        'user_id' => $user->id,
        'name' => 'Salary',
        'type' => 'income',
    ]);
    Category::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Dining',
        'type' => 'expense',
    ]);

    $this->actingAs($user)
        ->get('/api/categories/export?columns[]=name&columns[]=type&search=dining')
        ->assertOk();

    Excel::assertDownloaded('categories.xlsx', function (CategoriesExport $export) {
        return $export->headings() === ['Category', 'Type']
            && $export->query()->count() === 1
            && $export->query()->first()->name === 'Dining Out';
    });
});

test('category export requires at least one valid column', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/categories/export?columns[]=password')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('columns.0');
});

test('category PDF export downloads selected columns for matching user categories', function () {
    Pdf::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Category::create([
        'user_id' => $user->id,
        'name' => 'Dining Out',
        'type' => 'expense',
    ]);
    Category::create([
        'user_id' => $user->id,
        'name' => 'Salary',
        'type' => 'income',
    ]);
    Category::create([
        'user_id' => $otherUser->id,
        'name' => 'Private Dining',
        'type' => 'expense',
    ]);

    $this->actingAs($user)
        ->get('/api/categories/export/pdf?columns[]=name&columns[]=type&search=dining')
        ->assertOk();

    Pdf::assertRespondedWithPdf(fn ($pdf) => $pdf->viewName === 'pdf.categories'
        && $pdf->isDownload()
        && $pdf->downloadName === 'categories.pdf'
        && array_column($pdf->viewData['columns'], 'prop') === ['name', 'type']
        && $pdf->viewData['rows']->count() === 1
        && $pdf->viewData['rows']->first() === ['Dining Out', 'expense']
        && str_starts_with($pdf->viewData['logoDataUri'], 'data:image/png;base64,'));
});

test('category PDF export requires at least one valid column', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/categories/export/pdf?columns[]=password')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('columns.0');
});
