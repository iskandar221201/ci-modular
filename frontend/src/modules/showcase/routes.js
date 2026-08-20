export default [
  {
    path: '/showcase',
    name: 'showcase',
    component: () => import('./views/ShowcaseView.vue'),
    meta: { title: 'Component Gallery', layout: 'default' },
  },
]