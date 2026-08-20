import { ref } from 'vue'
import * as Tus from 'tus-js-client'
import { useToastStore } from '@/stores/toast'

export function useTusUpload(options = {}) {
  const file = ref(null)
  const upload = ref(null)
  const progress = ref(0)
  const isUploading = ref(false)
  const isComplete = ref(false)
  const error = ref(null)
  const result = ref(null)

  function start(fileInput) {
    file.value = fileInput
    progress.value = 0
    isUploading.value = true
    isComplete.value = false
    error.value = null
    result.value = null

    upload.value = new Tus.Upload(fileInput, {
      endpoint: options.endpoint || '/api/upload/tus',
      chunkSize: options.chunkSize || 5 * 1024 * 1024,
      retryDelays: options.retryDelays || [0, 1000, 3000, 5000],
      metadata: options.metadata || {},
      onError: (err) => {
        isUploading.value = false
        error.value = err
        useToastStore().catch(err)
        if (options.onError) options.onError(err)
      },
      onProgress: (bytesSent, bytesTotal) => {
        progress.value = bytesTotal > 0 ? (bytesSent / bytesTotal) * 100 : 0
        if (options.onProgress) options.onProgress(bytesSent, bytesTotal, progress.value)
      },
      onSuccess: () => {
        isUploading.value = false
        isComplete.value = true
        result.value = upload.value.url
        if (options.onSuccess) options.onSuccess(upload.value.url)
      },
    })

    upload.value.start()
  }

  function abort() {
    if (upload.value) {
      upload.value.abort()
      isUploading.value = false
    }
  }

  return { file, upload, progress, isUploading, isComplete, error, result, start, abort }
}
