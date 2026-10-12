<script setup>
const props = defineProps({
    body_x_padding: {
        type: Boolean,
        default: true
    },
    full_height: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-[var(--app-surface)] w-full"
        :class="{ 'flex h-full flex-col': full_height }">
        <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-3">
            <h3 class="font-semibold text-sm text-slate-700 dark:text-[var(--el-text-color-regular)] flex-1 min-w-[150px]">
                <slot name="header"></slot>
            </h3>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <slot name="addons"></slot>
            </div>
        </div>
        <div class="py-3" :class="[{ 'px-3': body_x_padding }, { 'flex flex-1 flex-col': full_height }]">
            <slot name="body"></slot>
        </div>
    </div>
</template>