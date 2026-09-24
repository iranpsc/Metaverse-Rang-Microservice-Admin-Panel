/**
 * Date conversion utilities for Persian (Jalali/Shamsi) and Gregorian dates
 * Format: YYYY/MM/DD (e.g., "1403/08/15")
 */

const persianDigitMap = {
  '۰': '0',
  '۱': '1',
  '۲': '2',
  '۳': '3',
  '۴': '4',
  '۵': '5',
  '۶': '6',
  '۷': '7',
  '۸': '8',
  '۹': '9'
}

function toEnglishDigits(input) {
  if (!input) return ''
  return String(input).replace(/[۰-۹]/g, (digit) => persianDigitMap[digit] || digit)
}

function parseGregorianDate(gregorianDate) {
  if (!gregorianDate) {
    return null
  }

  const value = String(gregorianDate)

  if (value.includes('T')) {
    const date = new Date(value)
    return Number.isNaN(date.getTime()) ? null : date
  }

  if (value.includes('-') || value.includes('/')) {
    const parts = value.split(/[-/]/).map((part) => parseInt(part, 10))
    if (parts.length !== 3 || parts.some(Number.isNaN)) {
      return null
    }
    // Local noon avoids DST/timezone day-boundary shifts for date-only values
    return new Date(parts[0], parts[1] - 1, parts[2], 12, 0, 0)
  }

  return null
}

function parseJalaliParts(shamsiDate) {
  if (!shamsiDate || !String(shamsiDate).trim()) {
    return null
  }

  const normalized = toEnglishDigits(String(shamsiDate).trim()).replace(/-/g, '/')
  const parts = normalized.split('/').map((part) => parseInt(part, 10))
  if (parts.length !== 3 || parts.some(Number.isNaN)) {
    return null
  }

  return { jy: parts[0], jm: parts[1], jd: parts[2] }
}

/**
 * Accurate Gregorian → Jalali via Intl (no year+621 approximation).
 */
function gregorianDateToJalaliString(date) {
  if (!date || Number.isNaN(date.getTime())) {
    return null
  }

  try {
    const formatter = new Intl.DateTimeFormat('en-u-ca-persian', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    })
    const parts = formatter.formatToParts(date)
    const year = toEnglishDigits(parts.find((part) => part.type === 'year')?.value || '')
    const month = toEnglishDigits(parts.find((part) => part.type === 'month')?.value || '')
    const day = toEnglishDigits(parts.find((part) => part.type === 'day')?.value || '')

    if (!year || !month || !day) {
      return null
    }

    return `${year.padStart(4, '0')}/${month.padStart(2, '0')}/${day.padStart(2, '0')}`
  } catch (error) {
    console.warn('Error converting Gregorian to Jalali:', error)
    return null
  }
}

function jalaliPartsFromDate(date) {
  const jalali = gregorianDateToJalaliString(date)
  return jalali ? parseJalaliParts(jalali) : null
}

/**
 * Convert Jalali → Gregorian by searching nearby Gregorian days until Intl matches.
 */
function jalaliToGregorianString(jy, jm, jd) {
  // Rough seed around the expected Gregorian year
  let guess = Date.UTC(jy + 621, Math.max(jm - 1, 0), Math.max(jd, 1), 12)

  for (let i = 0; i < 370; i += 1) {
    const date = new Date(guess)
    const parts = jalaliPartsFromDate(date)
    if (!parts) {
      break
    }

    if (parts.jy === jy && parts.jm === jm && parts.jd === jd) {
      const gy = date.getUTCFullYear()
      const gm = date.getUTCMonth() + 1
      const gd = date.getUTCDate()
      return `${gy}-${String(gm).padStart(2, '0')}-${String(gd).padStart(2, '0')}`
    }

    const deltaDays =
      (jy - parts.jy) * 365 +
      (jm - parts.jm) * 30 +
      (jd - parts.jd)

    if (deltaDays === 0) {
      break
    }

    guess += deltaDays * 24 * 60 * 60 * 1000
  }

  return null
}

/**
 * Load persian-date library if available
 */
function loadPersianDate() {
  return new Promise((resolve) => {
    if (typeof window !== 'undefined' && typeof window.persianDate !== 'undefined') {
      resolve(window.persianDate)
      return
    }

    const script = document.createElement('script')
    script.src = '/assets/vendor/persian-date/dist/persian-date.min.js'
    script.onload = () => resolve(window.persianDate)
    script.onerror = () => resolve(null)
    document.body.appendChild(script)
  })
}

/**
 * Convert Shamsi (Persian/Jalali) date to Gregorian
 * @param {string} shamsiDate - Date in format "YYYY/MM/DD"
 * @returns {Promise<string|null>} - Date in format "YYYY-MM-DD" (Gregorian)
 */
export async function shamsiToGregorian(shamsiDate) {
  if (!shamsiDate || !String(shamsiDate).trim()) {
    return null
  }

  if (typeof window !== 'undefined') {
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
  }

  return shamsiToGregorianSync(shamsiDate)
}

/**
 * Convert Gregorian date to Shamsi (Persian/Jalali)
 * @param {string} gregorianDate - Date in format "YYYY-MM-DD" or ISO string
 * @returns {Promise<string|null>} - Date in format "YYYY/MM/DD" (Shamsi)
 */
export async function gregorianToShamsi(gregorianDate) {
  const date = parseGregorianDate(gregorianDate)
  if (!date) {
    return null
  }

  if (typeof window !== 'undefined') {
    const persianDateLib = await loadPersianDate()
    if (persianDateLib) {
      try {
        const pDate = persianDateLib(date)
        return toEnglishDigits(pDate.format('YYYY/MM/DD'))
      } catch (e) {
        console.warn('Error using persianDate library:', e)
      }
    }
  }

  return gregorianDateToJalaliString(date)
}

export function shamsiToGregorianSync(shamsiDate) {
  if (typeof window !== 'undefined' && typeof window.persianDate !== 'undefined') {
    try {
      const pDate = window.persianDate(shamsiDate)
      const gregorianDate = pDate.toCalendar('gregorian')
      return gregorianDate.format('YYYY-MM-DD')
    } catch (e) {
      // Fall through to Intl search
    }
  }

  const parts = parseJalaliParts(shamsiDate)
  if (!parts) {
    return null
  }

  return jalaliToGregorianString(parts.jy, parts.jm, parts.jd)
}

export function gregorianToShamsiSync(gregorianDate) {
  if (typeof window !== 'undefined' && typeof window.persianDate !== 'undefined') {
    try {
      const date = parseGregorianDate(gregorianDate)
      if (!date) {
        return null
      }
      const pDate = window.persianDate(date)
      return toEnglishDigits(pDate.format('YYYY/MM/DD'))
    } catch (e) {
      // Fall through to Intl
    }
  }

  const date = parseGregorianDate(gregorianDate)
  return gregorianDateToJalaliString(date)
}

function localDateToIsoDateString(date) {
  const yyyy = date.getFullYear()
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const dd = String(date.getDate()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}`
}

/**
 * Today's date in Jalali YYYY/MM/DD (English digits).
 * @returns {string}
 */
export function todayInShamsi() {
  return gregorianToShamsiSync(localDateToIsoDateString(new Date())) || ''
}

/**
 * @param {number} days
 * @returns {string}
 */
export function addDaysInShamsi(days) {
  const date = new Date()
  date.setDate(date.getDate() + days)
  return gregorianToShamsiSync(localDateToIsoDateString(date)) || ''
}
