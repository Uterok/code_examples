<template>
	<div class="loading d-flex justify-content-center align-items-center" v-if="loading">
		<pulse-loader :color="$root.color"></pulse-loader>
	</div>
	<header class="header" :class="{'bg-white shadow' : auth}" v-else>
		<div class="py-3" :class="[auth ? 'container-fluid' : 'container']">
			<nav class="row justify-content-between align-items-center px-3">
				<router-link :to="{ name: 'home'}" tag="a">
					<logo></logo>
				</router-link>

				<ul class="d-flex align-items-center pl-0 mb-0">
					<li class="mr-3">
						<router-link :to="{ path: '/profile/create-project'}" tag="a" class="btn btn-transparent text-uppercase br-easy py-2 px-5" v-if="auth && auth.role_name == 4">
							Add a Project
						</router-link>
					</li>
					<li>
						<router-link :to="{ name: 'projects'}" tag="a">
							Projects
						</router-link>
					</li>
					<li>
						<router-link :to="{ name: 'users'}" tag="a">
							Users
						</router-link>
					</li>
					<template v-if="!auth">
						<li>
							<a role="button" data-toggle="modal" data-target="#loginModal" class="text-easy" ref="loginButton">Login</a>
						</li>
						<li>
							<a role="button" data-toggle="modal" data-target="#registerModal">Register</a>
						</li>
					</template>
					<template v-else>
						<li>
							<a href="##">
								<img src="/images/platform/icons/bell.svg" alt="notifications">
							</a>
						</li>
						<li>
							<div class="dropdown">
								<a role="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<img :src="$root.userImage(auth)" alt="user" class="rounded-circle" width="50" height="50">
								</a>
								<div class="dropdown-menu without-active px-3 header-dropdown mt-3" aria-labelledby="dropdownMenuButton">
									<router-link to="/profile/my-projects" tag="a" class="d-block">My Projects</router-link>
									<router-link to="/profile" tag="a" class="dropdown-item">My Profile</router-link>
									<router-link to="/profile/chats" tag="a" class="dropdown-item">Chat</router-link>
									<router-link to="/profile/reviews" tag="a" class="dropdown-item">Reviews</router-link>
									<div class="dropdown-divider"></div>
									<router-link to="/profile/help" tag="a" class="dropdown-item">Help</router-link>
									<router-link to="/profile/settings" tag="a" class="dropdown-item">Settings</router-link>
									<div class="dropdown-divider"></div>
									<a role="button" class="dropdown-item" @click="logout">Log Out</a>
								</div>
							</div>
						</li>
					</template>
					<li>
						<a href="##">
							<img src="/images/platform/icons/menu.svg" alt="" class="pb-1">
						</a>
					</li>
				</ul>
			</nav>
		</div>
	</header>
</template>
<script>
	import logo from './logo.vue'
	import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
	import { mapState, mapMutations } from 'vuex'

	export default {
		components: {
			PulseLoader,
			logo
		},

		data() {
			return {
				timer: null,
				loading: true
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth
			}),
		},

		watch: {
			auth() {
				$('body').removeClass('modal-open')
				this.timer = null
				this.loading = false
			}
		},

		mounted() {
		},

		methods: {
			logout() {
				this.$auth.logout().then(
					success => {
						this.$root.logout();
					},
				)
			},
			loginPlease() {
				this.$refs.loginButton.click()
			}
		},

		created() {
			this.timer = setTimeout(() => {
				if(this.timer !== null) {
					location.reload();
				}
			}, 30000);
		}

	}
</script>