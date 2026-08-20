<script setup>
import { reactive, watch } from 'vue'
import FormSubmitGroup from '@/shared/components/ui/FormSubmitGroup.vue'

const props = defineProps({
  mode: { type: String, default: 'create' },
  initial: { type: Object, default: () => ({ username: '', email: '', password: '' }) },
  errors: { type: Object, default: () => ({}) },
  isSubmitting: { type: Boolean, default: false },
  cancelUrl: { type: String, default: '/users' },
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({ username: '', email: '', password: '' })

watch(
  () => props.initial,
  (initial) => {
    form.username = initial.username ?? ''
    form.email = initial.email ?? ''
    form.password = ''
  },
  { immediate: true, deep: true },
)

function onSubmit() {
  emit('submit', { ...form })
}
</script>

<template>
  <form @submit.prevent="onSubmit()">
    <div class="space-y-5">
      <div>
        <label for="username" class="block mb-1.5 text-sm font-medium text-gray-700"
          >Username</label
        >
        <input
          id="username"
          v-model="form.username"
          type="text"
          class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
          :class="
            errors.username ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'
          "
          required
        />
        <span v-show="errors.username" class="mt-1 text-xs text-red-600 block">{{
          errors.username
        }}</span>
      </div>

      <div>
        <label for="email" class="block mb-1.5 text-sm font-medium text-gray-700">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition"
          :class="
            errors.email ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-gray-400'
          "
          required
        />
        <span v-show="errors.email" class="mt-1 text-xs text-red-600 block">{{
          errors.email
        }}</span>
      </div>

      <div v-if="mode === 'create'">
        <label for="password" class="block mb-1.5 text-sm font-medium text-gray-700"
          >Password</label
        >
        <input
          id="password"
          v-model="form.password"
          type="text"
          placeholder="Minimal 8 karakter"
          class="w-full px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition font-mono"
          :class="
            errors.password
              ? 'border-red-400 focus:ring-red-400'
              : 'border-gray-300 focus:ring-gray-400'
          "
          required
        />
        <p class="mt-1 text-xs text-gray-400">
          Password akan diberikan ke user untuk login pertama kali.
        </p>
        <span v-show="errors.password" class="mt-1 text-xs text-red-600 block">{{
          errors.password
        }}</span>
      </div>

      <FormSubmitGroup
        :is-submitting="isSubmitting"
        :cancel-url="cancelUrl"
        @cancel="emit('cancel')"
      />
    </div>
  </form>
</template>