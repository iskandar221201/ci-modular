import { describe, it, expect, vi, beforeEach } from 'vitest'
import { nextTick } from 'vue'
import { useDataTable } from '@/shared/composables/useDataTable'
import api from '@/shared/services/api'

vi.mock('@/shared/services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    request: vi.fn(),
  },
}))

describe('useDataTable', () => {
  beforeEach(() => {
    api.get.mockReset()
  })

  it('has correct initial state and loads data on init', async () => {
    api.get.mockResolvedValue({
      status: true,
      data: [{ id: 1, username: 'x' }],
      meta: { total_pages: 1 },
    })

    const table = useDataTable('/users')

    expect(table.loading.value).toBe(true)
    expect(table.data.value).toEqual([])

    await table.init()
    await nextTick()

    expect(table.loading.value).toBe(false)
    expect(table.data.value[0].username).toBe('x')
  })
})
