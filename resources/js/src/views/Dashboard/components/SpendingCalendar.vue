<script setup>
import { computed, ref, watch } from 'vue'
import Card from '../../../components/ui/Card.vue';
import handleError from '../../../utils/handleError.js';
import { getSpendingCalendar, getSpendingCalendarDayTransactions } from '../../../services/dashboard.service.js';
import { formatAmount } from '../../../utils/helpers.js';
import useAuthStore from '../../../stores/auth.js';

const props = defineProps({
    update: {
        type: Number,
    },
});

const date = ref(new Date());
const calendarData = ref([]);
const calendarLoading = ref(true);
const dayDialogVisible = ref(false);
const selectedDay = ref('');
const dayTransactions = ref([]);
const dayTransactionsLoading = ref(false);
const authStore = useAuthStore();
const userCurrencyIso = authStore.user?.preferences?.currency_iso;
const userCurrency = authStore.user?.preferences?.currency;

const MAX_METER_AMOUNT = 5000;

// Element Plus overrides via Tailwind arbitrary variants (no <style> needed)
const calendarClass = [
    'w-full min-w-0 [&_.el-calendar-day]:!h-[100px] [&_.el-calendar-day]:!p-1',
    'max-sm:[&_.el-calendar-day]:!h-[68px] max-sm:[&_.el-calendar-day]:!p-0.5',
    '[&_td.is-selected]:!bg-transparent',
    '[&_.el-calendar-day:hover]:!bg-transparent',
].join(' ');

// Full class names must stay static so Tailwind can detect them
const levelStyles = {
    low: {
        tile: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-200',
        track: 'bg-amber-200/70 dark:bg-amber-400/20',
        bar: 'bg-amber-400',
    },
    medium: {
        tile: 'border-amber-300 bg-amber-100 text-amber-900 dark:border-amber-400/40 dark:bg-amber-400/20 dark:text-amber-100',
        track: 'bg-amber-300/60 dark:bg-amber-400/25',
        bar: 'bg-amber-500',
    },
    high: {
        tile: 'border-amber-400 bg-amber-200 text-amber-950 dark:border-amber-500/55 dark:bg-amber-500/30 dark:text-amber-50',
        track: 'bg-amber-400/50 dark:bg-amber-400/25',
        bar: 'bg-amber-700 dark:bg-amber-300',
    },
    'very-high': {
        tile: 'border-amber-700 bg-[linear-gradient(135deg,#d97706,#b45309)] text-white dark:border-amber-500/60',
        track: 'bg-white/25',
        bar: 'bg-white',
    },
};

const intensityLevels = [
    { key: 'low', label: 'Low', range: 'Up to 999' },
    { key: 'medium', label: 'Medium', range: '1,000–2,499' },
    { key: 'high', label: 'High', range: '2,500–4,999' },
    { key: 'very-high', label: 'Very high', range: '5,000+' },
];

const calendarMonth = computed(() => {
    const year = date.value.getFullYear();
    const month = String(date.value.getMonth() + 1).padStart(2, '0');
    return `${year}-${month}`;
});

const getLevel = (amount) => {
    if (amount < 1000) return 'low';
    if (amount < 2500) return 'medium';
    if (amount < 5000) return 'high';
    return 'very-high';
};

const dailySpending = computed(() => new Map(
    calendarData.value.map((item) => {
        const amount = Number(item.amount);
        return [item.transaction_date, { amount, level: getLevel(amount) }];
    })
));

const spendingOf = (day) => dailySpending.value.get(day);

const monthStats = computed(() => {
    const prefix = calendarMonth.value;
    const amounts = calendarData.value
        .filter((item) => item.transaction_date?.startsWith(prefix))
        .map((item) => Number(item.amount));

    return {
        total: amounts.reduce((sum, value) => sum + value, 0),
        peak: amounts.length ? Math.max(...amounts) : 0,
    };
});

const todayKey = computed(() => {
    const now = new Date();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    return `${now.getFullYear()}-${m}-${d}`;
});

const levelLabel = (level) =>
    intensityLevels.find((item) => item.key === level)?.label ?? 'Low';

const meterWidth = (amount) =>
    `${Math.max(8, Math.min(100, (amount / MAX_METER_AMOUNT) * 100))}%`;

const formatCompact = (amount) =>
    new Intl.NumberFormat('en-US', {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(Number(amount)).replace(/[KMB]$/, (suffix) => suffix.toLowerCase());

const selectedDayLabel = computed(() => {
    if (!selectedDay.value) return '';
    const [year, month, day] = selectedDay.value.split('-').map(Number);
    return new Date(year, month - 1, day).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
});

const selectedDayTotal = computed(() =>
    dayTransactions.value.reduce((total, transaction) => total + Number(transaction.amount), 0)
);

const fetchDayTransactions = async () => {
    const requestedDay = selectedDay.value;
    if (!requestedDay) return;

    dayTransactions.value = [];
    dayTransactionsLoading.value = true;
    try {
        const response = await getSpendingCalendarDayTransactions(requestedDay);
        if (selectedDay.value === requestedDay && response.data?.success) {
            dayTransactions.value = response.data?.data ?? [];
        }
    } catch (e) {
        handleError(e);
    } finally {
        if (selectedDay.value === requestedDay) {
            dayTransactionsLoading.value = false;
        }
    }
};

const openDayTransactions = (day) => {
    selectedDay.value = day;
    dayDialogVisible.value = true;
    fetchDayTransactions();
};

const fetchSpendingCalendar = async () => {
    calendarLoading.value = true;
    try {
        const response = await getSpendingCalendar(calendarMonth.value);
        if (response.data?.success) {
            calendarData.value = response.data?.data ?? [];
        }
    } catch (e) {
        handleError(e);
    } finally {
        calendarLoading.value = false;
    }
};

watch(calendarMonth, (val) => {
    if (val) {
        fetchSpendingCalendar();
    }
}, { immediate: true });

watch(() => props.update, (val) => {
    if (val) {
        fetchSpendingCalendar();
        if (dayDialogVisible.value) {
            fetchDayTransactions();
        }
    }
});
</script>

<template>
    <Card class="w-full min-w-0 overflow-hidden">
        <template #header>
            <div class="flex w-full flex-wrap items-center justify-between gap-2">
                <span>Spending Calendar</span>

                <div class="flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[var(--el-border-color-lighter)] bg-[var(--el-bg-color)] px-2.5 py-0.5 text-xs font-medium text-[var(--el-text-color-secondary)] max-sm:px-2 max-sm:text-[0.65rem]">
                        <i class="size-[7px] rounded-full bg-[var(--primary-color)]"></i>
                        Total
                        <strong class="font-bold text-[var(--el-text-color-primary)]">
                            {{ userCurrency }} {{ formatCompact(monthStats.total) }}
                        </strong>
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-[var(--el-border-color-lighter)] bg-[var(--el-bg-color)] px-2.5 py-0.5 text-xs font-medium text-[var(--el-text-color-secondary)] max-sm:px-2 max-sm:text-[0.65rem]">
                        <i class="size-[7px] rounded-full bg-[var(--yellow-color)]"></i>
                        Peak day
                        <strong class="font-bold text-[var(--el-text-color-primary)]">
                            {{ userCurrency }} {{ formatCompact(monthStats.peak) }}
                        </strong>
                    </span>
                </div>
            </div>
        </template>

        <template #body>
            <el-calendar v-model="date" :class="calendarClass">
                <template #date-cell="{ data }">
                    <div v-if="calendarLoading"
                        class="flex h-full flex-col justify-between rounded-[10px] border border-[var(--el-border-color-lighter)] bg-[var(--el-bg-color)] px-2 py-1.5 max-sm:rounded-md max-sm:px-0.5 max-sm:py-0.5"
                        :class="data.type !== 'current-month' && 'opacity-40'">
                        <span
                            class="inline-flex size-[22px] items-center justify-center self-start rounded-full text-[0.8rem] font-semibold leading-none text-[var(--el-text-color-secondary)] max-sm:size-4 max-sm:text-[0.65rem]">
                            {{ data.date.getDate() }}
                        </span>
                        <div class="flex flex-col gap-1.5">
                            <el-skeleton-item variant="text" class="!h-4 !w-2/3" />
                            <el-skeleton-item variant="text" class="!h-1 !w-full !rounded-full max-sm:hidden" />
                        </div>
                    </div>

                    <el-tooltip v-else :disabled="dayDialogVisible || !spendingOf(data.day)" placement="top"
                        :show-after="150">
                        <template #content>
                            <template v-if="spendingOf(data.day)">
                                <div class="mb-0.5 text-[0.7rem] opacity-75">
                                    {{ levelLabel(spendingOf(data.day).level) }} spending
                                </div>
                                <div>
                                    {{ userCurrency }}
                                    {{ formatAmount(spendingOf(data.day).amount, userCurrencyIso) }}
                                </div>
                            </template>
                        </template>

                        <div class="flex h-full cursor-default flex-col justify-between rounded-[10px] border px-2 py-1.5 transition duration-150 max-sm:rounded-md max-sm:px-0.5 max-sm:py-0.5"
                            :class="[
                                spendingOf(data.day)
                                    ? [
                                        levelStyles[spendingOf(data.day).level].tile,
                                        'cursor-pointer hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-500/30',
                                    ]
                                    : 'cursor-pointer border-transparent text-[var(--el-text-color-regular)] hover:bg-black/5 dark:hover:bg-white/5',
                                data.type !== 'current-month' && 'opacity-40',
                            ]" role="button" tabindex="0" :aria-label="`View expense transactions for ${data.day}`"
                            @click.stop="openDayTransactions(data.day)"
                            @keydown.enter.stop.prevent="openDayTransactions(data.day)"
                            @keydown.space.stop.prevent="openDayTransactions(data.day)">
                            <span
                                class="inline-flex size-[22px] items-center justify-center self-start rounded-full text-[0.8rem] font-semibold leading-none max-sm:size-4 max-sm:text-[0.65rem]"
                                :class="data.day === todayKey
                                    ? 'bg-[var(--primary-color)] text-white'
                                    : spendingOf(data.day) ? 'opacity-85' : ''">
                                {{ data.date.getDate() }}
                            </span>

                            <div v-if="spendingOf(data.day)" class="flex flex-col gap-1.5">
                                <div class="flex min-w-0 items-baseline gap-1 max-sm:gap-0">
                                    <span class="text-[0.62rem] font-semibold tracking-wide opacity-75 max-sm:hidden">
                                        {{ userCurrency }}
                                    </span>
                                    <span
                                        class="truncate text-[clamp(0.7rem,1.1vw,1rem)] font-bold tracking-tight tabular-nums max-sm:text-[0.55rem] max-sm:leading-tight">
                                        {{ formatCompact(spendingOf(data.day).amount) }}
                                    </span>
                                </div>

                                <span class="block h-1 overflow-hidden rounded-full max-sm:hidden"
                                    :class="levelStyles[spendingOf(data.day).level].track">
                                    <span class="block h-full rounded-full transition-[width] duration-300"
                                        :class="levelStyles[spendingOf(data.day).level].bar"
                                        :style="{ width: meterWidth(spendingOf(data.day).amount) }"></span>
                                </span>
                            </div>
                        </div>
                    </el-tooltip>
                </template>
            </el-calendar>

            <el-dialog v-model="dayDialogVisible" width="min(560px, calc(100vw - 32px))" align-center
                class="spending-day-dialog">
                <template #header>
                    <div class="pr-5">
                        <p class="text-xs font-semibold text-[var(--primary-color)] max-sm:text-[0.65rem]">
                            Daily expense breakdown
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-[var(--el-text-color-primary)] max-sm:text-base">
                            {{ selectedDayLabel }}
                        </h2>
                    </div>
                </template>

                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between gap-4 rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-4 dark:border-amber-400/20 dark:from-amber-400/10 dark:via-[var(--app-surface)] dark:to-orange-400/10 max-sm:gap-2 max-sm:p-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-[var(--el-text-color-secondary)] max-sm:text-[0.65rem]">Total spent</p>
                            <p class="mt-1 text-2xl font-bold tracking-tight text-[var(--el-text-color-primary)] max-sm:text-xl">
                                {{ userCurrency }}
                                {{ formatAmount(selectedDayTotal, userCurrencyIso) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 whitespace-nowrap rounded-full border border-amber-200 bg-white/80 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-200 max-sm:px-1.5 max-sm:py-1 max-sm:text-[0.55rem] max-sm:leading-none">
                            {{ dayTransactions.length }}
                            {{ dayTransactions.length === 1 ? 'transaction' : 'transactions' }}
                        </span>
                    </div>

                    <div class="min-h-[120px] overflow-hidden" style="height: min(340px, calc(100dvh - 300px));">
                        <Transition name="day-content">
                            <el-skeleton v-if="dayTransactionsLoading" key="loading" animated class="h-full">
                                <template #template>
                                    <div class="flex h-full flex-col justify-center space-y-2">
                                        <div v-for="row in 3" :key="row"
                                            class="flex min-h-[66px] items-center gap-3 rounded-xl border border-[var(--el-border-color-lighter)] p-3">
                                            <el-skeleton-item variant="circle" class="!size-10 !shrink-0" />
                                            <div class="flex h-10 min-w-0 flex-1 flex-col justify-center gap-2">
                                                <el-skeleton-item variant="text" class="!m-0 !block !h-3 !w-1/3" />
                                                <el-skeleton-item variant="text" class="!m-0 !block !h-3 !w-2/3" />
                                            </div>
                                            <el-skeleton-item variant="text" class="!m-0 !block !h-4 !w-20 !shrink-0" />
                                        </div>
                                    </div>
                                </template>
                            </el-skeleton>

                            <el-scrollbar v-else-if="dayTransactions.length" key="transactions" class="h-full">
                                <div class="space-y-2">
                                    <div v-for="transaction in dayTransactions" :key="transaction.id"
                                        class="flex items-center gap-3 rounded-xl border border-[var(--el-border-color-lighter)] bg-[var(--el-bg-color)] p-3 transition-colors hover:border-amber-300/80 dark:hover:border-amber-400/30">
                                        <span
                                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[var(--primary-color)]/10 text-sm font-bold text-[var(--primary-color)]">
                                            {{ transaction.category?.name?.charAt(0)?.toUpperCase() ?? 'E' }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-semibold text-[var(--el-text-color-primary)] max-sm:text-xs">
                                                {{ transaction.category?.name || 'Expense' }}
                                            </p>
                                            <p v-if="transaction.note"
                                                class="mt-0.5 truncate text-xs text-[var(--el-text-color-secondary)] max-sm:text-[0.65rem]">
                                                {{ transaction.note }}
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 text-right text-sm font-bold tabular-nums text-[var(--el-text-color-primary)] max-sm:text-xs">
                                            {{ userCurrency }}
                                            {{ formatAmount(transaction.amount, userCurrencyIso) }}
                                        </span>
                                    </div>
                                </div>
                            </el-scrollbar>
                            <div v-else key="empty" class="flex h-full items-center justify-center">
                                <el-empty description="No expense transactions for this day" :image-size="88" />
                            </div>
                        </Transition>
                    </div>
                </div>
            </el-dialog>

            <div class="flex flex-wrap items-center justify-start gap-x-2.5 gap-y-2 border-t border-[var(--el-border-color-lighter)] px-2 pb-1 pt-3 sm:justify-end sm:gap-x-3.5 sm:px-4"
                aria-label="Daily spending intensity">
                <span class="text-xs font-semibold text-[var(--el-text-color-secondary)]">Less</span>

                <div v-for="level in intensityLevels" :key="level.key" class="inline-flex items-center gap-1.5"
                    :title="`${level.label}: ${level.range} ${userCurrency}`">
                    <span class="size-3.5 rounded-[5px] border" :class="levelStyles[level.key].tile"></span>
                    <span class="text-xs text-[var(--el-text-color-secondary)]">{{ level.label }}</span>
                </div>

                <span class="text-xs font-semibold text-[var(--el-text-color-secondary)]">More</span>
                <span class="sr-only">
                    Low up to 999, medium 1,000 to 2,499, high 2,500 to 4,999, and very high 5,000 or more
                    {{ userCurrency }}.
                </span>
            </div>
        </template>
    </Card>
</template>

<style scoped>
.day-content-enter-active,
.day-content-leave-active {
    transition: opacity 160ms ease, transform 160ms ease;
}

.day-content-enter-from,
.day-content-leave-to {
    opacity: 0;
    transform: translateY(4px);
}

@media (max-width: 640px) {
    :deep(.el-calendar) {
        width: 100%;
        min-width: 0;
    }

    :deep(.el-calendar__header) {
        align-items: stretch;
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    :deep(.el-calendar__title) {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    :deep(.el-calendar__button-group) {
        display: flex;
        width: 100%;
    }

    :deep(.el-calendar__button-group .el-button) {
        flex: 1;
        min-width: 0;
        padding: 0.35rem 0.25rem;
        font-size: 0.65rem;
    }

    :deep(.el-calendar-table) {
        table-layout: fixed;
        width: 100%;
    }

    :deep(.el-calendar-table th) {
        padding: 0.25rem 0;
        font-size: 0.625rem;
        font-weight: 600;
    }

    :deep(.el-calendar-table td) {
        min-width: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .day-content-enter-active,
    .day-content-leave-active {
        transition: none;
    }
}
</style>