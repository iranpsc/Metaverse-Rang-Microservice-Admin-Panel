import DOMPurify from 'dompurify'

export const sanitizeHtml = (html) => {
  if (!html) return ''
  return DOMPurify.sanitize(String(html))
}

export const stripHtml = (html) => {
  if (!html) return ''
  return DOMPurify.sanitize(String(html), { ALLOWED_TAGS: [], ALLOWED_ATTR: [] }).trim()
}

export const stripRichText = (html) => {
  return stripHtml(html).replace(/\s+/g, ' ').trim()
}

export const hasRichTextContent = (html) => stripHtml(html).replace(/\s+/g, '').length > 0
