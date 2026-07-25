/**
 * Date conversion utilities for Persian (Jalali/Shamsi) and Gregorian dates
 * Format: YYYY/MM/DD (e.g., "1403/08/15")
 * Uses persian-date library if available, otherwise falls back to conversion algorithm
 */

/**
 * Load persian-date library if available
 */
function loadPersianDate() {
  return new Promise((resolve) => {
    if (typeof window.persianDate !== 'undefined') {
      resolve(window.persianDate)
      return
    }

    // Try to load from public assets
    const script = document.createElement('script')
    script.src = '/assets/vendor/persian-date/dist/persian-date.min.js'
    script.onload = () => resolve(window.persianDate)
    script.onerror = () => resolve(null)
    document.body.appendChild(script)
  })
}

/**
 * Convert Shamsi (Persian/Jalali) date to Gregorian (Carbon format)
 * @param {string} shamsiDate - Date in format "YYYY/MM/DD"
 * @returns {string} - Date in format "YYYY-MM-DD" (Gregorian)
 */
export async function shamsiToGregorian(shamsiDate) {
  if (!shamsiDate || !shamsiDate.trim()) {
    return null
  }

  // Try using persianDate library first
  const persianDateLib = await loadPersianDate()
  if (persianDateLib) {
    try {
      const pDate = persianDateLib(shamsiDate)
      const gregorianDate = pDate.toCalendar('gregorian')
      return gregorianDate.format('YYYY-MM-DD')
    } catch (e) {
      console.warn('Error using persianDate library:', e)
    }
  }

  // Fallback to algorithm (simple conversion)
  const parts = shamsiDate.split('/').map(p => parseInt(p, 10))
  if (parts.length !== 3 || parts.some(isNaN)) {
    return null
  }

  const [year, month, day] = parts
  const date = new Date(year + 621, month - 1, day)
  date.setFullYear(year + 621)

  // Simple approximate conversion - for exact conversion, use backend
  const gregorianYear = date.getFullYear() - 621
  const monthStr = String(date.getMonth() + 1).padStart(2, '0')
  const dayStr = String(date.getDate()).padStart(2, '0')

  return `${gregorianYear}-${monthStr}-${dayStr}`
}

/**
 * Convert Gregorian date to Shamsi (Persian/Jalali)
 * @param {string} gregorianDate - Date in format "YYYY-MM-DD" or ISO string
 * @returns {string} - Date in format "YYYY/MM/DD" (Shamsi)
 */
export async function gregorianToShamsi(gregorianDate) {
  if (!gregorianDate) {
    return null
  }

  let date

  // Handle ISO string format
  if (gregorianDate.includes('T')) {
    date = new Date(gregorianDate)
  } else if (gregorianDate.includes('-')) {
    // Handle "YYYY-MM-DD" format
    const parts = gregorianDate.split('-').map(p => parseInt(p, 10))
    if (parts.length !== 3 || parts.some(isNaN)) {
      return null
    }
    date = new Date(parts[0], parts[1] - 1, parts[2])
  } else {
    return null
  }

  // Try using persianDate library first
  const persianDateLib = await loadPersianDate()
  if (persianDateLib) {
    try {
      const pDate = persianDateLib(date)
      return pDate.format('YYYY/MM/DD')
    } catch (e) {
      console.warn('Error using persianDate library:', e)
    }
  }

  // Fallback to simple algorithm
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()

  // Simple approximate conversion - for exact conversion, use backend
  const jalaliYear = year + 621
  const yearStr = String(jalaliYear).padStart(4, '0')
  const monthStr = String(month).padStart(2, '0')
  const dayStr = String(day).padStart(2, '0')

  return `${yearStr}/${monthStr}/${dayStr}`
}

// Synchronous versions that use a simple approximation (for default values)
export function shamsiToGregorianSync(shamsiDate) {
  if (!shamsiDate || !shamsiDate.trim()) {
    return null
  }

  // Use persianDate if available synchronously
  if (typeof window.persianDate !== 'undefined') {
    try {
      const pDate = window.persianDate(shamsiDate)
      const gregorianDate = pDate.toCalendar('gregorian')
      return gregorianDate.format('YYYY-MM-DD')
    } catch (e) {
      // Fall through to algorithm
    }
  }

  // Simple fallback
  const parts = shamsiDate.split('/').map(p => parseInt(p, 10))
  if (parts.length !== 3 || parts.some(isNaN)) {
    return null
  }

  // Very simple approximation - backend will handle exact conversion
  return `${parts[0] - 621}-${String(parts[1]).padStart(2, '0')}-${String(parts[2]).padStart(2, '0')}`
}

export function gregorianToShamsiSync(gregorianDate) {
  if (!gregorianDate) {
    return null
  }

  let date

  if (gregorianDate.includes('T')) {
    date = new Date(gregorianDate)
  } else if (gregorianDate.includes('-')) {
    const parts = gregorianDate.split('-').map(p => parseInt(p, 10))
    if (parts.length !== 3 || parts.some(isNaN)) {
      return null
    }
    date = new Date(parts[0], parts[1] - 1, parts[2])
  } else {
    return null
  }

  // Use persianDate if available synchronously
  if (typeof window.persianDate !== 'undefined') {
    try {
      const pDate = window.persianDate(date)
      return pDate.format('YYYY/MM/DD')
    } catch (e) {
      // Fall through to algorithm
    }
  }

  // Simple fallback approximation - backend handles exact conversion
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const jalaliYear = year + 621

  return `${String(jalaliYear).padStart(4, '0')}/${String(month).padStart(2, '0')}/${String(day).padStart(2, '0')}`
}

