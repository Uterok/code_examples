<template>
	<div class="container home_section">

		<page-header class="mb-4"></page-header>

		<!-- Best Categories -->
		<section v-if="categories.length > 0">
			<h6 class="text-uppercase text-dark-easy mb-3">Checkout the best Categories</h6>
			<div class="d-flex justify-content-between align-items-center mb-5">
				<div>
					<h2 class="mb-0">Find the best categories in the world</h2>
				</div>
			</div>

			<categories-list :categories="categories" class="mb-5"></categories-list>
		</section>

		<!-- Best Projects -->
		<template v-if="projects.data">	
			<section class="pt-5" v-if="projects.data.length > 0">
				<h6 class="text-uppercase text-dark-easy mb-3">Checkout the best projects</h6>
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h2 class="mb-0">Find the best projects in the world</h2>
					</div>
				</div>
				<div class="d-flex justify-content-end">
					<router-link :to="{ name: 'projects' }" tag="a" class="text text-underline text-capitalize mb-2">
						view all
					</router-link>
				</div>

				<projects-list :projects="projects.data" class="mb-5"></projects-list>
			</section>
		</template>
		
		<!-- Best Freelancers -->
		<template v-if="users.data">
			<section class="pt-5" v-if="users.data.length > 0">
				<h6 class="text-uppercase text-dark-easy mb-3">Checkout the best community</h6>
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h2 class="mb-0">Find the best workers in the world</h2>
					</div>
				</div>
				<div class="d-flex justify-content-end">
					<router-link :to="{ name: 'users' }" tag="a" class="text text-underline text-capitalize mb-2">
						view all
					</router-link>
				</div>

				<users-list :users="users.data" class="mb-5"></users-list>
			</section>
		</template>
		
	</div>
</template>
<script>
	import PageHeader from './PageHeader.vue'
	import ProjectsList from '../projects/ProjectsList.vue'
	import CategoriesList from './CategoriesList.vue'
	import UsersList from '../users/UsersList.vue'
	import { mapState } from 'vuex'

	export default {
		components: {
			PageHeader,
			ProjectsList,
			CategoriesList,
			UsersList
		},

		data() {
			return {
				users: []
			}
		},

		computed: {
			...mapState({
				projects: state => state.projects.projects,
				categories: state => state.categories.categories,
			}),
		},

		methods: {
		},

		created() {
			axios.get(this.$root.default_api_prefix + '/users?role=freelancer')
			.then(response => {
				this.users = response.data
			})
		}

	}
</script>