<template>
	<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content modal-card">
				<div class="row justify-content-center px-3">
					<div class="col-lg-10 modal-card-login px-4 py-5">
						<form class="d-flex flex-column align-items-center">
							<div class="col-lg-9 text-center">
								<h3 class="mb-0">Login to EasyBusy</h3>
								<p>Enter your details below</p>
							</div>
							<span class="error global-error text-easy d-flex justify-content-center w-100 px-3" v-if="error">{{error}}</span>
							<div class="form-group col-lg-9 d-flex justify-content-center">
								<div class="field">
									<span class="text-easy error-msg" v-if="$v.form.email.$error">
										<template v-if="$v.form.email.required">
											{{lang.errors.invalid_email}}
										</template>
										<template v-else>
											{{lang.inputs.email}} {{lang.errors.required}}
										</template>
									</span>
									<input id="email" type="email" name="email" placeholder=" " v-model="form.email" :class="{'error': $v.form.email.$error}">
									<label class="pl-1" for="email">{{lang.inputs.email}}</label>
								</div>
							</div>

							<div class="form-group  col-lg-9 d-flex justify-content-center mb-0">
								<div class="field">
									<span class="text-easy error-msg" v-if="$v.form.password.$error">{{lang.inputs.password}} {{lang.errors.required}}</span>
									<input id="password" type="password" name="password" placeholder=" " v-model="form.password" :class="{'error': $v.form.password.$error}" @keyup.enter="sendForm">
									<label class="pl-1" for="password">{{lang.inputs.password}}</label>
								</div>
							</div>

							<div class="form-group col-lg-9 d-flex mb-5 pt-1">
								<div class="d-flex justify-content-end w-100">
									<a class="btn btn-link px-0" href="/password/reset">
										{{lang.buttons.forgot_password}}
									</a>
								</div>
							</div>

							<div class="form-group col-lg-9 d-flex justify-content-center mb-5 pb-4">
								<button type="button" class="btn bg-easy text-uppercase text-white py-2 w-75" @click="sendForm">
									login
								</button>
							</div>

						<div class="form-group  col-lg-9 d-flex justify-content-center mb-0">

							<p>Don't have account? <a role="button" class="text text-underline pl-1" data-dismiss="modal" aria-label="Close" data-toggle="modal" data-target="#registerModal">Register</a></p>
						</div>
					</form>
					
				</div>
				<button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close" ref="closeLogin">
					<img src="/images/platform/icons/close.svg" alt="close" width="17" height="17">
				</button>
			</div>
		</div>
	</div>
</div>
</template>
<script>
	import { required, email } from 'vuelidate/lib/validators'
	import { mapState, mapActions } from 'vuex'

	export default {

		props: ['lang'],

		data() {
			return {
				form: {},
				error: null
			}
		},

		validations: {
			form: {
				email: {
					required,
					email
				},
				password: {
					required
				}
			}
		},

		methods: {
			sendForm() {
				this.error = null
				this.$v.form.$touch()
				let isValid = !this.$v.form.$invalid
				if(isValid) {
					this.$auth.login(this.form).then(
						success => {
							this.$refs.closeLogin.click()
							this.$router.push({name: 'profileinfo'});
							this.$root.getAuth()
							this.form = {}
						},
						error => {
							
						});
				}
			}
		}

	}
</script>