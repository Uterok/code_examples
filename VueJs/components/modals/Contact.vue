<template>
	<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content contact-modal p-5">

				<div class="row">
					<div class="col-lg-5">
						
					</div>
					<div class="col-lg-7">
						<div class="d-flex justify-content-between align-items-start">
							<div>
								<h4>Contact Us</h4>
								<p class="mb-5">We are here for you</p>
							</div>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" ref="closeContact">
								<span class="icon-close" aria-hidden="true">&times;</span>
							</button>
						</div>

						<div class="form-group mb-4">
							<label class="btn-bold" for="contact-email">Email</label>
							<input class="form-control platform-input" type="email" id="contact-email" v-model="form.email" placeholder="Enter Your Email">
							<span class="text-easy error-msg" v-if="$v.form.email.$error">
									<template v-if="$v.form.email.required">
										{{lang.errors.invalid_email}}
									</template>
									<template v-else>
										{{lang.inputs.email}} {{lang.errors.required}}
									</template>
								</span>
						</div>

						<div class="form-group mb-4">
							<label class="btn-bold" for="contact-nickname">Nickname</label>
							<input class="form-control platform-input" type="text" id="contact-nickname" v-model="form.nickname" placeholder="Enter Your Nickname">
							<span class="text-easy error-msg" v-if="$v.form.nickname.$error">Nickname {{lang.errors.required}}</span>
						</div>

						<div class="form-group mb-5">
							<label class="btn-bold" for="contact-message">Message</label>
							<textarea class="form-control platform-input" rows="3" type="text" id="contact-message" v-model="form.message" placeholder="Enter Your Message"></textarea>
							<span class="text-easy error-msg" v-if="$v.form.nickname.$error">Message {{lang.errors.required}}</span>
						</div>

						<div class="d-flex justify-content-center">
							<button class="btn btn-transparent py-1 px-5" @click="send">Contact Us</button>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</template>
<script>
	import { required, email } from 'vuelidate/lib/validators'

	export default {

		props: ['lang'],

		data() {
			return {
				form: {}
			}
		},

		validations: {
			form: {
				email: {
					required,
					email
				},
				nickname: {
					required
				},
				message: {
					required
				}
			}
		},

		computed: {

		},

		mounted() {

		},

		methods: {
			send() {
				this.$v.form.$touch()
				let isValid = !this.$v.form.$invalid
				if(isValid) {
					axios.post(this.$root.default_api_prefix + '/contact', this.form)
					.then(response => {
						this.form = {}
						this.$refs.closeContact.click()
					})
					.catch(error => {
									
					})
				}
			}
		},

		created() {

		}

	}
</script>