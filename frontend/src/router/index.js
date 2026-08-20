import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import WelcomeView from '@/views/WelcomeView.vue'

const routes = [
  {
    path: '/',
    name: 'welcome',
    component: WelcomeView,
    meta: { title: '', public: true, layout: 'blank' },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { title: 'Login', public: true, layout: 'auth' },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { title: 'Dashboard', layout: 'default' },
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('@/views/users/UserListView.vue'),
    meta: { title: 'Daftar Users', layout: 'default' },
  },
  {
    path: '/users/create',
    name: 'user-create',
    component: () => import('@/views/users/UserCreateView.vue'),
    meta: { title: 'Tambah User', layout: 'default' },
  },
  {
    path: '/users/:id(\\d+)',
    name: 'user-detail',
    component: () => import('@/views/users/UserDetailView.vue'),
    meta: { title: 'Detail User', layout: 'default' },
  },
  {
    path: '/showcase',
    name: 'showcase',
    component: () => import('@/views/ShowcaseView.vue'),
    meta: { title: 'Component Gallery', layout: 'default' },
  },
  {
    path: '/500',
    name: 'server-error',
    component: () => import('@/components/errors/ServerErrorView.vue'),
    meta: { title: 'Server Error', public: true, layout: 'blank' },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/components/errors/NotFoundView.vue'),
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
