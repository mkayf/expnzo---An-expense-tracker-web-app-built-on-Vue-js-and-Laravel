<script setup>

const props = defineProps({
    text: {
        type: String,
        default: 'Submit'
    },
    icon: {
        type: String,
        default: null
    },
    classes: {
        type: String,
        default: ''
    },
    isLoading: {
        type: Boolean,
        default: false
    }
});

</script>

<template>
    <el-button :loading="isLoading" native-type="submit" :class="`!bg-[var(--primary-green)] !border-[var(--primary-green)] hover:!bg-[var(--green-hover)] transition-all duration-500 !text-white ${classes}`">
        <i v-if="props.icon" :class="props.icon"></i>
        {{ props.text }}
    </el-button>
</template>