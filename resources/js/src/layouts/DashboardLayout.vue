<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import Header from "../components/Header.vue";
import Sidebar from "../components/Sidebar.vue";
import { useRoute } from "vue-router";

const isCollapsed = ref(false);
const isMobileOpen = ref(false);
const isMobile = ref(typeof window !== 'undefined' && window.innerWidth < 1024);

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


const onSidebarTransitionEnd = (e) => {
    if (e.target === e.currentTarget && e.propertyName === "width") {
        window.dispatchEvent(new Event("resize"));
    }
};

onUnmounted(() => {
    window.removeEventListener("resize", checkScreenSize);
});

const route = useRoute();

watch(
    () => route.path,
    () => {
        if (isMobile.value) isMobileOpen.value = false;
    }
);

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileOpen.value = !isMobileOpen.value;
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};
</script>

<template>
    <div class="flex min-h-screen bg-[var(--app-page-bg)]">
        <!-- Mobile overlay -->
        <div v-if="isMobile && isMobileOpen" @click="isMobileOpen = false" class="fixed inset-0 bg-black/50 z-20"></div>

        <!-- Sidebar Container with Right Border & Smooth Transition -->
        <aside
            class="h-screen bg-[var(--app-surface)] border-r border-[var(--el-border-color)] overflow-hidden z-30 transition-[width,transform] duration-300 ease-in-out"
            :class="isMobile
                ? [
                    'fixed left-0 top-0 w-[230px]',
                    isMobileOpen ? 'translate-x-0' : '-translate-x-full',
                ]
                : [
                    'sticky top-0 shrink-0',
                    isCollapsed ? 'w-[64px]' : 'w-[230px]',
                ]" @transitionend="onSidebarTransitionEnd">
            <Sidebar :collapsed="isCollapsed && !isMobile" @collapse-change="isCollapsed = $event"
                @close="isMobileOpen = false" />
        </aside>

        <div class="flex-1 min-w-0 flex flex-col">
            <Header @toggle-menu="toggleSidebar" />

            <main class="px-4 md:px-6 py-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>