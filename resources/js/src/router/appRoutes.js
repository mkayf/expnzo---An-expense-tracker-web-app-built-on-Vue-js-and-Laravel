export default [
    {
        path: '/app/dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        alias: '/app',
        meta: {
            requiresAuth: true
        }
    }
]