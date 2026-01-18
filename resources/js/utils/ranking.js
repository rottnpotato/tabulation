/**
 * @template T
 * @param {T[]} items
 * @param {(item: T) => number} getValue
 * @param {(item: T) => number} getId
 * @param {'asc' | 'desc'} [direction='desc']
 * @param {number} [epsilon=0.0001]
 * @returns {Map<number, number>}
 */
export const computeTiedRankMap = (items, getValue, getId, direction = 'desc', epsilon = 0.0001) => {
  const normalize = (value) => {
    if (Number.isFinite(value)) {
      return value
    }

    return direction === 'asc' ? Number.POSITIVE_INFINITY : Number.NEGATIVE_INFINITY
  }

  const sorted = items
    .map((item, index) => ({
      item,
      value: normalize(getValue(item)),
      index
    }))
    .sort((a, b) => {
      const delta = a.value - b.value

      if (Math.abs(delta) < epsilon) {
        return a.index - b.index
      }

      return direction === 'asc' ? delta : -delta
    })

  const map = new Map()
  let currentRank = 1
  let previousValue = null

  sorted.forEach((entry, index) => {
    if (previousValue !== null && Math.abs(entry.value - previousValue) < epsilon) {
      map.set(getId(entry.item), currentRank)
      return
    }

    currentRank = index + 1
    map.set(getId(entry.item), currentRank)
    previousValue = entry.value
  })

  return map
}
