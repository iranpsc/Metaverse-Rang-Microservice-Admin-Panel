const KARBARI_LABELS = {
  m: 'مسکونی',
  t: 'تجاری',
  a: 'آموزشی',
  s: 'فضای سبز',
  f: 'فرهنگی',
  p: 'پارکینگ',
  z: 'مذهبی',
  n: 'نمایشگاه',
  g: 'گردشگری',
  e: 'اداری',
  b: 'بهداشتی'
}

/**
 * @param {string|null|undefined} karbari
 * @param {string} [empty='-']
 * @returns {string}
 */
export function getKarbariLabel(karbari, empty = '-') {
  if (!karbari) {
    return empty
  }

  return KARBARI_LABELS[karbari] || empty
}

export { KARBARI_LABELS }
