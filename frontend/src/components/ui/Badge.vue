<script setup>
import { computed } from 'vue'

const colorStyles = {
  green: 'background-color:#dcfce7;color:#166534',
  red: 'background-color:#fee2e2;color:#991b1b',
  yellow: 'background-color:#fef9c3;color:#92400e',
  blue: 'background-color:#dbeafe;color:#1e40af',
  gray: 'background-color:#f3f4f6;color:#1f2937',
}

const props = defineProps({
  label: { type: String, default: '' },
  color: { type: String, default: 'gray' },
})

const style = computed(() => colorStyles[props.color] ?? colorStyles.gray)
</script>

<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :style="style"
  >
    {{ label }}
  </span>
</template>
