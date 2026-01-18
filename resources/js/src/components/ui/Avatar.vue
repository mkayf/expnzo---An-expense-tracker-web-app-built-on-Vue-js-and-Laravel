<script setup>
import { ref, watch } from 'vue';
import defaultAvatar from '../../assets/default-avatar.avif';

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

const avatar = ref(props.avatarURL || defaultAvatar);

watch(() => props.avatarURL, (val) => {
    avatar.value = val || defaultAvatar;
})

const onAvatarError = () => {
    avatar.value = defaultAvatar;
}
</script>

<template>
    <div class="outline-none cursor-pointer">
        <el-avatar :size="size" :src="avatar" @error="onAvatarError" />
    </div>
</template>


<style scoped>
.el-avatar.el-avatar--circle {
    cursor: pointer;
    transition: all;
    transition-duration: .3s ease-in-out;
}


/* @media (max-width: 576px) {
    .el-avatar.el-avatar--circle{
        height: 30px;
        width: 30px;
    }
} */
</style>
