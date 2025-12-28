<template>
    <div class="mt-4 bg-white p-4 rounded-md border border-[var(--el-border-color)] flex items-center justify-between">
        <el-page-header>
            <template #content>
                <span class="text-lg">Dashboard</span>
            </template>
        </el-page-header>
        <div class="flex items-center justify-center gap-4 !border-red-500">
            <global-search />
            <avatar />
        </div>
    </div>
</template>

<script setup>
import { ArrowLeft } from "@element-plus/icons-vue";
import GlobalSearch from "./GlobalSearch.vue";
import Avatar from "./ui/Avatar.vue";
</script>
