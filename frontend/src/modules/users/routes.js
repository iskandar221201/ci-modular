export default [
  {
    path: '/users',
    name: 'users',
    component: () => import('./views/UserListView.vue'),
    meta: { title: 'Daftar Users', layout: 'default' },
  },
  {
    path: '/users/create',
    name: 'user-create',
    component: () => import('./views/UserCreateView.vue'),
    meta: { title: 'Tambah User', layout: 'default' },
  },
  {
    path: '/users/:id(\\d+)',
    name: 'user-detail',
    component: () => import('./views/UserDetailView.vue'),
    meta: { title: 'Detail User', layout: 'default' },
  },
]