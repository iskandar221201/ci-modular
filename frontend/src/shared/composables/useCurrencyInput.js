import { ref } from 'vue'

export function useCurrencyInput(initialValue = '') {
  const raw = String(initialValue).replace(/\D/g, '')
  const fmt = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''

  const rawValue = ref(raw)
  const displayText = ref(fmt)

  function format(numStr) {
    return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  }

  function onInput(event) {
    const rawVal = event.target.value.replace(/\D/g, '')
    rawValue.value = rawVal
    displayText.value = rawVal ? format(rawVal) : ''
  }

  return { rawValue, displayText, format, onInput }
}
