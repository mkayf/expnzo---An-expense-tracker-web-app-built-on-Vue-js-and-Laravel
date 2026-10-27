<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import Card from './ui/Card.vue';
import { DocumentChartBarIcon, DocumentTextIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import handleError from '../utils/handleError.js';

const props = defineProps({
    indexing: {
        type: Boolean,
        default: true,
    },
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
const loading = ref(false);

const pagination = ref({
    perPage: 20,
    page: 1,
    total: 0,
    lastPage: 1
});

const nextPage = () => {
    if(pagination.value.page === pagination.value.lastPage) return;
    pagination.value.page++;
    fetchData();
}

const previousPage = () => {
    if(pagination.value.page === 1) return;
    pagination.value.page--;
    fetchData();
}

const fetchData = async () => {
    if (!props.apiService) return;
    loading.value = true;
    try {
        const response = await props.apiService({
            page: pagination.value.page,
            per_page: pagination.value.perPage
        });
        if (response.data?.success) {
            const data = response.data?.data;
            tableData.value = data.items ?? [];
            pagination.value.total = data.total ?? 0;
            pagination.value.lastPage = data.last_page ?? 1;
        }
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
}

const calculateIndex = (index) => {
    return (pagination.value.page - 1) * pagination.value.perPage + index + 1
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
                        <el-table-column v-if="props.indexing" type="index" label="#" width="100" :index="calculateIndex" />
                        <el-table-column v-for="column in columns" :key="column.label" :label="column.label"
                            :prop="column.prop">
                            <template #default="scope">
                                <slot :name="column.prop" :row="scope.row">
                                    {{ column.prop.split('.').reduce((obj, key) => obj?.[key], scope.row) ?? '-' }}
                                </slot>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <div class="responsive-table-mobile divide-y divide-gray-100 dark:divide-[var(--app-divide)] px-3">

                </div>

                <div class="pagination flex items-center justify-center gap-3 mt-4" v-if="pagination.total > 0">
                    <el-button size="small" @click="previousPage" :disabled="pagination.page === 1">Previous</el-button>
                    <el-button size="small" @click="nextPage" :disabled="pagination.page === pagination.lastPage">Next</el-button>
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