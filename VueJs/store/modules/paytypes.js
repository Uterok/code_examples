// initial state
const state = {
  paytypes: []
}

// getters
const getters = {}

// actions
const actions = {
  getPayTypes ({ commit, dispatch }) {
    axios.get('/api/v1/paytypes')
    .then(response => {
      commit('setPayTypes', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setPayTypes (state, paytypes) {
    state.paytypes = paytypes
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}