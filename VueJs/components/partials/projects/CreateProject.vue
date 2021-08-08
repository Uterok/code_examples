<template>
	<article class="container create-project list-card mt-5">
		<div class="row">
			<div class="col-12">
				<h1 class="mb-5">
					<template v-if="$route.params.id">
						Edit
					</template>
					<template v-else>
						Create a
					</template>
					project
				</h1>
			</div>

			<div class="col-lg-8">
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="name">Name for Project</label>
					<input type="text" class="form-control platform-input" id="name" placeholder="Enter a name for project" v-model="form.name">
					<span class="text-easy error-msg" v-if="$v.form.name.$error">{{lang.inputs.name}} {{lang.errors.required}}</span>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="description">Description</label>
					<textarea rows="3" class="form-control platform-input" id="description" placeholder="Enter description about project" v-model="form.description"></textarea>
					<span class="text-easy error-msg" v-if="$v.form.description.$error">{{lang.inputs.description}} {{lang.errors.required}}</span>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="category">Category</label>
					<v-select class=" px-0 platform-input" label="name" v-model.trim="form.category_id" :options="categories" id="categories-filter" placeholder="Enter needed category"></v-select>
					<span class="text-easy error-msg" v-if="$v.form.category_id.$error">Category {{lang.errors.required}}</span>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="skills">Skills</label>
					<v-select class="c px-0 platform-input" multiple :get-option-label="getLabel" label="name" v-model.trim="form.skills" :options="skills" id="skills" placeholder="Enter a skill name"></v-select>
				</div>
				<div class="form-group" :class="{'mb-4': $route.params.id}">
					<div class="d-flex justify-content-between">
						<label class="text-dark-easy" for="dropzone">Drop your files here</label>
						<a role="button" class="text-underline" @click="clearDropzone">Clear All</a>
					</div>
					<vue-dropzone
					class=" platform-input text-center dropzone"
					ref="myVueDropzone"
					id="dropzone"
					:options="dropzoneOptions"
					v-on:vdropzone-error="failed"
					v-on:vdropzone-success="showSuccess"></vue-dropzone>
				</div>
				<div class="form-group" v-if="$route.params.id">
					<div class="row">
						<div class="col-lg-3 hover-delete" v-for="(doc, index) in form.attachments">
							
							<template v-if="doc.mime_type == 'image/jpeg' || doc.mime_type == 'image/png' || doc.mime_type == 'image/jpg' || doc.mime_type == 'image/svg+xml'">
								<div class="hidden-card d-flex justify-content-center align-items-center">
									<button class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button mr-2" data-toggle="modal" data-target="#documentModal" @click="image = doc.link">
										<i class="fa fa-eye"></i>
									</button>
									<button class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button" @click="deleteFile(index)">
										<i class="fa fa-minus"></i>
									</button>
								</div>
								<img :src="doc.link" alt="" class="w-100 pointer">
							</template>

							<template v-if="doc.mime_type == 'application/pdf' || doc.mime_type == 'application/docx' || doc.mime_type == 'application/doc' || doc.mime_type == 'application/msword' || doc.mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'">
								<div class="hidden-card d-flex justify-content-center align-items-center">
									<a class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button mr-2" :href="doc.link" target="_blank">
										<i class="fa fa-eye"></i>
									</a>
									<button class="btn bg-easy text-white easy-shadow text-uppercase py-1 px-1 text-center delete-button" @click="deleteFile(index)">
										<i class="fa fa-minus"></i>
									</button>
								</div>
								<img src="/images/file-icon.png" class="w-100 pointer">
							</template>

						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-4 d-flex flex-column justify-content-between">
				<div class="form-group mb-4">
					<label class="text-dark-easy">Experience Level</label>
					<div class="row">
						<div class="col-lg-6 mb-2" v-for="explevel in experienceLevels">
							<input type="radio" class="mr-2" v-model="form.exp_level_name" name="exp_level_name" :value="explevel.exp_level_name" :id="explevel.name">
							<label :for="explevel.name">{{explevel.name}}</label>
						</div>
					</div>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy">Job Type</label>
					<div class="row">
						<div class="col-lg-6 mb-2" v-for="paytype in paytypes">
							<input type="radio" class="mr-2" v-model="form.pay_type_id" name="pay_type_id" :value="paytype.id" :id="paytype.name">
							<label :for="paytype.name">{{paytype.name}}</label>
						</div>
					</div>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="locations">Location preffered</label>
					<select name="locations" id="locations" class="form-control w-50 platform-input" v-model="form.country_code">
						<option :value="country.country_code" v-for="country in countries">{{country.name}}</option>
					</select>
					<!-- <span class="text-easy error-msg" v-if="$v.form.country_code.$error">Location {{lang.errors.required}}</span> -->
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="currency">Currency</label>
					<select name="currency" id="currency" class="form-control w-50 platform-input" v-model="form.currency_id">
						<option :value="currency.id" v-for="currency in currencies">{{currency.name}}</option>
					</select>
					<span class="text-easy error-msg" v-if="$v.form.currency_id.$error">Currency {{lang.errors.required}}</span>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="name">Count of Freelancers</label>
					<input type="number" class="form-control platform-input" id="freelancers_count" placeholder="Number of freelancers needed for the project" v-model.number="form.freelancers_count">
					<span class="text-easy error-msg" v-if="$v.form.freelancers_count.$error">{{lang.inputs.freelancers_count}} {{lang.errors.required}}</span>
				</div>
				<div class="form-group mb-4">
					<label class="text-dark-easy" for="minimum">Budget</label>
					<input type="number" class="form-control platform-input" id="minimum" placeholder="Enter project budget" v-model.number="form.pay_amount">
					<span class="text-easy error-msg" v-if="$v.form.pay_amount.$error">Budget {{lang.errors.required}}</span>
				</div>
				<div class="form-group">
					<label class="text-dark-easy" for="datepickerStart">Deadline</label>
					<datepicker
					ref="startDatePicker"
					id="datepickerStart"
					input-class="form-control platform-input"
					placeholder="Choose your deadline date"
					:monday-first="true"
					:clear-button="false"
					:full-month-name="true"
					:format="customFormatter"
					v-model="form.deadline"></datepicker>
				</div>
			</div>

			<div class="modal fade back" id="documentModal" tabindex="-1" role="dialog" aria-hidden="true">
				<button type="button" class="close mt-4 mr-4" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="modal-dialog modal-lg body-search" role="document">
					<div class="modal-content back-modal">
						<div class="modal-body">
							<div class="input-group input-group-lg offset-1 col-10 mx-auto border-img-none">
								<img :src="image" class="w-100 h-100">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 d-flex justify-content-center mt-4">
				<button class="btn bg-easy easy-shadow text-uppercase py-2 px-5 text-white mr-4" @click="create">
					<template v-if="$route.params.id">
						Save
					</template>
					<template v-else>
						Create
					</template>
				</button>
				<button class="btn btn-transparent text-uppercase py-2 px-5" @click="cancel">Cancel</button>
			</div>


		</div>
	</article>
</template>
<script>
	import { required, numeric, minValue, maxValue } from 'vuelidate/lib/validators'
	import moment from 'moment';
	import Datepicker from 'vuejs-datepicker'
	import vue2Dropzone from 'vue2-dropzone'
	import vSelect from 'vue-select'
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
				image: '',
				form: {
					pay_type_id: 1,
					exp_level_name: 1,
					country_code: '',
					attachments: []
				},
				dropzoneOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: this.$root.default_api_prefix + '/files/users',
					headers: {
						delete: false
					},
					dictDefaultMessage: "Drop file here to attach"
				},
			}
		},

		validations: {
			form: {
				name: {
					required
				},
				description: {
					required
				},
				category_id: {
					required
				},
				currency_id: {
					required
				},
				freelancers_count: {
					required
				},
				pay_amount: {
					required
				},
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
				skills: state => state.skills.skills,
				experienceLevels: state => state.experience_lvls.experience_lvls,
				paytypes: state => state.paytypes.paytypes,
				currencies: state => state.currencies.currencies,
				categories: state => state.categories.categories,
				countries: state => state.countries.countries,
			}),
		},

		mounted() {

		},

		methods: {
			...mapActions({
				getAuth: 'user/getAuthUser',
			}),
			deleteFile(index) {
				this.form.attachments.splice(index, 1)
			},
			getLabel(option) {
				return option.name
			},
			cancel() {
				this.$router.push({path: '/profile/my-projects'})
			},
			failed:function(file,message,xhr){
				let response = xhr.response;
				let parse = JSON.parse(response, (key, value)=>{
					return value;
				});
				$('.dz-error-message span').text(parse.message);
			},
			customFormatter(date) {
				this.form.deadline = date
				return moment(date).format('YYYY-MM-DD');
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
			create() {
				this.$v.form.$touch();
				let isValid = !this.$v.form.$invalid;
				if(isValid) {

					let data = {
						name: this.form.name,
						description: this.form.description,
						category_id: this.form.category_id.id,
						skills: this.form.skills,
						attachments: this.form.attachments,
						pay_type_id: this.form.pay_type_id,
						exp_level_name: this.form.exp_level_name,
						country_code: this.form.country_code,
						currency_id: this.form.currency_id,
						freelancers_count: this.form.freelancers_count,
						pay_amount: this.form.pay_amount,
						deadline: this.form.deadline,
						author_id: this.auth.id
					}
					if(this.$route.params.id) {
						axios.put(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id, data)
						.then(response => {
							this.form = {
								pay_type_id: '',
								exp_level_name: '',
								attachments: [],
								skills: []
							}
							this.$v.$reset()
							Bus.$emit('notification', {text: 'Project was updated'})
							this.$router.push({path: '/profile/my-projects'})
						})
					} else {
						axios.post(this.$root.default_api_prefix + '/jobs', data)
						.then(response => {
							this.form = {
								pay_type_id: '',
								exp_level_name: '',
								attachments: [],
								skills: []
							}
							this.$v.$reset()
							Bus.$emit('notification', {text: 'Project was created'})
							this.$router.push({path: '/profile/my-projects'})
						})
					}

				}
			}
		},

		created() {
			if(this.$route.params.id) {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.id)
				.then(response => {
					this.form = response.data
					for (var i = 0; i < this.categories.length; i++) {
						if(this.categories[i].id == response.data.category_id) {
							this.form.category_id = this.categories[i]
						}
					}
				})
			}

			if(this.$route.params.project_id) {
				axios.get(this.$root.default_api_prefix + '/jobs/' + this.$route.params.project_id)
				.then(response => {
					this.form = response.data
					this.form.name = null
					this.form.description = null
					this.form.attachments = []
					for (var i = 0; i < this.categories.length; i++) {
						if(this.categories[i].id == response.data.category_id) {
							this.form.category_id = this.categories[i]
						}
					}
				})
			}
		},
		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getAuth()
			});
		},

	}
</script>