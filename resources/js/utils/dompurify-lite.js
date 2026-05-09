const sanitize = (input, options = {}) => {
  if (!input) return ''
  const html = String(input)

  if (typeof document === 'undefined') {
    return html
  }

  const template = document.createElement('template')
  template.innerHTML = html

  template.content.querySelectorAll('script, style, iframe, object, embed').forEach((el) => el.remove())
  template.content.querySelectorAll('*').forEach((el) => {
    for (const attr of [...el.attributes]) {
      const name = attr.name.toLowerCase()
      const value = attr.value || ''
      if (name.startsWith('on') || value.toLowerCase().startsWith('javascript:')) {
        el.removeAttribute(attr.name)
      }
    }
  })

  if (Array.isArray(options.ALLOWED_TAGS) && options.ALLOWED_TAGS.length === 0) {
    return template.content.textContent || ''
  }

  return template.innerHTML
}

export default { sanitize }
