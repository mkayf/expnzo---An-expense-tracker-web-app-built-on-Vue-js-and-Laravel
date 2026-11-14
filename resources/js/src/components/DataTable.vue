<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import Card from './ui/Card.vue';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    DocumentChartBarIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon
} from "@heroicons/vue/24/outline";
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
    defaultSort: {
        type: Object,
        default: null
    },
    exportPDF: {
        type: Boolean,
        default: false,
    },
    exportExcel: {
        type: Boolean,
        default: false
    },
    exportService: {
        type: Function,
        default: null
    },
    pdfExportService: {
        type: Function,
        default: null
    },
    exportColumns: {
        type: Array,
        default: () => []
    },
    pdfExportColumns: {
        type: Array,
        default: () => []
    },
    excelFileName: {
        type: String,
        default: 'transactions.xlsx'
    },
    pdfFileName: {
        type: String,
        default: 'transactions.pdf'
    },
    bulkActions: {
        type: Array,
        default: () => []
    },
    update: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['bulk-action']);

const tableData = ref([]);
const loading = ref(false);
const selectedIds = ref([]);
const maxBulkSelection = 500;
const bulkActionLoading = computed(() => props.bulkActions.some((action) => action.loading));

const pagination = ref({
    perPage: 20,
    page: 1,
    total: 0,
    lastPage: 1
});

const sorting = ref({
    sortBy: props.defaultSort?.prop ?? null,
    sortDirection: props.defaultSort?.order === 'ascending'
        ? 'asc'
        : props.defaultSort?.order === 'descending'
            ? 'desc'
            : null
});
const searchQuery = ref('');
const emptyDescription = computed(() => searchQuery.value.trim()
    ? 'No matches found. Try a different search.'
    : 'Nothing to show just yet. Your records will appear here.');
const exportDialogVisible = ref(false);
const exporting = ref(false);
const selectedExportColumns = ref([]);
const exportFormat = ref('excel');
let latestRequestId = 0;
let searchDebounceTimer;

const currentPageIds = computed(() => tableData.value.map((row) => row.id));
const selectedCurrentPageCount = computed(() =>
    currentPageIds.value.filter((id) => selectedIds.value.includes(id)).length
);
const allCurrentPageSelected = computed(() =>
    currentPageIds.value.length > 0
    && selectedCurrentPageCount.value === currentPageIds.value.length
);
const canSelectCurrentPage = computed(() =>
    selectedIds.value.length
        + currentPageIds.value.filter((id) => !selectedIds.value.includes(id)).length
        <= maxBulkSelection
);

const isSelected = (id) => selectedIds.value.includes(id);

const toggleSelection = (id, selected) => {
    if (selected) {
        if (!isSelected(id) && selectedIds.value.length < maxBulkSelection) {
            selectedIds.value = [...selectedIds.value, id];
        }
        return;
    }

    selectedIds.value = selectedIds.value.filter((selectedId) => selectedId !== id);
};

const toggleCurrentPageSelection = (selected) => {
    if (selected) {
        if (!canSelectCurrentPage.value) return;
        selectedIds.value = [...new Set([...selectedIds.value, ...currentPageIds.value])];
        return;
    }

    clearSelection();
};

const clearSelection = () => {
    selectedIds.value = [];
};

const runBulkAction = (action) => {
    if (selectedIds.value.length === 0 || action.loading) return;
    emit('bulk-action', { key: action.key, ids: [...selectedIds.value] });
};

const selectableExportColumns = computed(() => {
    const configuredColumns = exportFormat.value === 'pdf'
        ? props.pdfExportColumns
        : props.exportColumns;

    return configuredColumns.length
        ? configuredColumns
        : props.columns.filter((column) => column.prop !== 'actions')
});
const allExportColumnsSelected = computed(() =>
    selectableExportColumns.value.length > 0
    && selectedExportColumns.value.length === selectableExportColumns.value.length
);
const exportColumnsIndeterminate = computed(() =>
    selectedExportColumns.value.length > 0 && !allExportColumnsSelected.value
);

const openExportDialog = (format = 'excel') => {
    exportFormat.value = format;
    selectedExportColumns.value = selectableExportColumns.value.map((column) => column.prop);
    exportDialogVisible.value = true;
};

const toggleAllExportColumns = (selected) => {
    selectedExportColumns.value = selected
        ? selectableExportColumns.value.map((column) => column.prop)
        : [];
};

const downloadExportFile = async () => {
    const exportService = exportFormat.value === 'pdf'
        ? props.pdfExportService
        : props.exportService;
    const fileName = exportFormat.value === 'pdf'
        ? props.pdfFileName
        : props.excelFileName;

    if (!exportService || selectedExportColumns.value.length === 0) return;

    exporting.value = true;
    try {
        const response = await exportService({
            columns: selectedExportColumns.value,
            ...(searchQuery.value.trim() ? { search: searchQuery.value.trim() } : {})
        });
        const fileUrl = URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(fileUrl), 1000);
        exportDialogVisible.value = false;
    } catch (error) {
        handleError(error);
    } finally {
        exporting.value = false;
    }
};

const goToPage = (page) => {
    if (
        loading.value
        || !Number.isInteger(page)
        || page < 1
        || page > pagination.value.lastPage
        || page === pagination.value.page
    ) return;

    pagination.value.page = page;
    fetchData();
}

const nextPage = () => goToPage(pagination.value.page + 1);

const previousPage = () => {
    goToPage(pagination.value.page - 1);
}

const paginationPages = computed(() => {
    const { page, lastPage } = pagination.value;

    if (lastPage <= 7) {
        return Array.from({ length: lastPage }, (_, index) => index + 1);
    }

    if (page <= 4) {
        return [1, 2, 3, 4, 5, 'ellipsis-right', lastPage];
    }

    if (page >= lastPage - 3) {
        return [1, 'ellipsis-left', lastPage - 4, lastPage - 3, lastPage - 2, lastPage - 1, lastPage];
    }

    return [1, 'ellipsis-left', page - 1, page, page + 1, 'ellipsis-right', lastPage];
});

const mobilePaginationPages = computed(() => {
    const { page, lastPage } = pagination.value;
    const start = Math.max(1, Math.min(page - 1, lastPage - 2));
    const end = Math.min(lastPage, start + 2);

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
});

const paginationText = computed(() => {
    if (pagination.value.total === 0) {
        return 'Showing 0 to 0 of 0';
    }

    const x = (pagination.value.page * pagination.value.perPage) - pagination.value.perPage + 1;

    const y = (pagination.value.perPage * pagination.value.page) >= pagination.value.total ? pagination.value.total : (pagination.value.perPage * pagination.value.page)

    const z = pagination.value.total;

    return `Showing ${x} to ${y} of ${z}`;
});

const fetchData = async () => {
    if (!props.apiService) return;
    const requestId = ++latestRequestId;
    const params = {
        page: pagination.value.page,
        per_page: pagination.value.perPage,
        ...(searchQuery.value.trim() ? { search: searchQuery.value.trim() } : {}),
        ...(sorting.value.sortBy && sorting.value.sortDirection
            ? {
                sort_by: sorting.value.sortBy,
                sort_direction: sorting.value.sortDirection
            }
            : {})
    };

    loading.value = true;
    try {
        const response = await props.apiService(params);
        if (requestId !== latestRequestId) return;

        if (response.data?.success) {
            const data = response.data?.data;
            tableData.value = data.items ?? [];
            pagination.value.total = data.total ?? 0;
            pagination.value.lastPage = data.last_page ?? 1;
        }
    } catch (e) {
        if (requestId === latestRequestId) {
            handleError(e);
        }
    } finally {
        if (requestId === latestRequestId) {
            loading.value = false;
        }
    }
}

watch(searchQuery, () => {
    clearTimeout(searchDebounceTimer);
    clearSelection();
    pagination.value.page = 1;
    searchDebounceTimer = setTimeout(() => {
        fetchData();
    }, 300);
});

const handleSortChange = ({ prop, order }) => {
    const column = props.columns.find((item) => item.prop === prop);
    if (order && !column?.sortable) return;

    sorting.value = {
        sortBy: order ? (column.sortBy ?? column.prop) : null,
        sortDirection: order === 'ascending' ? 'asc' : order === 'descending' ? 'desc' : null
    };
    clearSelection();
    pagination.value.page = 1;
    fetchData();
}

const calculateIndex = (index) => {
    return (pagination.value.page - 1) * pagination.value.perPage + index + 1
}

const skeletonRows = computed(() =>
    Array.from({ length: pagination.value.perPage }, (_, i) => ({ __skeleton: i }))
);

const displayData = computed(() => (loading.value ? skeletonRows.value : tableData.value));

const hasExplicitMobile = computed(() => props.columns.some(c => c.mobile));

const mobileColumns = computed(() =>
    props.columns
        .map((column, i) => ({
            ...column,
            role: column.mobile ?? (hasExplicitMobile.value ? 'detail' : (['title', 'subtitle', 'value', 'badge'][i] ?? 'detail'))
        }))
        .filter(column => column.role !== 'hidden')
);

const columnsByRole = (role) => mobileColumns.value.filter(column => column.role === role);

const getValue = (row, prop) => prop.split('.').reduce((obj, key) => obj?.[key], row) ?? '-';

watch(() => props.update, (val) => {
    if(val){
        clearSelection();
        fetchData();
    }
})

onMounted(() => {
    fetchData();
})

onBeforeUnmount(() => {
    clearTimeout(searchDebounceTimer);
})
</script>

<template>
    <Card :header_y_padding="false" :body_x_padding="false">
        <template #body>
            <div class="responsive-table-container w-full min-w-0">
                <div class="px-4 flex items-center justify-between flex-wrap gap-3">
                    <el-input v-model="searchQuery" clearable placeholder="Search records" size="small"
                        style="width: 200px;" :prefix-icon="MagnifyingGlassIcon" />

                    <div class="flex items-center justify-start md:justify-end flex-wrap gap-3">
                        <el-skeleton v-if="loading" animated>
                            <template #template>
                                <div class="flex items-center gap-3">
                                    <el-skeleton-item variant="rect"
                                        style="width: 90px; height: 24px; border-radius: 4px;" />
                                    <el-skeleton-item v-if="exportExcel" variant="rect"
                                        style="width: 110px; height: 24px; border-radius: 4px;" />
                                    <el-skeleton-item v-if="exportPDF" variant="rect"
                                        style="width: 100px; height: 24px; border-radius: 4px;" />
                                </div>
                            </template>
                        </el-skeleton>

                        <div v-show="!loading" class="flex items-center justify-start md:justify-end flex-wrap gap-3">
                            <slot name="addons"></slot>

                            <el-button size="small" type="primary" :icon="DocumentChartBarIcon"
                                v-if="exportExcel" @click="openExportDialog">Export
                                Excel</el-button>

                            <el-button size="small" type="danger" :icon="DocumentTextIcon" v-if="exportPDF"
                                @click="openExportDialog('pdf')">Export
                                PDF</el-button>
                        </div>
                    </div>
                </div>

                <div v-if="bulkActions.length" v-show="!loading"
                    class="mt-3 flex items-center justify-between gap-3 px-4">
                    <el-checkbox :model-value="allCurrentPageSelected"
                        :disabled="loading || bulkActionLoading || currentPageIds.length === 0 || (!allCurrentPageSelected && !canSelectCurrentPage)"
                        @change="toggleCurrentPageSelection">
                        Select all
                    </el-checkbox>
                    <div class="flex items-center justify-end gap-2">
                        <span v-if="!canSelectCurrentPage" class="text-xs text-gray-500">
                            Selection limit: {{ maxBulkSelection }} records
                        </span>
                        <el-button v-for="action in bulkActions" :key="action.key" size="small"
                            :type="action.type || 'default'" :icon="action.icon"
                            :loading="action.loading" :disabled="selectedIds.length === 0"
                            @click="runBulkAction(action)">
                            {{ action.label }}<span v-if="action.showSelectionCount" class="ml-1">{{ selectedIds.length }}</span>
                        </el-button>
                    </div>
                </div>

                <div class="responsive-table-desktop w-full min-w-0 mt-5">
                    <el-table class="app-table" :data="displayData" style="width: 100%" table-layout="fixed"
                        :show-header="true" :default-sort="props.defaultSort || {}" @sort-change="handleSortChange">
                        <template #empty>
                            <el-empty :description="emptyDescription" :image-size="80" />
                        </template>
                        <el-table-column v-if="bulkActions.length" width="52" align="center"
                            class-name="bulk-select-column">
                            <template #default="scope">
                                <el-skeleton v-if="loading" animated>
                                    <template #template>
                                        <el-skeleton-item variant="rect" style="width: 16px; height: 16px;" />
                                    </template>
                                </el-skeleton>
                                <el-checkbox v-else :model-value="isSelected(scope.row.id)"
                                    :disabled="bulkActionLoading || (!isSelected(scope.row.id) && selectedIds.length >= maxBulkSelection)"
                                    :aria-label="`Select record ${scope.row.id}`"
                                    @change="toggleSelection(scope.row.id, $event)" />
                            </template>
                        </el-table-column>

                        <el-table-column v-if="props.indexing" label="#" width="100">
                            <template #header>
                                <el-skeleton v-if="loading" animated>
                                    <template #template>
                                        <el-skeleton-item variant="text" style="width: 20px;" />
                                    </template>
                                </el-skeleton>
                                <span v-else>#</span>
                            </template>
                            <template #default="scope">
                                <el-skeleton v-if="loading" animated>
                                    <template #template>
                                        <el-skeleton-item variant="text" style="width: 24px;" />
                                    </template>
                                </el-skeleton>
                                <span v-else>{{ calculateIndex(scope.$index) }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column v-for="column in columns" :key="column.label" :label="column.label"
                            :prop="column.prop" :sortable="column.sortable ? 'custom' : false">
                            <template #header>
                                <el-skeleton v-if="loading" animated>
                                    <template #template>
                                        <el-skeleton-item variant="text" style="width: 60%;" />
                                    </template>
                                </el-skeleton>
                                <span v-else>{{ column.label }}</span>
                            </template>

                            <template #default="scope">
                                <el-skeleton v-if="loading" animated>
                                    <template #template>
                                        <el-skeleton-item variant="text" style="width: 80%;" />
                                    </template>
                                </el-skeleton>
                                <slot v-else :name="column.prop" :row="scope.row">
                                    {{column.prop.split('.').reduce((obj, key) => obj?.[key], scope.row) ?? '-'}}
                                </slot>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <div class="responsive-table-mobile divide-y divide-gray-100 dark:divide-[var(--app-divide)] px-3 mt-5">
                    <template v-if="loading">
                        <div v-for="n in 20" :key="'sk-' + n" class="py-3">
                            <el-skeleton animated>
                                <template #template>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex flex-1 flex-col gap-2">
                                            <el-skeleton-item variant="text" style="width: 50%;" />
                                            <el-skeleton-item variant="text" style="width: 30%;" />
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <el-skeleton-item variant="text" style="width: 70px;" />
                                            <el-skeleton-item variant="text" style="width: 50px;" />
                                        </div>
                                    </div>

                                </template>
                            </el-skeleton>
                        </div>
                    </template>

                    <template v-else>
                        <div v-for="(row, index) in tableData" :key="row.id ?? index" class="py-3 min-w-0">

                            <div class="flex min-w-0 items-start justify-between gap-2">
                                <el-checkbox v-if="bulkActions.length" :model-value="isSelected(row.id)"
                                    :disabled="bulkActionLoading || (!isSelected(row.id) && selectedIds.length >= maxBulkSelection)"
                                    :aria-label="`Select record ${row.id}`"
                                    @change="toggleSelection(row.id, $event)" />
                                <div class="min-w-0 flex-1">
                                    <div v-for="column in columnsByRole('title')" :key="'t-' + column.prop"
                                        class="truncate text-sm font-semibold">
                                        <span v-if="props.indexing"
                                            class="mr-1 text-xs font-normal text-gray-400 dark:text-[var(--el-text-color-secondary)]">
                                            {{ calculateIndex(index) }}.
                                        </span>
                                        <slot :name="column.prop" :row="row">{{ getValue(row, column.prop) }}</slot>
                                    </div>

                                    <div v-for="column in columnsByRole('subtitle')" :key="'s-' + column.prop"
                                        class="text-xs text-gray-500 dark:text-[var(--el-text-color-secondary)]">
                                        <slot :name="column.prop" :row="row">{{ getValue(row, column.prop) }}</slot>
                                    </div>
                                </div>

                                <div class="flex max-w-[55%] shrink-0 flex-col items-end gap-1">
                                    <div v-for="column in columnsByRole('value')" :key="'v-' + column.prop"
                                        class="max-w-full text-right text-sm font-semibold [overflow-wrap:anywhere]">
                                        <slot :name="column.prop" :row="row">{{ getValue(row, column.prop) }}</slot>
                                    </div>

                                    <div v-for="column in columnsByRole('badge')" :key="'b-' + column.prop">
                                        <slot :name="column.prop" :row="row">{{ getValue(row, column.prop) }}</slot>
                                    </div>
                                </div>
                            </div>

                            <div v-if="columnsByRole('detail').length || columnsByRole('actions').length"
                                class="mt-2 flex min-w-0 items-start justify-between gap-3">
                                <div v-if="columnsByRole('detail').length"
                                    class="grid min-w-0 flex-1 grid-cols-2 gap-x-3 gap-y-2">
                                    <div v-for="column in columnsByRole('detail')" :key="'d-' + column.prop"
                                        class="min-w-0">
                                        <p class="text-[11px] text-gray-500 dark:text-[var(--el-text-color-secondary)]">
                                            {{ column.label }}
                                        </p>
                                        <div class="text-sm [overflow-wrap:anywhere]">
                                            <slot :name="column.prop" :row="row">{{ getValue(row, column.prop) }}</slot>
                                        </div>
                                    </div>
                                </div>

                                <div v-for="column in columnsByRole('actions')" :key="'a-' + column.prop"
                                    class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                    <slot :name="column.prop" :row="row"></slot>
                                </div>
                            </div>
                        </div>

                        <div v-if="tableData.length === 0" class="py-4">
                            <el-empty :description="emptyDescription" :image-size="80" />
                        </div>
                    </template>
                </div>

                <div class="mt-4 flex flex-col items-center gap-3 px-4 sm:flex-row sm:justify-between">
                    <p class="text-center text-sm sm:text-left">{{ paginationText }}</p>
                    <div class="pagination hidden items-center justify-center gap-1 sm:flex"
                        v-if="pagination.total > 0">
                        <el-button size="small" @click="previousPage"
                            :disabled="loading || pagination.page === 1">Previous</el-button>
                        <template v-for="item in paginationPages" :key="item">
                            <span v-if="typeof item === 'string'"
                                  class="px-1 text-sm text-gray-500 dark:text-[var(--el-text-color-secondary)]"
                                aria-hidden="true">...</span>
                            <el-button v-else size="small" :type="pagination.page === item ? 'primary' : 'default'"
                                :aria-label="`Go to page ${item}`"
                                :aria-current="pagination.page === item ? 'page' : undefined"
                                :disabled="loading || pagination.page === item"
                                @click="goToPage(item)">{{ item }}</el-button>
                        </template>
                        <el-button size="small" @click="nextPage"
                            :disabled="loading || pagination.page === pagination.lastPage">Next</el-button>
                    </div>
                    <div class="pagination flex items-center justify-center gap-1 sm:hidden"
                        v-if="pagination.total > 0">
                        <el-button size="small" :icon="ChevronLeftIcon" aria-label="Previous page"
                            @click="previousPage" :disabled="loading || pagination.page === 1" />
                        <el-button v-for="page in mobilePaginationPages" :key="page" size="small"
                            :type="pagination.page === page ? 'primary' : 'default'"
                            :aria-label="`Go to page ${page}`"
                            :aria-current="pagination.page === page ? 'page' : undefined"
                            :disabled="loading || pagination.page === page"
                            @click="goToPage(page)">{{ page }}</el-button>
                        <el-button size="small" :icon="ChevronRightIcon" aria-label="Next page"
                            @click="nextPage" :disabled="loading || pagination.page === pagination.lastPage" />
                    </div>
                </div>
            </div>

            <el-dialog v-model="exportDialogVisible" :title="exportFormat === 'pdf' ? 'Export PDF' : 'Export Excel'"
                width="min(520px, calc(100vw - 32px))"
                :close-on-click-modal="!exporting" :close-on-press-escape="!exporting">
                <p class="mb-3 text-sm text-gray-600 dark:text-[var(--el-text-color-secondary)]">
                    Choose the columns to include in your {{ exportFormat === 'pdf' ? 'PDF report' : 'Excel file' }}.
                </p>
                <el-checkbox :model-value="allExportColumnsSelected" :indeterminate="exportColumnsIndeterminate"
                    @change="toggleAllExportColumns">Select all</el-checkbox>
                <el-checkbox-group v-model="selectedExportColumns"
                    class="export-column-options mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <el-checkbox v-for="column in selectableExportColumns" :key="column.prop"
                        :label="column.prop">{{ column.label }}</el-checkbox>
                </el-checkbox-group>
                <template #footer>
                    <div class="flex items-center gap-3 justify-end">
                        <el-button @click="exportDialogVisible = false" :disabled="exporting">Cancel</el-button>
                        <el-button type="primary" :loading="exporting"
                            :disabled="selectedExportColumns.length === 0 || !(exportFormat === 'pdf' ? pdfExportService : exportService)"
                            @click="downloadExportFile">Download {{ exportFormat === 'pdf' ? 'PDF' : 'Excel' }}</el-button>
                    </div>
                </template>
            </el-dialog>
        </template>
    </Card>
</template>


<style scoped>
.el-button+.el-button {
    margin: 0;
}

.app-table :deep(.bulk-select-column .cell) {
    padding: 0 !important;
    overflow: visible !important;
    text-overflow: clip !important;
}

.export-column-options :deep(.el-checkbox) {
    min-width: 0;
    height: auto;
    align-items: flex-start;
}

.export-column-options :deep(.el-checkbox__label) {
    min-width: 0;
    white-space: normal;
    overflow-wrap: anywhere;
}
</style>