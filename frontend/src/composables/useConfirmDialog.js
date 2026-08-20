import { ref } from 'vue'

export function useConfirmDialog() {
  const visible = ref(false)
  const message = ref('')
  const onConfirm = ref(null)

  function open(msg, callback) {
    message.value = msg
    onConfirm.value = callback
    visible.value = true
  }

  function confirm() {
    if (typeof onConfirm.value === 'function') onConfirm.value()
    visible.value = false
  }

  function cancel() {
    visible.value = false
  }

  return { visible, message, open, confirm, cancel }
}
