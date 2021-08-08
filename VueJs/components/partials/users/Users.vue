<template>
	<div class="container">
		<div class="col-12 d-flex my-5 px-0">
			<a class="table-pills" :class="{'active': active_tab == $root.role_freelancer_name}" role="button" @click="changeTab($root.role_freelancer_name)">FREELANCERS</a>
			<a class="table-pills" :class="{'active': active_tab == $root.role_agency_name}" role="button" @click="changeTab($root.role_agency_name)">AGENCIES</a>
			<a class="table-pills w-100" :class="{'active': active_tab == $root.role_employer_name}" role="button" @click="changeTab($root.role_employer_name)">EMPLOYERS</a>
		</div>

		<custom-filter
		ref="filter"
		:placeholder="'Enter the name of project or skill'"
		:skill="skill"
		:category="category"
		:location="location"
		:meta="filter_meta"
		:settings="filter_settings"
		@get-filtered="getData"
		@clean-props="cleanProps"
		@clean-location="cleanLocation"></custom-filter>
		
		<users-list
		:users="users"
		:auth="auth"
		@filter-skill="filterSkill"
		@filter-location="filterLocation"></users-list>

		<pagination
		v-if="paginateData.last_page !== 1"
		:pagination="paginateData"
		:per_page="1"
		:offset="3"
		@paginate="getData()"></pagination>

	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import Filter from '../../layouts/Filter.vue'
	import Pagination from '../../layouts/Paginate.vue'
	import UsersList from './UsersList.vue'

	export default {
		components: {
			UsersList,
			Pagination,
			'custom-filter': Filter
		},

		watch: {
			active_tab(val, oldVal) {
				if (val != oldVal) {
					this.onTabChanged(val);
				}
			}
		},

		data() {
			return {
				paginateData: {
					current_page: 1,
					per_page: 6,
				},
				users: [],
				skill: null,
				category: null,
				location: null,
				filter_current: [],
				filter_meta: {},
				filter_settings: {},
				// activeTable: 'open',
				active_tab: this.$root.role_freelancer_name,
				// allLoad: true,
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
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
				this.skill = skill;
				this.$refs.filter.pushToFilter('skills', skill);
			},
			filterLocation(location) {
				this.location = location
			},
			setFilterSettings(active_tab) {
				let settings = {};
				switch (active_tab) {
					case this.$root.role_agency_name:
						settings = {
							experience_lvl: {
								hide: true,
							},
							client_rating: {
								title: "Job success",
							},
							freelancer_type: {
								hide: true,
							},
							hourly_rate: {
								hide: true,
							},
							budget: {
								hide: true,
							},
						};
						break;
					case this.$root.role_employer_name:
						settings = {
							experience_lvl: {
								hide: true,
							},
							client_rating: {
								title: "Positive feedback",
							},
							freelancer_type: {
								hide: true,
							},
							hourly_rate: {
								hide: true,
							},
							budget: {
								hide: true,
							},
						};
						break;
					case this.$root.role_freelancer_name:
						settings = {
							client_rating: {
								title: "Job success",
							},
							freelancer_type: {
								hide: true,
							},
							hourly_rate: {
								hide: true,
							},
							budget: {
								hide: true,
							},
						};
						break;
				}
				Vue.set(this, 'filter_settings' ,settings);
			},
			getData(data) {
				if (data !== undefined) {
					this.filter_current = data;
				}

				let params = {
					page: this.paginateData.current_page,
					per_page: this.paginateData.per_page,
					filters: this.filter_current,
					role: this.active_tab
				}

				axios.get(this.$root.default_api_prefix + '/users', {params: params})
				.then(response => {
					this.users = response.data.data;
					this.paginateData = Object.assign({}, response.data.links, response.data.meta);
				});
			},
			changeTab(tab_name) {
				this.active_tab = tab_name;
			},
			onTabChanged(tab_name) {
				//set filter settings for tab
				//datas will be loaded when filter changed settings and emit get-filtered event with new filters
				this.setFilterSettings(tab_name);
				this.getData();
			}

		},

		beforeRouteEnter(to, from, next) {
			next(vm => {
				//choose tab if request
				let role_req = to.query.role;
				vm.$router.replace({ query: null });
				if (role_req) {
					console.log("REQ_ROLE", role_req);
					switch (role_req) {
						case vm.$root.role_agency_name:
							vm.active_tab = vm.$root.role_agency_name;
							break;
						case vm.$root.role_employer_name:
							vm.active_tab = vm.$root.role_employer_name;
							break;
						default:
							vm.active_tab = vm.$root.role_freelancer_name;
							break;
					}
				}
				//force change step event method and load Data because on this step watcher not active yet
				vm.onTabChanged(vm.active_tab);	
			});
		}

	}
</script>