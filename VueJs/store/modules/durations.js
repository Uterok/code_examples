// initial state
const state = {
  durations: []
}

// getters
const getters = {}

// actions
const actions = {
  getDurations ({ commit, dispatch }) {
    axios.get('/api/v1/durations')
    .then(response => {
      commit('setDurations', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setDurations (state, durations) {
    state.durations = durations
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}