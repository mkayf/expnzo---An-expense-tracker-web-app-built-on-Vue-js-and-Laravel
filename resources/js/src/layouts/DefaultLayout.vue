<script setup>
import { ref } from "vue";
import Header from "../components/Header.vue";
import Sidebar from "../components/Sidebar.vue";

const isCollapsed = ref(false); 

</script>

<template>
  <div class="common-layout">
    <el-container>
      <el-aside class="overflow-hidden transition-width" :width="isCollapsed ? '64px' : '230px'">
        <Sidebar :collapsed="isCollapsed" @collapse-change="isCollapsed = $event" />
      </el-aside>
      <el-container>
        <el-header>
            <Header />
        </el-header>
        <el-main>
            <RouterView />
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<style>
.transition-width {
  transition: width 0.3s ease-in-out;
}
</style>