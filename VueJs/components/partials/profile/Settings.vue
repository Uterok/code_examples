<template>
	<section>
		<div class="row pt-5">
			<div class="col-12 d-flex px-0 mb-5">
				<a class="table-pills text-center text-capitalize" :class="[activeTable == tab ? 'active br-easy text' : 'br-light-easy text-dark-easy']" role="button" @click="changeTab(tab)" v-for="tab in tabs">{{tab}}</a>
			</div>
		</div>
		<template name="fade" mode="out-in">

			<!-- Profile -->
			<div class="list-card row" v-if="activeTable == 'profile'" key="profile">
				<div class="form-group col-lg-8 mb-5">
					<h5 class="btn-bold mb-2">Profile image</h5>
					<template v-if="info.image">
						<img :src="info.image" alt="user" width="200" height="200" class="rounded-circle bg-secondary mb-3">
						<div class="col-12 mb-4">
							<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="deleteProfileImage">delete</button>
						</div>
					</template>
					<vue-dropzone
							class=" platform-input text-center dropzone"
							v-if="!info.image"
							ref="imageDropzone"
							id="dropzone"
							:options="imageDzOptions"
							v-on:vdropzone-error="imageDzError"
							v-on:vdropzone-success="imageDzSuccess"></vue-dropzone>
				</div>
				<div class="form-group col-lg-8 mb-5">
					<h5 class="btn-bold mb-2">Description header</h5>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" placeholder="Enter your description header" v-model="info.description_header">
					</div>
				</div>
				<div class="form-group col-lg-8 mb-5">
					<h5 class="btn-bold mb-2">Description</h5>
					<div class="input-group br-dark-easy text-dark-easy select-group py-3">
						<vue-editor placeholder="Enter your description" v-model="info.description"></vue-editor>
						<!-- <textarea placeholder="Enter your description" v-model="info.description"></textarea> -->
					</div>
				</div>
				<div class="form-group col-lg-8 mb-5" v-if="$root.isUserFreelancer(auth)">
					<h5 class="btn-bold mb-2" for="categories">Categories</h5>
					<p id="categories" class="form-text text-dark-easy text-muted mb-2">Start to type the category you need</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select class="col px-0" label="name" v-model="info.category" :options="categories" id="categories" placeholder="Choose your category"></v-select>
					</div>
				</div>
				<div class="form-group col-lg-8 mb-5" v-if="$root.isUserFreelancer(auth)">
					<h5 class="btn-bold mb-2" for="skills">Skills</h5>
					<p id="skills" class="form-text text-dark-easy text-muted mb-2">Start to type the skill you need</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select class="col px-0" multiple label="name" v-model="info.skills" :options="skills" id="skills" placeholder="Choose your skills"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12" v-if="$root.isUserFreelancer(auth)">
					<div class="row">
						<div class="form-group col-lg-4 mb-5">
							<h5 class="btn-bold mb-2" for="experiences">Experience Level</h5>
							<p id="experiences" class="form-text text-dark-easy text-muted mb-2">Entry Level</p>
							<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
								<v-select class="col px-0" label="name" v-model="info.experience_lvl" :options="experience_lvls" id="experiences" placeholder="Choose your Level"></v-select>
								<div class="input-group-prepend">
									<div class="input-group-text pr-4">
										<i class="fa fa-chevron-down text-dark-easy"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group col-lg-12 mb-0">
					<h5 class="btn-bold mb-2">Platform language</h5>
				</div>
				<div class="col-lg-4 mb-5">
					<p id="experiences" class="form-text text-dark-easy text-muted mb-2">Browse the platform in</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select label="name" class="col px-0" v-model="info.platform_language" :options="languages"  placeholder="Choose your language" disabled></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4 mb-5">
					<p id="experiences" class="form-text text-dark-easy text-muted mb-2">Browse projects in following languages</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select label="name" class="col px-0" v-model="info.projects_language" :options="languages" placeholder="Choose your language" disabled></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 mb-4">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="saveGeneralInfo">save changes</button>
				</div>
			</div>

			
			<!-- Notifications -->
			<div class="list-card row" v-else-if="activeTable == 'notifications'" key="notifications">
				<div class="col-12 mb-3">
					<h5 class="btn-bold mb-2">Notifications from your posted contests</h5>
				</div>
				<div class="col-12 mb-3" v-if="$root.isUserFreelancer">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.posted">
							<span></span>
						</label>
						When a bid placed / updated / retracted on your project
					</div>
				</div>
				<div class="col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Email frequency for project related activity</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4">
						<v-select class="col px-0" label="name" v-model="notifications.activity" :options="visibility"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>

				<div class="col-12">
					<h5 class="btn-bold mb-3">Digest emails for messages</h5>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.private">
							<span></span>
						</label>
						When you receive a private message from a contact 
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.project">
							<span></span>
						</label>
						When you receive a message about a project or contest
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.marketing">
							<span></span>
						</label>
						Marketing emails
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.emails">
							<span></span>
						</label>
						EasyBusy emails
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.deals">
							<span></span>
						</label>
						EasyBusy deals
					</div>
				</div>
				<div class="col-12 mb-5">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.newsletter">
							<span></span>
						</label>
						Monthly newsletter
					</div>
				</div>

				<div class="col-12">
					<h5 class="btn-bold mb-3">Individual Notifications</h5>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.announcements">
							<span></span>
						</label>
						News and announcements from EasyBusy Team 
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.payment">
							<span></span>
						</label>
						A freelancer requests you to release a milestone payment
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.bidder">
							<span></span>
						</label>
						We notify you of the top bidder for your project
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.services">
							<span></span>
						</label>
						We notify you of the latest activity regarding Services
					</div>
				</div>
				<div class="col-12 mb-3">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.freemarket">
							<span></span>
						</label>
						We notify you of the latest activity regarding Freemarket
					</div>
				</div>
				<div class="col-12 mb-5">
					<div class="check-toggle d-flex align-items-center">
						<label class="mb-0 mr-3 pt-1">
							<input type="checkbox" v-model="notifications.contact">
							<span></span>
						</label>
						A freelancer requests you as a contact
					</div>
				</div>

				<div class="col-12 mb-4">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="saveContactInfo">save changes</button>
				</div>
			</div>


			<!-- contact -->
			<div class="list-card row" v-else-if="activeTable == 'contact'" key="contact">
				<div class="col-12">
					<h5 class="btn-bold mb-2">Name</h5>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">First name</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" placeholder="Enter your First Name" v-model="contact.firstname">
					</div>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Last name</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" placeholder="Enter your Last Name" v-model="contact.lastname">
					</div>
				</div>

				<div class="col-12">
					<h5 class="btn-bold mb-2">Email</h5>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Email address</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="email" class="w-100" placeholder="Enter your recent Email" v-model="contact.email">
					</div>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Enter current password</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="password" class="w-100" placeholder="Enter your recent Password" v-model="contact.password">
					</div>
				</div>

				<div class="col-12">
					<h5 class="btn-bold mb-2">Address</h5>
				</div>
				<div class="form-group col-lg-8 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">Address</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" placeholder="Enter your recently Address" v-model="contact.first_address_line">
					</div>
				</div>
				<div class="form-group col-lg-8 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">Additional address</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" placeholder="Enter your second Address" v-model="contact.second_address_line">
					</div>
				</div>
				<div class="col-12"></div>
				<div class="form-group col-lg-4 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">Country</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select label="name" class="col px-0" v-model="contact.country" :options="locations.countries" placeholder="Enter your country"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group col-lg-4 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">State/Provincy</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select label="name" class="col px-0" v-model="contact.province" :options="locations.provinces" placeholder="Enter your province"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12"></div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">District</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-1">
						<v-select label="name" class="col px-0" v-model="contact.district" :options="locations.districts" placeholder="Enter your district"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">City</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="email" class="w-100" placeholder="Enter your City" v-model="contact.city">
					</div>
				</div>
				<div class="col-12"></div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Zip/Post Code</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="email" class="w-100" placeholder="Enter your Zip/Post Code" v-model="contact.zip">
					</div>
				</div>

				<div class="col-12 mb-4">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="saveContactInfo">save changes</button>
				</div>
			</div>


			<!-- password -->
			<div class="list-card row" v-else-if="activeTable == 'password'" key="password">
				<div class="col-12">
					<h5 class="btn-bold mb-2">Change Password</h5>
				</div>
				<div class="form-group col-lg-4 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">Current Password</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="password" class="w-100" placeholder="Enter your recent Password" v-model="password.current">
					</div>
				</div>
				<div class="col-12"></div>
				<div class="form-group col-lg-4 mb-3">
					<p class="form-text text-dark-easy text-muted mb-2">New Password</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="password" class="w-100" placeholder="Enter your new Password" v-model="password.password">
					</div>
				</div>
				<div class="col-12"></div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Confirm your password</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="password" class="w-100" placeholder="Enter your new Password" v-model="password.password_confirmation">
					</div>
				</div>

				<div class="col-12 mb-4">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="changePassword" :disabled="!disableChangePasswordButton">save changes</button>
				</div>
			</div>


			<!-- payment -->
			<div class="list-card row" v-else-if="activeTable == 'payment'" key="payment">
				<div class="col-12">
					<h5 class="btn-bold mb-4">Payment Methods</h5>
				</div>
				<div class="col-12 mb-5">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase">add payment method</button>
				</div>
				<template v-if="$root.isUserFreelancer(auth)">
					<div class="col-12">
						<h5 class="btn-bold mb-4">Finance Settings</h5>
					</div>
					<div class="col-lg-4 mb-4">
						<p class="form-text text-dark-easy text-muted mb-2">My Currency</p>
						<div class="input-group br-dark-easy text-dark-easy select-group pl-4">
							<v-select class="col px-0" label="name" v-model="payment.currency" :options="currencies" disabled></v-select>
							<div class="input-group-prepend">
								<div class="input-group-text pr-4">
									<i class="fa fa-chevron-down text-dark-easy"></i>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12">
						<p class="form-text text-dark-easy text-muted mb-2">Hourly rate</p>
					</div>
					<div class="col-12 mb-5">
						<div class="row">
							<div class="col-lg-3">
								<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
									<input type="text" class="w-100" :placeholder="`Rate in ${payment.currency}`" v-model="payment.rate">
								</div>
							</div>
						</div>
					</div>
				
					<div class="col-12 mb-4">
						<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="savePayment">save changes</button>
					</div>
				</template>
			</div>


			<!-- privacy -->
			<div class="list-card row" v-else-if="activeTable == 'privacy'" key="privacy">
				<div class="col-12">
					<h5 class="btn-bold mb-2">Visibility</h5>
				</div>
				<div class="form-group col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Who can see my account</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4">
						<v-select class="col px-0" label="name" v-model="privacy.visibility" :options="visibilities"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12">
					<h5 class="btn-bold mb-3">Search engine privacy</h5>
					<p>
						<input type="radio" id="fullname" name="show_name" :value="true" v-model="privacy.show_name">
						<label for="fullname">Show my full name</label>
					</p>
					<p>
						<input type="radio" id="nickname" name="show_name" :value="false" v-model="privacy.show_name">
						<label for="nickname">Show only my nickname</label>
					</p>
				</div>
				<div class="col-12">
					<h5 class="btn-bold mb-3">Project Preference</h5>
				</div>
				<div class="col-lg-4 mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Project preferences</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4">
						<v-select class="col px-0" label="name" v-model="privacy.project_preference" :options="project_preferences"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-chevron-down text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>

				<div class="col-12 mb-4">
					<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" @click="savePrivacy">save changes</button>
				</div>
			</div>

		</template>
	</section>
</template>
<script>
	import vSelect from 'vue-select'
	import vue2Dropzone from 'vue2-dropzone'
	import { VueEditor } from 'vue2-editor'
	import { mapState, mapActions } from 'vuex'

	export default {
		components: {
			vSelect,
			VueEditor,
			'vue-dropzone': vue2Dropzone,
		},

		props: ['lang', 'auth'],

		watch: {
			'contact.country'(val, oldVal) {
				if (val) {
					this.getLocations('provinces', val.id);
				}

				if ((val != oldVal) && oldVal) {
					this.contact.province = null;
				}
			},
			'contact.province'(val, oldVal) {
				if (val) {
					this.getLocations('districts', val.id);
				}

				if ((val != oldVal) && oldVal) {
					this.contact.district = null;
				}
			},
		},

		data() {
			return {
				tabs: ['profile', 'notifications', 'contact', 'password', 'payment', 'privacy'],
				languages: ['en', 'fr'],
				experiences: ['entry', 'intermediate', 'expert'],
				visibilities: [
					{
						id: 1,
						name: 'Anybody'
					},
					{
						id: 2,
						name: 'Employer only'
					},
					{
						id: 3,
						name: 'Nobody'
					},
				],
				project_preferences: [
					{
						id: 1,
						name: 'Both short-term and long-term projects'
					},
					{
						id: 2,
						name: 'Long-term projects (3+ months)'
					},
					{
						id: 3,
						name: 'Short-term projects (less than 3 months)'
					},
				],
				activeTable: 'profile',
				info: {},
				contact: {},
				password: {},
				privacy: {},
				notifications: {},
				payment: {},
				locations: {
					countries: [],
					provinces: [],
					districts: [],
				},
				imageDzOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: `${this.$root.files_upload_url}/profile`,
					headers: {
						delete: false
					},
					dictDefaultMessage: "Drop file here to attach",
				},
			}
		},

		computed: {
			...mapState({
				skills: state => state.skills.skills,
				categories: state => state.categories.categories,
				currencies: state => state.currencies.currencies,
				experience_lvls: state => state.experience_lvls.experience_lvls,
			}),
			disableChangePasswordButton() {
				return this.password.password &&
							 (this.password.password.length >= 6) &&
							 (this.password.password == this.password.password_confirmation);
			},
		},

		mounted() {

		},

		methods: {
			...mapActions({
		      getAuth: 'user/getAuthUser',
		    }),
			//DROPZONE METHODS
			imageDzSuccess(file, response) {
				console.log('file uploaded', response);
				this.info.image = response.link;
			},
			imageDzError(file, message, xhr) {
				console.error('file upload error', xhr);
			},
			changeTab(tab) {
				this.activeTable = tab
			},
			//IMAGE METHODS
			deleteProfileImage() {
				this.info.image = null;
				this.info.image_info = null;
			},
			success() {
				Bus.$emit('notification', {text: 'Your info was updated'})
				this.$router.push({name: 'profileinfo'} )
			},
			//send requests methods
			saveGeneralInfo() {
				let data = {
					id: this.auth.id,
					image: this.info.image,
					description_header: this.info.description_header,
					description: this.info.description,
					category: this.info.category,
					skills: this.info.skills,
					experience_lvl: this.info.experience_lvl,
				};

				axios.patch(`${this.$root.default_api_prefix}/users/profile/info`, data)
				.then(response => {
					this.getAuth();
					this.success()
				});
			},
			saveContactInfo() {
				let data = {
					id: this.auth.id,
				};
				Object.assign(data, this.contact);

				axios.patch(`${this.$root.default_api_prefix}/users/profile/contact`, data)
				.then(response => {
					this.getAuth();
					this.success()
				});
			},
			changePassword() {
				let data = this.password;
				axios.patch(`${this.$root.default_api_prefix}/users/profile/changepassword`, data)
				.then(response => {
					this.password = {};
					this.success()
				});
			},
			savePayment() {
				let data = this.payment;
				axios.patch(`${this.$root.default_api_prefix}/users/profile/payment`, data)
				.then(response => {
					this.getAuth();
					this.success()
				});
			},
			savePrivacy() {
				let data = this.privacy;
				axios.patch(`${this.$root.default_api_prefix}/users/profile/privacy`, data)
				.then(response => {
					this.getAuth();
					this.success()
				});
			},
			getLocations(level, parent_id = null) {
				let params = parent ? {parent_id} : null;
				console.log("PARENT", params);
				axios.get(`${this.$root.default_api_prefix}/locations/${level}`, {params: params})
				.then(response => {
					this.locations[level] = response.data;
				});
			},
		},

		created() {

		},
		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getLocations('countries');
				vm.info = {
					image: vm.auth.profile.image,
					description_header: vm.auth.profile.description_header,
					description: vm.auth.profile.description,
					category: vm.auth.profile.category,
					skills: vm.auth.skills,
					experience_lvl: vm.auth.profile.details ? vm.auth.profile.details.experience_lvl : null,
					rate: vm.auth.profile.details.rate,
					platform_language: 'English',
					projects_language: 'English'
				};
				vm.contact = {
					firstname: vm.auth.profile.firstname,
					lastname: vm.auth.profile.lastname,
					email: vm.auth.email,
					first_address_line: vm.auth.profile.first_address_line,
					second_address_line: vm.auth.profile.second_address_line,
					country: vm.auth.profile.country,
					province: vm.auth.profile.province,
					district: vm.auth.profile.district,
					city: vm.auth.profile.city,
					zip: vm.auth.profile.zip,
				};
				vm.privacy = {
					visibility: vm.visibilities[vm.visibilities.findIndex(x => x.id == vm.auth.profile.privacy.visibility_id)],
					show_name: vm.auth.profile.privacy.show_name,
					project_preference: vm.project_preferences[vm.project_preferences.findIndex(x => x.id == vm.auth.profile.privacy.project_preference_id)],
				};
				vm.payment = {
					currency: 'USD',
					rate: vm.$root.isUserFreelancer(vm.auth) ? vm.auth.profile.details.rate : null,
				};
			});
		}

	}
</script>