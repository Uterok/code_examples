// initial state
const state = {
  tasks: [],
}

// getters
const getters = {}

// actions
const actions = {
  getTasks ({ commit, dispatch }) {
    axios.get('/api/v1/jobs?status=2&page=1&per_page=6')
    .then(response => {
      commit('setTasks', response.data)
    })
    .catch(error => {
      
    })
  },
}

// mutations
const mutations = {
  setTasks (state, tasks) {
    state.tasks = tasks
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}