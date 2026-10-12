<script setup>
import ThemeToggle from "../components/ui/ThemeToggle.vue";
</script>

<template>
  <div class="relative min-h-screen bg-[#f9fafb] dark:bg-[var(--app-page-bg)] flex items-center justify-center p-6 font-sans">
    <ThemeToggle class="absolute top-4 right-4 bg-white dark:bg-[var(--app-surface)] border border-gray-100 dark:border-[var(--el-border-color)] shadow-sm" />
    <div class="max-w-2xl w-full text-center">
      <div class="relative mb-12">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-[#328e6e] opacity-10 blur-[100px] rounded-full"></div>
        
        <h1 class="text-9xl md:text-[12rem] font-extrabold tracking-tighter text-[#111827] dark:text-[var(--text-charcoal)] relative">
          4<span class="text-[#328e6e]">0</span>4
        </h1>
        
        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-white dark:bg-[var(--app-surface)] px-4 py-1 rounded-full shadow-sm border border-gray-100 dark:border-[var(--el-border-color)]">
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#374151] dark:text-[var(--el-text-color-regular)]">Error Found</p>
        </div>
      </div>

      <div class="space-y-4 mb-10">
        <h2 class="text-4xl md:text-5xl font-bold text-[#111827] dark:text-[var(--text-charcoal)] tracking-tight">
          Lost in Space?
        </h2>
        <p class="text-[#374151] dark:text-[var(--el-text-color-regular)] text-lg md:text-xl max-w-lg mx-auto leading-relaxed">
          The page you are looking for doesn't exist or has been moved. Let's get you back on track with your expenses.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
        <button 
          @click="$router.go(-1)"
          class="w-full sm:w-auto px-10 py-4 bg-white dark:bg-[var(--app-surface)] border-2 border-[#374151]/10 dark:border-[var(--el-border-color)] text-[#374151] dark:text-[var(--text-charcoal)] font-bold rounded-2xl hover:bg-[#374151] hover:text-white hover:border-[#374151] dark:hover:bg-[var(--el-color-primary)] dark:hover:border-[var(--el-color-primary)] transition-all duration-300 active:scale-95"
        >
          Go Back
        </button>
      </div>

    </div>
  </div>
</template>

<style scoped>
.min-h-screen {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>