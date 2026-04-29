<script setup>

const props = defineProps({
    text: {
        type: String,
        default: ''
    },
    color: {
        type: String,
    },
});

</script>

<template>
    <el-button :color="props.color !== undefined ? props.color : 'var(--el-color-primary)'"> <slot></slot> Add Expense</el-button>
</template>