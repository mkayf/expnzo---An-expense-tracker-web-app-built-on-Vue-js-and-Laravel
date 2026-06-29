<script setup>
import { computed, onMounted, ref } from "vue";
import useAuthStore from "../../../stores/auth";
import { formatAmount } from "../../../utils/helpers";
import VueApexCharts from "vue3-apexcharts";

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

const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency;
const userCurrencyIso = authStore.user?.preferences?.currency_iso;

const currentMonth = new Date().toLocaleDateString('en-US', {
    month: 'short',
    year: '2-digit',
});

const trendTextColor = ref(null);

const showMonth = computed(() => {
    if (props.type === 'income' || props.type === 'expense' || (props.type === 'budget' && props.data.amount !== 0)) {
        return ' • ' + currentMonth;
    }
})

const showTrendText = computed(() => {
    if (props.type === 'balance') {
        if(props.data?.trend?.direction === 'neutral'){
            return '↔ No change from last month';
        }
        else if (props.data?.trend?.direction === 'up') {
            trendTextColor.value = 'green';
            return `↑ ${props.data?.trend?.percentage}% more than last month`;
        } else if(props.data?.trend?.direction === 'down'){
            trendTextColor.value = 'red';
            return `↓ ${props.data?.trend?.percentage}% less than last month`;
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

const options = {
    xaxis: {
        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999],
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
    }
};

const series = [
    {
        name: "sales",
        data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
    },
];


// const options = {
//     chart: {
//         type: "donut",
//     },
//     legend: {
//         show: false,
//     },
//     dataLabels: {
//         enabled: false,
//     },
//     stroke: {
//         width: 0
//     },
//     colors: ['var(--el-color-primary)', '#FF5F1F'], // 'income' => 'green', 'expense' => 'orange'
//     labels: ['Income', 'Expense']
// };

// const series = [44, 55];

</script>
<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-white p-3">
        <div class="flex items-center gap-2">
            <span class="p-2 bg-[var(--el-color-primary-dark-2)] text-white rounded-2xl"
                style="box-shadow: rgba(100, 100, 111, 0.5) 0px 4px 8px 0px;">
                <slot name="icon"></slot>
            </span>
            <span class="font-semibold text-sm text-slate-700">
                <slot name="label"></slot> {{ showMonth }}
            </span>
        </div>
        <div class="grid grid-cols-4">
            <div class="col-span-3">

                <div class="mt-3">
                    <span class="text-md font-medium text-slate-700">{{
                        userCurrency ?? ""
                    }}</span>
                    <span class="ml-1 text-2xl font-semibold">
                        {{ formatAmount(data.amount, userCurrencyIso) }}</span>
                </div>
                <div class="mt-1 w-full">
                    <span v-if="showTrendText" class="text-xs w-full" :class="trendTextClass">{{ showTrendText }}</span>
                </div>
            </div>
            <div class="flex flex-col items-end justify-end">
                <VueApexCharts width="50" height="80" :options="options" :series="series">
                </VueApexCharts>
            </div>
        </div>
    </div>
</template>
