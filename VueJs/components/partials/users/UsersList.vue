<template>
	<div class="row">
		<div class="col-md-6 col-lg-4 mb-4" v-for="user in users">

			<div class="list-card h-100 mb-0 d-flex flex-column justify-content-between">


				<div>
					<div class="d-flex align-items-center users_header w-100 mb-2">
						<img :src="$root.userImage(user)" alt="user" class="rounded-circle mr-3 bg-light-easy" width="70" height="70">
						<span class="status bg-green"></span>
						<!-- <img src="/images/-platform/icons/top.png" alt="" class="top_user"> -->
						<div class="d-flex flex-column">
							<div class="d-flex align-items-center mb-2">
								<router-link :to="{ name: 'user', params: {id: user.id}}" tag="a" class="mb-0 mr-3 title-card">{{user.profile.fullname ? user.profile.fullname : user.name}}</router-link>
							</div>
							<h6>{{user.profile.description_header}}</h6>
							<div class="d-flex align-items-center">
								<i class="fa fa-star text-yellow mr-2"></i>
								<i class="fa fa-star text-yellow mr-2"></i>
								<i class="fa fa-star text-yellow mr-2"></i>
								<i class="fa fa-star text-yellow mr-2"></i>
								<i class="fa fa-star text-yellow mr-2"></i>
								<p class="mb-0 text-dark-easy">48 review</p>
							</div>
						</div>
					</div>

					<p class="description-card" v-if="user.profile.short_description">{{user.profile.short_description}}</p>

					<div class="d-flex flex-wrap mb-2" v-if="$root.isUserFreelancer(user)">
						<span class="btn bg-light-easy mr-2 mb-3" @click="filterSkill(skill)" v-for="skill in user.skills">{{skill.name}}</span>
					</div>

				</div>



				<div>

				<div class="d-flex justify-content-between mb-2">
					<span v-if="$root.isUserFreelancer(user) && user.profile.details.rate">${{user.profile.details.rate}}/hour</span>
					<button class="btn btn-transparent text border-0 pr-0" @click="filterLocation('Not India')" v-if="user.profile.country">
						<i class="fa fa-map-marker text-easy mr-2"></i>
						{{user.profile.country.name}}
					</button>
				</div>

				<div class="d-flex justify-content-between align-items-center mb-4">
					<div class="progress w-50 bg-light-easy">
						<div class="progress-bar bg-text" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
					<p class="mb-0 text-dark-easy">75% Success</p>
				</div>

				</div>
			</div>

		</div>
	</div>
</template>
<script>
	export default {

		props: {
			users: {
				type: Array,
				default: function () { return [] }
			},
		},

		data() {
			return {

			}
		},

		computed: {

		},

		mounted() {

		},

		methods: {
			filterSkill(skill) {
				if(this.$route.name !== 'users') {
					this.$router.push({name: 'users', params: {skill: skill}})
				} else {
					this.$root.scrollToTop()
					this.$emit('filter-skill', skill)
				}
			},
			filterLocation(location) {
				if(this.$route.name !== 'users') {
					this.$router.push({name: 'users', params: {location: location}})
				} else {
					this.$root.scrollToTop()
					this.$emit('filter-location', location)
				}
			}
		},

		created() {

		}

	}
</script>