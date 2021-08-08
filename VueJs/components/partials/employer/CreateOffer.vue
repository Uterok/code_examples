<template>
	<section class="pt-5">
		<article class="container pt-4 list-card ">

			<div class="row">
				<div class="col-lg-8 pr-lg-5">
					<h4 class="mb-4" v-if="!$route.params.offer_id">Create a offer</h4>
					<div class="d-flex justify-content-between">
						<p class="mb-0 mr-3 offer-text">Name for project</p>
					</div>
					<div class="d-flex justify-content-between">
						<p class="mb-0 my-1">{{project.name}}</p>
					</div>

					<p class="mb-0 mr-3 mt-4 offer-text">Description</p>
					<div class="d-flex justify-content-between">
						<div class="d-flex">
							<p class="mb-0 my-1">{{project.description}}</p>
						</div>
					</div>

					<p class="mb-0 mr-3 mt-4 offer-text">Category</p>
					<div class="d-flex justify-content-between">
						<div class="d-flex">
							<p class="mb-0 my-1">{{project.category.name}}</p>
						</div>
					</div>
					
					<template v-if="project.attachments.length > 0">
						<p class="mb-2 mr-3 mt-4 offer-text">Files</p>
						<div class="col-12 d-flex justify-content-center mb-4 px-0">
							<div class="row align-items-center border br-round br-mid-easy p-3 w-100">
								<template v-if="project.id" v-for="doc in project.attachments">
									<div class="col-lg-3" v-if="doc.mime_type == 'image/jpeg' || doc.mime_type == 'image/png' || doc.mime_type == 'image/jpg' || doc.mime_type == 'image/svg+xml' ">
										<img :src="doc.link" class="w-100" data-toggle="modal" data-target="#documentModal" @click="image = doc.link">
										<!-- <button type="button" class="close mr-5" @click="deleteFile(doc)">X</button> -->
									</div>
									<div class="col-md-3 mt-3" v-if="doc.mime_type == 'application/pdf' || doc.mime_type == 'application/docx' || doc.mime_type == 'application/doc' || doc.mime_type == 'application/msword' || doc.mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'">
										<a :href="doc.link" target="_blank">
											<img src="/images/file-icon.png" class="w-100">
										</a>
										<!-- <button type="button" class="close mr-5">X</button> -->
									</div>
								</template>
							</div>
						</div>
					</template>

					<div class="form-group mb-4">
						<p class="mb-2 mr-3 mt-4 offer-text">Notes</p>
						<textarea rows="3" class="form-control platform-input" id="notes" v-model="form.notes" :disabled="disabled"></textarea>
						<span class="text-easy error-msg" v-if="$v.form.notes.$error">Notes {{lang.errors.required}}</span>
					</div>

				</div>

				<div class="col-md-4">
					<div class="d-flex justify-content-between">
						<div class="d-flex flex-column w-50">
							<p class="mb-0 mr-3 mt-4 offer-text">Expirience Level</p>
							<p class="mb-0 my-1">{{project.experience_lvl.name}} Level</p>
						</div>
						<div class="d-flex flex-column w-50">
							<p class="mb-0 mr-3 mt-4 offer-text">Job Type</p>
							<p class="mb-0 my-1">{{project.pay_type.name}}</p>
						</div>
					</div>

					<div class="d-flex justify-content-between">
						<p class="mb-0 mr-3 mt-4 offer-text">Currency</p>
					</div>
					<div class="d-flex justify-content-between">
						<p class="mb-0 my-1">{{project.currency.name}}</p>
					</div>


					<div class="d-flex justify-content-between">
						<div class="d-flex flex-column w-50">
							<p class="mb-0 mr-3 mt-4 offer-text">Job Budget</p>
							<p class="mb-0 my-1">{{project.pay_amount}}</p>
						</div>
						<div class="d-flex flex-column w-50">
							<p class="mb-0 mr-3 mt-4 offer-text">Finish Budget</p>
							<p class="mb-0 my-1" v-if="$route.params.offer_id">{{form.finish_budget}}</p>
							<div class="form-group mb-4" v-else>
								<input type="number" class="form-control platform-input" id="finish" v-model.number="form.finish_budget">
								<span class="text-easy error-msg" v-if="$v.form.finish_budget.$error">Finish Budget {{lang.errors.required}}</span>
							</div>
						</div>
					</div>
					
					
					<div class="form-group">
						<label class="offer-text" for="offerStart">Offer Expires</label>
						<datepicker
						ref="startDatePicker"
						id="offerStart"
						input-class="form-control platform-input"
						:disabled="disabled"
						:monday-first="true"
						:clear-button="false"
						:full-month-name="true"
						:format="customFormatterOffer"
						v-model="form.offer_expires"></datepicker>
						<span class="text-easy error-msg" v-if="$v.form.offer_expires.$error">Offer Expires {{lang.errors.required}}</span>
					</div>


					<div class="form-group">
						<label class="offer-text" for="startdateStart">Start Date</label>
						<datepicker
						ref="startDatePicker"
						id="startdateStart"
						input-class="form-control platform-input"
						:disabled="disabled"
						:monday-first="true"
						:clear-button="false"
						:full-month-name="true"
						:format="customFormatterStart"
						v-model="form.start_date"></datepicker>
						<span class="text-easy error-msg" v-if="$v.form.start_date.$error">Start Date {{lang.errors.required}}</span>
					</div>

					<div class="form-group mb-4">
						<label class="offer-text" for="limit">Weekly limit</label>
						<select name="currency" id="limit" class="form-control w-100 platform-input" v-model="form.weekly_limit" :disabled="disabled">
							<option :value="weekhour.name" v-for="weekhour in weekhours">{{weekhour.name}}</option>
						</select>
						<span class="text-easy error-msg" v-if="$v.form.weekly_limit.$error">Weekly limit {{lang.errors.required}}</span>
					</div>
					<div class="form-group mb-4" v-if="!$route.params.freelancer">
						<label class="offer-text">To Freelancers</label>
						<v-select class="orm-control w-100 platform-input"
						:disabled="disabled"
						multiple 
						label="name" 
						v-model.trim="form.bidFreelancers" 
						:options="bidFreelancers" 
						id="freelancers">
					</v-select>
					<span class="text-easy error-msg" v-if="$v.form.bidFreelancers.$error">To Freelancers {{lang.errors.required}}</span>
				</div>
			</div>
		</div>
		<div class="d-flex justify-content-center pt-3">
			<template v-if="auth.role_name == 4">
				<button type="button" class="btn bg-easy easy-shadow text-white btn-attach text-uppercase py-2 px-5 mx-3" @click="sendOffer()" v-if="!$route.params.offer_id">Create</button>		
			</template>
			<button type="button" class="btn bg-easy easy-shadow text-white btn-attach text-uppercase py-2 px-5 mx-3" @click="acceptOffer()" v-else>Accept</button>
			<button type="button" class="btn btn-transparent text-uppercase py-2 px-5 mx-3" @click="declineOffer()" v-if="auth.role_name == 3">Decline</button>
			<button type="button" class="btn btn-transparent text-uppercase py-2 px-5 mx-3" @click="cancelOffer()">Back</button>
		</div>
	</article>
</section>
</template>
<script>
	import { required } from 'vuelidate/lib/validators'
	import { mapState, mapActions } from 'vuex'
	import moment from 'moment';
	import Datepicker from 'vuejs-datepicker'
	import vSelect from 'vue-select'

	export default {
		components: {
			Datepicker,
			vSelect,
		},

		props: ['lang'],

		data() {
			return {
				disabled: false,
				bidFreelancers: [],
				offer: {},
				project: {
					id: '',
					name: '',
					author: {
						id: '',
						name: ''
					},
					category: {
						id: '',
						name: ''
					},
					currency: {
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
					preffered_qualification: {
						country:  {
							name: ''
						}
					}
				},
				paid_types: [
				{
					id: 'milestone',
					name: 'By Milestone'
				},
				{
					id: 'project',
					name: 'By Project'
				}
				],
				form: {
					notes: '',
					weekly_limit: '',
					type: 'milestone',
					milestones: [{
						due_date: '',
						amount_milestone: '',
						description_milestone: ''
					}],
				},
				weekhours: [
				{
					id: 1,
					name: '10h/w'
				},
				{
					id: 2,
					name: '20/w'
				},
				{
					id: 3,
					name: '30/w'
				},
				{
					id: 4,
					name: '40/w'
				},
				],
			}
		},

		validations: {
			form: {
				notes: {
					required
				},
				finish_budget: {
					required
				},
				bidFreelancers: {
					required
				},
				start_date: {
					required
				},
				offer_expires: {
					required
				},
				weekly_limit: {
					required
				}
			}
		},

		mounted() {

		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
				currencies: state => state.currencies.currencies,
			}),
		},

		methods: {
			...mapActions({
				getAuth: 'user/getAuthUser',
			}),
			addMilestone() {
				this.form.milestones.push({
					due_date: '',
					amount_milestone: '',
					description_milestone: ''
				})
			},
			delete_milestone(index) {
				this.form.milestones.splice(index, 1)
			},
			acceptOffer() {
				axios.put(this.$root.default_api_prefix + '/users/' + this.auth.id + '/offers/' + this.$route.params.offer_id + '/acceptoffer')
				.then(response => {
					Bus.$emit('notification', {text: 'Offer was Accepted'})
					this.$router.push({name: 'project', params: {id: this.$route.params.project_id}})
				})
				.catch(error => {

				})
			},
			customFormatterSelectDuration(date) {
				this.form.deadline = date
				return moment(date).format('YYYY-MM-DD');
			},
			customFormatterOffer(date) {
				this.form.offer_expires = date
				return moment(date).format('YYYY-MM-DD');
			},
			customFormatterStart(date) {
				this.form.start_date = date
				return moment(date).format('YYYY-MM-DD');
			},
			postMethod() {
				let offer = {
					milestones: [],
					job_id: this.project.id,
					freelancer_id: this.freelancer_id,
					finish_budget: this.form.finish_budget,
					offer_expires: this.form.offer_expires,
					start_date: this.form.start_date,
					weekly_limit: this.form.weekly_limit,
					notes: this.form.notes,
					bidFreelancers: this.form.bidFreelancers
				}

				axios.post(this.$root.default_api_prefix + '/createoffer', offer)
				.then(response => {
					this.offer = response.data
					this.$v.$reset()
					Bus.$emit('notification', {text: 'Offer was created'})
					this.$router.push({name: 'milestone', params: {project: this.project, freelancer: this.freelancer_id, start_offer: this.offer.id, project_id: this.project.id}})
				})
			},
			sendOffer() {
				this.$v.form.$touch()
				let isValid = !this.$v.form.$invalid

				if(!this.$route.params.freelancer) {
					if(isValid) {
						this.postMethod()
					}
				} else {
					this.$v.form.notes.$touch()
					let isValidNotes = !this.$v.form.notes.$invalid
					this.$v.form.finish_budget.$touch()
					let isValidBudget = !this.$v.form.finish_budget.$invalid
					this.$v.form.start_date.$touch()
					let isValidDate = !this.$v.form.start_date.$invalid
					this.$v.form.offer_expires.$touch()
					let isValidExpires = !this.$v.form.offer_expires.$invalid
					this.$v.form.weekly_limit.$touch()
					let isValidWeekly = !this.$v.form.weekly_limit.$invalid

					if(isValidNotes && isValidBudget && isValidDate && isValidExpires && isValidWeekly) {
						this.postMethod()
					}
				}

			},
			declineOffer() {
				axios.delete(this.$root.default_api_prefix + '/offers/' + this.form.id + '/declineoffer')
				.then(response => {
					Bus.$emit('notification', {text: 'This Offer was declined'})
					this.cancelOffer();
				})
			},
			cancelOffer() {
				this.$router.push({path: '/profile/my-projects'})
			},
			bidFreelancersGet() {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.project_id + '/freelancersbid')
				.then(response => {
					this.bidFreelancers = response.data
				})
			},
			getProject() {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.project_id)
				.then(response => {
					this.project = response.data
				})
			}
		},
		created() {
			this.bidFreelancersGet()
			this.getProject()

			this.freelancer_id = this.$route.params.freelancer

			if(this.$route.params.offer_id) {
				axios.get(this.$root.default_api_prefix + '/offers/' + this.$route.params.offer_id)
				.then(response => {
					this.form = response.data
					this.disabled = true
				})
			}
		}	
	}
</script>
