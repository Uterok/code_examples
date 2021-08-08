// initial state
const state = {
  skills: []
}

// getters
const getters = {}

// actions
const actions = {
  getSkills ({ commit, dispatch }) {
    axios.get('/api/v1/skills')
    .then(response => {
      commit('setSkills', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setSkills (state, skills) {
    state.skills = skills
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}