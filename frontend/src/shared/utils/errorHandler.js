const handlers = []

export const errorHandler = {
  capture(err, context = {}) {
    const payload = {
      message: err?.message || String(err),
      stack: err?.stack,
      ...context,
      ts: new Date().toISOString(),
    }
    // ponytail: console.error only; register() is the hook for Sentry later
    console.error('[frontend]', JSON.stringify(payload))
    handlers.forEach((fn) => {
      try {
        fn(err, payload)
      } catch (_) {
        /* a broken handler must not break error capture */
      }
    })
  },
  register(fn) {
    handlers.push(fn)
  },
}
