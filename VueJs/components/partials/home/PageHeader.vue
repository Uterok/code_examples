<template>
	<article class="d-flex row align-items-start main-banner mb-5">
		<div class="col-lg-6">
			<h1 class="mb-5 text-capitalize">Lorem ipsum dolore.</h1>
			<h5 class="mb-5">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Porro eos fugit, eius asperiores sit deserunt sapiente. Quis quos, mollitia incidunt ea ratione voluptatem velit soluta?</h5>
			<router-link :to="{ name: 'projects' }" tag="a" class="text-uppercase btn btn-easy text-white px-5 py-2">
				Find a Job
			</router-link>
		</div>
		<img src="/images/platform/main_bg.png" alt="banner">
	</article>
</template>
<script>
	export default {

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