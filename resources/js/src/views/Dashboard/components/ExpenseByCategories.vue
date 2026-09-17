<script setup>
import Card from '../../../components/ui/Card.vue';
import { ref, onMounted, computed } from 'vue';
import useAuthStore from '../../../stores/auth.js';
import VueApexCharts from 'vue3-apexcharts';
import { formatAmount } from '../../../utils/helpers.js';

const selectedLastMonths = ref(0);
const loading = ref(false);
const authStore = useAuthStore();
const userCurrency = authStore.user.preferences.currency;

const lastMonthOptions = [
    {
        value: 0,
        label: 'This Month'
    },
    {
        value: 1,
        label: 'Last Month',
    },
    {
        value: 3,
        label: 'Last 3 Month',
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
                type: "donut",
            },
            dataLabels: {
                formatter: function (val) {
                    return val.toFixed(1) + '%'
                }
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        legend: {
                            show: false
                        }
                    }
                }
            ],
            stroke: {
                width: 0
            },
            colors: [
                '#B45309', // amber 700
                '#C2680D', // amber 650
                '#D97706', // amber 600
                '#EA8E0C', // amber 550
                '#F59E0B', // amber 500 — aapka base secondary color
                '#F0AD2E', // amber 450
                '#E8A63C', // muted amber — same depth, thora desaturated
                '#D9954A', // warm muted tan-amber — depth match primary jaisi
            ],
            labels: [
                'Food',
                'Transport',
                'Bills',
                'Shopping',
                'Health',
                'Entertainment',
                'Education',
                'Other'
            ],
            legend: {
                position: 'right'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return `${userCurrency} ` + formatAmount(val);
                    }
                }
            }
        },
        series: [18500, 9200, 12500, 6100, 3000, 4500, 2500, 1800]
    }
})

</script>

<template>
    <Card>
        <template #header>
            Expense By Categories
        </template>
        <template #addons>
            <el-select v-model="selectedLastMonths" placeholder="Select months" style="width: 140px" size="small">
                <el-option v-for="item in lastMonthOptions" :key="item.value" :label="item.label" :value="item.value" />
            </el-select>
        </template>
        <template #body>
            <div class="relative aspect-[16/10] md:aspect-auto md:h-[320px]">
                <VueApexCharts :class="{ 'opacity-0': loading }" class="transition-opacity duration-200" height="100%"
                    :options="chartConfig.options" :series="chartConfig.series" />
            </div>
        </template>
    </Card>
</template>