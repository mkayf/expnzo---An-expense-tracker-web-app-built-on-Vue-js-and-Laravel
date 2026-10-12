<script setup>
import { MoonIcon, SunIcon } from "@heroicons/vue/24/outline";
import useThemeStore from "../../stores/theme";

const themeStore = useThemeStore();
</script>

<template>
    <button
        type="button"
        class="theme-toggle flex h-9 w-9 items-center justify-center rounded-lg text-[var(--el-text-color-regular)] hover:bg-[var(--app-hover)] hover:text-[var(--el-color-primary)] transition-colors duration-200"
        :aria-label="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="themeStore.toggle()"
    >
        <SunIcon v-if="themeStore.isDark" class="h-5 w-5" />
        <MoonIcon v-else class="h-5 w-5" />
    </button>
</template>
