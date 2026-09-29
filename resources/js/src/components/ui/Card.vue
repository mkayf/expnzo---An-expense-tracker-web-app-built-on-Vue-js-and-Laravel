<script setup>
const props = defineProps({
    body_x_padding: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-white w-full">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-3 px-3 py-3">
            <h3 class="font-semibold text-sm text-slate-700 flex-1 min-w-[150px]">
                <slot name="header"></slot>
            </h3>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <slot name="addons"></slot>
            </div>
        </div>
        <div class="py-3" :class="{'px-3': body_x_padding}">
            <slot name="body"></slot>
        </div>
    </div>
</template>