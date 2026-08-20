import WelcomeView from './views/WelcomeView.vue'

export default [
  {
    path: '/',
    name: 'welcome',
    component: WelcomeView,
    meta: { title: '', public: true, layout: 'blank' },
  },
]