import { defineStore } from 'pinia'

let timer = null

export const useToastStore = defineStore('toast', {
  state: () => ({ message: '', type: 'error', visible: false }),
  actions: {
    show(message, type = 'error') {
      this.message = message
      this.type = type
      this.visible = true
      if (timer) clearTimeout(timer)
      timer = setTimeout(() => {
        this.visible = false
      }, 4000)
    },
    catch(err) {
      if (err && err.errors) return
      if (err && err.message) this.show(err.message)
      else this.show('Something went wrong. Please try again.')
    },
  },
})
