<template>
	<div>
		<section class="d-flex pb-4 mb-4 question-item" v-for="request in requests">
			<div class="mr-md-4 d-flex flex-column">
				<img src="/images/landing/Opened.svg" alt="" class="rounded-circle mb-1 bg-light-easy mx-2" width="70" height="70">
				<router-link :to="{ name: 'user', params: {id: 1}}" tag="a" class="mb-0 text text-center">Jhon Snow</router-link>
			</div>
			<div class="d-flex flex-column justify-content-between w-100">

				<div class="row d-flex mb-3">
					<div class="col-lg-8 d-flex flex-column">
						<h5>Lorem ipsum dolor sit amet, consectetur adipisicing.</h5>
						<p class="mb-0 text-dark-easy">23 October 2018</p>
					</div>
					<div class="col-lg-4 d-flex flex-column flex-grow-1 align-items-end pl-0">
						<p class="text-easy fs-18 mb-1">$300.00 USD</p>
						<div class="d-flex align-items-center">
							<!-- <span class="btn btn-secondary py-0 px-1 rounded mr-3">5.0</span> -->
							<i class="fa fa-star text-yellow mr-2"></i>
							<i class="fa fa-star text-yellow mr-2"></i>
							<i class="fa fa-star text-yellow mr-2"></i>
							<i class="fa fa-star text-yellow mr-2"></i>
							<i class="fa fa-star text-yellow mr-2"></i>
							<p class="mb-0 text-dark-easy">248 review</p>
						</div>
					</div>
				</div>

				<div class="description-card">
					<p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iusto, alias, rerum, eveniet repellat cumque et animi, nemo reprehenderit omnis quam illo obcaecati ipsum necessitatibus commodi. Dolorum dolor, veritatis magnam et eligendi provident officia sequi assumenda eos quia temporibus a voluptas.</p>
				</div>
			</div>
		</section>
	</div>
</template>
<script>
	export default {

		props: ['requests'],

		data() {
			return {			}
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