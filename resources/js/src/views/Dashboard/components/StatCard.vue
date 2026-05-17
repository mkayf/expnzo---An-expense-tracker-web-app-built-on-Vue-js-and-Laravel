<script setup>
import { onMounted } from "vue";
import useAuthStore from "../../../stores/auth";
import { formatAmount } from "../../../utils/helpers";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    label: {
        type: String,
    },
    amount: {
        type: Number,
        default: 0,
    },
});

const authStore = useAuthStore();
const userCurrency = authStore.user?.preferences?.currency;
const userCurrencyIso = authStore.user?.preferences?.currency_iso;

// const options = {
//     xaxis: {
//         categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998, 1999],
//     },
//     zoom: {
//         enabled: false,
//     },
//     chart: {
//         type: 'area',
//         sparkline: {
//             enabled: true
//         }
//     },
//     colors: ['var(--el-color-primary)'],
//     stroke: {
//         width: 2,
//         curve: 'smooth'
//     },
//     fill: {
//         type: 'gradient'
//     }
// };

// const series = [
//     {
//         name: "sales",
//         data: [30, 40, 35, 50, 49, 60, 70, 91, 125],
//     },
// ];

const options = {
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
    colors: ['var(--el-color-primary)', '#FF5F1F'], // 'income' => 'green', 'expense' => 'orange'
    labels: ['Income', 'Expense']
};

const series = [44, 55];

</script>
<template>
    <div
        class="border border-[var(--el-border-color)] rounded-2xl bg-white p-4"
    >
        <div class="grid grid-cols-4">
            <div class="col-span-3">
                <div class="flex items-center gap-2">
                    <span
                        class="p-2 bg-[var(--el-color-primary-dark-2)] text-white rounded-2xl"
                        style="
                            box-shadow: rgba(100, 100, 111, 0.5) 0px 4px 16px
                                0px;
                        "
                    >
                        <slot name="icon"></slot>
                    </span>
                    <span class="font-medium text-md text-slate-700">{{
                        label
                    }}</span>
                </div>
                <div class="mt-3">
                    <span class="text-md font-medium text-slate-700">{{
                        userCurrency ?? ""
                    }}</span>
                    <span class="ml-1 text-2xl font-semibold">
                        {{ formatAmount(100000, userCurrencyIso) }}</span
                    >
                </div>
                <div class="mt-1 w-full">
                    <span class="text-xs w-full text-green-700"
                        >↑ 20% more than last month</span
                    >
                </div>
            </div>
            <div class="flex flex-col justify-center items-end">
                <VueApexCharts
                    width="60"
                    height="80"
                    :options="options"
                    :series="series"
                >
                </VueApexCharts>
            </div>
        </div>
    </div>
</template>
