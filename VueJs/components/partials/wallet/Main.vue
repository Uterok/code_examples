<template>
	<section class="row user-dashboard">
		<nav class="col-lg-3 col-xl-2 pt-5 bg-white pr-lg-5">
			<ul class="profile-nav pr-lg-4">
				<li class="d-flex align-items-center py-3 pl-3">
					<router-link class="d-flex align-items-center" tag="a" :to="{ path: '/tribunal', params: {id: auth.id}}">
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<defs></defs>
							<g id="Trib" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="Dispute-feed" transform="translate(-50.000000, -162.000000)" class="fill" fill="#9BA3B5">
									<g id="Group" transform="translate(0.000000, 80.000000)">
										<path d="M63,94 L69,94 C69.5522847,94 70,94.4477153 70,95 L70,101 C70,101.552285 69.5522847,102 69,102 L63,102 C62.4477153,102 62,101.552285 62,101 L62,95 C62,94.4477153 62.4477153,94 63,94 Z M51,82 L57,82 C57.5522847,82 58,82.4477153 58,83 L58,89 C58,89.5522847 57.5522847,90 57,90 L51,90 C50.4477153,90 50,89.5522847 50,89 L50,83 C50,82.4477153 50.4477153,82 51,82 Z M51,94 L57,94 C57.5522847,94 58,94.4477153 58,95 L58,101 C58,101.552285 57.5522847,102 57,102 L51,102 C50.4477153,102 50,101.552285 50,101 L50,95 C50,94.4477153 50.4477153,94 51,94 Z M63,82 L69,82 C69.5522847,82 70,82.4477153 70,83 L70,89 C70,89.5522847 69.5522847,90 69,90 L63,90 C62.4477153,90 62,89.5522847 62,89 L62,83 C62,82.4477153 62.4477153,82 63,82 Z" id="Rectangle-12-Copy-4"></path>
									</g>
								</g>
							</g>
						</svg>
						Overview
					</router-link>
				</li>
				<li class="d-flex align-items-center py-3 pl-3">
					<router-link class="d-flex align-items-center" tag="a" to="/profile">
						<svg width="20px" height="19px" viewBox="0 0 20 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<defs></defs>
							<g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="Elements/Left-Nav/Wallet" transform="translate(-50.000000, -143.000000)">
									<g id="Icons/Dash/Navigation/Wallet" transform="translate(50.000000, 142.000000)">
										<rect id="Rectangle-13" fill="#B4BAC7" x="0" y="5" width="20" height="15" rx="1" class="fill"></rect>
										<circle id="Oval-2" fill="#ECEDF2" cx="16" cy="12" r="2"></circle>
										<polygon id="Path-2" fill="#ECEDF2" points="0 5.10115463 13.8988753 1.27445273 18.2554055 5.10115463"></polygon>
									</g>
								</g>
							</g>
						</svg>
						Wallet
					</router-link>
				</li>
				<li class="d-flex align-items-center py-3 pl-3">
					<!-- <router-link class="d-flex align-items-center" tag="a" to="/profile">Offers</router-link> -->
					<router-link class="d-flex align-items-center" tag="a" :to="{ name: 'reviews', params: {id: auth.id}}">
						<svg width="20px" height="15px" viewBox="0 0 20 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<defs></defs>
							<g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="Elements/Left-Nav/Wallet" transform="translate(-50.000000, -207.000000)">
									<g id="Icons/Dash/Navigation/Card" transform="translate(50.000000, 204.000000)">
										<rect id="Rectangle-13" fill="#ECEDF2" x="0" y="3" width="20" height="15" rx="1"></rect>
										<rect id="Rectangle" fill="#B4BAC7" x="0" y="7" width="20" height="4" class="fill"></rect>
									</g>
								</g>
							</g>
						</svg>
						Cards
					</router-link>
				</li>
				<li class="d-flex align-items-center py-3 pl-3">
					<!-- <router-link class="d-flex align-items-center" tag="a" to="/profile">Offers</router-link> -->
					<router-link class="d-flex align-items-center" tag="a" :to="{ name: 'reviews', params: {id: auth.id}}">
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<defs></defs>
							<g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="Elements/Left-Nav/Wallet" transform="translate(-50.000000, -263.000000)">
									<g id="Icons/Dash/Navigation/Exchange" transform="translate(50.000000, 263.000000)">
										<rect id="Rectangle-13-Copy" fill="#B4BAC8" x="0" y="0" width="20" height="20" rx="2"></rect>
										<g id="two-circling-arrows" stroke-width="1" transform="translate(2.500000, 2.500000)" fill="#ECEDF2" class="fill">
											<path d="M3.59717733,11.7823518 C2.28318223,10.79659 1.44230769,9.28508211 1.44230769,7.58991336 C1.44230769,5.08410351 3.28231241,2.98476568 5.76923077,2.3861886 L5.76923077,1 C2.47755473,1.62295005 0,4.33669779 0,7.58991336 C0,9.60029944 0.946549794,11.4030355 2.44555375,12.6351179 C3.60582068,13.6429497 4.32692308,12.2979265 3.59717733,11.7823518 Z M12.4936341,2.19867321 C11.7548334,1.57016463 10.6730769,2.58905603 11.3822433,3.09380124 C12.7083802,4.08914173 13.5577952,5.62305157 13.5577952,7.34343026 C13.5577952,9.87471585 11.7174818,11.9954598 9.23076923,12.5996422 L9.23076923,14 C12.5224453,13.3701346 15,10.6296046 15,7.34333334 C15,5.28607018 14.0281375,3.4440603 12.4936341,2.19867321 Z M6.44704412,3.90465752 L8.95642038,2.41078517 C9.32150257,2.19339125 9.32227255,1.839306 8.95840034,1.62038114 L6.42581457,0.0965460448 C6.06150237,-0.122050752 5.76747867,0.0429630381 5.76923863,0.466268791 L5.78287834,3.53198224 C5.78397831,3.95506929 6.08152194,4.12205143 6.44704412,3.90465752 Z M8.57420602,11.0965335 L6.0417807,12.6204025 C5.67764902,12.8393323 5.67841885,13.1933161 6.04387025,13.4108242 L8.55254068,14.9048392 C8.91755218,15.1218005 9.21569805,14.9553614 9.21756765,14.5317182 L9.23076481,11.4660457 C9.23208453,11.0430586 8.93767785,10.8779318 8.57420602,11.0965335 Z" id="Shape" fill-rule="nonzero"></path>
										</g>
									</g>
								</g>
							</g>
						</svg>
						Exchange
					</router-link>
				</li>
				<li class="d-flex align-items-center py-3 pl-3 border-bottom br-dark-easy">
					<!-- <router-link class="d-flex align-items-center" tag="a" to="/profile">Offers</router-link> -->
					<router-link class="d-flex align-items-center" tag="a" :to="{ name: 'reviews', params: {id: auth.id}}">
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<defs></defs>
							<g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="Elements/Left-Nav/Wallet" transform="translate(-50.000000, -323.000000)">
									<g id="Icons/Dash/Navigation/History" transform="translate(50.000000, 323.000000)">
										<g id="history">
											<path d="M9.96136364,0 C4.46872727,0 9.09090909e-05,4.46863636 9.09090909e-05,9.96127273 C9.09090909e-05,15.454 4.46863636,19.9227273 9.96127273,19.9227273 C15.454,19.9227273 19.9225455,15.454 19.9225455,9.96127273 C19.9226364,4.46863636 15.4540909,0 9.96136364,0 Z" id="Shape" fill="#B4BAC8" fill-rule="nonzero" class="fill"></path>
											<path d="M15.1010909,9.82827273 L10.3691818,9.82827273 L10.3691818,4.659 C10.3691818,4.28245455 10.064,3.97718182 9.68736364,3.97718182 C9.31081818,3.97718182 9.00554545,4.28245455 9.00554545,4.659 L9.00554545,10.5100909 C9.00554545,10.8866364 9.31081818,11.1919091 9.68736364,11.1919091 L15.1010909,11.1919091 C15.4777273,11.1919091 15.7829091,10.8866364 15.7829091,10.5100909 C15.7829091,10.1335455 15.4776364,9.82827273 15.1010909,9.82827273 Z" id="Shape" fill="#FAFAFA" fill-rule="nonzero"></path>
										</g>
									</g>
								</g>
							</g>
						</svg>
						History
					</router-link>
				</li>
				<li class="d-flex align-items-center pt-5 pb-3 pl-3">
					<router-link class="d-flex align-items-center" tag="a" to="/wallet/help">
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<g id="Employer" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="My-projects-Open" transform="translate(-50.000000, -444.000000)" fill-rule="nonzero">
									<g id="help-copy-2" transform="translate(50.000000, 444.000000)">
										<path d="M10,0 C4.47328125,0 0,4.47253906 0,10 L0,19.21875 C0,19.6502344 0.349765625,20 0.78125,20 L10,20 C15.5266797,20 20,15.5274609 20,10 C20,4.47328125 15.5274609,0 10,0 Z" id="Shape" fill="#9BA3B5" class="fill"></path>
										<path d="M9.49018395,13 C8.66670417,13 8,13.6818182 8,14.5 C8,15.2986773 8.64707206,16 9.49018395,16 C10.3332958,16 11,15.2987332 11,14.5 C11,13.6818182 10.3137762,13 9.49018395,13 Z" id="Shape" fill="#FFFFFF"></path>
										<path d="M9.8829086,4 C7.90730089,4 7,5.24510231 7,6.08557427 C7,6.69260909 7.48289181,6.97275153 7.87803854,6.97275153 C8.66824804,6.97275153 8.34632016,5.77430986 9.83899408,5.77430986 C10.5707209,5.77430986 11.1560939,6.1167409 11.1560939,6.83268031 C11.1560939,7.67319692 10.33658,8.15565453 9.85364625,8.5914515 C9.42927915,8.98054318 8.87316848,9.61869998 8.87316848,10.9572129 C8.87316848,11.7665182 9.07800495,12 9.67803014,12 C10.3951047,12 10.5414165,11.6576136 10.5414165,11.3618879 C10.5414165,10.5525379 10.5561107,10.0856189 11.3609723,9.41638481 C11.7560771,9.08953708 13,8.03116663 13,6.56812118 C13,5.10507574 11.7560771,4 9.8829086,4 Z" id="Shape" fill="#FFFFFF"></path>
									</g>
								</g>
							</g>
						</svg>
						Help
					</router-link>
				</li>
				<li class="d-flex align-items-center py-3 pl-3">
					<router-link class="d-flex align-items-center" tag="a" to="/wallet/settings">
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="mr-3">
							<g id="Employer" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="My-projects-Open" transform="translate(-50.000000, -504.000000)" fill="#9BA3B5" fill-rule="nonzero" class="fill">
									<g id="settings-(1)-copy-2" transform="translate(50.000000, 504.000000)">
										<path d="M19.2681909,8.54371489 L17.2870207,8.21320113 C17.1430293,7.62449512 16.9330801,7.06174895 16.6596505,6.5368823 L17.9279396,4.96429125 C18.1976862,4.63194713 18.1854321,4.15375464 17.9010431,3.83299535 L17.1130972,2.94618632 C16.8274805,2.62576186 16.3538335,2.55799393 15.9925493,2.78625311 L14.2982681,3.85134358 C13.5530665,3.33067336 12.7089392,2.94618632 11.7983407,2.7230834 L11.4668771,0.731228495 C11.3973255,0.309464631 11.0323584,0 10.6039334,0 L9.41651247,0 C8.98895791,0 8.62276318,0.309464631 8.55475176,0.731228495 L8.22148018,2.7237084 C7.46862256,2.90803896 6.76275019,3.20558393 6.11857245,3.59745937 L4.50727992,2.44750273 C4.16001321,2.19939978 3.68304045,2.23819446 3.37999121,2.54060551 L2.5411093,3.38011538 C2.23870736,3.68317375 2.19991384,4.16016089 2.44865662,4.50743807 L3.60125711,6.12185951 C3.21307641,6.76119058 2.91859834,7.46306637 2.73429566,8.20925023 L0.731161819,8.54373721 C0.310057967,8.61329086 0,8.97829127 0,9.40675157 L0,10.5932708 C0,11.0216864 0.310057967,11.3867315 0.731161819,11.4562851 L2.73429566,11.7907721 C2.88565287,12.4047906 3.10537864,12.9913091 3.39742374,13.5351267 L2.13491573,15.0988338 C1.86608424,15.4308654 1.87737856,15.9093704 2.16176763,16.2298395 L2.94911085,17.1166709 C3.23474988,17.437698 3.70864238,17.5042383 4.06997118,17.2765595 L5.78867131,16.1968708 C6.51590467,16.6943043 7.33862633,17.0599074 8.22148018,17.2765595 L8.55475176,19.2687492 C8.6227855,19.6905354 8.98895791,20 9.41651247,20 L10.6039334,20 C11.0323584,20 11.3973255,19.6905577 11.4668771,19.2687715 L11.7988987,17.2765818 C12.5423593,17.0947289 13.239281,16.8023625 13.8777223,16.4172058 L15.5561557,17.6159795 C15.9027975,17.8649753 16.3801274,17.8259128 16.6828641,17.5225419 L17.5220808,16.6833222 C17.8241256,16.381179 17.8650396,15.9044597 17.6141987,15.5565575 L16.4192334,13.8811316 C16.8085301,13.2396576 17.1045706,12.5365318 17.2877126,11.7870221 L19.2688605,11.4562181 C19.691192,11.3866645 20,11.0216418 20,10.5932038 L20,9.40668461 C19.999375,8.97829127 19.690567,8.61329086 19.2681909,8.54371489 Z M10.0314053,13.7500153 C7.96084042,13.7500153 6.28151415,12.070326 6.28151415,10.0000112 C6.28151415,7.92938385 7.96084042,6.25000698 10.0314053,6.25000698 C12.1016131,6.25000698 13.7812519,7.92938385 13.7812519,10.0000112 C13.7812519,12.070326 12.1016131,13.7500153 10.0314053,13.7500153 Z" id="settings_1_"></path>
									</g>
								</g>
							</g>
						</svg>
						Settings
					</router-link>
				</li>
			</ul>
		</nav>
		<div class="col-lg-9 col-xl-10 pt-5 d-flex justify-content-center">
			<div class="col-lg-9 pb-5">
				<router-view :auth="auth" :lang="lang"></router-view>
			</div>
		</div>
	</section>
</template>
<script>
	import { mapActions, mapState } from 'vuex'
	export default {

		props: ['lang'],

		data() {
			return {

			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
			}),
		},

		mounted() {

		},

		methods: {
			...mapActions({
				getAuth: 'user/getAuthUser',
			}),
		},

		created() {
		},
		beforeRouteEnter(to, from, next) {
			next(vm => {
				vm.getAuth()
			});
		}

	}
</script>