export default [
  {
    path: '/login',
    name: 'login',
    component: () => import('./views/LoginView.vue'),
    meta: { title: 'Login', public: true, layout: 'auth' },
  },
]