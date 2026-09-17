<script setup>

</script>

<template>
    <div class="border border-[var(--el-border-color)] rounded-2xl bg-white p-3 w-full">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
            <h3 class="font-semibold text-sm text-slate-700 flex-1 min-w-[150px]">
                <slot name="header"></slot>
            </h3>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <slot name="addons"></slot>
            </div>
        </div>
        <div class="">
            <slot name="body"></slot>
        </div>
    </div>
</template>