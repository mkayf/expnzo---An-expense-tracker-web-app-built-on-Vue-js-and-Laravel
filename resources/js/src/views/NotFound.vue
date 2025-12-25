<template>
  <div class="min-h-screen bg-[#f9fafb] flex items-center justify-center p-6 font-sans">
    <div class="max-w-2xl w-full text-center">
      <div class="relative mb-12">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-[#328e6e] opacity-10 blur-[100px] rounded-full"></div>
        
        <h1 class="text-9xl md:text-[12rem] font-extrabold tracking-tighter text-[#111827] relative">
          4<span class="text-[#328e6e]">0</span>4
        </h1>
        
        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-white px-4 py-1 rounded-full shadow-sm border border-gray-100">
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#374151]">Error Found</p>
        </div>
      </div>

      <div class="space-y-4 mb-10">
        <h2 class="text-4xl md:text-5xl font-bold text-[#111827] tracking-tight">
          Lost in Space?
        </h2>
        <p class="text-[#374151] text-lg md:text-xl max-w-lg mx-auto leading-relaxed">
          The page you are looking for doesn't exist or has been moved. Let's get you back on track with your expenses.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
        <button 
          @click="$router.go(-1)"
          class="w-full sm:w-auto px-10 py-4 bg-white border-2 border-[#374151]/10 text-[#374151] font-bold rounded-2xl hover:bg-[#374151] hover:text-white hover:border-[#374151] transition-all duration-300 active:scale-95"
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