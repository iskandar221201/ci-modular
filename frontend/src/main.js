import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue'
import 'flowbite'
import App from './App.vue'
import router from './router'
import './assets/main.css'
import { errorHandler } from '@/shared/utils/errorHandler'
import { useAuthStore } from '@/modules/auth/stores/auth'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(createHead())

app.config.errorHandler = (err, _instance, info) =>
  errorHandler.capture(err, { source: 'vue', info })
window.addEventListener('error', (e) =>
  errorHandler.capture(e.error ?? e, { source: 'window.error' }),
)
window.addEventListener('unhandledrejection', (e) =>
  errorHandler.capture(e.reason, { source: 'unhandledrejection' }),
)

// Bootstrap auth state via /api/auth/me before mount so the router guard
// sees the correct state on the first navigation (cookie cannot be read from JS).
const auth = useAuthStore(pinia)

async function bootstrap() {
  await auth.fetchMe()
  // Install router after fetchMe: app.use(router) triggers the initial
  // navigation, whose guard reads auth state. Installing it earlier would
  // redirect to /login before fetchMe() resolves (logout on refresh).
  app.use(router)
  app.mount('#app')
}

bootstrap()
