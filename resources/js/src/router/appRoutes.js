export default [
    {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
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
        path: 'account',
        name: 'Account',
        component: () => import('../views/Account/Account.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Account'
        },
        children: [
            {
                path: 'profile',
                name: 'Profile',
                component: () => import('../views/Account/components/Profile.vue')
            },
            {
                path: 'password',
                name: 'Password',
                component: () => import('../views/Account/components/Password.vue')
            },
            {
                path: 'preferences',
                name: 'Preferences',
                component: () => import('../views/Account/components/Preferences.vue')
            },
        ]
    },
]