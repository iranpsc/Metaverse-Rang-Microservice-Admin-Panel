import DOMPurify from 'dompurify'

export const sanitizeHtml = (html) => {
  if (!html) return ''
  return DOMPurify.sanitize(String(html))
}

export const stripHtml = (html) => {
  if (!html) return ''
  return DOMPurify.sanitize(String(html), { ALLOWED_TAGS: [], ALLOWED_ATTR: [] }).trim()
}
