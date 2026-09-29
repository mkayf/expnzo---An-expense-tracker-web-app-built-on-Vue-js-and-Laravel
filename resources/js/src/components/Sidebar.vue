<script lang="ts" setup>
import { Cog6ToothIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { computed, ref, watch } from "vue";
import menus from "../utils/menu.config";
import { useRoute, useRouter } from "vue-router";

const props = defineProps({
    collapsed: Boolean,
});

const emit = defineEmits(["collapse-change", "close"]);

const isLocked = ref(true);
const isHovered = ref(false);

const handleMouseEnter = () => {
    if (!isLocked.value) isHovered.value = true;
};

const handleMouseLeave = () => {
    if (!isLocked.value) isHovered.value = false;
};

const isCollapsedComputed = computed(() => {
    if (isLocked.value) return false;
    return !isHovered.value;
});

const router = useRouter();
const route = useRoute();

const navigate = (path: string) => {
    router.push(path);
};

watch(
    isCollapsedComputed,
    (val) => {
        emit("collapse-change", val);
    },
    { immediate: true }
);
</script>

<template>
    <div class="h-full w-[230px] min-w-[230px] flex flex-col overflow-hidden select-none" @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave">
        <el-menu :default-active="route.path"
            class="el-menu-vertical-demo h-full flex flex-col border-none w-full !bg-transparent" :collapse="false">
            <!-- Mobile header: logo + close button (top right) -->
            <div class="flex lg:hidden items-center justify-between px-5 pt-6 pb-4 w-full box-border shrink-0 h-[68px]">
                <img src="../assets/logo/logo.png" alt="Logo" class="h-7 w-auto max-w-none object-contain" />
                <button type="button" aria-label="Close menu"
                    class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-600 hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    @click="emit('close')">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            <!-- Header: logo + lock toggle -->
            <div class="hidden lg:flex items-center justify-between px-5 pt-6 pb-4 w-full box-border shrink-0 h-[68px]">
                <div class="relative h-7 w-[130px] shrink-0">
                    <img src="../assets/logo/logo.png" alt="Logo"
                        class="absolute left-0 top-0 h-7 w-auto max-w-none object-contain transition-opacity duration-300"
                        :class="collapsed ? 'opacity-0' : 'opacity-100'" />
                    <img src="../assets/logo/monogram.png" alt="Monogram"
                        class="absolute left-0 top-0 h-7 w-7 object-contain transition-opacity duration-300"
                        :class="collapsed ? 'opacity-100' : 'opacity-0'" />
                </div>

                <label class="relative inline-flex items-center cursor-pointer shrink-0 transition-opacity duration-300"
                    :class="collapsed ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                    <input type="checkbox" class="sr-only peer" v-model="isLocked" />
                    <div
                        class="group peer ring-0 bg-gradient-to-r from-gray-600 to-gray-800 rounded-full outline-none duration-700 after:duration-300 w-12 h-6 shadow-md peer-checked:bg-gradient-to-r peer-checked:from-emerald-500 peer-checked:to-emerald-900 peer-focus:outline-none after:content-[''] after:rounded-full after:absolute after:bg-gray-50 after:outline-none after:h-5 after:w-5 after:top-0.5 after:left-0.5 peer-checked:after:translate-x-6">
                        <svg class="duration-300 absolute top-1 right-1 fill-white w-4 h-4" viewBox="0 0 100 100"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M50,18A19.9,19.9,0,0,0,30,38v8a8,8,0,0,0-8,8V74a8,8,0,0,0,8,8H70a8,8,0,0,0,8-8V54a8,8,0,0,0-8-8H38V38a12,12,0,0,1,23.6-3,4,4,0,1,0,7.8-2A20.1,20.1,0,0,0,50,18Z">
                            </path>
                        </svg>
                        <svg class="duration-300 absolute top-1 left-1 fill-white w-4 h-4" viewBox="0 0 100 100"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M30,46V38a20,20,0,0,1,40,0v8a8,8,0,0,1,8,8V74a8,8,0,0,1-8,8H30a8,8,0,0,1-8-8V54A8,8,0,0,1,30,46Zm32-8v8H38V38a12,12,0,0,1,24,0Z"
                                fill-rule="evenodd"></path>
                        </svg>
                    </div>
                </label>
            </div>

            <!-- Menu items -->
            <el-menu-item v-for="menu in menus" :key="menu.path" :index="menu.path" :title="menu.name"
                @click="navigate(menu.path)">
                <component :is="menu.icon" class="w-6 h-6 mr-3 shrink-0" />
                <span class="whitespace-nowrap transition-opacity duration-200"
                    :class="collapsed ? 'opacity-0' : 'opacity-100'">
                    {{ menu.name }}
                </span>
            </el-menu-item>

            <el-menu-item index="/app/settings" title="Settings" @click="navigate('/app/settings')">
                <Cog6ToothIcon class="w-6 h-6 mr-3 shrink-0" />
                <span class="whitespace-nowrap transition-opacity duration-200"
                    :class="collapsed ? 'opacity-0' : 'opacity-100'">
                    Settings
                </span>
            </el-menu-item>
        </el-menu>
    </div>
</template>

<style scoped>
:deep(.el-menu) {
    border-right: none !important;
    width: 230px !important;
}

:deep(.el-menu-item) {
    height: 50px !important;
    line-height: 50px !important;
}
</style>