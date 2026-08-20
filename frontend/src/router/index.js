import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/stores/auth'
import welcomeRoutes from '@/modules/welcome/routes.js'
import authRoutes from '@/modules/auth/routes.js'
import dashboardRoutes from '@/modules/dashboard/routes.js'
import usersRoutes from '@/modules/users/routes.js'
import showcaseRoutes from '@/modules/showcase/routes.js'

const routes = [
  ...welcomeRoutes,
  ...authRoutes,
  ...dashboardRoutes,
  ...usersRoutes,
  ...showcaseRoutes,
  {
    path: '/500',
    name: 'server-error',
    component: () => import('@/shared/components/errors/ServerErrorView.vue'),
    meta: { title: 'Server Error', public: true, layout: 'blank' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/shared/components/errors/NotFoundView.vue'),
    meta: { title: '404', public: true, layout: 'blank' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  const isPublic = to.meta.public === true || to.path === '/'
  if (!auth.isAuthenticated && !isPublic) return { path: '/login' }
  if (auth.isAuthenticated && to.path === '/login') return { path: '/dashboard' }
  return true
})

export default router