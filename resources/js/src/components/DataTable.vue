<script setup>
import { ref, onMounted, watch } from 'vue';
import Card from './ui/Card.vue';
import { DocumentChartBarIcon, DocumentTextIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    columns: {
        type: Array,
        default: () => []
    },
    api: {
        type: String,
        default: null
    },
    exportPDF: {
        type: Boolean,
        default: false,
    },
    exportExcel: {
        type: Boolean,
        default: false
    }
});


const tableData = [
    {
        name: "kaif",
        role: "developer",
        age: 20,
        company: "Murree Brewery",
        city: "karachi"
    },
    {
        name: "ali",
        role: "designer",
        age: 25,
        company: "Systems Limited",
        city: "lahore"
    },
    {
        name: "ahmed",
        role: "backend developer",
        age: 28,
        company: "10Pearls",
        city: "karachi"
    },
    {
        name: "usman",
        role: "frontend developer",
        age: 23,
        company: "Folio3",
        city: "islamabad"
    },
    {
        name: "hamza",
        role: "project manager",
        age: 32,
        company: "Contour Software",
        city: "karachi"
    },
    {
        name: "hassan",
        role: "full stack developer",
        age: 27,
        company: "Systems Limited",
        city: "lahore"
    },
    {
        name: "bilal",
        role: "QA engineer",
        age: 26,
        company: "10Pearls",
        city: "islamabad"
    },
    {
        name: "saad",
        role: "UI/UX designer",
        age: 24,
        company: "Folio3",
        city: "karachi"
    },
    {
        name: "owais",
        role: "DevOps engineer",
        age: 30,
        company: "Contour Software",
        city: "lahore"
    },
    {
        name: "zain",
        role: "mobile developer",
        age: 22,
        company: "Techlogix",
        city: "islamabad"
    }
];

</script>

<template>
    <Card :header_y_padding="false" :body_x_padding="false">
        <template #body>
            <div class="responsive-table-container w-full min-w-0">
                <div class="px-4 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <el-input placeholder="Search records" size="small" style="width: 200px;"
                            :prefix-icon="MagnifyingGlassIcon" />
                    </div>
                    <div class="flex items-center justify-start md:justify-end flex-wrap gap-3">
                        <slot name="addons"></slot>

                        <el-button size="small" type="primary" :icon="DocumentChartBarIcon" v-if="exportExcel">Export Excel</el-button>

                        <el-button size="small" type="danger" :icon="DocumentTextIcon" v-if="exportPDF">Export PDF</el-button>
                    </div>
                </div>

                <div class="responsive-table-desktop w-full min-w-0 mt-5">
                    <el-table class="app-table" :data="tableData" style="width: 100%" table-layout="fixed"
                        :show-header="true">
                        <el-table-column v-for="column in columns" :key="column.label" :label="column.label" :prop="column.prop">
                            <template #default="scope">
                                <slot :name="column.prop" :row="scope.row">
                                    {{ scope.row[column.prop] }}
                                </slot>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <div class="responsive-table-mobile divide-y divide-gray-100 dark:divide-[var(--app-divide)] px-3">

                </div>
            </div>
        </template>
    </Card>
</template>


<style scoped>
.el-button+.el-button {
    margin: 0;
}
</style>