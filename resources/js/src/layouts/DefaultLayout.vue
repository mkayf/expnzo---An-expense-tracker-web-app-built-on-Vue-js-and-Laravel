<script setup>
import Header from '../components/Header.vue';
import Sidebar from '../components/Sidebar.vue';

</script>

<template>
    <el-row>
        <Sidebar />
        <Header></Header>
    </el-row>
    <RouterView />
</template>

