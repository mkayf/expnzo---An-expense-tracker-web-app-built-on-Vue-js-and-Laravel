<script setup>
import { computed, onMounted, ref } from "vue";
import useAuthStore from "../../../stores/auth";
import { formatAmount } from "../../../utils/helpers";
import VueApexCharts from "vue3-apexcharts";
import { getCurrentPeriod } from "../../../utils/helpers";

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    data: {
        type: Object,
        required: true
    },
    chart: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['open-budget-form']);

const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency;
const userCurrencyIso = authStore.user?.preferences?.currency_iso;

const trendTextColor = ref(null);
const showBudgetBtn = ref(false);

const showMonth = computed(() => {
    if (props.type === 'income' || props.type === 'expense' || (props.type === 'budget' && props.data.amount !== 0)) {
        return ' • ' + getCurrentPeriod();
    }
})

const showTrendText = computed(() => {
    if (props.type === 'balance' || props.type == 'income') {
        if (props.data?.current_month_transactions == 0) {
            return 'No transactions this month';
        }
        else if (props.data?.trend?.direction === 'neutral') {
            return '↔ No change from last month';
        }
        else if (props.data?.trend?.direction === 'up') {
            trendTextColor.value = 'green';
            return `↑ ${props.data?.trend?.percentage}% vs last month`;
        }
        else if (props.data?.trend?.direction === 'down') {
            trendTextColor.value = 'red';
            return `↓ ${props.data?.trend?.percentage}% vs last month`;
        }
    }
    else if (props.type === 'expense') {
        if (props.data?.trend?.direction === 'neutral') {
            return '↔ No change from last month';
        }
        else if (props.data?.trend?.direction === 'up') {
            trendTextColor.value = 'red';
            return `↑ ${props.data?.trend?.percentage}% vs last month`;
        }
        else if (props.data?.trend?.direction === 'down') {
            trendTextColor.value = 'green';
            return `↓ ${props.data?.trend?.percentage}% vs last month`;
        }
    }
    else if (props.type === 'budget') {
        showBudgetBtn.value = true;
        if (props.data?.trend?.percentage == 0 && props.data?.trend?.direction == null) {
            return 'No budget set for this month';
        }
        else if (props.data?.trend?.direction === 'over') {
            trendTextColor.value = 'red';
            return `${props.data?.trend?.over_budget_percentage}% over budget`;
        }
        else if (props.data?.trend?.direction === 'up') {
            trendTextColor.value = 'yellow';
            return `${props.data?.trend?.percentage}% is used`;
        }
        else if (props.data?.trend?.direction === 'down') {
            trendTextColor.value = 'gray';
            return `${props.data?.trend?.percentage}% is used`;
        }

    }


    return null;
})

const trendTextClass = computed(() => {
    switch (trendTextColor.value) {
        case 'green':
            return 'text-green-700'
        case 'red':
            return 'text-red-700'
        case 'yellow':
            return 'text-yellow-700'
        default:
            return 'text-gray-700'
    }
})

const chartConfig = computed(() => {
    if (props.type === 'balance' || props.type === 'budget') {
        return {
            options: {
                chart: {
                    type: "donut",
                },
                legend: {
                    show: false,
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    width: 0
                },
                colors: props.type === 'balance' ? ['var(--el-color-primary)', '#F59E0B'] : ['var(--el-color-primary)', '#F59E0B'],
                labels: props.type === 'balance' ? ['Total Income', 'Total Expense'] : ['Remaining Budget', 'Used Budget'],
                tooltip: {
                    fixed: {
                        enabled: true,
                        position: 'topRight',
                        offsetX: 0,
                        offsetY: 0,
                    }
                }
            },
            series: props.type === 'balance' ? [props.data?.chart_data?.total_incomes, props.data?.chart_data?.total_expense] : [props.data?.chart_data?.remaining_budget, props.data?.chart_data?.used_budget]
        }
    }

    return {
        options: {
            xaxis: {
                categories: props.data?.chart_data?.dates,
            },
            zoom: {
                enabled: false,
            },
            chart: {
                type: 'area',
                sparkline: {
                    enabled: true
                }
            },
            colors: ['var(--el-color-primary)'],
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            fill: {
                type: 'gradient'
            },
            tooltip: {
            fixed: {
                enabled: true,
                position: 'topRight',
                offsetX: 0,
                offsetY: 0,
            }
        }
        },
        series: [
            {
                name: props.type === "income" ? 'Daily Income' : 'Daily Expense',
                data: props.data?.chart_data?.amounts,
            },
        ]
    }
})


</script>
<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-white p-3">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="p-2 bg-[var(--el-color-primary-dark-2)] text-white rounded-2xl"
                    style="box-shadow: rgba(100, 100, 111, 0.5) 0px 4px 8px 0px;">
                    <slot name="icon"></slot>
                </span>
                <span class="font-semibold text-sm text-slate-700">
                    <slot name="label"></slot> {{ showMonth }}
                </span>
            </div>
            <!-- <div v-if="showBudgetBtn">
                <el-button size="small" @click="emit('open-budget-form')">Set budget</el-button>
            </div> -->
        </div>
        <div class="grid grid-cols-4">
            <div class="col-span-3">
                <div class="mt-3 flex items-center justify-between">
                    <div>
                        <span class="text-md font-medium text-slate-700">{{
                            userCurrency ?? ""
                        }}</span>
                        <span class="ml-1 text-2xl font-semibold">
                            {{ formatAmount(props.data?.amount, userCurrencyIso) }}</span>
                    </div>

                </div>
                <div class="mt-1 w-full">
                    <span v-if="showTrendText" style="font-size: 0.7rem;" class="w-full" :class="trendTextClass">{{
                        showTrendText }}</span>
                </div>
            </div>
            <div class="flex flex-col items-end justify-end h-[70px]">
                <VueApexCharts width="50" height="70" :options="chartConfig.options" :series="chartConfig.series">
                </VueApexCharts>
            </div>
        </div>
    </div>
</template>