<template>
	<section class="pt-5">
		<div class="d-flex mb-5 justify-content-between list-card">
			<div class="d-flex w-100">
				<div class="d-flex flex-column align-items-center">
					<img :src="$root.userImage(auth)" alt="user" width="200" height="200" class="rounded-circle bg-secondary mb-3">
					<h6>@{{auth.name}}</h6>
				</div>
				<div class="d-flex flex-column justify-content-start pl-lg-5 w-100">
					<div class="d-flex justify-content-between align-items-center mb-3 w-100">
						<h1 class="mb-0">{{auth.name}}</h1>
						<div class="d-flex">
							<router-link :to="{ name: 'settings', params: {id: auth.id}}" tag="a" class="btn bg-easy text-white easy-shadow mr-3 text-uppercase px-4 py-2 d-flex align-items-center">edit profile</router-link>
							<router-link :to="{ name: 'user', params: {id: auth.id}}" tag="a" class="btn btn-transparent text-uppercase px-4 py-2 d-flex align-items-center text">view profile</router-link>
						</div>
					</div>
					<template v-if="auth.profile">
						<h4 class="text-dark-easy">{{auth.profile.fullname}}</h4>
					</template>
					<!-- <p class="mb-4">{{auth.email}}</p> -->
					<div class="d-flex">
						<p class="text" v-if="auth.profile">
							<i class="fa fa-map-marker text-easy mr-1"></i>
							{{auth.profile.country ? auth.profile.country.name : 'Location not set'}}
						</p>
					</div>
					<p class="text" v-html="auth.profile.description" v-if="auth.profile"></p>
					<p class="text-dark-easy text-center" v-else>Add some info about yourself</p>

					<div class="row mt-5" v-if="auth.role_name == 3">
						<div class="col-lg-4 col-xl-3">
							<p class="text-dark-easy">Category:</p>
							<p class="text" v-if="auth.profile.category">{{auth.profile.category.name}}</p>
							<p class="text-dark-easy" v-else>Add your categories</p>
						</div>
						<div class="col-lg-4 col-xl-3">
							<p class="text-dark-easy">Experience Level:</p>
							<p class="text" v-if="auth.profile.details.experience_lvl">{{auth.profile.details.experience_lvl.name}}</p>
							<p class="text-dark-easy" v-else>Tell about experience level</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		

			<div class="col-12 list-card mb-5" v-if="$root.isUserEmployer(auth)">
				<div class="row">
					<div class="col-lg-4 d-flex flex-column justify-content-center px-lg-5 border-right br-light-easy">
						<img src="/images/platform/icons/total-projects.svg" alt="total projects" height="54" class="mb-4">
						<div class="d-flex justify-content-between align-items-center">
							<h4 class="mb-0">Total Projects:</h4>
							<h2 class="mb-0">{{auth.jobs_count ? auth.jobs_count : 0}}</h2>
						</div>
					</div>
					<div class="col-lg-4 d-flex flex-column justify-content-center px-lg-5 border-right br-light-easy">
						<img src="/images/platform/icons/feedbacks.svg" alt="feedbacks" height="54" class="mb-4">
						<div class="d-flex justify-content-between align-items-center">
							<h4 class="mb-0">Feedbacks:</h4>
							<div class="d-flex align-items-center">
								<h2 class="mb-0 mr-2">90%</h2>
								<h4 class="mb-0 text-dark-easy">Positive</h4>
							</div>
						</div>
					</div>
					<div class="col-lg-4 d-flex flex-column justify-content-center px-lg-5">
						<img src="/images/platform/icons/budget.svg" alt="budget" height="54" class="mb-4">
						<div class="d-flex justify-content-between align-items-center">
							<h4 class="mb-0">Budget spent:</h4>
							<h2 class="mb-0">$10000</h2>
						</div>
					</div>
				</div>
			</div>


		<!-- Cards -->
		<div class="row mb-5">
			
			<template v-if="$root.isUserFreelancer(auth)">
				
				<div class="col-lg-8">
					<div class="list-card d-flex flex-column">
						<div class="d-flex justify-content-between">
							<h4 class="mb-4">Skills</h4>
						</div>
						<div class="d-flex flex-row flex-wrap" v-if="auth.skills.length">
							<span class="mb-3 mr-3 btn bg-light-easy text px-4" v-for="skill in auth.skills">{{skill.name}}</span>
						</div>
						<div class="d-flex justify-content-center my-5" v-else>
							<p class="text-dark-easy mb-0">Add all your skills</p>
						</div>
					</div>
				</div>


				<div class="col-lg-4">
					<div class="list-card d-flex flex-column">

						<div class="d-flex justify-content-between">
							<h4 class="mb-4">Portfolio</h4>
						</div>

						<div class="row mb-4" v-if="auth.portfolios.length > 0">
							<div class="col-lg-6 mb-3 hover-delete" v-for="(image, index) in auth.portfolios">
								<div class="hidden-card d-flex justify-content-center align-items-center">
									<button class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button" data-toggle="modal" data-target="#portfolioModal" @click="showImage(image)">
										<i class="fa fa-eye"></i>
									</button>
								</div>
									<img :src="image.images[0].link" alt="" class="w-100 h-100">
							</div>
						</div>

						<div class="d-flex justify-content-center mb-5 mt-2" v-else>
							<p class="text-dark-easy mb-0">Add your first work</p>
						</div>

						<div class="d-flex justify-content-center">
							<button class="btn bg-easy text-white easy-shadow text-uppercase py-2 px-5" data-toggle="modal" data-target="#portfolioModal" @click="createPortfolio">add new</button>
						</div>
					</div>
				</div>
			
			</template>
		</div>

		<portfolio :item="item" :auth="auth"></portfolio>

	</section>
</template>
<script>
	import Portfolio from '../../modals/Portfolio'

	export default {

		components: {
			Portfolio
		},

		props: ['lang' ,'auth'],

		data() {
			return {
				item: null,
				user: {
					portfolios: []
				},
				modalType: 'create',
			}
		},

		computed: {
			
		},

		mounted() {

		},

		methods: {
			createPortfolio() {
				this.item = null
			},
			showImage(item) {
				this.item = item
			},
		},

		created() {
			
		},

		beforeRouteEnter(from, to, next) {
			next();
		}

	}
</script>