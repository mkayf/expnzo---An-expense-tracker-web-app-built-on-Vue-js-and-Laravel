<script setup>

import useAuthStore from "./stores/auth";
const authStore = useAuthStore();

</script>

<template>
    <template v-if="authStore.isReady">
        <RouterView />
    </template>
</template>

<style>

.app-loader {
  display: flex !important;
  justify-content: center;
  align-items: center;
}

.app-loader .el-loading-spinner {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.app-loader .el-loading-spinner::before {
    content: "";
    height: 50px;
    width: 200px;
    background-image: url("@/assets/logo/logo.png");
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
    margin-bottom: 12px;
}

.app-loader .el-loading-spinner .circular {
    width: 35px !important;
    height: 35px !important;
}

.app-loader .el-loading-spinner .path {
    stroke: var(--primary-green);
}

.app-loader .el-loading-text {
    color: var(--primary-green);
}
</style>
