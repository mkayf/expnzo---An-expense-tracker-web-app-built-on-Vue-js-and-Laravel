export default [
    {
        path: '',
        name: 'Home',
        component: () => import('../views/Home.vue'),
    },
    {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: {
            requiresAuth: true
        }
    }
]