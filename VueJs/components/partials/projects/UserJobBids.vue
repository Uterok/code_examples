<template>
	<div>
		<section class="d-flex pb-4 mb-4 question-item" v-for="jobBid in jobBids">
			<div class="mr-md-4 d-flex flex-column">
				<span class="status bg-green"></span>
				<img :src="$root.userImage(jobBid.user)" :alt="jobBid.user.name" class="rounded-circle mb-1 bg-light-easy mx-auto pointer" width="70" height="70" @click="userRoute(jobBid.user.id)">
				<router-link :to="{ name: 'user', params: {id: jobBid.user.id}}" tag="a" class="mb-2 text text-center">{{jobBid.user.name}}</router-link>
				<div class="d-flex justify-content-center align-items-center mb-2">
					<i class="fa fa-star text-yellow mr-2"></i>
					<i class="fa fa-star text-yellow mr-2"></i>
					<i class="fa fa-star text-yellow mr-2"></i>
					<i class="fa fa-star text-yellow mr-2"></i>
					<i class="fa fa-star text-yellow mr-2"></i>
				</div>
				<p class="mb-0 text-dark-easy text-center">248 review</p>
			</div>
			<div class="d-flex flex-column justify-content-between w-100">

				<div class="d-flex">
					<div class="col-lg-8 d-flex flex-column pl-0">
						<h5>{{jobBid.title}}</h5>
						<p class="mb-0 text-dark-easy">{{ dateFormat(jobBid.created_at) }}</p>
					</div>
					<div class="col-lg-4 d-flex flex-column flex-grow-1 align-items-end pl-0">
						<p class="text-easy fs-18 mb-1">${{jobBid.price}} USD</p>
						<p></p>
					</div>
				</div>

				<div class="description-card my-4 pr-3">
					<p class="mb-0">{{$options.filters.truncate(jobBid.cover_letter)}}</p>
				</div>

				<div class="d-flex">
					<router-link class="btn bg-easy text-white text-uppercase btn-attach py-1 px-4 mr-2"  :to="{ name: 'offerform', params: {project_id: jobBid.job.id, project: jobBid.job, freelancer: jobBid.user_id }}" 
						tag="a"
						v-if="project.author_id == auth.id">
						Send Offer
					</router-link>
					<button class="btn btn-transparent btn-attach text-uppercase py-1 mr-2" data-toggle="modal" data-target="#chatModal" @click="sendMessage(jobBid)" v-if="project.author_id == auth.id">chat</button>
					<button class="btn btn-transparent btn-attach text-uppercase py-2 px-4 mr-2" @click="declineBid(jobBid)" v-if="project.author_id == auth.id">
						Decline
					</button>
					<button class="btn btn-transparent btn-attach text-uppercase py-2 px-4" @click="revokeBid(jobBid)" v-if="jobBid.user_id == auth.id && jobBid.revoked == false">
						Revoke
					</button>
				</div>
			</div>
		</section>

		<!-- Chat Modal -->
		<div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-body create-project">

						<!-- chat-body -->
						<div class="form-group mb-4">
							<label class="btn-bold" for="message">Your Message</label>
							<textarea rows="5" class="form-control platform-input" id="message" placeholder="Enter your message" v-model="bid_message_info.message"></textarea>
						</div>

						<!-- chat message docs -->
						<div class="form-group">
							<div class="d-flex justify-content-between">
								<label class="btn-bold" for="docs">Drop your files here</label>
								<a role="button" @click="clearDropzone">Clear All</a>
							</div>
							<vue-dropzone
							class=" platform-input text-center dropzone"
							ref="myVueDropzone"
							id="dropzone"
							:options="dropzoneOptions"
							v-on:vdropzone-success="atatchDzSuccess"></vue-dropzone>
						</div>
						<div class="d-flex justify-content-end">
							<button type="button" class="btn btn-transparent btn-attach text-uppercase py-1 px-5 mx-2" data-dismiss="modal">Close</button>
							<button type="button" class="btn bg-easy easy-shadow text-white btn-attach text-uppercase py-1 px-5" @click="createJobUserThread">SEND</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import vue2Dropzone from 'vue2-dropzone'
	export default {
		components: {
			'vue-dropzone': vue2Dropzone
		},

		props: ['project'],

		data() {
			return {
				jobBids: [],
				form: {
					message: '',
					image: null
				},
				dropzoneOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: `${this.$root.files_upload_url}/messages`,
					headers: {
						delete: false
					},
					dictDefaultMessage: ""
				},
				bid_to_message: null,
				bid_message_info: {
					message: null,
					attached_files: [],
				},
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
		},

		filters: {
			truncate: function(value) {
				if (!value) return ''
					value = value.toString()
				if(value.length >= 95) {
					return value.split(" ").splice(0, 10).join(" ") + ' ...';
				} else {
					return value
				}
			}
		},

		methods: {
			userRoute(id) {
				this.$router.push({name: 'user', params: {id: id}})
			},
			dateFormat(date) {
				return date.substring(0,10)
			},
			atatchDzSuccess(file, response) {
				if (!this.bid_message_info.attached_files) {
					this.bid_message_info.attached_files = [];
				}
				this.bid_message_info.attached_files.push(response);
			},
			customFormatter(date) {
				this.form.deadline = date
				return moment(date).format('YYYY-MM-DD');
			},
			clearDropzone() {
				this.$refs.myVueDropzone.removeAllFiles();
			},
			revokeBid(jobBid) {
				let data = {
					revoked: 1
				}
				axios.delete(this.$root.default_api_prefix + '/users/' + this.auth.id + '/bids/' + jobBid.id + '/revoke', data)
				.then(response => {
					this.jobBids = response.data.data
					Bus.$emit('update-bids')
					Bus.$emit('notification', {text: 'Your Bid was revoked'})
				})
				.catch(error => {
					this.$swal('Error',
						'',
						'error',
						);
				})
			},
			declineBid(jobBid) {
				let data = {
					declined: 1
				}
				axios.post(this.$root.default_api_prefix + '/users/' + jobBid.user_id + '/bids/' + jobBid.id + '/decline', data)
				.then(response => {
					Bus.$emit('update-bids')
					this.jobBids = response.data
				})
				.catch(error => {
					this.$swal('Error',
						'',
						'error',
						);
				})
			},
			redirectToThread(thread_id) {
				this.$router.push({name: 'profilechats', query: {open: thread_id}});
			},
			clearBidToMessage() {
				this.bid_to_message = null;
				this.bid_message_info = {
					message: null,
					attached_files: [],
				};
				this.clearDropzone();
			},
			sendMessage(bid) {
				if (bid.thread_id) {
					this.redirectToThread(bid.thread_id);
				} else {
					this.bid_to_message = bid;
					$('#chatModal').modal('show');
				}
			},
			createJobUserThread() {
				let bid = this.bid_to_message;
				let data = {
					users_id_to: [bid.user_id],
					entity_id: bid.job_id,
					entity_type: 'job',
					subject: `Job ${bid.job ? bid.job.name : ''}`,
					message: this.bid_message_info.message,
					attached_files: this.bid_message_info.attached_files,
				};

				axios.post(`${this.$root.default_api_prefix}/threads`, data)
				.then(response => {
					let thread = response.data;
					this.redirectToThread(thread.id);
					$('#chatModal').modal('hide');
					this.clearBidToMessage();
				})
				.catch(error => {
					Bus.$emit('notification', {text: 'Couldn`t create new chat!'});
				});

			},
		},

		created() {
			axios.get(this.$root.default_api_prefix + `/jobs/${this.$route.params.id}/jobbids?per_page=6`).then(response => {
				this.jobBids = response.data.data
			})
			
		}

	}
</script>