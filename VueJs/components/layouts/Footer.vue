<template>
	<footer class="py-4 bg-white">
		<div class="container">
			<div class="row justify-content-between align-items-center px-3">
				<router-link :to="{ name: 'home'}" tag="a">
					<logo></logo>
				</router-link>
				<router-link :to="{ name: 'projects'}" tag="a" class="pt-2">
					Projects
				</router-link>
				<router-link :to="{ name: 'users'}" tag="a" class="pt-2">
					Freelancers
				</router-link>
				<router-link :to="{ name: 'terms'}" tag="a" class="pt-2">
					Privacy
				</router-link>
				<a href="##" class="pt-2">
					Support
				</a>
				<router-link :to="{ name: 'faq'}" tag="a" class="pt-2">
					FAQ
				</router-link>
				<p class="mb-0 pt-2"> © 2018 EasyBusy</p>
			</div>
		</div>
	</footer>
</template>
<script>
	import logo from './logo.vue'

	export default {
		components: {
			logo
		},

		props: [],

		data() {
			return {

			}
		},

		computed: {

		},

		mounted() {

		},

		methods: {

		},

		created() {

		}

	}
</script>