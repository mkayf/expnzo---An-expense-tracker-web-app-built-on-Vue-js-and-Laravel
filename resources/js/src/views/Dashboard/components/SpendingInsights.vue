<script setup>
import {ref, onMounted, watch} from "vue";
import Card from "../../../components/ui/Card.vue";

const props = defineProps({
    update: {
        type: Number,
    }
});

watch(() => props.update, (val) => {
    if(val){
        // fire fetch
    }
});

</script>

<template>
    <Card>
        <template #header>
            <span>
                Spending Insights
            </span>
        </template>
        <template #body>
            
        </template>
    </Card>
</template>