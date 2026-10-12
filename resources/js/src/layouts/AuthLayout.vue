<script setup>
import ThemeToggle from "../components/ui/ThemeToggle.vue";
</script>

<template>
<div class="relative h-screen flex justify-center items-center p-4">
    <ThemeToggle class="absolute top-4 right-4 bg-[var(--app-surface)] border border-[var(--el-border-color)] shadow-sm" />
    <RouterView />
</div>    
</template>
