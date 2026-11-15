<script setup>
import ThemeToggle from "../components/ui/ThemeToggle.vue";
</script>

<template>
    <div class="relative flex min-h-screen flex-col p-3">
        <ThemeToggle class="absolute right-3 top-3 bg-[var(--app-surface)] border border-[var(--el-border-color)] shadow-sm" />
        <div class="flex flex-1 items-center justify-center pt-12 pb-2">
            <RouterView />
        </div>
    </div>
</template>
