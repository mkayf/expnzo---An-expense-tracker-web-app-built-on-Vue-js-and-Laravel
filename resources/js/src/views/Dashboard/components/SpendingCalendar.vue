<script setup>
import { ref } from 'vue'
import Card from '../../../components/ui/Card.vue';
const value = ref(new Date())
</script>

<template>
    <Card>
        <template #header>
            <span>Spending Calendar</span>
        </template>
        <template #body>
            <el-calendar v-model="value" />
        </template>
    </Card>
</template>