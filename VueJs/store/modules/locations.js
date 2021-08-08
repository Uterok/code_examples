// initial state
const state = {
  locations: []
}

// getters
const getters = {}

// actions
const actions = {
  getLocations ({ commit, dispatch }) {
    axios.get('/api/v1/locations')
    .then(response => {
      commit('setLocations', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setLocations (state, locations) {
    state.locations = locations
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}