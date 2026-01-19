/**
 * @typedef {Object} Round
 * @property {number} id
 * @property {string} name
 * @property {number} [display_order]
 * @property {string} [type]
 * @property {number} [top_n_proceed]
 */

/**
 * @typedef {Object} Contestant
 * @property {Record<string, number>} [scores]
 */

/**
 * @param {Object} params
 * @param {Round[]} params.rounds
 * @param {string} params.activeRound
 * @param {Contestant[]} params.displayedContestants
 * @returns {Round[]}
 */
export const getDisplayedRounds = ({ rounds, activeRound, displayedContestants }) => {
  const roundList = Array.isArray(rounds) ? rounds : []

  const sortRounds = (items) => {
    return [...items].sort((a, b) => {
      const orderA = a.display_order || 0
      const orderB = b.display_order || 0

      if (orderA !== orderB) {
        return orderA - orderB
      }

      const aHasAdvancement = a.top_n_proceed && a.top_n_proceed > 0 ? 1 : 0
      const bHasAdvancement = b.top_n_proceed && b.top_n_proceed > 0 ? 1 : 0

      return aHasAdvancement - bHasAdvancement
    })
  }

  if (activeRound !== 'overall') {
    const selectedRound = roundList.find(round => round.id.toString() === activeRound)

    if (!selectedRound) {
      return sortRounds(roundList)
    }

    if (selectedRound.type?.toLowerCase() === 'final') {
      return sortRounds([selectedRound])
    }

    const selectedOrder = selectedRound.display_order || 0
    let roundsUpToSelected = roundList.filter(round => (round.display_order || 0) <= selectedOrder)

    if (Array.isArray(displayedContestants) && displayedContestants.length > 0) {
      const availableRoundNames = new Set(Object.keys(displayedContestants[0]?.scores || {}))
      roundsUpToSelected = roundsUpToSelected.filter(round => availableRoundNames.has(round.name))
    }

    return sortRounds(roundsUpToSelected)
  }

  return sortRounds(roundList)
}
