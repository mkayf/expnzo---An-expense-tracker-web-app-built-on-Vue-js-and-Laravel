<template>
    <el-dialog v-model="dialogVisible" width="400" align-center>
        <template #header>
            <h2 class="text-lg font-semibold">Set monthly budget</h2>
        </template>
        <span class="">Set the maximum amount you want to spend this month.</span>
        <div class="py-4 flex items-center">
            <div class="flex-1">
                <span class="font-semibold text-xl text-black">{{ userCurrency }}</span>
            </div>
            <div class="flex-4">
                <el-input-number style="width: 100%;" v-model="num" :precision="2" :step="0.01" :formatter="formatted"
                    :parser="parsed" />
            </div>
        </div>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="closeDialog">Cancel</el-button>
                <el-button type="primary">
                    Confirm
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import useAuthStore from '../stores/auth';
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
    console.log('value: ', value);
    if (value === null || value === undefined || value === '') return ''
    return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const parsed = (value) => {
  if (!value) return ''
  return value.replace(/,/g, '')
}


</script>