import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'

// router/index.js
const router = createRouter({
  history: createWebHistory(import.meta.env.VITE_BASE_URL || '/admin/'),
  routes: [
    {
      path: '/admin',
      name: 'home',
      component: () => import('../pages/index.vue'),
      meta: { requiresAuth: true },
    },
    ...setupLayouts(routes),
  ],
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('userData') && localStorage.getItem('accessToken')

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) next({ name: 'login' })
    else next()
  } else {
    next()
  }
})

// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
export default router
