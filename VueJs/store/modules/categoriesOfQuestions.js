// initial state
const state = {
  categoriesOfQuestions: []
}

// getters
const getters = {}

// actions
const actions = {
  getCategoriesOfQuestions ({ commit, dispatch }) {
    axios.get('/api/v1/category_question')
    .then(response => {
      commit('setCategoriesOfQuestions', response.data)
    })
    .catch(error => {
      
    })
  }
}

// mutations
const mutations = {
  setCategoriesOfQuestions (state, categoriesOfQuestions) {
    for (var i = 0; i < categoriesOfQuestions.length; i++) {
      for (var x = 0; x < categoriesOfQuestions[i].platform_questions.length; x++) {
        categoriesOfQuestions[i].platform_questions[x].show = false
      }
    }
    state.categoriesOfQuestions = categoriesOfQuestions
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}