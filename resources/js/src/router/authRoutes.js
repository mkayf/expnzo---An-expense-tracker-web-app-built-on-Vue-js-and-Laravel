export default [
    {
        path: '/',
        name: 'Auth Layout',
        component: () => import("../views/authLayout/AuthLayout.vue"),
        meta: {
            requiresAuth: false
        },
        children: [
            {
                path: 'register',
                name: 'Register',
                component: () => import("../views/auth/Register.vue")
            },
            {
                path: 'login',
                name: 'Login',
                component: () => import("../views/auth/Login.vue")
            }
        ]
    }
]