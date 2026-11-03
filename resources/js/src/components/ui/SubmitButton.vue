<script setup>
const props = defineProps({
    text: {
        type: String,
        default: "Submit",
    },
    icon: {
        type: String,
        default: null,
    },
    classes: {
        type: String,
        default: "",
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    isDisabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

</script>

<template>
    <el-button
        type="primary"
        :disabled="isDisabled"
        :loading="isLoading"
        native-type="submit"
        @click="emit('click', $event)"
        :class="props.classes"
    >
        <i v-if="props.icon" :class="props.icon"></i>
        {{ props.text }}
    </el-button>
</template>
