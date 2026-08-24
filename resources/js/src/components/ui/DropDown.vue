<template>
  
</template>

<script setup>
import { ElMessage } from 'element-plus'
import { ArrowDown } from '@element-plus/icons-vue'

const handleCommand = (command) => {
  ElMessage(`click on item ${command}`)
}
</script>

<style scoped>
.el-dropdown-link {
  cursor: pointer;
  color: var(--el-color-primary);
  display: flex;
  align-items: center !important;
  outline: none !important;
  font-size: .7rem !important;
}
</style>
