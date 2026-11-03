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
    <el-button :type="props.color ? undefined : 'primary'" :color="props.color"> <slot></slot> Add Expense</el-button>
</template>