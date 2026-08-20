<script setup>
import { computed } from 'vue'
import { useCurrencyInput } from '@/shared/composables/useCurrencyInput'

const props = defineProps({
  name: { type: String, default: '' },
  label: { type: String, default: '' },
  modelValue: { type: [String, Number], default: '' },
  placeholder: { type: String, default: '0' },
  prefix: { type: String, default: 'Rp' },
})
const emit = defineEmits(['update:modelValue'])

const ci = useCurrencyInput(props.modelValue)
const fieldId = computed(() => props.name.replace(/\W/g, '_'))

function onInput(e) {
  ci.onInput(e)
  emit('update:modelValue', ci.rawValue.value)
}
</script>

<template>
  <div>
    <label
      v-if="label"
      :for="`${fieldId}_input`"
      class="block mb-1.5 text-sm font-medium text-gray-700"
    >
      {{ label }}
    </label>

    <input :id="fieldId" type="hidden" :name="name" :value="ci.rawValue.value" />

    <div class="flex">
      <span
        class="inline-flex items-center px-3.5 py-2.5 text-sm font-medium border border-r-0 rounded-l-lg bg-gray-50 text-gray-600 select-none border-gray-300"
      >
        {{ prefix }}
      </span>
      <input
        :id="`${fieldId}_input`"
        type="text"
        inputmode="numeric"
        :value="ci.displayText.value"
        :placeholder="placeholder"
        class="flex-1 min-w-0 px-3.5 py-2.5 text-sm text-gray-900 bg-white border rounded-r-lg outline-none focus:ring-1 transition placeholder-gray-400 border-l-0 border-gray-300 focus:ring-gray-400"
        autocomplete="off"
        @input="onInput"
      />
    </div>
  </div>
</template>
