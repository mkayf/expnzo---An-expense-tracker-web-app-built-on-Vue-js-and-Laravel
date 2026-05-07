<script setup>
import useAuthStore from "../../../stores/auth";
import { formatAmount } from "../../../utils/helpers";

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
</script>
<template>
    <div
        class="border border-[var(--el-border-color)] rounded-2xl bg-white p-4"
    >
        <div class="grid grid-cols-4">
            <div class="border-2 border-red-600 col-span-3">
                <div class="flex items-center gap-2">
                    <!-- <span
                        class="p-2 bg-[var(--el-color-primary-dark-2)] text-white rounded-lg"
                        >
                        <slot name="icon"></slot>
                    </span> -->
                    <span class="font-medium text-md text-slate-700">{{
                        label
                    }}</span>
                </div>
                <div class="mt-3">
                    <span class="text-md text-slate-700">{{
                        userCurrency ?? ""
                    }}</span>
                    <span class="ml-1 text-3xl font-semibold">
                        {{ formatAmount(505000,userCurrencyIso) }}</span
                    >
                </div>
                <div class="mt-1 border-4 border-amber-600 w-full">
                    <span class="text-xs w-full text-green-700"
                        >↑ 20% more than last month</span
                    >
                </div>
            </div>
            <div class="flex justify-end">chart</div>
        </div>
    </div>
</template>
