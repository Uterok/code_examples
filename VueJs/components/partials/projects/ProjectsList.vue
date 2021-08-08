<template>
	<article>
		<section class="list-card" v-for="project in projects">

			<div class="d-flex justify-content-between mb-2">
				<div class="d-flex flex-column">
					<div class="d-flex align-items-center">
						<router-link :to="{ name: [$route.meta.platformname == 'main' ? 'project' : 'dispute'], params: {id: project.id}}" tag="a" class="title-card">
							{{project.name}}
						</router-link>
						<span class="mx-3">|</span>
						<p class="mb-0 text-dark-easy">{{dateFormat(project.created_at)}}</p>
						<template>
							<span class="mx-3">|</span>
							<p class="mb-0 text-green">NEW</p>
						</template>
					</div>
					<a role="button" class="mb-0 subtitle-card" @click="filterCategory(project.category.name)">{{project.category.name}}</a>
				</div>

				<div class="d-flex align-items-center">
					<h5 class="mr-2 mb-0">{{project.pay_type.name}}:</h5>
					<strong>${{project.pay_amount}}</strong>
				</div>
			</div>

			<div class="d-flex justify-content-between align-items-start">
				<div class="d-flex flex-column pr-5">

					<p v-html="project.description"></p>

					<div class="d-flex flex-wrap" v-if="project.skills.length > 0">
						<a role="button" class="btn btn-grey px-4 mr-2 mb-3" @click="filterSkill(skill)" v-for="skill in project.skills">{{skill.name}}</a>
					</div>
					
					<!-- Main Platform-->
					<template v-if="$route.meta.platformname == 'main'">
						<div class="d-flex align-items-end" v-if="project.job_freelancer_attached.length > 0">
							<p class="mb-0 mr-2">Proposals:</p>
							<h4 class="mb-0">{{project.job_freelancer_attached.length}}</h4>
						</div>

						<div class="d-flex align-items-center">
							<span class="status bg-green mr-3"></span>
							<router-link :to="{ name: 'user', params: {id: project.author_id}}" tag="a" class="text">{{project.author.name}}</router-link>
							<div class="mx-3">
								<i class="fa fa-star text-yellow mr-1"></i>
								<i class="fa fa-star text-yellow mr-1"></i>
								<i class="fa fa-star text-yellow mr-1"></i>
								<i class="fa fa-star text-yellow mr-1"></i>
								<i class="fa fa-star text-yellow mr-1"></i>
							</div>
							<span class="mr-2 text-dark-easy">48 review</span>
							<h6 class="mb-0 ml-2" v-if="project.preffered_qualification !==null">
								<i class="fa fa-map-marker mr-1 text-easy"></i>
								{{project.preffered_qualification.country.name}}
							</h6>
						</div>
					</template>
				</div>

				<!-- Main Platform -->
				<template v-if="$route.meta.platformname == 'main'">
					<router-link :to="{ name: 'formbid', params: {project_id: project.id, user_id: auth.id, role_name: auth.role_name}}" tag="a" v-if="auth && auth.role_name == 3" class="btn btn-easy py-2 text-white text-uppercase easy-shadow px-5">
						DO THIS JOB
					</router-link>
					<router-link :to="{ name: 'same-project', params: {project_id: project.id}}" tag="a" v-else-if="auth && auth.role_name == 4" class="btn btn-easy py-2 text-white text-uppercase easy-shadow px-5">
						POST A JOB LIKE THIS
					</router-link>
					<button class="btn btn-easy py-2 text-white text-uppercase easy-shadow px-5" v-else data-toggle="modal" data-target="#loginModal">
						DO THIS JOB
					</button>
				</template>

				<!-- Tribunal Platform -->
				<template v-if="$route.meta.platformname == 'tribunal'">
					<router-link :to="{ name: 'dispute', params: {id: project.id}}" tag="a" class="btn btn-easy py-2 text-white text-uppercase easy-shadow px-5">
						Resolve
					</router-link>
				</template>

			</div>

		</section>
	</article>
</template>
<script>
	import { mapActions, mapState } from 'vuex'
	export default {

		props: {
			projects: {
				type: Array,
				default: function () { return [] }
			},
		},

		data() {
			return {
				project_id: '',
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
		},

		mounted() {

		},

		methods: {
			...mapActions({
				getAuth: 'user/getAuthUser',
			}),

			dateFormat(date) {
				if(date !== null) {
					return date.substring(0,10)
				}
			},

			filterCategory(category) {
				if(this.$route.name !== 'projects') {
					this.$router.push({name: 'projects', params: {category: category}})
				} else {
					this.$root.scrollToTop()
					this.$emit('filter-category', category)
				}
			},

			filterSkill(skill) {
				if(this.$route.name !== 'projects') {
					this.$router.push({name: 'projects', params: {skill: skill}})
				} else {
					this.$root.scrollToTop()
					this.$emit('filter-skill', skill)
				}
			},

			filterLocation(location) {
				if(this.$route.name !== 'projects') {
					this.$router.push({name: 'projects', params: {location: location}})
				} else {
					this.$root.scrollToTop()
					this.$emit('filter-location', location)
				}
			},

			loginPlease() {
				Bus.$emit('notification', {text: 'To get a job you need to log in as a freelancer!'})
			}
		},

		created() {

		},

		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getAuth()
			});
		},

	}
</script>