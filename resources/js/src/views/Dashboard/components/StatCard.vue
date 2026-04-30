<script setup>

const props = defineProps({
    label: {
        type: String,
    }
});

</script>
<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-white p-4">
        <div class="grid grid-cols-2">
            <div>
                <div class="flex items-center gap-4">
                    <span class="p-2 bg-[var(--el-color-primary-dark-2)] text-white rounded-lg"><slot name="icon"></slot></span>
                    <span class="font-semibold text-lg">{{ label }}</span>
                </div>
            </div>
            <div>
                chart
            </div>
        </div>
    </div>
</template>