<script lang="ts" setup>
import {
    BanknotesIcon,
    ChartBarSquareIcon,
    Cog6ToothIcon,
    CurrencyDollarIcon,
    DocumentCurrencyDollarIcon,
    Square3Stack3DIcon,
} from "@heroicons/vue/24/outline";

const handleOpen = (key: string, keyPath: string[]) => {
    console.log("handle open", key, keyPath);
};
const handleClose = (key: string, keyPath: string[]) => {
    console.log("handle close", key, keyPath);
};
</script>

<template>
    <el-col :span="5">
        <el-menu
            default-active=""
            class="el-menu-vertical-demo h-screen flex flex-col"
            @open="handleOpen"
            @close="handleClose"
        >
            <div class="flex justify-between items-center px-6 pt-6 pb-4">
                <div>
                    <img src="../assets/logo/logo.png" class="w-50" />
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" value="" class="sr-only peer" />
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
            </div>
            <el-menu-item index="1">
                <ChartBarSquareIcon class="w-6 h-6 mr-2" />
                <span>Dashboard</span>
            </el-menu-item>
            <el-menu-item index="2">
                <currency-dollar-icon class="w-6 h-6 mr-2" />
                <span>Income</span>
            </el-menu-item>
            <el-menu-item index="3">
                <banknotes-icon class="w-6 h-6 mr-2" />
                <span>Expenses</span>
            </el-menu-item>
            <el-menu-item index="4">
                <Square3Stack3DIcon class="w-6 h-6 mr-2" />
                <span>Categories</span>
            </el-menu-item>
            <el-menu-item index="5">
                <document-currency-dollar-icon class="w-6 h-6 mr-2" />
                <span>Reports</span>
            </el-menu-item>
            <el-menu-item class="mt-auto" index="6">
                <cog6-tooth-icon class="w-6 h-6 mr-2" />
                <span>Settings</span>
            </el-menu-item>
        </el-menu>
    </el-col>
</template>

<style scoped>
li {
    font-weight: 500;
}
</style>
