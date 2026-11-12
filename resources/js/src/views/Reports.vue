<script setup>
import { computed, onMounted, ref, watch } from "vue";
import {
    ArrowTrendingDownIcon,
    ArrowTrendingUpIcon,
    BanknotesIcon,
    DocumentChartBarIcon,
    DocumentTextIcon,
    WalletIcon,
} from "@heroicons/vue/24/outline";
import VueApexCharts from "vue3-apexcharts";
import Card from "../components/ui/Card.vue";
import StatShimmerCard from "../components/ui/StatShimmerCard.vue";
import handleError from "../utils/handleError.js";
import { formatAmount, formatDate } from "../utils/helpers.js";
import useAuthStore from "../stores/auth";
import useThemeStore from "../stores/theme";
import useCategoryStore from "../stores/categoryStore.js";
import { withChartTheme } from "../utils/chartTheme.js";
import { getCategories } from "../services/category.service.js";
import {
    exportFinancialReportExcel,
    exportFinancialReportPdf,
    getFinancialReport,
} from "../services/report.service.js";

const authStore = useAuthStore();
const themeStore = useThemeStore();
const categoryStore = useCategoryStore();
const currency = authStore.user?.preferences?.currency ?? 'PKR';
const currencyIso = authStore.user?.preferences?.currency_iso ?? 'PK';
const selectedPeriod = ref(6);
const transactionType = ref('all');
const selectedCategoryId = ref('all');
const startDate = ref('');
const endDate = ref('');
const categoriesLoading = ref(false);
const report = ref(null);
const loading = ref(false);
const exporting = ref('');
let reportRequestId = 0;

const categories = computed(() => [
    ...(transactionType.value === 'income'
        ? categoryStore.incomeCategories
        : transactionType.value === 'expense'
            ? categoryStore.expenseCategories
            : [...categoryStore.incomeCategories, ...categoryStore.expenseCategories]),
].filter((category, index, list) =>
    list.findIndex((item) => item.id === category.id) === index
));

const toDateInputValue = (date) =>
    `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

const dateRangeError = computed(() => {
    if (selectedPeriod.value !== 'custom') return '';
    if (!startDate.value && !endDate.value) return '';
    if (!startDate.value || !endDate.value) return 'Both start and end dates are required.';
    if (startDate.value > endDate.value) return 'Start date must not be later than end date.';
    return '';
});

const customRangeIncomplete = computed(() =>
    selectedPeriod.value === 'custom' && (!startDate.value || !endDate.value)
);

const clearCustomDateRange = () => {
    startDate.value = '';
    endDate.value = '';
    selectedPeriod.value = 6;
};

const currentReportFilters = () => ({
    ...(selectedPeriod.value === 'custom'
        ? { start_date: startDate.value, end_date: endDate.value }
        : { months: selectedPeriod.value }),
    transaction_type: transactionType.value,
    ...(selectedCategoryId.value !== 'all' && selectedCategoryId.value !== null
        ? { category_id: selectedCategoryId.value }
        : {}),
});

const disableStartDate = (date) =>
    Boolean(endDate.value && toDateInputValue(date) > endDate.value);

const disableEndDate = (date) =>
    Boolean(startDate.value && toDateInputValue(date) < startDate.value);

const hasMatchingTransactions = computed(() =>
    Boolean(report.value?.monthly?.some((month) => Number(month.transaction_count) > 0))
);

const reportStatCards = computed(() => {
    if (!report.value) return [];

    const monthly = report.value.monthly;
    const cards = [
        {
            key: 'income',
            label: 'Total Income',
            icon: ArrowTrendingUpIcon,
            amount: report.value.summary.total_income,
            values: monthly.map((month) => month.income),
            currencyValue: true,
        },
        {
            key: 'expenses',
            label: 'Total Expenses',
            icon: ArrowTrendingDownIcon,
            amount: report.value.summary.total_expenses,
            values: monthly.map((month) => month.expenses),
            currencyValue: true,
            caption: `Monthly avg.: ${formatCurrency(report.value.summary.average_monthly_expenses)}`,
        },
        {
            key: 'net',
            label: 'Net Cash Flow',
            icon: WalletIcon,
            amount: report.value.summary.net_cash_flow,
            values: monthly.map((month) => month.net),
            currencyValue: true,
        },
        {
            key: 'savings',
            label: 'Savings Rate',
            icon: BanknotesIcon,
            amount: report.value.summary.savings_rate,
            values: monthly.map((month) => month.savings_rate),
            currencyValue: false,
        },
    ];

    return cards.map((card) => {
        const chartValues = card.values.filter((value) => Number.isFinite(value));
        const minimum = chartValues.length ? Math.min(...chartValues) : 0;
        const maximum = chartValues.length ? Math.max(...chartValues) : 0;
        const padding = Math.max((maximum - minimum) * 0.1, Math.abs(maximum) * 0.1, 1);
        const comparison = card.key === 'income'
            ? report.value.period_comparison.income
            : card.key === 'expenses'
                ? report.value.period_comparison.expenses
                : card.key === 'net'
                    ? report.value.period_comparison.net_cash_flow
                    : null;
        const favorableDirection = card.key === 'expenses' ? 'decrease' : 'increase';
        const percentageChange = comparison
            ? comparison.percentage_change
                ?? (comparison.previous !== 0
                    ? (comparison.difference / comparison.previous) * 100
                    : null)
            : null;
        const trend = card.key === 'savings'
            ? { text: 'Calculated for All Transactions and All Categories', color: 'neutral' }
            : !comparison
                ? null
                : comparison.direction === 'unchanged'
                    ? { text: 'No change vs previous period', color: 'neutral' }
                    : {
                        text: `${comparison.direction === 'increase' ? '↑' : '↓'} ${
                            percentageChange == null
                                ? 'New'
                                : `${Math.abs(percentageChange).toFixed(1)}%`
                        } vs previous period`,
                        color: comparison.direction === favorableDirection ? 'positive' : 'negative',
                    };

        return {
            ...card,
            trend,
            chartOptions: withChartTheme({
                chart: {
                    type: 'area',
                    width: 50,
                    height: 70,
                    parentHeightOffset: 0,
                    sparkline: { enabled: true },
                    animations: { enabled: false },
                },
                xaxis: {
                    categories: monthly.map((month) => month.month),
                },
                yaxis: {
                    min: minimum - padding,
                    max: maximum + padding,
                    show: false,
                },
                markers: {
                    size: 0,
                    hover: { size: 0 },
                },
                grid: {
                    padding: { top: 2, right: 0, bottom: 0, left: 0 },
                },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: 'smooth' },
                fill: { type: 'gradient' },
                colors: [card.key === 'expenses' ? 'var(--yellow-color)' : 'var(--el-color-primary)'],
                tooltip: {
                    y: {
                        formatter: (value) => card.currencyValue
                            ? formatCurrency(value)
                            : formatSavingsRate(value),
                    },
                },
            }, themeStore.isDark),
            chartSeries: [{ name: card.label, data: card.values }],
        };
    });
});

const formatCurrency = (amount) =>
    `${currency} ${formatAmount(Number(amount ?? 0), currencyIso)}`;

const formatSavingsRate = (rate) =>
    rate === null || rate === undefined ? '—' : `${Number(rate).toFixed(1)}%`;

const formatBudgetPeriod = (period) =>
    new Date(`${period}-01T00:00:00`).toLocaleDateString('en-US', {
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC',
    });

const formatPercentageChange = (value) =>
    value === null ? 'No previous-period baseline' : `${value > 0 ? '+' : ''}${Number(value).toFixed(1)}%`;

const comparisonTagType = (metric, direction) => {
    if (direction === 'unchanged') return 'info';
    const favorableDirection = metric === 'expenses' ? 'decrease' : 'increase';
    return direction === favorableDirection ? 'success' : 'danger';
};

const comparisonTextClass = (metric, direction) => {
    if (direction === 'unchanged') return 'text-gray-500 dark:text-gray-400';
    const favorableDirection = metric === 'expenses' ? 'decrease' : 'increase';
    return direction === favorableDirection
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-red-600 dark:text-red-400';
};

const comparisonLabel = (direction) => ({
    increase: 'Increase',
    decrease: 'Decrease',
    unchanged: 'No change',
}[direction] ?? 'No change');

const fetchCategories = async () => {
    categoriesLoading.value = true;

    try {
        await Promise.all(['income', 'expense'].map(async (type) => {
            const cachedCategories = type === 'income'
                ? categoryStore.incomeCategories
                : categoryStore.expenseCategories;
            if (cachedCategories.length) return;

            const response = await getCategories(type);
            if (!response.data?.success) {
                throw new Error(response.data?.message ?? 'Unable to load categories.');
            }
            categoryStore.setCategories(type, response.data.data);
        }));
    } catch (error) {
        handleError(error);
    } finally {
        categoriesLoading.value = false;
    }
};

const exportReport = async (format) => {
    if (customRangeIncomplete.value || dateRangeError.value || !report.value) return;
    const filters = currentReportFilters();
    exporting.value = format;

    try {
        const exportService = format === 'pdf'
            ? exportFinancialReportPdf
            : exportFinancialReportExcel;
        const response = await exportService(filters);
        const fileUrl = URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = `financial-report-${report.value.period.start_date}-to-${report.value.period.end_date}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(fileUrl), 1000);
    } catch (error) {
        handleError(error);
    } finally {
        exporting.value = '';
    }
};

const fetchReport = async () => {
    const requestId = ++reportRequestId;
    if (customRangeIncomplete.value || dateRangeError.value) {
        loading.value = false;
        return;
    }

    loading.value = true;

    try {
        const response = await getFinancialReport(currentReportFilters());
        if (!response.data?.success) {
            throw new Error(response.data?.message ?? 'Unable to load the financial report.');
        }
        if (requestId === reportRequestId) report.value = response.data.data;
    } catch (error) {
        if (requestId === reportRequestId) handleError(error);
    } finally {
        if (requestId === reportRequestId) loading.value = false;
    }
};

watch(
    transactionType,
    () => {
        selectedCategoryId.value = 'all';
    }
);

watch(
    [selectedPeriod, transactionType, selectedCategoryId, startDate, endDate],
    fetchReport
);

onMounted(() => {
    fetchCategories();
    fetchReport();
});
</script>

<template>
    <div class="w-full min-w-0 space-y-5 overflow-x-clip">
        <div class="flex min-w-0 flex-col gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold">Reports</h1>
            </div>
            <div class="flex w-full min-w-0 flex-col gap-3">
                <el-alert
                    title="Filter your financial report by date range, transaction type, and category. Your selected filters will also be applied to Excel and PDF exports."
                    type="info" show-icon :closable="false" />
                <div class="flex w-full min-w-0 flex-wrap items-center gap-3">
                    <el-select v-model="selectedPeriod" aria-label="Date Range" placeholder="Date Range"
                        class="!w-44 shrink-0" :disabled="!!exporting">
                        <el-option :value="3" label="Last 3 months" />
                        <el-option :value="6" label="Last 6 months" />
                        <el-option :value="12" label="Last 12 months" />
                        <el-option value="custom" label="Custom Date Range" />
                    </el-select>
                    <template v-if="selectedPeriod === 'custom'">
                        <el-date-picker v-model="startDate" type="date" value-format="YYYY-MM-DD"
                            format="MMM DD, YYYY" placeholder="Start date *" aria-label="Start date, required"
                            class="!w-36 shrink-0" :disabled-date="disableStartDate" :disabled="!!exporting" />
                        <el-date-picker v-model="endDate" type="date" value-format="YYYY-MM-DD"
                            format="MMM DD, YYYY" placeholder="End date *" aria-label="End date, required"
                            class="!w-36 shrink-0" :disabled-date="disableEndDate" :disabled="!!exporting" />
                        <el-button class="shrink-0" size="small" :disabled="!!exporting"
                            @click="clearCustomDateRange">
                            Clear range
                        </el-button>
                    </template>
                    <el-select v-model="transactionType" aria-label="Transaction Type" placeholder="Transaction Type"
                        class="!w-40 shrink-0" :disabled="!!exporting">
                        <el-option value="all" label="All Transactions" />
                        <el-option value="income" label="Income" />
                        <el-option value="expense" label="Expenses" />
                    </el-select>
                    <el-select v-model="selectedCategoryId" aria-label="Category" placeholder="All Categories"
                        filterable :loading="categoriesLoading"
                        class="!w-44 shrink-0" :disabled="!!exporting">
                        <el-option value="all" label="All Categories" />
                        <el-option v-if="!categories.length && !categoriesLoading" value="no-categories"
                            label="No categories available for this transaction type" disabled />
                        <el-option v-for="category in categories" :key="category.id" :value="category.id"
                            :label="`${category.name} · ${category.type === 'income' ? 'Income' : 'Expenses'}`" />
                    </el-select>
                    <el-button class="shrink-0" size="small" type="primary" :icon="DocumentChartBarIcon"
                        :loading="exporting === 'excel'"
                        :disabled="loading || customRangeIncomplete || !!dateRangeError || !report || (!!exporting && exporting !== 'excel')"
                        @click="exportReport('excel')">
                        Export Excel
                    </el-button>
                    <el-button class="!ml-0 shrink-0" size="small" type="danger" :icon="DocumentTextIcon"
                        :loading="exporting === 'pdf'"
                        :disabled="loading || customRangeIncomplete || !!dateRangeError || !report || (!!exporting && exporting !== 'pdf')"
                        @click="exportReport('pdf')">
                        Export PDF
                    </el-button>
                </div>
                <span v-if="dateRangeError" class="w-full text-xs text-red-600 dark:text-red-400">
                    {{ dateRangeError }}
                </span>
            </div>
        </div>

        <div v-if="loading && !report" class="space-y-5">
            <div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                <StatShimmerCard v-for="title in ['Total income', 'Total expenses', 'Net cash flow', 'Savings rate']"
                    :key="title" />
            </div>
            <Card v-for="section in [
                { title: 'Previous period comparison', rows: 3 },
                { title: 'Monthly cash flow', rows: 4 },
                ...(transactionType === 'income' ? [] : [{ title: 'Budget performance', rows: 3 }]),
                ...(transactionType === 'income' ? [] : [{ title: 'Largest expenses', rows: 3 }]),
            ]" :key="section.title">
                <template #header>{{ section.title }}</template>
                <template #body><el-skeleton :rows="section.rows" animated /></template>
            </Card>
        </div>
        <template v-else-if="report">
            <div v-if="!loading && !hasMatchingTransactions" role="status"
                class="mb-3 rounded-lg border border-[var(--el-border-color)] bg-[var(--app-surface)] px-4 py-3 text-sm text-[var(--el-text-color-secondary)]">
                No matching transactions were found for the selected filters.
            </div>
            <div class="grid min-w-0 grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="stat in reportStatCards" :key="stat.key"
                    class="relative h-full min-h-[120px] min-w-0 rounded-2xl border border-[var(--el-border-color)] bg-[var(--app-surface)] p-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="shrink-0 rounded-2xl bg-[var(--el-color-primary-dark-2)] p-2 text-white"
                                style="box-shadow: rgba(100, 100, 111, 0.5) 0px 4px 8px 0px;">
                                <component :is="stat.icon" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 break-words text-sm font-semibold text-slate-700 dark:text-[var(--el-text-color-regular)]">
                                {{ stat.label }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 min-w-0 pr-[58px]">
                        <div class="flex min-w-0 flex-wrap items-baseline">
                            <span v-if="stat.currencyValue"
                                class="text-md font-medium text-slate-700 dark:text-[var(--el-text-color-regular)]">
                                {{ currency }}
                            </span>
                            <span class="ml-1 break-words text-2xl font-semibold [overflow-wrap:anywhere]"
                                :class="stat.key === 'net' && stat.amount < 0 ? 'text-red-500' : ''">
                                {{ stat.key === 'savings' && stat.amount == null
                                    ? '0%'
                                    : stat.currencyValue
                                        ? formatAmount(stat.amount, currencyIso)
                                        : formatSavingsRate(stat.amount) }}
                            </span>
                        </div>
                        <div class="mt-1 w-full">
                            <span v-if="stat.trend"
                                class="block break-words text-[0.7rem] [overflow-wrap:anywhere]"
                                :class="{
                                    'text-emerald-600 dark:text-emerald-400': stat.trend.color === 'positive',
                                    'text-red-600 dark:text-red-400': stat.trend.color === 'negative',
                                    'text-gray-500 dark:text-gray-400': stat.trend.color === 'neutral',
                                }">
                                {{ stat.trend.text }}
                            </span>
                            <span v-if="stat.caption"
                                class="mt-1 block w-full break-words text-[0.7rem] text-[var(--el-text-color-secondary)] [overflow-wrap:anywhere]">
                                {{ stat.caption }}
                            </span>
                        </div>
                    </div>
                    <div class="absolute bottom-3 right-3 flex h-[70px] w-[50px] items-end justify-end">
                        <VueApexCharts width="50" height="70" :options="stat.chartOptions"
                            :series="stat.chartSeries" />
                    </div>
                    <el-skeleton v-if="loading" :rows="1" animated class="mt-2" />
                </div>
            </div>

            <Card :body_x_padding="false">
                <template #header>Previous period comparison</template>
                <template #addons>
                    <span v-if="report.period_comparison" class="min-w-0 break-words text-xs text-[var(--el-text-color-secondary)]">
                        {{ formatDate(report.period.start_date) }} – {{ formatDate(report.period.end_date) }}
                        compared with
                        {{ formatDate(report.period_comparison.previous_period.start_date) }} –
                        {{ formatDate(report.period_comparison.previous_period.end_date) }}
                    </span>
                </template>
                <template #body>
                    <el-skeleton v-if="loading" :rows="3" animated class="px-4" />
                    <div v-else class="reports-responsive-container responsive-table-container w-full min-w-0">
                        <div class="responsive-table-desktop w-full min-w-0">
                            <el-table class="app-table" :data="[
                                { key: 'income', label: 'Total income', ...report.period_comparison.income },
                                { key: 'expenses', label: 'Total expenses', ...report.period_comparison.expenses },
                                { key: 'net_cash_flow', label: 'Net cash flow', ...report.period_comparison.net_cash_flow },
                            ]" stripe>
                                <el-table-column prop="label" label="Measure" min-width="130" />
                                <el-table-column label="Current period" min-width="150" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.current) }}</template>
                                </el-table-column>
                                <el-table-column label="Previous period" min-width="150" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.previous) }}</template>
                                </el-table-column>
                                <el-table-column label="Change" min-width="190" align="right">
                                    <template #default="{ row }">
                                        <span :class="comparisonTextClass(row.key, row.direction)">
                                            {{ formatCurrency(row.difference) }}
                                        </span>
                                        <el-tag class="ml-2" size="small" :type="comparisonTagType(row.key, row.direction)">
                                            {{ comparisonLabel(row.direction) }}
                                            <template v-if="row.key !== 'net_cash_flow'">
                                                · {{ formatPercentageChange(row.percentage_change) }}
                                            </template>
                                        </el-tag>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <div class="responsive-table-mobile mt-3 space-y-3 px-4">
                            <div v-for="metric in [
                                { key: 'income', label: 'Total income', ...report.period_comparison.income },
                                { key: 'expenses', label: 'Total expenses', ...report.period_comparison.expenses },
                                { key: 'net_cash_flow', label: 'Net cash flow', ...report.period_comparison.net_cash_flow },
                            ]" :key="metric.key"
                                class="min-w-0 rounded-xl border border-[var(--el-border-color)] p-3">
                                <h4 class="text-sm font-semibold">{{ metric.label }}</h4>
                                <div class="report-detail-grid mt-3 gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Current period</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(metric.current) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Previous period</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(metric.previous) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Difference</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]"
                                            :class="comparisonTextClass(metric.key, metric.direction)">
                                            {{ formatCurrency(metric.difference) }}
                                        </p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Change</p>
                                        <el-tag class="mt-1 max-w-full !h-auto whitespace-normal break-words" size="small"
                                            :type="comparisonTagType(metric.key, metric.direction)">
                                            {{ comparisonLabel(metric.direction) }}
                                            <template v-if="metric.key !== 'net_cash_flow'">
                                                · {{ formatPercentageChange(metric.percentage_change) }}
                                            </template>
                                        </el-tag>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card :body_x_padding="false">
                <template #header>
                    Monthly cash flow
                </template>
                <template #addons>
                    <span v-if="report.period" class="min-w-0 break-words text-xs text-[var(--el-text-color-secondary)]">
                        {{ formatDate(report.period.start_date) }} – {{ formatDate(report.period.end_date) }}
                    </span>
                </template>
                <template #body>
                    <el-skeleton v-if="loading" :rows="4" animated class="px-4" />
                    <div v-else class="reports-responsive-container responsive-table-container w-full min-w-0">
                        <div class="responsive-table-desktop w-full min-w-0">
                            <el-table class="app-table" :data="report.monthly" stripe>
                                <el-table-column prop="month" label="Month" min-width="110" />
                                <el-table-column label="Income" min-width="140" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.income) }}</template>
                                </el-table-column>
                                <el-table-column label="Expenses" min-width="140" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.expenses) }}</template>
                                </el-table-column>
                                <el-table-column label="Net cash flow" min-width="150" align="right">
                                    <template #default="{ row }">
                                        <span :class="row.net >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                            {{ formatCurrency(row.net) }}
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Savings rate" min-width="120" align="right">
                                    <template #default="{ row }">{{ formatSavingsRate(row.savings_rate) }}</template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <div class="responsive-table-mobile mt-3 space-y-3 px-4">
                            <div v-for="month in report.monthly" :key="month.month"
                                class="min-w-0 rounded-xl border border-[var(--el-border-color)] p-3">
                                <h4 class="text-sm font-semibold">{{ month.month }}</h4>
                                <div class="report-detail-grid mt-3 gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Income</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(month.income) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Expenses</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(month.expenses) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Net cash flow</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]"
                                            :class="month.net >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                            {{ formatCurrency(month.net) }}
                                        </p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Savings rate</p>
                                        <p class="mt-1 text-sm font-medium">{{ formatSavingsRate(month.savings_rate) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card v-if="transactionType !== 'income'" :body_x_padding="false">
                <template #header>Budget performance</template>
                <template #addons>
                    <span v-if="selectedCategoryId !== 'all' && selectedCategoryId !== null"
                        class="text-xs text-[var(--el-text-color-secondary)]">
                        Budgets are overall and not category-specific.
                    </span>
                </template>
                <template #body>
                    <el-skeleton v-if="loading" :rows="3" animated class="px-4" />
                    <el-empty v-else-if="report.budget_performance.length === 0"
                        description="No monthly budgets were set during this period." class="px-4" />
                    <div v-else class="reports-responsive-container responsive-table-container w-full min-w-0">
                        <div class="responsive-table-desktop w-full min-w-0">
                            <el-table class="app-table" :data="report.budget_performance" stripe>
                                <el-table-column label="Month" min-width="110">
                                    <template #default="{ row }">{{ formatBudgetPeriod(row.period) }}</template>
                                </el-table-column>
                                <el-table-column label="Budget limit" min-width="140" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.budget_limit) }}</template>
                                </el-table-column>
                                <el-table-column label="Actual spending" min-width="150" align="right">
                                    <template #default="{ row }">{{ formatCurrency(row.actual_spending) }}</template>
                                </el-table-column>
                                <el-table-column label="Remaining" min-width="140" align="right">
                                    <template #default="{ row }">
                                        <span :class="row.remaining_amount >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                            {{ formatCurrency(row.remaining_amount) }}
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Status" min-width="130" align="center">
                                    <template #default="{ row }">
                                        <el-tag :type="row.status === 'exceeded' ? 'danger' : 'success'">
                                            {{ row.status === 'exceeded' ? 'Exceeded' : 'Within budget' }}
                                        </el-tag>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <div class="responsive-table-mobile mt-3 space-y-3 px-4">
                            <div v-for="budget in report.budget_performance" :key="budget.period"
                                class="min-w-0 rounded-xl border border-[var(--el-border-color)] p-3">
                                <div class="flex min-w-0 flex-wrap items-center justify-between gap-2">
                                    <h4 class="min-w-0 text-sm font-semibold">{{ formatBudgetPeriod(budget.period) }}</h4>
                                    <el-tag class="max-w-full" :type="budget.status === 'exceeded' ? 'danger' : 'success'">
                                        {{ budget.status === 'exceeded' ? 'Exceeded' : 'Within budget' }}
                                    </el-tag>
                                </div>
                                <div class="report-detail-grid mt-3 gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Budget limit</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(budget.budget_limit) }}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Actual spending</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]">{{ formatCurrency(budget.actual_spending) }}</p>
                                    </div>
                                    <div class="report-grid-wide min-w-0">
                                        <p class="text-[11px] text-[var(--el-text-color-secondary)]">Remaining</p>
                                        <p class="mt-1 break-words text-sm font-medium [overflow-wrap:anywhere]"
                                            :class="budget.remaining_amount >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                            {{ formatCurrency(budget.remaining_amount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </Card>

            <Card v-if="transactionType !== 'income'" :body_x_padding="false">
                <template #header>Largest expenses</template>
                <template #body>
                    <el-skeleton v-if="loading" :rows="3" animated class="px-4" />
                    <el-empty v-else-if="report.largest_expenses.length === 0" description="No expenses in this period." class="px-4" />
                    <div v-else class="divide-y divide-[var(--el-border-color)]">
                        <div v-for="expense in report.largest_expenses" :key="expense.id"
                            class="flex flex-col gap-1 px-4 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="break-words text-sm font-semibold">{{ expense.category ?? 'Uncategorized' }}</p>
                                <p class="break-words text-xs text-[var(--el-text-color-secondary)]">
                                    {{ expense.note || 'No note' }} · {{ formatDate(expense.transaction_date) }}
                                </p>
                            </div>
                            <p class="max-w-full break-words text-sm font-semibold sm:shrink-0">{{ formatCurrency(expense.amount) }}</p>
                        </div>
                    </div>
                </template>

            </Card>
        </template>
    </div>
</template>

<style scoped>
.report-detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
}

.report-grid-wide {
    grid-column: span 1;
}

@container (min-width: 420px) {
    .report-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-grid-wide {
        grid-column: span 2;
    }
}

@container (max-width: 719px) {
    .reports-responsive-container .responsive-table-desktop {
        display: none;
    }

    .reports-responsive-container .responsive-table-mobile {
        display: block;
    }
}

@container (min-width: 720px) {
    .reports-responsive-container .responsive-table-desktop {
        display: block;
    }

    .reports-responsive-container .responsive-table-mobile {
        display: none;
    }
}
</style>