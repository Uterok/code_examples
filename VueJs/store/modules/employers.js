// initial state
const state = {
  auth: null,
  employers: []
}

// getters
const getters = {}

// actions
const actions = {
  getEmployers ({ commit, dispatch }) {
    axios.get('/api/v1/getEmployers?per_page=6')
    .then(response => {
      commit('setEmployers', response.data)
    })
    .catch(error => {
      
    })
  },
  getAuthUser ({ commit, dispatch, state }) {
    //check if user was loaded less than 3 second ago
    if (!state.last_sent_update || (+new Date() - state.last_sent_update >= 3000)) {
      state.last_sent_update = +new Date();
      axios.get('/api/v1/users/0')
      .then(response => {
        commit('setAuthUser', response.data)
      })
      .catch(error => {
        console.log(error);
      })
    }
  },
}

// mutations
const mutations = {
  setEmployers(state, users) {
    state.employers = employers
  },
  setAuthUser(state, auth) {
    state.auth = auth
  },
  logout(state, data) {
    state.auth = null
  }
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}