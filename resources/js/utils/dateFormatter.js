/**
 * Date formatting utilities
 * Supports both Gregorian and Persian (Jalali) calendars
 */

/**
 * Parse a date-like value into a Date instance.
 * @param {string|Date|null|undefined} value
 * @returns {Date|null}
 */
export function parseDateValue(value) {
  if (!value) {
    return null
  }

  const date = value instanceof Date ? value : new Date(value)
  if (Number.isNaN(date.getTime())) {
    return null
  }

  return date
}

/**
 * Table/display date using fa-IR locale (Persian calendar).
 * @param {string|Date|null|undefined} value
 * @param {string} emptyValue
 * @returns {string}
 */
export function formatDisplayDate(value, emptyValue = '-') {
  if (!value) {
    return emptyValue
  }

  const date = parseDateValue(value)
  if (!date) {
    return emptyValue
  }

  return date.toLocaleDateString('fa-IR')
}

/**
 * Gregorian date as YYYY/MM/DD (English digits). Used for some land API fields.
 * @param {string|Date|null|undefined} value
 * @param {string} emptyValue
 * @returns {string}
 */
export function formatGregorianSlashDate(value, emptyValue = '-') {
  if (!value) {
    return emptyValue
  }

  const date = parseDateValue(value)
  if (!date) {
    return emptyValue
  }

  return formatGregorianDate(date, 'Y/m/d')
}

/**
 * ISO datetime → Jalali YYYY/MM/DD with English digits (for date pickers/forms).
 * @param {string|null|undefined} isoString
 * @returns {string}
 */
export function formatIsoToJalaliDate(isoString) {
  if (!isoString) {
    return ''
  }

  const date = parseDateValue(isoString)
  if (!date) {
    return ''
  }

  const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })

  const parts = formatter.formatToParts(date)
  const year = persianToEnglishNumbers(parts.find((part) => part.type === 'year')?.value || '')
  const month = persianToEnglishNumbers(parts.find((part) => part.type === 'month')?.value || '')
  const day = persianToEnglishNumbers(parts.find((part) => part.type === 'day')?.value || '')

  if (!year || !month || !day) {
    return ''
  }

  return `${year.padStart(4, '0')}/${month.padStart(2, '0')}/${day.padStart(2, '0')}`
}

/**
 * Format time for tables and detail views.
 * @param {string|Date|null|undefined} value
 * @param {object} [options]
 * @param {string} [options.emptyValue='-']
 * @param {boolean} [options.includeSeconds=false]
 * @param {boolean} [options.useLocale=true] - When false, uses padded 24h H:i[:s]
 * @returns {string}
 */
export function formatDisplayTime(value, options = {}) {
  const { emptyValue = '-', includeSeconds = false, useLocale = true } = options

  if (!value) {
    return emptyValue
  }

  const date = parseDateValue(value)
  if (!date) {
    return emptyValue
  }

  if (!useLocale) {
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    if (!includeSeconds) {
      return `${hours}:${minutes}`
    }
    const seconds = String(date.getSeconds()).padStart(2, '0')
    return `${hours}:${minutes}:${seconds}`
  }

  const timeOptions = {
    hour: '2-digit',
    minute: '2-digit',
    ...(includeSeconds ? { second: '2-digit' } : {})
  }

  return date.toLocaleTimeString('fa-IR', timeOptions)
}

/**
 * Split backend Jalali datetime string into date and time parts.
 * @param {string|null|undefined} jalaliValue
 * @param {string|null|undefined} timeValue
 * @returns {{ date: string, time: string }}
 */
export function splitJalaliDateTime(jalaliValue, timeValue) {
  if (timeValue) {
    return {
      date: jalaliValue || '-',
      time: timeValue
    }
  }

  if (!jalaliValue) {
    return { date: '-', time: '-' }
  }

  const parts = String(jalaliValue).trim().split(/\s+/)
  if (parts.length >= 2) {
    return {
      date: parts[0],
      time: parts.slice(1).join(' ')
    }
  }

  return { date: jalaliValue, time: '-' }
}

/**
 * Format date to Persian format
 * @param {string|Date} date - Date to format
 * @param {string} format - Format string (default: 'Y/m/d')
 * @returns {string} Formatted date
 */
export function formatPersianDate(date, format = 'Y/m/d') {
  if (!date) return ''

  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date

    // Basic Persian date formatting using Intl.DateTimeFormat
    const options = {
      calendar: 'persian',
      locale: 'fa-IR',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }

    const formatter = new Intl.DateTimeFormat('fa-IR', options)
    return formatter.format(dateObj).replace(/٫/g, '')
  } catch (error) {
    console.error('Error formatting Persian date:', error)
    return formatGregorianDate(date, format)
  }
}

/**
 * Format date to Gregorian format
 * @param {string|Date} date - Date to format
 * @param {string} format - Format string (default: 'Y-m-d')
 * @returns {string} Formatted date
 */
export function formatGregorianDate(date, format = 'Y-m-d') {
  if (!date) return ''

  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date

    const year = dateObj.getFullYear()
    const month = String(dateObj.getMonth() + 1).padStart(2, '0')
    const day = String(dateObj.getDate()).padStart(2, '0')
    const hours = String(dateObj.getHours()).padStart(2, '0')
    const minutes = String(dateObj.getMinutes()).padStart(2, '0')
    const seconds = String(dateObj.getSeconds()).padStart(2, '0')

    return format
      .replace('Y', year)
      .replace('m', month)
      .replace('d', day)
      .replace('H', hours)
      .replace('i', minutes)
      .replace('s', seconds)
  } catch (error) {
    console.error('Error formatting Gregorian date:', error)
    return ''
  }
}

/**
 * Format date with time
 * @param {string|Date} date - Date to format
 * @param {boolean} usePersian - Use Persian calendar (default: true)
 * @returns {string} Formatted date with time
 */
export function formatDateTime(date, usePersian = true) {
  if (!date) return ''

  const dateObj = typeof date === 'string' ? new Date(date) : date

  if (usePersian) {
    const persianDate = formatPersianDate(dateObj)
    const time = formatGregorianDate(dateObj, 'H:i')
    return `${persianDate} - ${time}`
  }

  return formatGregorianDate(dateObj, 'Y-m-d H:i')
}

/**
 * Get relative time (e.g., "2 hours ago")
 * @param {string|Date} date - Date to format
 * @returns {string} Relative time string
 */
export function getRelativeTime(date) {
  if (!date) return ''

  const dateObj = typeof date === 'string' ? new Date(date) : date
  const now = new Date()
  const diffMs = now - dateObj
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffHour = Math.floor(diffMin / 60)
  const diffDay = Math.floor(diffHour / 24)
  const diffMonth = Math.floor(diffDay / 30)
  const diffYear = Math.floor(diffDay / 365)

  if (diffSec < 60) {
    return 'لحظاتی پیش'
  } else if (diffMin < 60) {
    return `${diffMin} دقیقه پیش`
  } else if (diffHour < 24) {
    return `${diffHour} ساعت پیش`
  } else if (diffDay < 30) {
    return `${diffDay} روز پیش`
  } else if (diffMonth < 12) {
    return `${diffMonth} ماه پیش`
  } else {
    return `${diffYear} سال پیش`
  }
}

/**
 * Check if date is today
 * @param {string|Date} date - Date to check
 * @returns {boolean} True if date is today
 */
export function isToday(date) {
  if (!date) return false

  const dateObj = typeof date === 'string' ? new Date(date) : date
  const today = new Date()

  return (
    dateObj.getDate() === today.getDate() &&
    dateObj.getMonth() === today.getMonth() &&
    dateObj.getFullYear() === today.getFullYear()
  )
}

/**
 * Convert Persian numbers to English
 * @param {string} str - String with Persian numbers
 * @returns {string} String with English numbers
 */
export function persianToEnglishNumbers(str) {
  const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']

  let result = str
  for (let i = 0; i < 10; i++) {
    result = result.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i])
  }

  return result
}

/**
 * Convert English numbers to Persian
 * @param {string} str - String with English numbers
 * @returns {string} String with Persian numbers
 */
export function englishToPersianNumbers(str) {
  const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']

  let result = str
  for (let i = 0; i < 10; i++) {
    result = result.replace(new RegExp(englishNumbers[i], 'g'), persianNumbers[i])
  }

  return result
}

