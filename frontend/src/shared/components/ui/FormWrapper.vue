<script setup>
import { useForm } from '@/shared/composables/useForm'

const props = defineProps({
  endpoint: { type: String, required: true },
  method: { type: String, default: 'POST' },
  redirectUrl: { type: String, default: null },
})

const { errors, isSubmitting, submit } = useForm(props.endpoint, props.method, props.redirectUrl)
defineExpose({ errors, isSubmitting, submit })
</script>

<template>
  <form novalidate @submit.prevent="submit(Object.fromEntries(new FormData($event.target)))">
    <slot :errors="errors" :is-submitting="isSubmitting" />

    <p v-if="errors._form" class="mt-2 text-sm text-red-600" role="alert">{{ errors._form }}</p>
  </form>
</template>
