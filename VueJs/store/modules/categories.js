// initial state
const state = {
  categories: []
}

// getters
const getters = {}

// actions
const actions = {
  getCategories ({ commit, dispatch }) {
    axios.get('/api/v1/jobcategories')
    .then(response => {
      commit('setCategories', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setCategories (state, categories) {
    state.categories = categories
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}