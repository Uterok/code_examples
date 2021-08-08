// initial state
const state = {
  projects: [],
}

// getters
const getters = {}

// actions
const actions = {
  getProjects ({ commit, dispatch }) {
    axios.get('/api/v1/jobs?status=1&page=1&per_page=6')
    .then(response => {
      commit('setProjects', response.data)
      // commit('setPagination', response.data)
    })
    .catch(error => {
      
    })
  },
}

// mutations
const mutations = {
  setProjects (state, projects) {
    state.projects = projects
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}