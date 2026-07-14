<template>
    <el-skeleton animated>
        <template #template>
            <div class="border border-[var(--el-border-color)] rounded-2xl bg-white p-3">
                <div class="flex items-center gap-2">
                    <el-skeleton-item variant="circle" style="--el-skeleton-circle-size: 32px"/>
                    <el-skeleton-item variant="h3" style="width: 50%;" />
                </div>
                <div class="grid grid-cols-4">
                    <div class="col-span-3">
                        <div class="mt-3">
                            <el-skeleton-item variant="h1" style="width: 100%" />
                        </div>
                        <div class="mt-1 w-full">
                            <el-skeleton-item variant="p" style="width: 60%;" />
                        </div>
                    </div>
                    <div class="flex flex-col items-end justify-end">
                        <el-skeleton-item variant="circle" style="--el-skeleton-circle-size: 40px" />
                    </div>
                </div>
            </div>
        </template>
    </el-skeleton>
</template>