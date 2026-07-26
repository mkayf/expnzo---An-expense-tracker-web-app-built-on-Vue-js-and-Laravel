<template>
    <div class="">
        <el-dialog v-model="dialogVisible" align-center :width="500">
            <template #header>
                <h2 class="text-md sm:text-lg font-semibold">Set monthly budget</h2>
                <p class="text-xs">Set the maximum amount you want to spend this month.</p>
            </template>
            <div class="flex justify-end">
                <el-button size="small" type="primary" plain>Use Last Budget</el-button>
            </div>
            <div class="py-4 flex items-center">
                <div class="flex-1">
                    <span class="font-semibold text-xl text-black">{{ userCurrency }}</span>
                </div>
                <div class="flex-4">
                    <el-input-number style="width: 100%;" v-model="num" :precision="2" :step="1" :formatter="formatted"
                        :parser="parsed" :max="100000000" />
                    <span class="text-xs float-right"></span>
                </div>
            </div>
            <el-row class="flex" :gutter="10">
                <el-col :xs="24" :sm="12" :md="8">
                    <div class="bg-[#EFF6FF] rounded-lg p-2 mb-3">
                        <div class="flex gap-1 items-center">
                            <WalletIcon class="w-5 h-5 !text-[#182f70]" />
                            <p class="!text-[#182f70] font-medium">Current</p>
                        </div>
                        <span class="!text-[#1E3A8A] font-semibold text-md sm:text-lg">600,000,000</span>
                    </div>
                </el-col>
                <el-col :xs="24" :sm="12" :md="8">
                    <div class="bg-[#FFFBEB]  rounded-lg p-2 mb-3">
                        <div class="flex gap-1 items-center">
                            <ReceiptPercentIcon class="w-5 h-5 !text-[#92400E]" />
                            <p class="!text-[#92400E] font-medium">Used</p>
                        </div>
                        <span class="!text-[#92400E] font-semibold text-md sm:text-lg">20,000</span>
                    </div>
                </el-col>
                <el-col :xs="24" :md="8">
                    <div class="bg-[#ECFDF5] rounded-lg p-2">
                        <div class="flex gap-1 items-center">
                            <BanknotesIcon class="w-5 h-5 !text-[#065F46]" />
                            <p class="!text-[#065F46] font-medium">Remaining</p>
                        </div>
                        <span class="!text-[#065F46] font-semibold text-md sm:text-lg">40,000</span>
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
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import useAuthStore from '../stores/auth';
import { BanknotesIcon, WalletIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close-dialog']);

const authStore = useAuthStore();

const num = ref(2);
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


</script>