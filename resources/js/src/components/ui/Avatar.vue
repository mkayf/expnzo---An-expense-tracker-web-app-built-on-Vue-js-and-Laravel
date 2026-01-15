<script setup>
import { ref, watch } from 'vue';
import defaultAvatar from '../../assets/default-avatar.avif';
import { RouterLink } from 'vue-router';
const props = defineProps({
    avatarURL: {
        type: String,
        default: null,
    },
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
    <el-avatar :src="avatar" @error="onAvatarError" />
</template>


<style scoped>
.el-avatar.el-avatar--circle {
    cursor: pointer;
    transition: all;
    transition-duration: .3s ease-in-out;
}

@media (max-width: 576px) {
    .el-avatar.el-avatar--circle{
        height: 30px;
        width: 30px;
    }
}
</style>
