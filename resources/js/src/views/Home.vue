<script setup>
import { computed } from 'vue'
import useAuthStore from '../stores/auth'
import useThemeStore from '../stores/theme'

// NOTE: adjust these two paths if Home.vue does not sit one level below resources/js/src
import lightLogo from '../assets/logo/expnzo-light-logo.png'
import darkLogo from '../assets/logo/expnzo-dark-logo.png'
const authStore = useAuthStore()
const themeStore = useThemeStore()

/* ------------------------------------------------------------------ */
/* Routes                                                              */
/* ------------------------------------------------------------------ */
const routes = {
    login: '/auth/login',
    register: '/auth/register', // change if your register url is different
    dashboard: '/app/dashboard',
}

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isDark = computed(() => themeStore.isDark)
const setTheme = (dark) => themeStore.setTheme(dark ? 'dark' : 'light')
const toggleTheme = () => themeStore.toggle()

/* ------------------------------------------------------------------ */
/* Navigation                                                          */
/* ------------------------------------------------------------------ */
const navLinks = [
    { label: 'Features', id: 'features' },
    { label: 'How it works', id: 'how-it-works' },
    { label: 'Reports', id: 'reports' },
]

const scrollToSection = (id) => {
    const el = document.getElementById(id)
    if (!el) return
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' })
}

const year = new Date().getFullYear()

/* ------------------------------------------------------------------ */
/* Shared class sets                                                   */
/* ------------------------------------------------------------------ */
const container = 'mx-auto w-full max-w-6xl px-6 lg:px-8'
const sectionCls = 'scroll-mt-20 py-24 sm:py-32 lg:py-40'
const h2Cls = 'text-3xl font-semibold leading-[1.15] tracking-[-0.03em] sm:text-4xl lg:text-[2.6rem]'
const leadCls = 'text-base leading-8 text-(color:--lp-body)! sm:text-lg'
const cardCls = 'rounded-2xl border border-(color:--lp-line) bg-(--lp-surface)'
const focusCls = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-(color:--lp-green)'
const btnPrimary = `inline-flex h-12 items-center justify-center rounded-full bg-[#29755a] px-7 text-[15px] font-semibold text-white transition-colors hover:bg-[#246b52] ${focusCls}`
const btnSecondary = `inline-flex h-12 items-center justify-center rounded-full border border-(color:--lp-line-strong) bg-(--lp-surface) px-7 text-[15px] font-semibold text-(color:--lp-ink) transition-colors hover:bg-(--lp-soft) ${focusCls}`

/* ------------------------------------------------------------------ */
/* Icons (stroke paths)                                                */
/* ------------------------------------------------------------------ */
const icons = {
    sun: ['M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10z', 'M12 2v2', 'M12 20v2', 'M4.93 4.93l1.41 1.41', 'M17.66 17.66l1.41 1.41', 'M2 12h2', 'M20 12h2', 'M6.34 17.66l-1.41 1.41', 'M19.07 4.93l-1.41 1.41'],
    moon: ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z'],
    download: ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4', 'M7 10l5 5 5-5', 'M12 15V3'],
    user: ['M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2', 'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
    coins: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z', 'M14.5 9.5c-.4-.9-1.4-1.5-2.5-1.5-1.4 0-2.5.8-2.5 2s1 1.7 2.5 2 2.5.8 2.5 2-1.1 2-2.5 2c-1.1 0-2.1-.6-2.5-1.5', 'M12 6.5V8', 'M12 16v1.5'],
    palette: ['M12 3a9 9 0 1 0 0 18c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.3-.5-.8-.5-1.2 0-1.1.9-2 2-2h2.4a3.6 3.6 0 0 0 3.6-3.6C21 6.6 17 3 12 3z'],
}

/* ------------------------------------------------------------------ */
/* Sample data used by the visual previews                             */
/* ------------------------------------------------------------------ */
const sidebarItems = ['Dashboard', 'Transactions', 'Categories', 'Reports', 'Settings']

const stats = [
    { label: 'Total balance', value: '31,500.00' },
    { label: 'Income this month', value: '55,300.00' },
    { label: 'Expenses this month', value: '23,800.00' },
    { label: 'Monthly budget', value: '30,000.00', note: '79.33% used' },
]

const chartMonths = [
    { m: 'Jun', income: 42, expense: 30 },
    { m: 'Jul', income: 58, expense: 44 },
    { m: 'Aug', income: 36, expense: 28 },
    { m: 'Sep', income: 64, expense: 48 },
    { m: 'Oct', income: 50, expense: 40 },
    { m: 'Nov', income: 100, expense: 43 },
]

const breakdown = [
    { name: 'Utilities', pct: '42.0%', color: '#b45309' },
    { name: 'Shopping', pct: '25.2%', color: '#d97706' },
    { name: 'Kids & Family', pct: '14.8%', color: '#e8890c' },
    { name: 'Entertainment', pct: '10.0%', color: '#f59e0b' },
    { name: 'Food & Dining', pct: '8.0%', color: '#fbbf24' },
]
const donutGradient =
    'conic-gradient(#b45309 0 42%, #d97706 42% 67.2%, #e8890c 67.2% 82%, #f59e0b 82% 92%, #fbbf24 92% 100%)'

const sampleTransactions = [
    { name: 'Salary', category: 'Salary', date: 'Nov 1', amount: '+PKR 55,300', income: true },
    { name: 'Electricity bill', category: 'Utilities', date: 'Nov 4', amount: '-PKR 6,800' },
    { name: 'Weekly groceries', category: 'Food & Dining', date: 'Nov 6', amount: '-PKR 4,250' },
    { name: 'Bus pass', category: 'Transportation', date: 'Nov 8', amount: '-PKR 1,200' },
]

const categoryChips = ['Food & Dining', 'Transportation', 'Utilities', 'Salary', 'Freelance Projects', 'Health & Fitness']

const calendarLevels = [0, 1, 0, 2, 1, 0, 0, 1, 3, 1, 0, 2, 4, 1, 0, 1, 2, 1, 0, 3, 1, 2, 0, 1, 4, 1, 0, 1, 1, 2, 0, 1, 3, 0, 0]
const levelCls = [
    'bg-(--lp-soft)',
    'bg-[#328e6e]/25',
    'bg-[#328e6e]/45',
    'bg-[#328e6e]/70',
    'bg-[#328e6e]',
]

const insights = [
    'Food & Dining is the category you spend on most often',
    'Small purchases added up to a noticeable share of this month',
    'Your highest spending falls on the same days each week',
]

const reportFilters = ['Last 6 months', 'All transactions', 'All categories']
const reportStats = [
    { label: 'Net cash flow', value: 'PKR 31,500' },
    { label: 'Savings rate', value: '57.0%' },
    { label: 'Avg. monthly expenses', value: 'PKR 23,800' },
]

const steps = [
    { title: 'Record it', text: 'Add a transaction with its type, amount, date, and an optional note.' },
    { title: 'Sort it', text: 'Pick a starter category or create your own so every entry has a clear place.' },
    { title: 'Review it', text: 'Open the dashboard and reports to see your balance, budget, and spending patterns.' },
]

const personalize = [
    { icon: 'user', title: 'Your profile', text: 'Set your name and avatar, and update your password whenever you need to.' },
    { icon: 'coins', title: 'Currency preference', text: 'Choose the currency you track in. Expnzo does not convert amounts between currencies.' },
    { icon: 'palette', title: 'Light and dark theme', text: 'Pick the look that is easiest on your eyes, day or night.' },
]
</script>

<template>
    <div class="lp min-h-screen overflow-x-clip bg-(--lp-bg) text-(color:--lp-ink) antialiased">
        <!-- ============================================================ -->
        <!-- Navbar                                                        -->
        <!-- ============================================================ -->
        <header class="sticky top-0 z-50 border-b border-(color:--lp-line) bg-(--lp-bg)/80 backdrop-blur-md">
            <nav :class="[container, 'flex h-16 items-center justify-between gap-4']" aria-label="Main">
                <router-link to="/" class="flex shrink-0 items-center" aria-label="Expnzo home">
                    <img :src="lightLogo" alt="Expnzo" class="h-7 w-auto dark:hidden" />
                    <img :src="darkLogo" alt="Expnzo" class="hidden h-7 w-auto dark:block" />
                </router-link>

                <ul class="hidden items-center gap-1 md:flex">
                    <li v-for="link in navLinks" :key="link.id">
                        <button
                            type="button"
                            :class="['rounded-full px-4 py-2 text-sm font-medium text-(color:--lp-body) transition-colors hover:bg-(--lp-soft) hover:text-(color:--lp-ink)', focusCls]"
                            @click="scrollToSection(link.id)"
                        >
                            {{ link.label }}
                        </button>
                    </li>
                </ul>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button
                        type="button"
                        :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                        :class="['inline-flex size-9 items-center justify-center rounded-full border border-(color:--lp-line-strong) bg-(--lp-surface) text-(color:--lp-ink) transition-colors hover:bg-(--lp-soft)', focusCls]"
                        @click="toggleTheme"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path v-for="d in isDark ? icons.sun : icons.moon" :key="d" :d="d" />
                        </svg>
                    </button>

                    <template v-if="isAuthenticated">
                        <router-link
                            :to="routes.dashboard"
                            :class="['inline-flex h-9 items-center rounded-full bg-[#29755a] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#246b52]', focusCls]"
                        >
                            Dashboard
                        </router-link>
                    </template>
                    <template v-else>
                        <router-link
                            :to="routes.login"
                            :class="['inline-flex h-9 items-center rounded-full border border-(color:--lp-line-strong) bg-(--lp-surface) px-4 text-sm font-semibold text-(color:--lp-ink) transition-colors hover:bg-(--lp-soft)', focusCls]"
                        >
                            Log in
                        </router-link>

                        <router-link
                            :to="routes.register"
                            :class="['hidden h-9 items-center rounded-full bg-[#29755a] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#246b52] sm:inline-flex', focusCls]"
                        >
                            Get started
                        </router-link>
                    </template>
                </div>
            </nav>
        </header>

        <main>
            <!-- ============================================================ -->
            <!-- Hero                                                          -->
            <!-- ============================================================ -->
            <section class="relative pt-20 sm:pt-28 lg:pt-32">
                <!-- soft mesh background -->
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-[640px] bg-[radial-gradient(40%_50%_at_28%_30%,rgba(50,142,110,0.22),transparent),radial-gradient(34%_45%_at_72%_22%,rgba(59,130,246,0.14),transparent),radial-gradient(30%_40%_at_55%_62%,rgba(245,158,11,0.13),transparent)] [mask-image:linear-gradient(to_bottom,black_55%,transparent)] dark:opacity-70"
                    aria-hidden="true"
                ></div>

                <div :class="[container, 'relative']">
                    <div class="mx-auto max-w-3xl text-center">
                        <h1 class="text-[2.6rem] font-semibold leading-[1.08] tracking-[-0.04em] sm:text-6xl lg:text-[4.25rem]">
                            Know where your money goes.
                        </h1>
                        <p :class="[leadCls, 'mx-auto mt-8 max-w-xl']">
                            Track income and expenses, set a monthly budget, and make sense of your spending in one personal finance dashboard.
                        </p>

                        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                            <router-link :to="isAuthenticated ? routes.dashboard : routes.register" :class="[btnPrimary, 'w-full sm:w-auto']">
                                {{ isAuthenticated ? 'Dashboard' : 'Create your account' }}
                            </router-link>
                            <button type="button" :class="[btnSecondary, 'w-full sm:w-auto']" @click="scrollToSection('how-it-works')">
                                See how it works
                            </button>
                        </div>
                        <p v-if="!isAuthenticated" class="mt-5 text-sm text-(color:--lp-mute)!">Sign up with your email or continue with Google.</p>
                    </div>

                    <!-- Dashboard preview built with markup, so it follows the theme -->
                    <div class="relative mx-auto mt-16 max-w-5xl sm:mt-20" role="img" aria-label="Preview of the Expnzo dashboard showing balance, income, expenses, budget and charts using sample data">
                        <div
                            class="relative max-h-[430px] overflow-hidden rounded-2xl border border-(color:--lp-line) bg-(--lp-surface) shadow-[0_40px_90px_-40px_rgba(17,24,39,0.35)] [mask-image:linear-gradient(to_bottom,black_68%,transparent)] sm:max-h-[520px] dark:shadow-[0_40px_90px_-40px_rgba(0,0,0,0.9)]"
                            aria-hidden="true"
                        >
                            <div class="flex">
                                <!-- sidebar -->
                                <div class="hidden w-44 shrink-0 border-r border-(color:--lp-line) p-4 md:block">
                                    <img :src="lightLogo" alt="" class="mb-6 h-5 w-auto dark:hidden" />
                                    <img :src="darkLogo" alt="" class="mb-6 hidden h-5 w-auto dark:block" />
                                    <ul class="space-y-1">
                                        <li
                                            v-for="(item, i) in sidebarItems"
                                            :key="item"
                                            :class="['rounded-md px-3 py-2 text-xs font-medium', i === 0 ? 'bg-(--lp-green-soft) text-(color:--lp-green-text)' : 'text-(color:--lp-body)']"
                                        >
                                            {{ item }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- main -->
                                <div class="min-w-0 flex-1 p-4 sm:p-6">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold">Welcome back</span>
                                        <span class="rounded-md bg-[#29755a] px-3 py-1.5 text-[11px] font-semibold text-white">Add transaction</span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                                        <div v-for="s in stats" :key="s.label" class="rounded-xl border border-(color:--lp-line) bg-(--lp-bg) p-3 sm:p-4">
                                            <span class="block text-[11px] font-medium text-(color:--lp-mute)">{{ s.label }}</span>
                                            <span class="mt-2 block text-sm font-semibold sm:text-lg">
                                                <span class="text-[11px] font-medium text-(color:--lp-mute) sm:text-xs">PKR</span> {{ s.value }}
                                            </span>
                                            <span v-if="s.note" class="mt-1 block text-[10px] font-medium text-amber-600 dark:text-amber-400">{{ s.note }}</span>
                                            <span v-else class="mt-1 block text-[10px]">&nbsp;</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 grid gap-3 lg:grid-cols-5">
                                        <div class="rounded-xl border border-(color:--lp-line) p-4 lg:col-span-3">
                                            <span class="text-[11px] font-semibold">Income vs expense, last 6 months</span>
                                            <div class="mt-4 flex h-32 items-end justify-between gap-2 sm:gap-4">
                                                <div v-for="c in chartMonths" :key="c.m" class="flex flex-1 flex-col items-center gap-2">
                                                    <div class="flex h-24 items-end gap-1">
                                                        <div class="w-2.5 rounded-t-sm bg-[#328e6e] sm:w-4" :style="{ height: c.income + '%' }"></div>
                                                        <div class="w-2.5 rounded-t-sm bg-[#fbbf24] sm:w-4" :style="{ height: c.expense + '%' }"></div>
                                                    </div>
                                                    <span class="text-[10px] text-(color:--lp-mute)">{{ c.m }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-(color:--lp-line) p-4 lg:col-span-2">
                                            <span class="text-[11px] font-semibold">Expense by category</span>
                                            <div class="mt-4 flex items-center gap-4">
                                                <div class="relative size-24 shrink-0 rounded-full" :style="{ background: donutGradient }">
                                                    <div class="absolute inset-[22%] rounded-full bg-(--lp-surface)"></div>
                                                </div>
                                                <ul class="space-y-1.5">
                                                    <li v-for="b in breakdown" :key="b.name" class="flex items-center gap-2 text-[10px] text-(color:--lp-body)">
                                                        <span class="size-2 rounded-full" :style="{ background: b.color }"></span>
                                                        {{ b.name }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-center text-xs text-(color:--lp-mute)!">Preview with sample data.</p>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- Problem                                                       -->
            <!-- ============================================================ -->
            <section :class="sectionCls">
                <div :class="container">
                    <div class="grid gap-10 lg:grid-cols-2 lg:gap-20">
                        <h2 :class="[h2Cls, 'max-w-md']">Small spends add up before you notice them.</h2>
                        <div class="space-y-6">
                            <p :class="leadCls">
                                Rent, groceries, a freelance payment, a coffee on the way to work. When these live in different places, it is hard to say what you earned, what you spent, and what is left.
                            </p>
                            <p :class="leadCls">
                                Expnzo keeps every transaction in one place. You enter what happened. Expnzo sorts it, adds it up, and shows you the picture.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- Features                                                      -->
            <!-- ============================================================ -->
            <section id="features" :class="[sectionCls, 'border-t border-(color:--lp-line) bg-(--lp-surface)']">
                <div :class="container">
                    <div class="max-w-2xl">
                        <h2 :class="h2Cls">Everything you need to follow your money.</h2>
                        <p :class="[leadCls, 'mt-6 max-w-xl']">
                            Record transactions, organize them, and watch your dashboard update as you go.
                        </p>
                    </div>

                    <div class="mt-16 grid gap-5 lg:mt-20 lg:grid-cols-6 lg:gap-6">
                        <!-- Transactions -->
                        <article :class="[cardCls, 'bg-(--lp-bg)! p-8 sm:p-10 lg:col-span-4']">
                            <h3 class="text-xl font-semibold tracking-[-0.02em]">Add income and expenses in seconds</h3>
                            <p :class="[leadCls, 'mt-4 max-w-lg text-base!']">
                                Enter the amount, date, category, and an optional note. Edit or delete any entry later, search and sort your list, and clear several transactions at once.
                            </p>
                            <ul class="mt-10 divide-y divide-(--lp-line) rounded-xl border border-(color:--lp-line) bg-(--lp-surface)" aria-label="Sample transactions">
                                <li v-for="t in sampleTransactions" :key="t.name" class="flex items-center justify-between gap-4 px-5 py-4">
                                    <div class="min-w-0">
                                        <span class="block truncate text-sm font-semibold">{{ t.name }}</span>
                                        <span class="mt-0.5 block text-xs text-(color:--lp-mute)">{{ t.category }}, {{ t.date }}</span>
                                    </div>
                                    <span :class="['shrink-0 text-sm font-semibold', t.income ? 'text-(color:--lp-green-text)' : 'text-(color:--lp-ink)']">{{ t.amount }}</span>
                                </li>
                            </ul>
                        </article>

                        <!-- Categories -->
                        <article :class="[cardCls, 'p-8 sm:p-10 lg:col-span-2']">
                            <h3 class="text-xl font-semibold tracking-[-0.02em]">Categories that fit your life</h3>
                            <p :class="[leadCls, 'mt-4 text-base!']">
                                Start with ready-made income and expense categories, or add your own.
                            </p>
                            <ul class="mt-10 flex flex-wrap gap-2">
                                <li v-for="c in categoryChips" :key="c" class="rounded-full border border-(color:--lp-line-strong) bg-(--lp-bg) px-3.5 py-1.5 text-xs font-medium text-(color:--lp-body)">
                                    {{ c }}
                                </li>
                            </ul>
                        </article>

                        <!-- Budget -->
                        <article :class="[cardCls, 'p-8 sm:p-10 lg:col-span-2']">
                            <h3 class="text-xl font-semibold tracking-[-0.02em]">One clear monthly limit</h3>
                            <p :class="[leadCls, 'mt-4 text-base!']">
                                Set your spending limit for the month and see what you have used and what remains. Reuse last month's amount in one click.
                            </p>
                            <div class="mt-10 rounded-xl border border-(color:--lp-line) bg-(--lp-bg) p-5">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs font-medium text-(color:--lp-mute)">November budget</span>
                                    <span class="text-sm font-semibold">PKR 30,000</span>
                                </div>
                                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-(--lp-soft)">
                                    <div class="h-full w-[79.33%] rounded-full bg-amber-500"></div>
                                </div>
                                <div class="mt-3 flex justify-between text-xs text-(color:--lp-body)">
                                    <span>PKR 23,800 used</span>
                                    <span>PKR 6,200 left</span>
                                </div>
                            </div>
                        </article>

                        <!-- Patterns -->
                        <article :class="[cardCls, 'bg-(--lp-bg)! p-8 sm:p-10 lg:col-span-4']">
                            <h3 class="text-xl font-semibold tracking-[-0.02em]">See your spending patterns</h3>
                            <p :class="[leadCls, 'mt-4 max-w-lg text-base!']">
                                Break expenses down by category for any period from this month to the last 12 months. A monthly calendar shows what you spent each day, and insights built from your own records point out spikes and habits.
                            </p>
                            <div class="mt-10 grid items-center gap-8 sm:grid-cols-2">
                                <div class="grid grid-cols-7 gap-1.5" role="img" aria-label="Sample calendar where darker days mean more spending">
                                    <span v-for="(level, i) in calendarLevels" :key="i" :class="['aspect-square rounded-md', levelCls[level]]"></span>
                                </div>
                                <ul class="space-y-4">
                                    <li v-for="line in insights" :key="line" class="flex gap-3 text-sm leading-6 text-(color:--lp-body)">
                                        <svg class="mt-1 size-4 shrink-0 text-(color:--lp-green-text)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                        {{ line }}
                                    </li>
                                </ul>
                            </div>
                            <p class="mt-8 text-xs text-(color:--lp-mute)!">Insights are generated from your recorded transactions using simple rules.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- Reports (inverted band)                                       -->
            <!-- ============================================================ -->
            <section id="reports" class="scroll-mt-20 bg-(--lp-band) py-24 text-(color:--lp-band-fg) sm:py-32 lg:py-40">
                <div :class="container">
                    <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
                        <div>
                            <h2 :class="[h2Cls, 'max-w-md']">Reports for the bigger picture.</h2>
                            <p class="mt-6 max-w-lg text-base leading-8 text-(color:--lp-band-mute)! sm:text-lg">
                                Filter by the last 3, 6, or 12 months or a custom date range, by income or expenses, and by category. See net cash flow, savings rate, and how this period compares with the one before.
                            </p>
                            <p class="mt-6 max-w-lg text-base leading-8 text-(color:--lp-band-mute)! sm:text-lg">
                                Export what you see to Excel or PDF, with your filters applied.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-(color:--lp-band-line) bg-white/5 p-6 sm:p-8" role="img" aria-label="Preview of a report with filters, key figures and export buttons using sample data">
                            <div class="flex flex-wrap gap-2" aria-hidden="true">
                                <span v-for="f in reportFilters" :key="f" class="rounded-full border border-(color:--lp-band-line) px-3.5 py-1.5 text-xs font-medium text-(color:--lp-band-mute)">{{ f }}</span>
                            </div>
                            <div class="mt-8 grid gap-6 sm:grid-cols-3" aria-hidden="true">
                                <div v-for="s in reportStats" :key="s.label">
                                    <span class="block text-xs text-(color:--lp-band-mute)">{{ s.label }}</span>
                                    <span class="mt-2 block text-lg font-semibold">{{ s.value }}</span>
                                </div>
                            </div>
                            <div class="mt-8 flex h-24 items-end gap-2 sm:gap-3" aria-hidden="true">
                                <span v-for="c in chartMonths" :key="c.m" class="flex-1 rounded-t-md bg-[#328e6e]" :style="{ height: c.income + '%', opacity: 0.45 + c.income / 200 }"></span>
                            </div>
                            <div class="mt-8 flex flex-wrap gap-3" aria-hidden="true">
                                <span class="inline-flex h-10 items-center gap-2 rounded-full bg-white px-5 text-sm font-semibold text-[#0f1714]">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path v-for="d in icons.download" :key="d" :d="d" /></svg>
                                    Export Excel
                                </span>
                                <span class="inline-flex h-10 items-center gap-2 rounded-full border border-(color:--lp-band-line) px-5 text-sm font-semibold">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path v-for="d in icons.download" :key="d" :d="d" /></svg>
                                    Export PDF
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- How it works                                                  -->
            <!-- ============================================================ -->
            <section id="how-it-works" :class="sectionCls">
                <div :class="container">
                    <div class="max-w-2xl">
                        <h2 :class="h2Cls">Three steps, then it does the math.</h2>
                        <p :class="[leadCls, 'mt-6 max-w-xl']">
                            You stay in control of what goes in. Expnzo keeps your totals, budget, and charts up to date.
                        </p>
                    </div>

                    <ol class="mt-16 grid gap-12 lg:mt-20 lg:grid-cols-3 lg:gap-10">
                        <li v-for="(step, i) in steps" :key="step.title" class="relative">
                            <span class="flex size-10 items-center justify-center rounded-full border border-(color:--lp-line-strong) bg-(--lp-surface) text-sm font-semibold">{{ i + 1 }}</span>
                            <h3 class="mt-6 text-xl font-semibold tracking-[-0.02em]">{{ step.title }}</h3>
                            <p class="mt-3 max-w-xs text-base leading-7 text-(color:--lp-body)!">{{ step.text }}</p>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- Personalize                                                   -->
            <!-- ============================================================ -->
            <section :class="[sectionCls, 'border-t border-(color:--lp-line) bg-(--lp-surface)']">
                <div :class="container">
                    <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-20">
                        <div>
                            <h2 :class="[h2Cls, 'max-w-md']">Make it yours.</h2>
                            <ul class="mt-12 space-y-10">
                                <li v-for="item in personalize" :key="item.title" class="flex gap-5">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-(--lp-green-soft) text-(color:--lp-green-text)">
                                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path v-for="d in icons[item.icon]" :key="d" :d="d" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-semibold tracking-[-0.01em]">{{ item.title }}</h3>
                                        <p class="mt-2 max-w-sm text-base leading-7 text-(color:--lp-body)!">{{ item.text }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- live theme demo -->
                        <div :class="[cardCls, 'bg-(--lp-bg)! p-8 sm:p-10']">
                            <h3 class="text-xl font-semibold tracking-[-0.02em]">Try the theme</h3>
                            <p class="mt-3 text-base leading-7 text-(color:--lp-body)!">Switch between light and dark and watch this page change.</p>
                            <div class="mt-8 inline-flex rounded-full border border-(color:--lp-line-strong) bg-(--lp-surface) p-1" role="group" aria-label="Theme">
                                <button
                                    type="button"
                                    :aria-pressed="!isDark"
                                    :class="['inline-flex h-10 items-center gap-2 rounded-full px-5 text-sm font-semibold transition-colors', !isDark ? 'bg-[#29755a] text-white' : 'text-(color:--lp-body) hover:text-(color:--lp-ink)', focusCls]"
                                    @click="setTheme(false)"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-for="d in icons.sun" :key="d" :d="d" /></svg>
                                    Light
                                </button>
                                <button
                                    type="button"
                                    :aria-pressed="isDark"
                                    :class="['inline-flex h-10 items-center gap-2 rounded-full px-5 text-sm font-semibold transition-colors', isDark ? 'bg-[#29755a] text-white' : 'text-(color:--lp-body) hover:text-(color:--lp-ink)', focusCls]"
                                    @click="setTheme(true)"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-for="d in icons.moon" :key="d" :d="d" /></svg>
                                    Dark
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- Final call to action                                          -->
            <!-- ============================================================ -->
            <section class="relative py-24 sm:py-32 lg:py-40">
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(40%_60%_at_50%_100%,rgba(50,142,110,0.2),transparent)]"
                    aria-hidden="true"
                ></div>
                <div :class="[container, 'relative']">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 :class="h2Cls">Start tracking your money today.</h2>
                        <p :class="[leadCls, 'mx-auto mt-6 max-w-lg']">
                            Create an account, add your first transaction, and watch your dashboard fill in.
                        </p>
                        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                            <template v-if="isAuthenticated">
                                <router-link :to="routes.dashboard" :class="[btnPrimary, 'w-full sm:w-auto']">Dashboard</router-link>
                            </template>
                            <template v-else>
                                <router-link :to="routes.register" :class="[btnPrimary, 'w-full sm:w-auto']">Create your account</router-link>
                                <router-link :to="routes.login" :class="[btnSecondary, 'w-full sm:w-auto']">Log in</router-link>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- ============================================================ -->
        <!-- Footer                                                        -->
        <!-- ============================================================ -->
        <footer class="border-t border-(color:--lp-line) py-16">
            <div :class="[container, 'flex flex-col gap-10 md:flex-row md:items-start md:justify-between']">
                <div class="max-w-xs">
                    <router-link to="/" class="inline-flex" aria-label="Expnzo home">
                        <img :src="lightLogo" alt="Expnzo" class="h-7 w-auto dark:hidden" />
                        <img :src="darkLogo" alt="Expnzo" class="hidden h-7 w-auto dark:block" />
                    </router-link>
                    <p class="mt-5 text-sm leading-6 text-(color:--lp-body)!">
                        A personal finance tracker for income, expenses, and monthly budgets.
                    </p>
                </div>

                <ul class="flex flex-wrap gap-x-8 gap-y-4 text-sm font-medium text-(color:--lp-body)">
                    <li v-for="link in navLinks" :key="link.id">
                        <button type="button" :class="['transition-colors hover:text-(color:--lp-ink)', focusCls]" @click="scrollToSection(link.id)">{{ link.label }}</button>
                    </li>
                    <li>
                        <router-link
                            :to="isAuthenticated ? routes.dashboard : routes.login"
                            :class="['transition-colors hover:text-(color:--lp-ink)', focusCls]"
                        >
                            {{ isAuthenticated ? 'Dashboard' : 'Log in' }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div :class="[container, 'mt-12']">
                <p class="text-xs text-(color:--lp-mute)!">&copy; {{ year }} Expnzo. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style>
/* Landing page tokens. They follow the same `html.dark` switch as app.css. */
.lp {
    --lp-bg: #f9fafb;
    --lp-surface: #ffffff;
    --lp-soft: #f3f4f6;
    --lp-ink: #111827;
    --lp-body: #4b5563;
    --lp-mute: #6b7280;
    --lp-line: #e5e7eb;
    --lp-line-strong: #d1d5db;
    --lp-green: #328e6e;
    --lp-green-text: #287258;
    --lp-green-soft: #ecf5f2;
    --lp-band: #0f1714;
    --lp-band-fg: #f3f6f4;
    --lp-band-mute: #a9b7b0;
    --lp-band-line: rgba(255, 255, 255, 0.14);
    font-family: "Montserrat", sans-serif;
}

html.dark .lp {
    --lp-bg: #0d1210;
    --lp-surface: #161c19;
    --lp-soft: #222b27;
    --lp-ink: #e8eee9;
    --lp-body: #b7c2bd;
    --lp-mute: #9aa8a0;
    --lp-line: #2a3530;
    --lp-line-strong: #34423c;
    --lp-green: #328e6e;
    --lp-green-text: #5ba58e;
    --lp-green-soft: rgba(50, 142, 110, 0.16);
    --lp-band: #141b18;
}

/* app.css sets a global color on h1-h6 and p, this lets them inherit from the page instead */
.lp :is(h1, h2, h3, h4, p) {
    color: inherit;
}
</style>
