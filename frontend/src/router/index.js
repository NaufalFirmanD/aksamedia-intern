import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('@/pages/Login.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('@/pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/employees',
        name: 'Employees',
        component: () => import('@/pages/Employees.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('@/pages/Profile.vue'),
        meta: { requiresAuth: true }
    },
]

const router = createRouter({
    history: createWebHistory('/'),
    routes,
})

router.beforeEach((to, from, next) => {
    const { isAuthenticated } = useAuth()

    if (to.meta.requiresAuth && !isAuthenticated.value) {
        next('/login')
    } else if (to.meta.requiresGuest && isAuthenticated.value) {
        next('/')
    } else {
        next()
    }
})

export default router
