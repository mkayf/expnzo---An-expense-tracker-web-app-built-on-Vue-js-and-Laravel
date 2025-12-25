<script setup>
import { ref } from "vue";
import Header from "../components/Header.vue";
import Sidebar from "../components/Sidebar.vue";

const isCollapsed = ref(false);
</script>

<template>
    <div class="flex overflow-hidden">
        <el-aside
            class="transition-width h-full fixed left-0 top-0"
            :width="isCollapsed ? '64px' : '230px'"
        >
            <Sidebar
                :collapsed="isCollapsed"
                @collapse-change="isCollapsed = $event"
            />
        </el-aside>

        <div
            class=" flex-1 -[230px] h-full flex flex-col overflow-y-auto"
            :class="isCollapsed ? 'ml-[64px]' : 'ml-[230px]'"
        >
            <el-header class="">
                <Header />
            </el-header>

            <el-main class="">
                <RouterView />
            </el-main>
        </div>
    </div>
</template>

<style>
.transition-width {
    transition: width 0.3s ease-in-out;
}
</style>
