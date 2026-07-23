<template>
    <el-dialog v-model="dialogVisible"   width="400" align-center>
        <template #header>
            <h2 class="text-lg font-semibold">Set monthly budget</h2>
        </template>
        <span class="">Set the maximum amount you want to spend this month.</span>
        <div>
            <el-input-number v-model="num" :min="1" :max="10" size="large" />
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

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close-dialog']);

const num = ref(0);


const dialogVisible = computed({
    get: () => props.visible,
    set: () => {
        closeDialog();
    }
});

const closeDialog = () => {
    emit('close-dialog');
}

</script>