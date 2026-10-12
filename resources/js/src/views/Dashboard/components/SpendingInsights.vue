<script setup>
import { computed, ref, onMounted, watch } from "vue";
import Card from "../../../components/ui/Card.vue";
import useAuthStore from "../../../stores/auth";
import { formatAmount, formatDate } from "../../../utils/helpers";
import {
    ExclamationTriangleIcon,
    InformationCircleIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';
import handleError from "../../../utils/handleError.js";
import { getSpendingInsights } from "../../../services/dashboard.service.js";

const props = defineProps({
    update: {
        type: Number,
    }
});

const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency ?? 'PKR';
const userCurrencyIso = authStore.user?.preferences?.currency_iso ?? 'PK';
const insights = ref([]);
const loading = ref(true);

const computedInsights = computed(() => insights.value.map((insight) => {
    switch (insight.type) {
        case 'spending_spike':
            return {
                ...insight,
                title: 'Spending Spike',
                message: `${insight.name} spending increased by ${Number(insight.increase_percentage).toFixed(2)}% this month compared with its 3-month average.`,
            };
        case 'highest_spending_day': {
            const amount = `${userCurrency} ${formatAmount(Number(insight.amount), userCurrencyIso)}`;

            return {
                ...insight,
                title: 'Highest Spending Day',
                message: insight.is_shared || !insight.date
                    ? `Your highest spending was ${amount} in a single day.`
                    : `Your highest spending day was ${formatDate(insight.date)}, when you spent ${amount}.`,
            };
        }
        case 'most_frequent_category': {
            const categories = Array.isArray(insight.categories)
                ? insight.categories.filter((category) => typeof category === 'string' && category.length > 0)
                : [];
            const transactionCount = Number(insight.transaction_count) || 0;
            const categoryNames = categories.length > 1
                ? `${categories.slice(0, -1).join(', ')} and ${categories[categories.length - 1]}`
                : categories[0] ?? 'No category';

            return {
                ...insight,
                title: 'Most Spent Category',
                message: categories.length > 1
                    ? `${categoryNames} were your most frequent spending categories, with ${transactionCount} transactions each this month.`
                    : `${categoryNames} is your most frequent spending category, with ${transactionCount} transactions this month.`,
            };
        }
        case 'average_transaction_size': {
            const currentAverage = `${userCurrency} ${formatAmount(Number(insight.current_month_average), userCurrencyIso)}`;
            let message = `Your average expense is ${currentAverage} per transaction.`;

            if (insight.comparison_direction === 'increased' || insight.comparison_direction === 'decreased') {
                const previousAverage = `${userCurrency} ${formatAmount(Number(insight.previous_month_average), userCurrencyIso)}`;
                message = `Your average expense ${insight.comparison_direction} from ${previousAverage} to ${currentAverage} per transaction.`;
            } else if (insight.comparison_direction === 'unchanged') {
                message = `Your average expense stayed the same at ${currentAverage} per transaction.`;
            }

            return {
                ...insight,
                title: 'Average Transaction Size',
                message,
            };
        }
        case 'small_purchases_impact': {
            const amount = new Intl.NumberFormat(`en-${userCurrencyIso}`, {
                maximumFractionDigits: 2,
                minimumFractionDigits: 2,
            }).format(Number(insight.small_purchases_total));

            return {
                ...insight,
                title: 'Small Purchases Impact',
                message: `Small purchases made up ${Number(insight.spending_percentage).toFixed(0)}% of your spending this month, totaling ${userCurrency} ${amount} across ${Number(insight.transaction_count)} transactions.`,
            };
        }
        default:
            return {
                ...insight,
                title: 'Spending Insight',
                message: insight.name,
            };
    }
}));

const severityMap = {
    warning: {
        wrap: 'border-amber-100 bg-amber-50/60 dark:border-amber-500/20 dark:bg-amber-500/10',
        iconBox: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
        icon: ExclamationTriangleIcon,
        tagType: 'warning',
        label: 'Warning',
    },
    neutral: {
        wrap: 'border-gray-200 bg-gray-50 dark:border-[var(--el-border-color)] dark:bg-[var(--app-muted)]',
        iconBox: 'bg-gray-200 text-gray-600 dark:bg-[var(--el-fill-color-dark)] dark:text-[var(--el-text-color-regular)]',
        icon: InformationCircleIcon,
        tagType: 'info',
        label: 'Neutral',
    },
    positive: {
        wrap: 'border-emerald-100 bg-emerald-50/60 dark:border-emerald-500/20 dark:bg-emerald-500/10',
        iconBox: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400',
        icon: CheckCircleIcon,
        tagType: 'success',
        label: 'Positive',
    },
};


const getSeverity = (severity) => severityMap[severity] ?? severityMap.neutral;

const fetchSpendingInsights = async () => {
    loading.value = true;

    try {
        const response = await getSpendingInsights();
        if(response.data?.success){
            insights.value = response.data?.data ?? [];
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
}


watch(() => props.update, () => {
    fetchSpendingInsights();
});

onMounted(() => {
    fetchSpendingInsights();
});

</script>

<template>
    <Card :full_height="true">
        <template #header>
            <span>
                Spending Insights
            </span>
        </template>
        <template #body>
            <el-skeleton v-if="loading" :rows="3" animated />
            <template v-else>
                <div v-for="insight in computedInsights" :key="insight.category_id != null ? `${insight.type}-${insight.category_id}` : insight.type" class="flex items-start gap-3 rounded-xl border p-2 mb-2"
                    :class="getSeverity(insight.severity).wrap">
                    <!-- Icon -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                        :class="getSeverity(insight.severity).iconBox">
                        <component :is="getSeverity(insight.severity).icon" class="h-5 w-5" />
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="truncate text-xs font-semibold text-gray-900 dark:text-[var(--text-charcoal)]">{{ insight.title }}</h4>
                        </div>
                        <p class="mt-1 text-[10px] leading-relaxed text-gray-600 dark:text-[var(--el-text-color-secondary)]">{{ insight.message }}</p>
                    </div>
                </div>
                <div v-if="computedInsights.length === 0"
                    class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 dark:border-[var(--el-border-color)] bg-gray-50/70 dark:bg-[var(--app-muted)] px-4 py-6 text-center">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-600 dark:bg-[var(--el-fill-color-dark)] dark:text-[var(--el-text-color-regular)]">
                        <InformationCircleIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-[var(--text-charcoal)]">No insights to show</h4>
                    <p class="mt-1 max-w-xs text-xs leading-relaxed text-gray-500 dark:text-[var(--el-text-color-secondary)]">
                        Helpful spending insights will appear here when available.
                    </p>
                </div>
            </template>
        </template>
    </Card>
</template>