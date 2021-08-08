<template>
	<article class="container pt-4">

			
			<div class="row justify-content-between pt-4 mb-3">
			<!-- Breadcrumbs -->
				<div class="breadcrumb">
					<ul>
						<li 
							v-for="(breadcrumb, index) in $root.breadcrumbList"
							:key="index"
							v-on:click="$root.RouteTo(index)"
							:class="{'linked pointer': !!breadcrumb.link}">
							{{breadcrumb.name}}
						</li>
						<li>
							{{project.name}}
						</li>
					</ul>
				</div>
		<template v-if="auth">

				<div class="d-flex align-items-center" v-if="$route.meta.platformname == 'main'">
					<template v-if="project.author_id == auth.id">

						<template v-if="project.status == 1">
							<router-link :to="{ name: 'view-project', params: {id: project.id}}" tag="a" class="btn btn-transparent text-uppercase py-2 px-4">Edit project</router-link>
							<router-link class="btn btn-transparent text-uppercase py-2 px-4 mx-2" 
								:to="{ name: 'offerform', params: {project_id: project.id }}" 
								tag="a"
								v-if="project.author_id == auth.id && jobBids.length > 0">
								Create Offer
							</router-link>
						</template>

						<template v-if="project.status == 2">
							<router-link tag="a" :to="{name: 'task'}" class="btn btn-transparent text-uppercase py-2 px-4 mr-3">
								Go to task
							</router-link>
							<router-link tag="a" :to="{ name: 'dispute', params: {id: project.id} }" class="btn btn-transparent br-dark-red text-dark-red text-uppercase py-2 px-4 mr-3"
								v-if="project.status == 2">
								Sue To Tribunal
							</router-link>
							<button class="btn btn-transparent text-uppercase py-2 px-4" @click="closeProject">
								Close Project
							</button>
						</template>

					</template>

					<template v-if="auth.role_name == 3">

						<router-link class="btn btn-transparent text-uppercase py-2 px-4 mx-2" 
							:to="{name: 'view-offerform', params: {offer_id: offer_id, project_id: project.id}}" 
							tag="a"
							v-if="haveOffer && project.status == 1">
							Show Offer
						</router-link>
						
						<router-link tag="a" :to="{name: 'task'}" class="btn btn-transparent text-uppercase py-2 px-4 mr-3" v-if="project.status == 2">
							Go to task
						</router-link>

						<router-link tag="a" :to="{ name: 'dispute', params: {id: project.id} }" class="btn btn-transparent br-dark-red text-dark-red text-uppercase py-2 px-4 mx-2"
							v-if="project.status == 2">
							Sue To Tribunal
						</router-link>
					</template>
				</div>
		</template>
			</div>

	<div class="list-card row">
		<div class="col-lg-8 pr-lg-5">
			<div class="d-flex justify-content-between">
				<h1 class="title-card">{{project.name}}</h1>
				<div class="d-flex flex-column align-items-end">
					<p class="subtitle-card mb-0" v-if="project.created_at">{{dateFormat(project.created_at)}}</p>
				</div>
			</div>

			<div class="d-flex justify-content-between mb-3">
				<div class="d-flex">
					<h6 class="mb-0 mr-3 text-dark-easy">Qualification:</h6>
					<h6 class="mb-0">{{project.category.name}}</h6>
				</div>
			</div>
			
			<template v-if="project.skills">
				<div class="d-flex flex-wrap align-items-center mb-3" v-if="project.skills.length > 0">
					<h6 class="mb-3 mr-3 text-dark-easy">Skills:</h6>
					<router-link :to="{ name: 'projects', params: {skill: skill}}" tag="a" class="btn bg-light-easy text px-4 mr-3 mb-3" v-for="(skill, index) in project.skills" :key="index">{{skill.name}}</router-link>
				</div>
			</template>

			<h6 class="mb-3 text-dark-easy">Description</h6>
			<p class="description-card mb-5" v-html="project.description"></p>

			<template v-if="auth">
				<template v-if="auth.id !== project.author_id && !haveOffer && $route.meta.platformname == 'main'">
					<h5 class="mb-4">User Info</h5>

					<div class="d-flex align-items-center">
						<img :src="$root.userImage(project.author)" alt="user" class="rounded-circle bg-light-easy mr-3" width="60" height="60">
						<span class="status user-status bg-green"></span>
						<router-link :to="{ name: 'user', params: {id: project.author_id}}" tag="a" class="text mb-0">{{project.author.name}}</router-link>
						<div class="mx-3">
							<i class="fa fa-star text-yellow mr-1"></i>
							<i class="fa fa-star text-yellow mr-1"></i>
							<i class="fa fa-star text-yellow mr-1"></i>
							<i class="fa fa-star text-yellow mr-1"></i>
							<i class="fa fa-star text-yellow mr-1"></i>
						</div>
						<p class="mr-3 mb-0 text-dark-easy">48 review</p>
						<h6 class="mb-0" v-if="project.preffered_qualification !== null">
							<i class="fa fa-map-marker text-easy mr-2"></i>
							{{project.preffered_qualification.country.name}}
						</h6>
					</div>
				</template>
			</template>

		</div>

		<div class="col-lg-4">
			<h5 class="mb-5">Whant to do this project ?</h5>
			<div class="d-flex justify-content-between mb-3">
				<h5 class="text-dark-easy">Budget:</h5>
				<h5 class="text">
					${{project.pay_amount}}
					<template v-if="project.pay_type_id == 1">
						/ hour
					</template>
					<template v-if="project.pay_type_id == 3">
						/ week
					</template>
				</h5>
			</div>
			<div class="d-flex justify-content-between mb-3">
				<h5 class="text-dark-easy">Pay Type:</h5>
				<h5 class="text">{{project.pay_type.name}}</h5>
			</div>
			<div class="d-flex justify-content-between mb-3">
				<h5 class="text-dark-easy">Entry level:</h5>
				<h5 class="text">{{project.experience_lvl.name}}</h5>
			</div>
			<div class="d-flex justify-content-between mb-3">
				<h5 class="text-dark-easy">Length:</h5>
				<h5 class="text">
						Less than 1 month
				</h5>
			</div>
			<div class="d-flex justify-content-between" v-if="project.status == 1">
				<h5 class="text-dark-easy">Proposals:</h5>
				<h5 class="text">
					{{jobBids.length}}
				</h5>
			</div>


			<template v-if="auth">

				<div class="col-12 pt-4" v-if="$route.meta.platformname == 'main' && (!haveBid && project.status == 1 && auth.role_name == 3) || (auth.role_name == 4 && project.author_id !== auth.id)">
					<!-- Main Platform -->
					<div class="d-flex justify-content-center w-100">
						<router-link class="btn bg-easy py-2 text-white easy-shadow text-uppercase w-75"
							tag="a"
							:to="{ name: 'formbid', params: {project_id: project.id, user_id: auth.id}}"
							v-if="!haveBid && project.status == 1 && auth.role_name == 3">
							Do this Job
						</router-link>
						<router-link class="btn bg-easy py-2 text-white easy-shadow text-uppercase w-75"
							tag="a"
							:to="{ name: 'same-project', params: {project_id: project.id}}"
							v-if="auth.role_name == 4 && project.author_id !== auth.id">
							Post A Job like this
						</router-link>
					</div>
				</div>

				<div class="col-12 pt-4" v-if="$route.meta.platformname == 'tribunal'">
					<!-- Tribunal Platform -->
					<div class="d-flex flex-column justify-content-center align-items-center w-100">
						<button class="btn bg-easy py-2 text-white easy-shadow text-uppercase w-75 mb-3">Vote</button>
						<button class="btn bg-easy py-2 text-white easy-shadow text-uppercase w-75">Chat</button>
					</div>
				</div>
			</template>
		</div>


	</div>
		</div>

		<div class="row mb-4" v-if="auth">
			<div class="col-lg-8 pl-lg-0" v-if="project.status == 1 && jobBids.length > 0 && !haveOffer && $route.meta.platformname == 'main'">
				<div class="list-card mb-0 pb-0 pr-0">
					<h5 class="mb-4">
						{{jobBids.length}} freelancers requesting this job
					</h5>
					<div class="project-bids">
						<user-job-bids :project="project"></user-job-bids>
					</div>
				</div>
			</div>

			<div class="col-lg-4 pl-lg-0 h-100" v-if="auth.id !== project.author_id && haveOffer && $route.meta.platformname == 'main'">
				<div class="list-card pb-0 mb-0">
					<h5 class="mb-3">Employer Profile</h5>
					<div class="p-4">
						<div class="d-flex align-items-center mb-3 users_header">
							<img :src="$root.userImage(project.author)" alt="user" class="rounded-circle mr-3 bg-light-easy" width="70" height="70">
							<!-- <span class="status bg-green mt-65"></span> -->
							<img src="/images/platform/icons/top.png" alt="" class="top_user mt-65">
							<div class="d-flex flex-column">
								<div class="d-flex align-items-center mb-2">
									<router-link :to="{ name: 'user', params: {id: project.author.id}}" tag="a" class="mb-0 mr-3 title-card">{{project.author.name}}</router-link>
								</div>
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

						<p class="description-card">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Possimus dignissimos ex neque, quibusdam officia nisi iure minima itaque?</p>

						<div class="d-flex justify-content-between mb-2">
							<p class="mb-0">58 projects</p>

							<button class="btn btn-transparent text border-0 pr-0" @click="filterLocation('Not India')">
								<i class="fa fa-map-marker text-easy mr-2"></i>
								Not India
							</button>
						</div>

						<div class="d-flex justify-content-between align-items-center mb-4">
							<div class="progress w-50 bg-light-easy">
								<div class="progress-bar bg-text" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
							<p class="mb-0 text-dark-easy">75% Success</p>
						</div>

						<div class="d-flex justify-content-center">
							<button class="btn bg-easy easy-shadow text-white text-uppercase px-5 py-2 w-75">Chat</button>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-4 pl-lg-0 h-100" v-if="project.status !== 1 && auth.id == project.author_id && $route.meta.platformname == 'main'">
				<div class="list-card pb-0 mb-0" v-if="project.job_freelancer_attached">
					<h5 class="mb-3">{{project.job_freelancer_attached.length}} freelancer
					<template v-if="project.job_freelancer_attached.length > 1">
						s
					</template>
					are doing this job</h5>
					<div class="p-4"  v-for="freelancer in project.job_freelancer_attached">
						<template v-if="freelancer.freelancer">
							<div class="d-flex align-items-center mb-3 users_header">
								<img :src="$root.userImage(freelancer)" alt="user" class="rounded-circle mr-3 bg-light-easy" width="70" height="70">
								<!-- <span class="status bg-green mt-65"></span> -->
								<img src="/images/platform/icons/top.png" alt="" class="top_user mt-65">
								<div class="d-flex flex-column">
									<div class="d-flex align-items-center mb-2">
										<router-link :to="{ name: 'user', params: {id: freelancer.id}}" tag="a" class="mb-0 mr-3 title-card">{{freelancer.freelancer.name}}</router-link>
									</div>
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

							<p class="description-card">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Possimus dignissimos ex neque, quibusdam officia nisi iure minima itaque?</p>

							<div class="d-flex flex-wrap mb-2">
								<span class="btn bg-light-easy mr-2 mb-3" @click="filterSkill('html')">html</span>
								<span class="btn bg-light-easy mr-2 mb-3" @click="filterSkill('css')">CSS</span>
								<span class="btn bg-light-easy mr-2 mb-3" @click="filterSkill('JQuery')">JQuery</span>
							</div>

							<div class="d-flex justify-content-between mb-2">
								<span>$40/hour</span>
								<button class="btn btn-transparent text border-0 pr-0" @click="filterLocation('Not India')" v-if="freelancer.freelancer.profile.country">
									<i class="fa fa-map-marker text-easy mr-2"></i>
									{{freelancer.freelancer.profile.country.name}}
								</button>
							</div>

							<div class="d-flex justify-content-between align-items-center mb-4">
								<div class="progress w-50 bg-light-easy">
									<div class="progress-bar bg-text" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<p class="mb-0 text-dark-easy">75% Success</p>
							</div>

							<div class="d-flex justify-content-center">
								<button class="btn bg-easy easy-shadow text-white text-uppercase px-5 py-2 w-75">Chat</button>
							</div>
						</template>
					</div>
				</div>
			</div>

			<template v-if="project.attachments && $route.meta.platformname == 'main'">
			<div class="pr-lg-0 ml-auto" :class="[project.status == 1 && !haveOffer  ? 'col-lg-4' : 'col-lg-8']" v-if="project.attachments.length > 0">
				<div class="list-card h-100 mb-0">
					<h5 class="mb-3">File</h5>
					<div class="row">
						<div class="mb-3" :class="[project.status == 1 && !haveOffer  ? 'col-lg-6' : 'col-lg-4 col-xl-3']" v-for="doc in project.attachments">

							<img :src="doc.link" alt="" class="w-100 pointer" data-toggle="modal" data-target="#documentModal" @click="image = doc" v-if="doc.mime_type == 'image/jpeg' || doc.mime_type == 'image/png' || doc.mime_type == 'image/jpg' || doc.mime_type == 'image/svg+xml'">

							<a :href="doc.link" target="_blank" v-if="doc.mime_type == 'application/pdf' || doc.mime_type == 'application/docx' || doc.mime_type == 'application/doc' || doc.mime_type == 'application/msword' || doc.mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'">
								<img src="/images/file-icon.png" class="w-100">
							</a>

						</div>
					</div>
				</div>
			</div>
			</template>

		</div>
	</div>

	<div class="modal fade back" id="documentModal" tabindex="-1" role="dialog" aria-hidden="true">
		<button type="button" class="close mt-4 mr-4" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<div class="modal-dialog modal-lg body-search" role="document">
			<div class="modal-content back-modal">
				<div class="modal-body" v-if="image !== null">
					<div class="input-group input-group-lg offset-1 col-10 mx-auto border-img-none">
						<img :src="image.link" class="w-100 h-100">
					</div>
					<div class="d-flex justify-content-center py-4">
						<a :download="image.real_name" :href="image.link" :title="image.real_name" class="btn btn-easy easy-shadow py-2 px-5 text-white text-uppercase" data-dismiss="modal" aria-label="Close">download</a>
					</div>
				</div>
			</div>
		</div>
	</div>

</article>
</template>
<script>
	import { mapActions, mapState } from 'vuex'
	import UserRequests from './UserRequests.vue'
	import UserJobBids from './UserJobBids.vue'
	export default {
		components: {
			UserRequests,
			UserJobBids
		},

		data() {
			return {
				offer_id: null,
				haveBid: false,
				haveOffer: false,
				image: null,
				jobBids: [],
				project: {
					id: '',
					author: {
						id: '',
						name: ''
					},
					category: {
						id: '',
						name: ''
					},
					skills: [{
						id: '',
						name: ''
					}],
					pay_type: {
						id: '',
						name: ''
					},
					experience_lvl: {
						id: '',
						name: ''
					},
					job_freelancer_attached: [],
					preffered_qualification: {
						country:  {
							name: ''
						}
					}
				}
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
		},

		watch: {
			auth(val) {
				if(val !== null) {
					if(this.auth.role_name == 3) {
						this.checkBid()
					}
				}
			}
		},

		mounted() {
			Bus.$on('update-bids', data => {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id +'/jobbids?per_page=6').then(response => {
					this.jobBids = response.data.data
					if(this.auth !== null) {
						if(this.auth.role_name == 3) {
							this.checkBid()
						}
					}
				})
			})
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
			checkBid() {
				axios.get(this.$root.default_api_prefix + '/users/' + this.auth.id + '/jobs/' + this.$route.params.id)
						.then(response => {
							this.haveBid = response.data.bid

							this.haveOffer = false
							this.offer_id = null

							if(response.data.offer !== null) {
								for (var i = 0; i < response.data.offer.length; i++) {
									if(response.data.offer[i].freelancer_id == this.auth.id) {
										this.haveOffer = true
										this.offer_id = response.data.offer[i].id
									} 
								}
							}

						})
			},
			closeProject() {
				axios.put(this.$root.default_api_prefix + '/users/' + this.project.job_freelancer_attached[0].freelancer_id + '/jobs/' + this.project.id + '/done')
				.then(response => {
					Bus.$emit('notification', {text: 'This Project was Close'})
					this.$router.push({name: 'my_projects'})
				})
			},
			allRequests() {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id +'/jobbids')
				.then(response => {
					this.jobBids = response.data
				})
			}
		},

		created() {
			axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id +'/jobbids?per_page=6').then(response => {
				this.jobBids = response.data.data
			})

			axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id)
			.then(response => {
				this.project = response.data
			})
		},

		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getAuth()
			});
		},

	}
</script>