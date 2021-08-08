<template>
	<div>
		<div class="row user-header px-0 mb-4">
			<div class="breadcrumb mb-4 w-100">
				<div class="container w-100">
						<ul class="pl-0">
							<li 
							v-for="(breadcrumb, index) in $root.breadcrumbList"
							:key="index"
							v-on:click="$root.RouteTo(index)"
							:class="{'linked pointer text-white': !!breadcrumb.link}">
							{{breadcrumb.name}}
						</li>
						<li class="text-light-easy">
							{{user.name}}
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="container" v-if="loaded">
			<div class="row">
				<div class="col-lg-8">

					<div class="list-card user-header-card">

						<!-- Header -->
						<div class="d-flex mb-4">
							<div class="d-flex flex-column align-items-center">
								<img :src="$root.userImage(user)" alt="user" width="230" height="230" class="rounded-circle user-avatar mb-2">
								<p class="mb-0">@{{user.name}}</p>
							</div>
							<span class="status bg-green ml-4 mt-2"></span>
							<div class="d-flex flex-column pl-2">
								<div class="d-flex align-items-center mb-3">
									<h1 class="title-card mb-0 mr-4">{{user.name}}</h1>
									<span class="btn btn-grey" v-if="$root.isUserAdmin(user)">admin</span>
									<router-link :to="routeToUsersList" tag="a" class="mb-0 mr-3 title-card">
										<span class="btn btn-grey">{{user.role.name}}</span>
									</router-link>
								</div>
								<!-- <h6 class="mb-3">Web Developer</h6> -->
								<h5 class="mb-3" v-show="user.profile.fullname">{{user.profile.fullname}}</h5>
								<div class="d-flex">
									<p class="mb-0" v-if="user.profile.country">
										<i class="fa fa-map-marker text-easy mr-2"></i>
										{{user.profile.country.name}}
									</p>
									<div class="mr-3">
										<i class="fa fa-star text-yellow mr-1"></i>
										<i class="fa fa-star text-yellow mr-1"></i>
										<i class="fa fa-star text-yellow mr-1"></i>
										<i class="fa fa-star text-yellow mr-1"></i>
										<i class="fa fa-star text-yellow mr-1"></i>
									</div>
									<span class="text-dark-easy">48 reviews <span class="px-2 text-dark-easy">|</span> 59 projects</span>
								</div>
							</div>
						</div>

						<!-- Description -->
						<div class="mb-4">
							<h4>{{user.profile.description_header}}</h4>
							<p v-html="user.profile.description" v-if="user.profile.description"></p>
						</div>

					</div>

					<!-- Portfolio -->
					<div class="list-card pb-0 mb-5" v-if="user.portfolios.length > 0">
						<div class="d-flex justify-content-between mb-2">
							<h4>Portfolio</h4>
							<!-- <a href="##" class="text text-underline">See full profile</a> -->
						</div>
						<div class="row">
							<div class="col-lg-3 col-md-6 mb-4 hover-delete" v-for="(image, index) in user.portfolios">
								<div class="hidden-card d-flex justify-content-center align-items-center">
									<button class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button" data-toggle="modal" data-target="#portfolioModal" @click="showImage(image)">
										<i class="fa fa-eye"></i>
									</button>
								</div>
									<img :src="image.images[0].link" alt="" class="w-100 h-100">
							</div>
						</div>
					</div>

				</div>

				<div class="col-lg-4 pl-lg-4 pt-4">
					<template v-if="$root.isUserFreelancer(user)"> 
						<div class="list-card mb-5 px-5 py-4">
							<h5 class="fs-20 mb-4">Want to work with me ?</h5>
							<div class="d-flex justify-content-between align-items-center mb-3" v-if="user.profile.details.rate">
								<h5 class="fs-20 mb-0">Price:</h5>
								<p class="fs-20 mb-0 text-dark-easy"><strong class="mr-1 text">{{user.profile.details.rate}}</strong> USD/hr</p>
							</div>
							<div class="d-flex justify-content-between align-items-center mb-3">
								<h5 class="fs-20 mb-0">Projects:</h5>
								<p class="fs-20 mb-0 text-dark-easy"><strong class="mr-1 text">90%</strong> Success</p>
							</div>
							<a role="button" class="btn bg-easy easy-shadow text-white text-uppercase w-100 mb-4 py-1" v-if="$root.isUserEmployer(auth)">Hire Me</a>
						</div>

						<div class="list-card px-5 py-4" v-if="user.skills.length > 0">
							<h5 class="mb-4 fs-20">Strong skills</h5>
							<div class="d-flex justify-content-between" v-for="skill in user.skills">
								<p class="fs-18">-&nbsp;{{skill.name}}</p>
							</div>
						</div>
					</template>
					<!-- If employer -->
					<template v-else-if="$root.isUserEmployer(user)">
						<div class="list-card mb-5 px-5 py-4">
							<h5 class="fs-20 mb-4">Want to work with me ?</h5>
							<div class="d-flex justify-content-between align-items-center mb-3">
								<h5 class="fs-20 mb-0">Total Projects:</h5>
								<p class="fs-20 mb-0 text-dark-easy"><strong class="mr-1 text">{{user.jobs_count ? user.jobs_count : 0}}</strong></p>
							</div>
							<div class="d-flex justify-content-between align-items-center mb-3">
								<h5 class="fs-20 mb-0">Rating:</h5>
								<p class="fs-20 mb-0 text-dark-easy"><strong class="mr-1 text">90%</strong></p>
							</div>
							<div class="d-flex justify-content-between align-items-center mb-4">
								<h5 class="fs-20 mb-0">Budget spent:</h5>
								<p class="fs-20 mb-0 text-dark-easy"><strong class="mr-1 text">$10000</strong></p>
							</div>
							<a role="button" class="btn bg-easy easy-shadow text-white text-uppercase w-100 mb-4 py-2" v-if="$root.isUserFreelancer(auth)">Contact me</a>
						</div>
					</template>
				</div>
			</div>
		</div>

		<portfolio :item="item" :auth="auth"></portfolio>

	</div>
</template>
<script>
	import Portfolio from '../../modals/Portfolio'
	import UserRequests from '../projects/UserRequests.vue'
	import { mapActions, mapState } from 'vuex'

	export default {
		components: {
			UserRequests,
			Portfolio
		},

		data() {
			return {
				item: null,
				loaded: false, //if user info loaded
				skills: [1,2,3,4,5,6,7,8],
				user: {
					portfolios: []
				}
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
			routeToUsersList() {
				let query = {role: this.user.role.name};
				return { name: 'users', query};
			},
		},

		mounted() {

		},

		methods: {
			showImage(item) {
				this.item = item
			},
		},

		created() {
			axios.get(this.$root.default_api_prefix + '/users/' + this.$route.params.id)
			.then(response => {
				this.user = response.data
			})
		},
		beforeRouteEnter(to, from, next) {
			next(vm => {
				let user_id = to.params.id;
				axios.get(`${vm.$root.default_api_prefix}/users/${user_id}`)
				.then(response => {
					vm.user = response.data;
					vm.loaded = true;
				})
				.catch(error => {
					console.error(error);
				})
			});
		}

	}
</script>