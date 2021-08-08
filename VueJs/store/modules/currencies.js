// initial state
const state = {
  currencies: []
}

// getters
const getters = {}

// actions
const actions = {
  getCurrencies ({ commit, dispatch }) {
    axios.get('/api/v1/currencies')
    .then(response => {
      commit('setCurrencies', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setCurrencies (state, currencies) {
    state.currencies = currencies
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}