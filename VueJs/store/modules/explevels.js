// initial state
const state = {
  experience_lvls: []
}

// getters
const getters = {}

// actions
const actions = {
  getExpLevels ({ commit, dispatch }) {
    axios.get('/api/v1/experiencelevels')
    .then(response => {
      commit('setExpLevels', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setExpLevels (state, experience_lvls) {
    state.experience_lvls = experience_lvls
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}