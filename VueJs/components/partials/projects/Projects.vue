<template>
	<div class="container">

		<custom-filter
		ref="filter"
		:placeholder="'Enter the name of project or skill'"
		:skill="skill"
		:category="category"
		:location="location"
		:meta="filter_meta"
		@get-filtered="getData"
		@clean-props="cleanProps"
		@clean-location="cleanLocation"></custom-filter>

		<projects-list
		:projects="projects"
		@filter-skill="filterSkill"
		@filter-category="filterCategory"
		@filter-location="filterLocation"></projects-list>

		<pagination
		v-if="paginateData.last_page !== 1"
		:pagination="paginateData"
		:per_page="5"
		:offset="3"
		@paginate="getData"></pagination>
	<!-- 	@paginate-page="setPage"
		@paginate-per-page="setPerPage" -->

	</div>
</template>
<script>
	import { mapState, mapActions, mapMutations } from 'vuex'
	import Filter from '../../layouts/Filter.vue'
	import ProjectsList from './ProjectsList.vue'
	import Pagination from '../../layouts/Paginate.vue'

	export default {
		components: {
			ProjectsList,
			Pagination,
			'custom-filter': Filter
		},

		data() {
			return {
				// form: {},
				projects: [{
						name: '',
						category: {
							name: ''
						},
						author: {
							name: ''
						},
						pay_type: {
							name: ''
						},
						skills: [{
							name: ''
						}],
						preffered_qualification: {
							country: {
								name: ''
							}
						}

				}],
				paginateData: {
					current_page: 1,
					per_page: 5,
				},
				skill: null,
				category: null,
				location: null,
				filter_current: [],
				filter_meta: {},
			}
		},

		mounted() {
			if(this.$route.params.skill) {
				this.filterSkill(this.$route.params.skill)
			}
			if(this.$route.params.category) {
				this.filterCategory(this.$route.params.category)
			}
			if(this.$route.params.location) {
				this.filterLocation(this.$route.params.location)
			}
		},

		methods: {
			cleanProps() {
				this.skill = null
			},
			cleanLocation() {
				this.location = null
			},
			filterCategory(category) {
				this.category = category
			},
			filterSkill(skill) {
				this.skill = skill
				this.$refs.filter.pushToFilter('skills', skill);
			},
			filterLocation(location) {
				this.location = location
			},
			getData(data) {

				if (data !== undefined) {
					this.filter_current = data;
				}

				if(this.$route.meta.platformname == 'main') {
					axios.get(this.$root.default_api_prefix + '/jobs' + '?status=1&page=' + this.paginateData.current_page + '&per_page=' + this.paginateData.per_page, {params: {filters: this.filter_current}})
					.then(response => {
						this.projects = response.data.data
						this.paginateData = Object.assign({}, response.data.links, response.data.meta);
						Vue.set(this, 'filter_meta', response.data.filter_meta);
					});
				}
				
				if(this.$route.meta.platformname == 'tribunal') {
					axios.get(this.$root.default_api_prefix + '/jobs' + '?status=2&page=' + this.paginateData.current_page + '&per_page=' + this.paginateData.per_page, {params: {filters: this.filter_current}})
					.then(response => {
						this.projects = response.data.data
						this.paginateData = Object.assign({}, response.data.links, response.data.meta);
						Vue.set(this, 'filter_meta', response.data.filter_meta);
					});
				}
			}
		},
		
		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getData();
			});
		}
	}
</script>