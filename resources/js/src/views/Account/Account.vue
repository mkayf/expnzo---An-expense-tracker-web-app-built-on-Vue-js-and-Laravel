<script setup>
import {UserIcon, LockClosedIcon, AdjustmentsHorizontalIcon} from "@heroicons/vue/24/outline";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const navigate = (routeName) => {
    router.push({name: routeName});
}

</script>

<template>
    <div>
        <h1 class="text-md sm:text-xl md:text-3xl font-semibold">
            Account settings
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-[1fr_3fr] gap-6 mt-6">
            <div class="">
                <el-menu
                    :default-active="route.name"
                    class="el-menu-vertical-demo border border-[var(--el-border-color)] rounded-md overflow-hidden"
                >
                    <el-menu-item @click="navigate('Profile')" :index="'Profile'" class="account-menu-item">
                        <UserIcon class="w-6 h-6 mr-3" />
                        <template #title>
                            Profile
                        </template>
                    </el-menu-item>
                    <el-menu-item @click="navigate('Password')" :index="'Password'" class="account-menu-item">
                        <LockClosedIcon class="w-6 h-6 mr-3" />
                        <template #title>
                            Password
                        </template>
                    </el-menu-item>
                    <el-menu-item @click="navigate('Preferences')" :index="'Preferences'" class="account-menu-item">
                        <AdjustmentsHorizontalIcon class="w-6 h-6 mr-3" />
                        <template #title>
                            Preferences
                        </template>
                    </el-menu-item>
                    
                </el-menu>
            </div>
            <div class=" bg-white border border-[var(--el-border-color)] rounded-md">
                <RouterView />
            </div>
        </div>
    </div>
</template>
