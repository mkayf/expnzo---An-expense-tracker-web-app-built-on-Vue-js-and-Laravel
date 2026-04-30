<script setup>
import { ElMessage } from "element-plus";
import "element-plus/es/components/message/style/css";
import { onMounted, ref, watch } from "vue";
import PopupButton from "../../components/ui/PopupButton.vue";
import { PlusIcon, WalletIcon } from "@heroicons/vue/24/outline";
import StatCard from "./components/StatCard.vue";

const monthFilter = ref(null);

onMounted(() => {
    if (window.__FLASH__?.auth_success) {
        ElMessage({
            type: "success",
            message: window.__FLASH__.auth_success,
        });
        delete window.__FLASH__.auth_success;
    }
});
</script>

<template>
    <div>
        <div class="container">
            <div class="dashboard-header flex items-center justify-between">
                <h1 class="text-md sm:text-xl md:text-3xl font-semibold">
                    Dashboard
                </h1>
                <div class="flex items-center gap-4">
                    <div class="block">
                        <el-date-picker
                            v-model="monthFilter"
                            value-format="YYYY-MM"
                            type="month"
                            placeholder="Pick a month"
                        />
                    </div>
                    <PopupButton text="Add Expense">
                        <PlusIcon class="w-5 h-5" />
                    </PopupButton>
                </div>
            </div>
            <div class="dashboard-body mt-6">
                <StatCard label="Total Balance">
                    <template #icon>
                        <WalletIcon class="h-6 w-6" />
                    </template>
                </StatCard>
            </div>
        </div>
    </div>
</template>
