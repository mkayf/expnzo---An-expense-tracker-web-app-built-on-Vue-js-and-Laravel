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
    },
    isDisabled: {
        type: Boolean,
        default: false
    }
});

</script>

<template>
    <el-button :disabled="isDisabled" :loading="isLoading" native-type="submit" class="transition-all duration-500" :class="{
        '!bg-[var(--primary-green)] !border-[var(--primary-green)] hover:!bg-[var(--green-hover)]  !text-white`' : isDisabled === false,
        '!bg-gray-200 hover:!bg-gray-300 hover:!text-[#606266] !border-none' : isDisabled === true
    }">
        <i v-if="props.icon" :class="props.icon"></i>
        {{ props.text }}
    </el-button>
</template>