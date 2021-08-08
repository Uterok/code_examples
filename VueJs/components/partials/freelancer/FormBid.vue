<template>
	<section class="pt-5">
		<article class="container pt-4 list-card">

			<div class="row">
				<div class="col-lg-12">
					<div class="col-12">
						<h4 class="mt-3 mb-4">How do you want to paid?</h4>
					</div>
					<div class="col-lg-6 mb-2" v-for="paid_type in paid_types">
						<input type="radio" class="mr-2" name="paid_type" :value="paid_type.id" :id="paid_type.name" v-model="form.type">
						<label :for="paid_type.name">{{paid_type.name}}</label>
					</div>


					<!-- By Milestone -->
					<template v-if="form.type == 'milestone'">
						<div class="col-12">
							<h4 class="mb-4 mt-3 mr-3">How many milestones do you want to include</h4>
						</div>

						<div class="row mb-4 px-3" v-for="(milestoneSingle, index) in form.milestones">
							<div class="col-lg-3">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text" for="categories-filter">Description</label>
									<div class="input-group select-group pl-2 py-1">
										<input type="text" class="form-control platform-input" id="description_milestone" v-model="milestoneSingle.description_milestone">
									</div>
									<span class="text-easy error-msg" v-if="$v.form.milestones.$each[index].description_milestone.$error">{{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-3">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text" for="location-filter">Amount</label>
									<div class="input-group select-group pl-2 py-1">
										<input type="number" class="form-control platform-input" id="amount_milestone" v-model.number="milestoneSingle.amount_milestone">
									</div>
									<span class="text-easy error-msg" v-if="$v.form.milestones.$each[index].amount_milestone.$error">{{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-2">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text">Currency</label>
									<div class="input-group br-dark-easy text-dark-easy singl-select select-group pl-2">
										<v-select label="name" class="col px-0" v-model="milestoneSingle.currency" :options="currencies"></v-select>
										<div class="input-group-prepend">
											<div class="input-group-text pr-4">
												<i class="fa fa-chevron-down text-dark-easy"></i>
											</div>
										</div>
									</div>
									<span class="text-easy error-msg" v-if="$v.form.milestones.$each[index].currency.$error">{{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-3">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text" for="due_date">Due Date</label>
									<div class="input-group select-group pl-2 py-1">
										<datepicker
										ref="dueDatePicker"
										id="due_date"
										input-class="form-control platform-input"
										:monday-first="true"
										:clear-button="false"
										:full-month-name="true"
										v-model="milestoneSingle.due_date"></datepicker>
									</div>
									<span class="text-easy error-msg" v-if="$v.form.milestones.$each[index].due_date.$error">{{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-1 d-flex align-items-end">
								<button class="btn bg-easy rounded text-white py-1 px-2 mb-2" @click="delete_milestone(index)" v-if="form.milestones.length > 1">X</button>
							</div>
						</div>


						<div class="col-12 d-flex pb-4 pt-3">
							<button class="btn bg-none mr-3 pl-0" @click="addMilestone()">
								<i class="fa fa-plus"></i>
							</button>
							<h5 @click="addMilestone()">Add a milestone</h5>
						</div>

					</template>
					
					<!-- By Project -->
					<template v-else>
						<div class="row px-3">

							<div class="col-lg-4 mb-4">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text">Amount</label>
									<div class="input-group select-group pl-2 py-1">
										<input type="number" class="form-control platform-input" v-model.number="form.price">
									</div>
									<span class="text-easy error-msg" v-if="$v.form.price.$error">Amount {{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-2">
								<div class="form-group mb-0">
									<label class="mb-3 offer-text">Currency</label>
									<div class="input-group br-dark-easy text-dark-easy singl-select select-group pl-2">
										<v-select label="name" class="col px-0" v-model="form.currency" :options="currencies"></v-select>
										<div class="input-group-prepend">
											<div class="input-group-text pr-4">
												<i class="fa fa-chevron-down text-dark-easy"></i>
											</div>
										</div>
									</div>
									<span class="text-easy error-msg" v-if="$v.form.currency.$error">Currency {{lang.errors.required}}</span>
								</div>
							</div>

							<div class="col-lg-3">
								<div class="form-group">
									<label class="mb-3 offer-text" for="select_duration">Due Date</label>
									<div class="input-group select-group pl-2 py-1">
										<datepicker
										ref="selectDurDatePicker"
										id="select_duration"
										input-class="form-control platform-input"
										:monday-first="true"
										:clear-button="false"
										:full-month-name="true"
										:format="customFormatterSelectDuration"
										v-model="form.deadline"></datepicker>
									</div>
									<span class="text-easy error-msg" v-if="$v.form.deadline.$error">Title {{lang.errors.required}}</span>
								</div>
							</div>
						</div>

					</template>

					<div class="col-12 mt-5">
						<h4 class="mb-4 mt-3 mr-3">Additional Details</h4>
					</div>
					<div class="form-group col-12 mb-4">
						<label class="offer-text" for="title">Title</label>
						<div class="input-group select-group pl-2 py-1">
							<input type="text" class="form-control platform-input" id="title" v-model="form.title">
						</div>
						<span class="text-easy error-msg" v-if="$v.form.title.$error">Title {{lang.errors.required}}</span>
					</div>
					<div class="form-group col-12 mb-4">
						<label class="offer-text" for="cover_letter">Cover Letter</label>
						<textarea rows="3" class="form-control platform-input br-dark-easy" id="cover_letter" v-model="form.cover_letter"></textarea>
						<span class="text-easy error-msg" v-if="$v.form.cover_letter.$error">Cover Letter {{lang.errors.required}}</span>
					</div>

					<div class="form-group col-12">
						<div class="d-flex justify-content-between">
							<label class="offer-text" for="attachments_milestone">Attachments</label>
							<a role="button" class="text-underline" @click="clearDropzone">Clear All</a>
						</div>
						<vue-dropzone
						class=" platform-input text-center dropzone"
						ref="myVueDropzone"
						id="attachments_milestone"
						:options="dropzoneOptions"
						v-on:vdropzone-error="failed"
						v-on:vdropzone-success="showSuccess"
						v-on:vdropzone-files-added="multiFiles"
						v-on:vdropzone-removed-file="removedFile"></vue-dropzone>
					</div>
					<div class="col-12 my-3 mt-4">
						<button type="button" class="btn bg-easy easy-shadow text-white text-uppercase py-2 px-5 mr-3" @click="sendMilestones()">Submit</button>
						<button type="button" class="btn btn-transparent text-uppercase py-2 px-5 mx-3" @click="cancelMilestone()">Cancel</button>
					</div>
				</div>
			</div>
		</article>
	</section>
</template>
<script>
	import { required } from 'vuelidate/lib/validators'
	import vSelect from 'vue-select'
	import moment from 'moment';
	import Datepicker from 'vuejs-datepicker'
	import vue2Dropzone from 'vue2-dropzone'
	import { mapState, mapActions } from 'vuex'
	export default {
		components: {
			vSelect,
			Datepicker,
			'vue-dropzone': vue2Dropzone
		},

		props: ['lang'],

		data() {
			return {
				offer:{},
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
					type: 'milestone',
					milestones: [{
						due_date: '',
						amount_milestone: '',
						description_milestone: ''
					}],
					attachments: [],
				},
				dropzoneOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: this.$root.default_api_prefix + '/files/milestones',
					headers: {
						delete: false
					},
					dictDefaultMessage: "Drop file here to attach"
				},
				
			}
		},

		validations: {
			form: {
				deadline: {
					required
				},
				title: {
					required
				},
				cover_letter: {
					required
				},
				milestones: {
					$each: {
						due_date: {
							required
						},
						amount_milestone: {
							required
						},
						description_milestone: {
							required
						},
						currency: {
							required
						}
					}
				},
				price: {
					required
				},
				currency: {
					required
				}
			}
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
			multiFiles() {
				this.$refs.myVueDropzone.processQueue()
			},
			removedFile(file, error, xhr) {
				for (var i = 0; i < this.form.attachments.length; i++) {
					if(this.form.attachments[i].real_name == file.name) {
						this.form.attachments.splice(i, 1)
					}
				}
			},
			showSuccess(response) {
				let resp = JSON.parse(response.xhr.response)
				this.form.attachments.push({
					link: resp.link,
					mime_type: resp.mime_type,
					type: resp.type,
					real_name: resp.real_name,
					filesize: resp.filesize,
				})
			},
			clearDropzone() {
				this.$refs.myVueDropzone.removeAllFiles()
			},
			customFormatterDueDate(date) {
				this.form.milestones.due_date = date
				return moment(date).format('YYYY-MM-DD');
			},
			customFormatterSelectDuration(date) {
				this.form.deadline = date
				return moment(date).format('YYYY-MM-DD');
			},
			failed:function(file,message,xhr){
				let response = xhr.response;
				let parse = JSON.parse(response, (key, value)=>{
					return value;
				});
				$('.dz-error-message span').text(parse.message);
			},
			validateForm() {
				if(this.form.type == 'project') {
					this.$v.form.deadline.$touch()
					let isValidDeadline = !this.$v.form.deadline.$invalid
					this.$v.form.cover_letter.$touch()
					let isValidLetter = !this.$v.form.cover_letter.$invalid
					this.$v.form.title.$touch()
					let isValidTitle = !this.$v.form.title.$invalid

					this.$v.form.price.$touch()
					let isValidPrice = !this.$v.form.price.$invalid
					this.$v.form.currency.$touch()
					let isValidCurrency = !this.$v.form.currency.$invalid

					if(isValidDeadline && isValidLetter && isValidTitle && isValidPrice && isValidCurrency) {
						return true
					} else {
						return false
					}
				} else {
					this.$v.form.cover_letter.$touch()
					let isValidLetter = !this.$v.form.cover_letter.$invalid
					this.$v.form.title.$touch()
					let isValidTitle = !this.$v.form.title.$invalid

					this.$v.form.milestones.$touch()
					let isValidMilestones = !this.$v.form.milestones.$invalid

					if(isValidLetter && isValidTitle && isValidMilestones) {
						return true
					} else {
						return false
					}
				}
			},
			sendMilestones() {
				if(this.form.type == 'project') {
					delete this.form.milestones
				}

				if(this.$route.name == 'milestone') {
					if(this.validateForm()) {
						axios.put(this.$root.default_api_prefix + '/offers/' + this.$route.params.start_offer + '/updateoffer', this.form)
						.then(response => {
							Bus.$emit('notification', {text: 'Your Offer will be send'})
							this.$router.push({path: '/projects/' + this.$route.params.project_id})
						})
					}
				} else {
					if(this.validateForm()) {
						axios.post(this.$root.default_api_prefix + '/users/' + this.auth.id + '/jobs/' + this.$route.params.project_id, this.form)
						.then(response => {
							Bus.$emit('notification', {text: 'Your Bid was accepted'})
							this.$router.push({path: '/projects/' + this.$route.params.project_id})
						})
					}
				}

			},
			cancelMilestone() {
				this.$router.push({name: 'my_projects'})
			}
		},

		created() {
			if(this.$route.name == 'milestone') {
				if(!this.$route.params.project) {
					this.$router.push({name: 'view-offerform', params: {project_id: this.$route.params.project_id, offer_id: this.$route.params.start_offer} })
				}
				this.project = this.$route.params.project
				this.freelancer_id = this.$route.params.freelancer
				this.offer = this.$route.params.start_offer
			}
		}	
	}
</script>
<style>
.vdp-datepicker {
	width: 100% !important;
}
</style>