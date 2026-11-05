@php
    $currencyLabel = $currencySymbol ?? $currency;
    $net = $incomeTotal - $expenseTotal;
    $typeIndex = collect($columns)->search(fn ($c) => $c['prop'] === 'type');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Expnzo Transaction Report</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-50: #f1f8f5;
            --color-primary-100: #dff0e8;
            --color-primary-200: #bde0d0;
            --color-primary-500: #43a483;
            --color-primary: #328e6e;
            --color-primary-700: #28725a;
            --color-primary-800: #225b49;
            --color-primary-900: #1c4a3c;
        }

        @page {
            size: A4 landscape;
        }

        html, body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: "Montserrat", sans-serif;
        }

    </style>
</head>
<body class="bg-white text-[10px] leading-relaxed text-slate-700">

    {{-- Header --}}
    <header class="flex items-center justify-between border-b border-slate-200 pb-5">
        <div class="flex items-center gap-4">
            <img src="{{ $logoDataUri }}" alt="Expnzo" class="block h-auto w-40">
        </div>

        <div class="text-right">
            <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-primary">Financial Statement</p>
            <h1 class="mt-1 text-[26px] font-bold leading-none tracking-tight text-slate-900">Transaction Report</h1>
            <p class="mt-1.5 text-slate-500">Your finances, clearly in view.</p>
        </div>
    </header>

    {{-- Accent line --}}
    <div class="-mt-px h-0.75 w-28 rounded-full bg-primary"></div>

    {{-- Meta --}}
    <div class="mb-5 mt-4 flex items-center justify-between text-slate-500">
        <div class="flex items-center gap-2">
            <span class="inline-block h-1.5 w-1.5 rounded-full bg-primary"></span>
            <span>Generated on <strong class="font-semibold text-slate-700">{{ $generatedAt->format('M d, Y, h:i A') }}</strong></span>
        </div>

        @if ($search)
            <span class="rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-primary-800">
                Filtered by: <strong class="font-semibold">{{ $search }}</strong>
            </span>
        @endif
    </div>

    {{-- Summary --}}
    <section class="mb-6 grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <span class="block text-[8.5px] font-bold uppercase tracking-widest text-slate-400">Transactions</span>
            <span class="mt-1.5 block text-[20px] font-bold leading-none text-slate-900">{{ number_format($recordCount) }}</span>
            <span class="mt-2 block text-[9px] text-slate-400">Total records in this report</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <span class="text-[8.5px] font-bold uppercase tracking-widest text-slate-400">Total income</span>
                <span class="h-2 w-2 rounded-full bg-primary"></span>
            </div>
            <span class="mt-1.5 block text-[20px] font-bold leading-none text-primary-700">{{ $currencyLabel }} {{ number_format($incomeTotal, 2) }}</span>
            <span class="mt-2 block text-[9px] text-slate-400">Money received</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <span class="text-[8.5px] font-bold uppercase tracking-widest text-slate-400">Total expenses</span>
                <span class="h-2 w-2 rounded-full bg-[#e6a23c]"></span>
            </div>
            <span class="mt-1.5 block text-[20px] font-bold leading-none text-[#e6a23c]">{{ $currencyLabel }} {{ number_format($expenseTotal, 2) }}</span>
            <span class="mt-2 block text-[9px] text-slate-400">Money spent</span>
        </div>

        <div class="rounded-xl bg-primary-900 p-4 text-white">
            <span class="block text-[8.5px] font-bold uppercase tracking-widest text-primary-200">Net balance</span>
            <span class="mt-1.5 block text-[20px] font-bold leading-none">
                {{ $net < 0 ? '-' : '' }}{{ $currencyLabel }} {{ number_format(abs($net), 2) }}
            </span>
            <span class="mt-2 block text-[9px] text-primary-200">
                {{ $net >= 0 ? 'Income exceeds expenses' : 'Expenses exceed income' }}
            </span>
        </div>
    </section>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="w-full border-collapse">
            <thead class="table-header-group">
                <tr class="bg-primary-900">
                    @foreach ($columns as $column)
                        <th class="px-3 py-2.5 text-[8.5px] font-bold uppercase tracking-wider text-white {{ $column['prop'] === 'amount' ? 'text-right' : 'text-left' }}">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $row)
                    @php($rowType = $typeIndex !== false ? ($row[$typeIndex] ?? null) : null)
                    <tr class="break-inside-avoid border-b border-slate-100 last:border-b-0 {{ $loop->even ? 'bg-[#f7faf9]' : 'bg-white' }}">
                        @foreach ($columns as $index => $column)
                            @php($value = $row[$index] ?? null)

                            @if ($column['prop'] === 'amount')
                                <td class="whitespace-nowrap px-3 py-2.5 text-right align-top font-semibold tabular-nums
                                    {{ $rowType === 'income' ? 'text-primary-700' : ($rowType === 'expense' ? 'text-[#e6a23c]' : 'text-slate-800') }}">
                                    @if ($value !== null)
                                        {{ $rowType === 'income' ? '+' : ($rowType === 'expense' ? '-' : '') }}{{ $currencyLabel }} {{ number_format((float) $value, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @elseif ($column['prop'] === 'type')
                                <td class="px-3 py-2.5 align-top">
                                    @if ($value)
                                        <span class="inline-block rounded-full px-2.5 py-0.5 text-[8.5px] font-bold uppercase tracking-wide
                                            {{ $value === 'income' ? 'bg-primary-100 text-primary-800' : 'bg-[#fdf6ec] text-[#b88230]' }}">
                                            {{ $value }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @else
                                <td class="px-3 py-2.5 align-top wrap-anywhere {{ $column['prop'] === 'id' ? 'font-semibold text-slate-900' : 'text-slate-600' }}">
                                    {{ $value ?: '-' }}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-6 py-12 text-center text-slate-400">
                            No transactions found for this report.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <footer class="mt-5 flex items-center justify-between border-t border-slate-200 pt-3 text-[8.5px] text-slate-400">
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-1.5 w-1.5 rounded-full bg-primary"></span>
            Expnzo | Personal finance, made simple
        </span>
        <span>{{ number_format($recordCount) }} {{ \Illuminate\Support\Str::plural('record', $recordCount) }}</span>
    </footer>
</body>
</html>