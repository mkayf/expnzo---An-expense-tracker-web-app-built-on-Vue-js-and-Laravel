<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import useAuthStore from '../stores/auth';
import { BanknotesIcon, WalletIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline';
import { getCurrentPeriod, formatAmount } from '../utils/helpers';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    data: {
        type: Object,
        default: {}
    }
});

const emit = defineEmits(['close-dialog']);

const authStore = useAuthStore();

const budgetAmount = ref(props.data?.current_budget ?? 0);
const userCurrency = authStore.user?.preferences?.currency ?? 'PKR';

const dialogVisible = computed({
    get: () => props.visible,
    set: () => {
        closeDialog();
    }
});

const closeDialog = () => {
    emit('close-dialog');
}

const formatted = (value) => {
    if (value === null || value === undefined || value === '') return ''
    return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const parsed = (value) => {
    if (!value) return ''
    return value.replace(/,/g, '')
}

const useLastBudget = () => {
    budgetAmount.value = props.data?.last_budget ?? 0;
}


</script>

<template>
    <el-dialog v-model="dialogVisible" align-center :width="500">
        <template #header>
            <h2 class="text-md sm:text-lg font-semibold">Set monthly budget</h2>
            <p class="text-xs">Set the maximum amount you want to spend this month.</p>
        </template>
        <div class="flex justify-between">
            <el-tag class="font-semibold"
                style="--el-tag-bg-color: #f0f9ff; --el-tag-border-color: #e0f2fe; --el-tag-text-color: var(--accent-sky-blue);">
                Period: {{ getCurrentPeriod() }}
            </el-tag>
            <el-button size="small" type="primary" plain v-if="data.last_budget && data.last_budget > 0">Use Last Budget</el-button>
        </div>
        <div class="py-4 flex items-center">
            <div class="flex-1">
                <span class="font-semibold text-xl text-black">{{ userCurrency }}</span>
            </div>
            <div class="flex-4">
                <el-input-number style="width: 100%;" v-model="budgetAmount" :precision="2" :step="1"
                    :formatter="formatted" :parser="parsed" :max="100000000" />
                <span class="text-xs float-right"></span>
            </div>
        </div>
        <el-row class="flex" :gutter="8">
            <el-col :xs="12" :sm="8" class="min-w-0 mb-2 sm:mb-0">
                <div
                    class="@container bg-white rounded-xl border border-gray-100 p-2 sm:p-3 shadow hover:shadow-md transition-shadow duration-200 h-full flex flex-col gap-1 sm:gap-2 min-w-0">
                    <div
                        class="inline-flex items-center gap-1 self-start bg-gray-100 rounded-full pl-1.5 pr-2 py-0.5 max-w-full">
                        <WalletIcon class="w-3 h-3 shrink-0 text-(--secondary-gray)!" />
                        <span
                            class="text-[9px] @[110px]:text-[10px] font-bold uppercase tracking-wide text-(--secondary-gray)! whitespace-nowrap">Current</span>
                    </div>
                    <p
                        class="text-xs @[110px]:text-sm @[150px]:text-base @[190px]:text-lg font-bold text-(--text-charcoal)! leading-tight break-words">
                        {{ formatAmount(data?.current_budget ?? 0) }}</p>
                </div>
            </el-col>

            <el-col :xs="12" :sm="8" class="min-w-0 mb-2 sm:mb-0">
                <div
                    class="@container bg-white rounded-xl border border-gray-100 p-2 sm:p-3 shadow hover:shadow-md transition-shadow duration-200 h-full flex flex-col gap-1 sm:gap-2 min-w-0">
                    <div
                        class="inline-flex items-center gap-1 self-start bg-sky-50 rounded-full pl-1.5 pr-2 py-0.5 max-w-full">
                        <ReceiptPercentIcon class="w-3 h-3 shrink-0 text-(--accent-sky-blue)!" />
                        <span
                            class="text-[9px] @[110px]:text-[10px] font-bold uppercase tracking-wide text-(--accent-sky-blue)! whitespace-nowrap">Used</span>
                    </div>
                    <p
                        class="text-xs @[110px]:text-sm @[150px]:text-base @[190px]:text-lg font-bold text-(--text-charcoal)! leading-tight break-words">
                        {{ formatAmount(data?.used_budget ?? 0) }}</p>
                </div>
            </el-col>

            <el-col :xs="24" :sm="8" class="min-w-0">
                <div
                    class="@container bg-white rounded-xl border border-gray-100 p-2 sm:p-3 shadow hover:shadow-md transition-shadow duration-200 h-full flex flex-row items-center justify-between sm:flex-col sm:items-stretch sm:justify-normal gap-1 sm:gap-2 min-w-0">
                    <div
                        class="inline-flex items-center gap-1 self-start bg-(--el-color-primary-light-9) rounded-full pl-1.5 pr-2 py-0.5 max-w-full">
                        <BanknotesIcon class="w-3 h-3 shrink-0 text-(--primary-green)!" />
                        <span
                            class="text-[9px] @[110px]:text-[10px] font-bold uppercase tracking-wide text-(--primary-green)! whitespace-nowrap">Remaining</span>
                    </div>
                    <p
                        class="text-sm sm:text-xs sm:@[110px]:text-sm sm:@[150px]:text-base sm:@[190px]:text-lg font-bold text-(--primary-green)! leading-tight break-words">
                        {{ formatAmount(data?.current_budget ?? 0) }}
                    </p>
                </div>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="closeDialog">Cancel</el-button>
                <el-button type="primary">
                    Save
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>
