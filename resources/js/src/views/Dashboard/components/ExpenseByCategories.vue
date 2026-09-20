<script setup>
import Card from '../../../components/ui/Card.vue';
import { ref, onMounted, computed } from 'vue';
import useAuthStore from '../../../stores/auth.js';
import VueApexCharts from 'vue3-apexcharts';
import { formatAmount } from '../../../utils/helpers.js';
import { getExpenseByCategories } from '../../../services/dashboard.service.js';

const selectedMonths = ref(0);
const loading = ref(false);
const authStore = useAuthStore();
const userCurrency = authStore.user.preferences.currency;
const expenseCategoriesData = ref([]);

const monthOptions = [
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
                '#B45309', 
                '#C2680D',
                '#D97706',
                '#EA8E0C', 
                '#F59E0B', 
                '#F0AD2E', 
                '#E8A63C', 
                '#D9954A',
            ],
            labels: expenseCategoryChartData.value.labels,
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
        series: expenseCategoryChartData.value.amounts
    }
})


const fetchExpenseByCategories = async () => {
    try {
        const response = await getExpenseByCategories(selectedMonths.value);
        if(response.data?.success){
            expenseCategoriesData.value = response.data?.data;
            console.log(response.data?.data);
        }
    } catch (error) {
        handleError(error);
    }
}

const expenseCategoryChartData = computed(() => {
    if(!expenseCategoriesData.  value || !expenseCategoriesData.value.length){
        return {
            labels: [],
            amounts: []
        }
    }

    return {
        labels: expenseCategoriesData.value.map(item => item.name),
        amounts: expenseCategoriesData.value.map(item => item.amount)
    }
})

onMounted(() => {
    fetchExpenseByCategories();

    setTimeout(() => {
        console.log(expenseCategoriesData.value);
        console.log(expenseCategoryChartData.value);
    }, 4000)
})

</script>

<template>
    <Card>
        <template #header>
            Expense By Categories
        </template>
        <template #addons>
            <el-select v-model="selectedMonths" placeholder="Select months" style="width: 140px" size="small" @change="fetchExpenseByCategories">
                <el-option v-for="item in monthOptions" :key="item.value" :label="item.label" :value="item.value" />
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