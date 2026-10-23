<script setup>
import { ref, onMounted, watch } from 'vue';
import Card from './ui/Card.vue';
import { DocumentChartBarIcon, DocumentTextIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import handleError from '../utils/handleError.js';

const props = defineProps({
    columns: {
        type: Array,
        default: () => []
    },
    apiService: {
        type: Function,
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

const tableData = ref([]);
const pagination = ref({
    perPage: 20,
    page: 1
});

const nextPage = () => {
    pagination.value.page++;
    fetchData();
}

const previousPage = () => {
    if(pagination.value.page === 1) return;
    pagination.value.page--;
    fetchData;
}

const fetchData = async () => {
    if (!props.apiService) return;
    try {
        const response = await props.apiService({
            page: pagination.value.page,
            per_page: pagination.value.perPage
        });
        if (response.data?.success) {
            console.log('data: ', response.data?.data);
        }
    } catch (e) {
        handleError(e);
    } finally {

    }
}

onMounted(() => {
    fetchData();
})
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

                        <el-button size="small" type="primary" :icon="DocumentChartBarIcon" v-if="exportExcel">Export
                            Excel</el-button>

                        <el-button size="small" type="danger" :icon="DocumentTextIcon" v-if="exportPDF">Export
                            PDF</el-button>
                    </div>
                </div>

                <div class="responsive-table-desktop w-full min-w-0 mt-5">
                    <el-table class="app-table" :data="tableData" style="width: 100%" table-layout="fixed"
                        :show-header="true">
                        <el-table-column v-for="column in columns" :key="column.label" :label="column.label"
                            :prop="column.prop">
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

                <div class="pagination flex items-center justify-center gap-3">
                    <el-button size="small" @click="previousPage">Previous</el-button>
                    <el-button size="small" @click="nextPage">Next</el-button>
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