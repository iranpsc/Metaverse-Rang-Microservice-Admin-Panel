/**
 * Normalize Laravel-style pagination payload for Table/Pagination components.
 * @param {object|null|undefined} payload
 * @returns {object|null}
 */
export function normalizePagination(payload) {
  if (!payload) {
    return null
  }

  return {
    current_page: payload.current_page,
    last_page: payload.last_page,
    per_page: payload.per_page,
    total: payload.total,
    from: payload.from,
    to: payload.to
  }
}
