<script setup>
import { computed, onMounted, ref } from 'vue';
import Card from '../../../components/ui/Card.vue';
import VueApexCharts from "vue3-apexcharts";
import useAuthStore from '../../../stores/auth.js';
import { formatAmount } from '../../../utils/helpers.js';
import { getIncomeExpense } from '../../../services/dashboard.service.js';
import handleError from '../../../utils/handleError.js';
import StatShimmerCard from '../../../components/ui/StatShimmerCard.vue';

const selectedLastMonths = ref(6);

const authStore = useAuthStore();
const userCurrency = authStore.user.preferences.currency;
const incomeExpenseData = ref([]);
const loading = ref(false);

const lastMonthOptions = [
    {
        value: 3,
        label: 'Last 3 Months',
    },
    {
        value: 6,
        label: 'Last 6 Months',
    },
    {
        value: 12,
        label: 'Last 12 Months',
    },
];

const chartConfig = computed(() => {
    return {
        options: {
            chart: {
                type: 'bar',
                height: 250,
                toolbar: {
                    tools: {
                        download: false
                    }
                }
            },
            noData: {
                text: 'Loading...',
                align: 'center',
                verticalAlign: 'middle',
                style: {
                    color: '#999',
                    fontSize: '14px'
                }
            },
            grid: {
                show: false
            },
            menu: {
                enabled: false
            },
            zoom: {
                enabled: false,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '70%',
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            colors: [
                'var(--el-color-primary)', 'var(--yellow-color)'
            ],
            xaxis: {
                categories: incomeExpenseChartData.value.months,
            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return `${userCurrency} ` + formatAmount(val)
                    },
                },
            },

        },
        series: [
            {
                name: 'Income',
                data: incomeExpenseChartData.value.income,
            },
            {
                name: 'Expense',
                data: incomeExpenseChartData.value.expense,
            },
        ],

    }
});

const fetchIncomeExpense = async () => {
    try {
        loading.value = true;
        const response = await getIncomeExpense(selectedLastMonths.value);
        if (response.data?.success) {
            incomeExpenseData.value = response.data?.data;
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
}

const incomeExpenseChartData = computed(() => {
    if (!incomeExpenseData.value || !incomeExpenseData.value.length) {
        return {
            months: [],
            income: [],
            expense: []
        }
    }

    return {
        months: incomeExpenseData.value.map(item => item.month),
        income: incomeExpenseData.value.map(item => item.income),
        expense: incomeExpenseData.value.map(item => item.expense)
    }
})


onMounted(() => {
    fetchIncomeExpense();
})

</script>

<template>
    <Card>
        <template #header>
            Income vs Expense • Last {{ selectedLastMonths }} Months
        </template>
        <template #addons>
            <el-select v-model="selectedLastMonths" placeholder="Select months" style="width: 140px" size="small"
                @change="fetchIncomeExpense">
                <el-option v-for="item in lastMonthOptions" :key="item.value" :label="item.label" :value="item.value" />
            </el-select>
        </template>
        <template #body>
            <div class="relative h-[250px]">
                <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/60 z-10">
                    <el-skeleton :loading="loading" animated class="w-full h-full px-6">
                        <template #template>
                            <div class="flex items-end justify-around w-full h-full gap-3 pb-6">
                                <el-skeleton-item variant="rect" style="width: 8%; height: 45%;" />
                                <el-skeleton-item variant="rect" style="width: 8%; height: 70%;" />
                                <el-skeleton-item variant="rect" style="width: 8%; height: 35%;" />
                                <el-skeleton-item variant="rect" style="width: 8%; height: 85%;" />
                                <el-skeleton-item variant="rect" style="width: 8%; height: 55%;" />
                                <el-skeleton-item variant="rect" style="width: 8%; height: 65%;" />
                            </div>
                        </template>
                    </el-skeleton>
                </div>

                <VueApexCharts :class="{ 'opacity-0': loading }" class="transition-opacity duration-200"
                    :options="chartConfig.options" :series="chartConfig.series" />
            </div>
        </template>
    </Card>
</template>