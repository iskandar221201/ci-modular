export default [
  {
    path: '/{{MODULES_LOWER}}',
    name: '{{MODULES_LOWER}}',
    component: () => import('./views/{{MODULE}}ListView.vue'),
    meta: { title: 'Daftar {{MODULES}}', layout: 'default' },
  },
]
