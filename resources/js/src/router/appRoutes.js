export default [
    {
        path: '/app/dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        alias: '/app',
        meta: {
            requiresAuth: true,
        }
    },
    {
        path: 'income',
        name: 'Income',
        component: () => import('../views/Income.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Income'
        }
    },
    {
        path: 'expenses',
        name: 'Expenses',
        component: () => import('../views/Expenses.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Expenses'
        }
    },
    {
        path: 'categories',
        name: 'Categories',
        component: () => import('../views/Categories.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Categories'
        }
    },
    {
        path: 'reports',
        name: 'Reports',
        component: () => import('../views/Reports.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Reports'
        }
    },
    {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/Settings.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Settings'
        }
    },
    {
        path: 'profile',
        name: 'Profile',
        component: () => import('../views/Profile.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Profile'
        }
    },
]