<script setup>
import { ElMessage } from 'element-plus';
import "element-plus/es/components/message/style/css";
import { onMounted } from 'vue';


onMounted(() => {
    if(window.__FLASH__?.auth_success){
        ElMessage({
            type: 'success',
            'message': window.__FLASH__.auth_success
        })
        delete window.__FLASH__.auth_success;
    }
})
</script>

<template>  
<div class="h-[900px]">
    <h1 class="text-md sm:text-xl md:text-3xl font-semibold">
            Dashboard
    </h1>
</div>            
</template> 