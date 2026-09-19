/**
 * @param {string|null|undefined} text
 * @param {number} [maxLength=60]
 * @param {string} [ellipsis='…']
 * @returns {string}
 */
export function truncateWithEllipsis(text, maxLength = 60, ellipsis = '…') {
  if (!text) {
    return ''
  }

  const plain = String(text)
  if (plain.length <= maxLength) {
    return plain
  }

  return `${plain.slice(0, maxLength)}${ellipsis}`
}

/**
 * @param {string|null|undefined} value
 * @param {number} [start=6]
 * @param {number} [end=4]
 * @param {string} [empty='-']
 * @returns {string}
 */
export function truncateMiddle(value, start = 6, end = 4, empty = '-') {
  if (!value) {
    return empty
  }

  const str = String(value)
  if (str.length <= start + end + 3) {
    return str
  }

  return `${str.slice(0, start)}...${str.slice(-end)}`
}
