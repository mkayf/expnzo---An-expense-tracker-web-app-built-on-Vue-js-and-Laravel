<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    avatarURL: {
        type: String,
        default: null,
    },
    size: {
        type: Number,
        default: 40
    }
});

const avatar = ref(props.avatarURL);

watch(() => props.avatarURL, (val) => {
    if(val){
        console.log('Avatar URL updated:', val);
        avatar.value = val;
    }
})
</script>

<template>
    <div class="outline-none cursor-pointer">
        <el-avatar :size="size" :src="avatar" />
    </div>
</template>


<style scoped>
.el-avatar.el-avatar--circle {
    cursor: pointer;
    transition: all;
    transition-duration: .3s ease-in-out;
}


</style>
