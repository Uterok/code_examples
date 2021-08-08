import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from "vuex-persistedstate";
import user from './modules/user'
import freelancers from './modules/freelancers'
import employers from './modules/employers'
import projects from './modules/projects'
import skills from './modules/skills'
import paytypes from './modules/paytypes'
import experience_lvls from './modules/explevels'
import currencies from './modules/currencies'
import categories from './modules/categories'
import categoriesOfQuestions from './modules/categoriesOfQuestions'
import locations from './modules/locations'
import countries from './modules/countries'
import durations from './modules/durations'
import tasks from './modules/tasks'


Vue.use(Vuex)

export default new Vuex.Store({
	modules: {
		skills,
		paytypes,
		experience_lvls,
		categories,
		user,
		freelancers,
		employers,
		projects,
		currencies,
		categoriesOfQuestions,
		locations,
		countries,
		durations,
		tasks,
		plugins: [createPersistedState()]
	},
})