/**
 * Date formatting utilities
 * Supports both Gregorian and Persian (Jalali) calendars
 */

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

