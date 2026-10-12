<script setup>
import { computed, ref, watch } from "vue";
import { Bars3Icon, MagnifyingGlassIcon, UserIcon, ArrowRightEndOnRectangleIcon } from "@heroicons/vue/24/outline";
import GlobalSearch from "./GlobalSearch.vue"; 
import Avatar from "./ui/Avatar.vue";
import NotificationBell from "./ui/NotificationBell.vue";
import ThemeToggle from "./ui/ThemeToggle.vue";
import { ArrowRight } from "@element-plus/icons-vue";
import { useRoute } from "vue-router";
import { logout } from "../services/auth.service.js";
import { ElMessage } from "element-plus";
import handleError from "../utils/handleError";
import useAuthStore from "../stores/auth";

defineEmits(["toggle-menu"]);

const authStore = useAuthStore();

const showSearchInput = ref(false);
const route = useRoute();
const breadcrumbs = computed(() =>
    route.matched.filter((route) => route.meta?.breadcrumb)
);

const logoutUser = async () => {
    try{
        const res = await logout();
        if(res.data.success){
            authStore.logout();
            ElMessage({
                type: 'info',
                message: res.data.message
            })
        }
        window.location.reload();
    }
    catch(e){
        handleError(e);
    }
}
</script>

<template>
    <div
        class="mt-4 mx-4 md:mx-6 bg-[var(--app-surface)] px-4 py-2 rounded-md border border-[var(--el-border-color)] flex items-center justify-between relative"
    >
        <div class="flex items-center gap-3">
            <button
                @click="$emit('toggle-menu')"
                class="lg:hidden px-2 -ml-2 rounded-md hover:bg-gray-100 dark:hover:bg-[var(--app-hover)] text-gray-600 dark:text-[var(--el-text-color-regular)]"
            >
                <Bars3Icon class="w-6 h-6" />
            </button>

            <div class="lg:hidden flex items-center">
                <img
                    src="../assets/logo/logo.png"
                    alt="Logo"
                    class="h-6 w-auto object-contain"
                />
            </div>

            <div class="breadcrumbs">
                <el-breadcrumb :separator-icon="ArrowRight" :replace="false">
                    <el-breadcrumb-item
                        v-for="route in breadcrumbs"
                        :to="route.path"
                        class="cursor-pointer"
                        >{{ route.meta.breadcrumb }}</el-breadcrumb-item
                    >
                </el-breadcrumb>
            </div>
        </div>

        <div class="flex items-center gap-2">

            <div class="hidden lg:block w-64">
            </div>
            <div class="flex items-center gap-2 sm:gap-2">
                <ThemeToggle />
                <el-dropdown placement="bottom-end">
                    <div class="flex items-center gap-2 outline-none cursor-pointer">
                        <avatar :avatarURL="authStore.user?.avatar" />
                        <span v-if="authStore.user.name" class="hidden md:block text-md font-medium text-[var(--text-charcoal)]">{{ authStore.user.name }}</span>
                    </div>
                    <template #dropdown>
                        <el-dropdown-menu>
                            <router-link to="/app/account/profile">
                                <el-dropdown-item>
                                    <UserIcon class="w-5 h-5" />
                                    <span class="ml-2">My Account</span>
                                </el-dropdown-item>
                            </router-link>
                            <el-dropdown-item @click="logoutUser">
                                <ArrowRightEndOnRectangleIcon class="w-5 h-5" />
                                <span class="ml-2">Logout</span>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </template>
                </el-dropdown>
            </div>
        </div>

    </div>
</template>

<style>
.el-breadcrumb__inner a,
.el-breadcrumb__inner.is-link {
    font-weight: 500 !important;
}

@media (max-width: 1024px) {
    .breadcrumbs {
        display: none;
    }
}
</style>
