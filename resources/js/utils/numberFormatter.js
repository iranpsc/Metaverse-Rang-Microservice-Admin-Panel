/**
 * Number formatting for admin tables and forms.
 */

/**
 * @param {unknown} value
 * @param {object} [options]
 * @param {string} [options.empty='-']
 * @param {boolean} [options.passthroughInvalid=false] - Return raw value when not numeric
 * @returns {string}
 */
export function formatPersianNumber(value, options = {}) {
  const { empty = '-', passthroughInvalid = false } = options

  if (value === null || value === undefined || value === '') {
    return empty
  }

  const parsed = Number(value)
  if (Number.isNaN(parsed)) {
    return passthroughInvalid ? String(value) : empty
  }

  return parsed.toLocaleString('fa-IR')
}

/**
 * Latin-digit grouping (e.g. PSC / IRR amounts in lands pricing).
 * @param {unknown} value
 * @param {object} [options]
 * @param {string} [options.empty='0']
 * @returns {string}
 */
export function formatLatinNumber(value, options = {}) {
  const { empty = '0' } = options

  if (value === null || value === undefined || value === '') {
    return empty
  }

  const parsed = Number(value)
  if (Number.isNaN(parsed)) {
    return empty
  }

  return parsed.toLocaleString('en-US')
}
