const APP_NAME = import.meta.env.VITE_APP_NAME || 'CI4 Kit'

export function pageTitle(title) {
  return title ? `${title} — ${APP_NAME}` : APP_NAME
}
