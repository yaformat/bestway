import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'

const router = createRouter({
  //history: createWebHistory(import.meta.env.BASE_URL),
  history: createWebHistory('/'), 
  routes: [
    ...setupLayouts(routes),
  ],
})

router.beforeEach((to, from, next) => {
  // Check the localStorage for userData or accessToken
  const isAuthenticated = localStorage.getItem('userData') && localStorage.getItem('accessToken');

  // if we're going to a route that requires authentication
  //if (to.matched.some(record => record.meta.requiresAuth)) {
  if (to.href === '/') {
    // and user isn't authenticated
    if (!isAuthenticated) {
      // Redirect to login page
      next({ name: 'login' })
    } else {
      next()
    }
  } else {
    next() // make sure to always call next()!
  }
})

// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
export default router
