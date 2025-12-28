<script setup>
import { ref, watch } from 'vue';
import defaultAvatar from '../../assets/default-avatar.avif';

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
    width: 50px;
    cursor: pointer;
}
</style>
