import { useToastStore } from '@/stores/toast'

export async function usePdfExport(endpoint, params = {}) {
  const query = new URLSearchParams(params).toString()
  const url = query ? `${endpoint}?${query}` : endpoint
  try {
    const res = await fetch(url, {
      headers: { Accept: 'application/pdf' },
      credentials: 'include',
    })
    if (!res.ok) {
      useToastStore().show('Failed to download PDF. Please try again.')
      return
    }
    const blob = await res.blob()
    const blobUrl = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = blobUrl
    a.download = 'export.pdf'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(blobUrl)
  } catch (err) {
    useToastStore().catch(err)
  }
}
