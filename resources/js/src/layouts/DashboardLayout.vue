<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import Header from "../components/Header.vue";
import Sidebar from "../components/Sidebar.vue";

const isCollapsed = ref(false); 
const isMobileOpen = ref(false);
const isMobile = ref(false);

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) {
        isCollapsed.value = false;
        isMobileOpen.value = false;
    }
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener("resize", checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener("resize", checkScreenSize);
});

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileOpen.value = !isMobileOpen.value;
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};

const contentMargin = computed(() => {
    if (isMobile.value) return "ml-0";
    return isCollapsed.value ? "ml-[64px]" : "ml-[230px]";
});
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 relative">
        
        <div 
            v-if="isMobile && isMobileOpen" 
            @click="isMobileOpen = false"
            class="fixed inset-0 bg-black/50 z-20 transition-opacity"
        ></div>

        <aside
            class="h-full fixed left-0 top-0 z-30 transition-all duration-300 bg-white "
            :class="[
                isMobile 
                    ? (isMobileOpen ? 'translate-x-0 w-[230px]' : '-translate-x-full w-[230px]') 
                    : (isCollapsed ? 'w-[64px]' : 'w-[230px]')
            ]"
        >
            <Sidebar
                :collapsed="isCollapsed && !isMobile"
                @collapse-change="isCollapsed = $event"
            />
        </aside>

        <div
            class="flex-1 h-full flex flex-col overflow-y-auto transition-all duration-300"
            :class="contentMargin"
        >
            <Header @toggle-menu="toggleSidebar" />

            <main class="px-4 md:px-6 py-6 mt-2">
                <RouterView />
            </main>
        </div>
    </div>
</template>