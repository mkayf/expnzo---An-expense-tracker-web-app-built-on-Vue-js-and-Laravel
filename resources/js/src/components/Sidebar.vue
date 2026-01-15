<script lang="ts" setup>
import {Cog6ToothIcon} from "@heroicons/vue/24/outline";

import { computed, ref, Transition, watch } from "vue";
import menus from "../utils/menu.config";
import {useRoute, useRouter} from 'vue-router'

const props = defineProps({
    collapsed: Boolean,
});

const emit = defineEmits(["collapse-change"]);

const isLocked = ref(true);
const isHoverExpandable = ref(true);

const handleOpen = (key: string, keyPath: string[]) => {
    console.log(key, keyPath);
};
const handleClose = (key: string, keyPath: string[]) => {
    console.log(key, keyPath);
};

const handleMouseEnter = () => {
    isHoverExpandable.value = false;
};

const handleMouseLeave = () => {
    isHoverExpandable.value = true;
};

const handleCollapse = computed(() => {
    return isLocked.value === true || isHoverExpandable.value === false
        ? false
        : true;
});

const router = useRouter();
const route = useRoute();

const navigate = (path) => router.push(path);

watch(handleCollapse, (val) => {
    emit("collapse-change", val);
});
</script>

<template>
    <el-menu
        :default-active="route.path"
        class="el-menu-vertical-demo h-full flex flex-col border-none w-full"
        :collapse="collapsed"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <div
            class="hidden lg:flex justify-between items-center px-6 pt-6 pb-4 w-full box-border transition-all duration-300"
        >
            <div class="flex items-center">
                <Transition name="fade" mode="out-in">
                    <img
                        v-if="!collapsed"
                        src="../assets/logo/logo.png"
                        class="w-35 object-contain"
                        alt="Logo"
                        key="full-logo"
                    />
                    <img
                        v-else
                        src="../assets/logo/monogram.png"
                        class="w-8 object-contain"
                        alt="Monogram"
                        key="mini-logo"
                    />
                </Transition>
            </div>
            <Transition name="fade">
                <label
                    class="relative inline-flex items-center cursor-pointer transition-all duration-500 ease-in-out"
                    v-show="!collapsed"
                >
                    <input
                        type="checkbox"
                        value=""
                        class="sr-only peer"
                        v-model="isLocked"
                    />
                    <div
                        class="group peer ring-0 bg-gradient-to-r from-gray-600 to-gray-800 rounded-full outline-none duration-700 after:duration-300 w-12 h-6 shadow-md peer-checked:bg-gradient-to-r peer-checked:from-emerald-500 peer-checked:to-emerald-900 peer-focus:outline-none after:content-[''] after:rounded-full after:absolute after:bg-gray-50 after:outline-none after:h-5 after:w-5 after:top-0.5 after:left-0.5 peer-checked:after:translate-x-6"
                    >
                        <svg
                            class="duration-300 absolute top-1 right-1 fill-white w-4 h-4"
                            viewBox="0 0 100 100"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M50,18A19.9,19.9,0,0,0,30,38v8a8,8,0,0,0-8,8V74a8,8,0,0,0,8,8H70a8,8,0,0,0,8-8V54a8,8,0,0,0-8-8H38V38a12,12,0,0,1,23.6-3,4,4,0,1,0,7.8-2A20.1,20.1,0,0,0,50,18Z"
                            ></path>
                        </svg>

                        <svg
                            class="duration-300 absolute top-1 left-1 fill-white w-4 h-4"
                            viewBox="0 0 100 100"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M30,46V38a20,20,0,0,1,40,0v8a8,8,0,0,1,8,8V74a8,8,0,0,1-8,8H30a8,8,0,0,1-8-8V54A8,8,0,0,1,30,46Zm32-8v8H38V38a12,12,0,0,1,24,0Z"
                                fill-rule="evenodd"
                            ></path>
                        </svg>
                    </div>
                </label>
            </Transition>
        </div>
        <el-menu-item v-for="menu in menus" :key="menu.path" @click="navigate(menu.path)" :index="menu.path">
            <component :is="menu.icon" class="w-6 h-6 mr-2" />
            <template #title>
                {{ menu.name }}
            </template>
        </el-menu-item>
        <el-menu-item @click="navigate('/app/settings')" index="/app/settings">
            <Cog6ToothIcon class="w-6 h-6 mr-2" />
            <template #title>
                Settings
            </template>
        </el-menu-item>
    </el-menu>
</template>

<style scoped>

.el-menu-vertical-demo:not(.el-menu--collapse) {
    width: 100%;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
