export default [
    {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard/Dashboard.vue'),
        meta: {
            requiresAuth: true,
        }
    },
    {
        path: 'transactions',
        name: 'Transactions',
        component: () => import('../views/Transactions.vue'),
        meta: {
            requiresAuth: true,
            breadcrumb: 'Transactions'
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