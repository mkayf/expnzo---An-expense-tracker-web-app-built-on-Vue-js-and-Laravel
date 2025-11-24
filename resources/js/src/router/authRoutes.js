export default [
    {
        path: 'login',
        name: 'Login',
        component: () => import('../views/auth/Login.vue'),
        meta: {
            requiresAuth: false
        }   
    },
    {
        path: 'register',
        name: 'Register',
        component: () => import('../views/auth/Register.vue'),
        meta: {
            requiresAuth: false
        }
    },
    {
        path: 'verify_email',
        name: 'VerifyEmail',
        component: () => import('../views/auth/VerifyEmail.vue'),
        meta: {
            requiresAuth: true
        }
    }
]