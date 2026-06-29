<script setup>
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import { onMounted, ref, watch } from "vue";
import PopupButton from "../../components/ui/PopupButton.vue";
import { PlusIcon, WalletIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon, BanknotesIcon } from "@heroicons/vue/24/outline";
import StatCard from "./components/StatCard.vue";
import useAuthStore from "../../stores/auth";
import { getSummaryStats } from "../../services/dashboard.service.js";
import handleError from "../../utils/handleError.js";

const monthFilter = ref(null);

const statsSummary = ref({
    balance: {},
    income: {},
    expense: {},
    budget: {}
});

const authStore = useAuthStore();
const username = authStore.user.name ?? 'User'

const fetchStatsSummary = async () => {
    try {
        const response = await getSummaryStats();
        if (response?.data?.success) {
            statsSummary.value = response.data?.summary;
            console.log(statsSummary.value);
        }
    }
    catch (e) {
        handleError(e);
        console.log(e);
    }
}

onMounted(() => {
    if (window.__FLASH__?.auth_success) {
        ElMessage({
            type: "success",
            message: window.__FLASH__.auth_success,
        });
        delete window.__FLASH__.auth_success;
    }

    fetchStatsSummary();

});
</script>

<template>
    <div class="">
        <div class="dashboard-header flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
            <h1 class="text-md md:text-lg font-semibold">
                Welcome Back, <span class="text-[var(--primary-color)]">{{ username }}</span>
            </h1>
            <div class="flex items-center gap-4">
                <el-date-picker v-model="monthFilter" value-format="YYYY-MM" type="month" placeholder="Pick a month" />
                <PopupButton text="Add Expense">
                    <PlusIcon class="w-5 h-5" />
                </PopupButton>
            </div>
        </div>
        <div class="dashboard-body mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:!grid-cols-4 gap-5">
                <StatCard type="balance" :data="statsSummary.balance" chart="donut">
                    <template #label>
                        Total Balance
                    </template>
                    <template #icon>
                        <WalletIcon class="h-4 w-4" />
                    </template>
                </StatCard>
                <StatCard type="income" :data="statsSummary.income" chart="area">
                    <template #label>
                        Total Income
                    </template>
                    <template #icon>
                        <ArrowTrendingUpIcon class="h-4 w-4" />
                    </template>
                </StatCard>
                <StatCard type="expense" :data="statsSummary.expense" chart="area">
                    <template #label>
                        Total Expense
                    </template>
                    <template #icon>
                        <ArrowTrendingDownIcon class="h-4 w-4" />
                    </template>
                </StatCard>
                <StatCard type="budget" :data="statsSummary.budget" chart="donut">
                    <template #label>
                        Budget
                    </template>
                    <template #icon>
                        <BanknotesIcon class="h-4 w-4" />
                    </template>
                </StatCard>
            </div>
        </div>
    </div>
</template>
