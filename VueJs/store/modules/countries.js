// initial state
const state = {
  countries: []
}

// getters
const getters = {}

// actions
const actions = {
  getCountries ({ commit, dispatch }) {
    axios.get('/api/v1/locations')
    .then(response => {
      commit('setCountries', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setCountries (state, countries) {
    state.countries = countries
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}