<template>
	<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content modal-card">
			<div class="row justify-content-center px-3">
				<div class="col-lg-11 modal-card-login p-4">
					<form class="d-flex flex-column align-items-center">
						<div class="col-lg-8 text-center mb-2">
							<h3 class="mb-0">Register to EasyBusy</h3>
							<p>Join our platform as a:</p>
						</div>



						<ul class="nav nav-pills mb-3 col-lg-8 d-flex justify-content-center px-3" id="pills-tab" role="tablist">
						  <li class="nav-item w-50">
						    <a class="nav-link text-center left-tab" :class="{'active': form.role_name == 3}" id="pills-worker-tab" data-toggle="pill" href="#pills-worker" role="tab" aria-controls="pills-worker" aria-selected="true" @click="form.role_name = 3">Worker</a>
						  </li>
						  <li class="nav-item w-50">
						    <a class="nav-link text-center right-tab" :class="{'active': form.role_name == 4}" id="pills-employer-tab" data-toggle="pill" href="#pills-employer" role="tab" aria-controls="pills-employer" aria-selected="false" @click="form.role_name = 4">Employer</a>
						  </li>
						</ul>

						<div class="form-group  col-lg-8 d-flex justify-content-center mb-1">
							<div class="field">
								<span class="text-easy error-msg" v-if="$v.form.name.$error">{{lang.inputs.name}} {{lang.errors.required}}</span>
								<span class="text-easy error-msg" v-if="error && !$v.form.name.$error">Nickname must be without spaces</span>
								<input id="username-regestration" type="text" name="name" placeholder=" " v-model="form.name" :class="{'error': $v.form.name.$error}" @keyup="checkNickname">
								<label class="pl-1" for="username-regestration">Nickname</label>
							</div>
						</div>

						<div class="form-group col-lg-8 d-flex justify-content-center mb-1">
							<div class="field">
								<span class="text-easy error-msg" v-if="$v.form.email.$error">
									<template v-if="$v.form.email.required">
										{{lang.errors.invalid_email}}
									</template>
									<template v-else>
										{{lang.inputs.email}} {{lang.errors.required}}
									</template>
								</span>
								<input id="email-regestration" type="email" name="email" placeholder=" " v-model="form.email" :class="{'error': $v.form.email.$error}">
								<label class="pl-1" for="email-regestration">{{lang.inputs.email}}</label>
							</div>
						</div>

						<div class="form-group  col-lg-8 d-flex justify-content-center mb-1">
							<div class="field">
								<span class="text-easy error-msg" v-if="$v.form.password.$error">{{lang.inputs.password}} {{lang.errors.required}}</span>
								<input id="password-regestration" type="password" name="password" placeholder=" " v-model="form.password" :class="{'error': $v.form.password.$error}">
								<label class="pl-1" for="password-regestration">{{lang.inputs.password}}</label>
							</div>
						</div>

						<div class="form-group col-lg-8 d-flex justify-content-center mb-3">
							<div class="field">
								<span class="text-easy error-msg" v-if="$v.form.password_confirmation.$error">
									<template v-if="$v.form.password_confirmation.required">
										{{lang.inputs.password_confirmation}} {{lang.errors.required}}
									</template>
									<template v-else>
										{{lang.alerts.password_confirmation}}
									</template>
								</span>
								<input id="password_confirmation-regestration" type="password" name="password_confirmation-regestration" placeholder=" " v-model="form.password_confirmation" :class="{'error': $v.form.password_confirmation.$error}">
								<label class="pl-1" for="password_confirmation-regestration">{{lang.inputs.confird_password}}</label>
							</div>
						</div>

						<div class="form-group col-lg-8 d-flex align-items-center mb-5">
							<label class="checkgroup" for="agree">I agree to the <a href="/terms" class="text-underline">Terms of services</a>
							  <input type="checkbox" id="agree" v-model="agree">
							  <span class="checkmark"></span>
							</label>
						</div>

						
						
						<div class="form-group col-lg-8 d-flex justify-content-center mb-5">
							<button type="button" class="btn bg-easy w-75 py-1 text-white" @click="sendForm()">
								Register
							</button>
						</div>

						<div class="form-group  col-lg-8 d-flex justify-content-center mb-0">
								
								<p>{{lang.alerts.have_account}} <a role="button" class="text text-underline pl-1" data-dismiss="modal" aria-label="Close" data-toggle="modal" data-target="#loginModal">Login</a></p>
						</div>
					</form>
					
				</div>
				<button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close" ref="closeRegister">
					<img src="/images/platform/icons/close.svg" alt="close" width="17" height="17">
				</button>
			</div>
		</div>
	</div>
</div>
</template>
<script>
	import { required, email, sameAs } from 'vuelidate/lib/validators'

	export default {

		props: ['lang'],

		data() {
			return {
				form: {
					role_name: 3
				},
				error: null,
				agree: false
			}
		},

		validations: {
			form: {
				name: {
					required
				},
				email: {
					required,
					email
				},
				password: {
					required
				},
				password_confirmation: {
					required,
					sameAsPassword: sameAs('password')
				}
			}
		},

		methods: {
			checkNickname() {
				if (/\s/.test(this.form.name)) {
					this.error = true
				} else {
					this.error = false
				}
			},
			sendForm() {
				this.$v.form.$touch()
				let isValid = !this.$v.form.$invalid


				if(isValid && this.agree && !this.error) {
					this.$auth.register(this.form).then(
						success => {
							this.agree = false
							this.form =  {
								role_name: 3
							}
							this.$v.$reset()
							this.$refs.closeRegister.click()
							Bus.$emit('notification', {text: 'Your account is created, try login.'})
                })
				}
			}
		},

		created() {

		}

	}
</script>