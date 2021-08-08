<template>
	<section>
		<div class="col-12 d-flex justify-content-between mb-4 filter-group pt-5">
			<div>
				<a class="text-center px-5 text-capitalize" :class="{'active': activeTable == 'received'}" role="button" @click="changeTab('received')">Received</a>
				<a class="text-center px-5 text-capitalize" :class="{'active': activeTable == 'written'}" role="button" @click="changeTab('written')">Written</a>
			</div>
			<div class="d-flex justify-content-end align-items-center px-3">
				<h5 class="mb-0 mr-3">Sort by:</h5>
				<button class="btn btn-transparent px-5 py-2 text-uppercase h-44">Rating</button>
			</div>
		</div>

		<div class="list-card pb-0">
			<user-requests :requests="requests"></user-requests>
		</div>
	</section>
</template>
<script>
	import UserRequests from '../projects/UserRequests.vue'
	import Pagination from '../../layouts/Paginate.vue'
	export default {
		components: {
			UserRequests,
			Pagination
		},

		props: ['auth'],

		data() {
			return {
				activeTable: 'received',
				requests: [1,2,3,4,5,6],
				paginateData: {
					current_page: 1,
					per_page: 5,
				},
			}
		},

		computed: {

		},

		mounted() {

		},

		methods: {
			changeTab(tab) {
				this.activeTable = tab
			},
			getData(data) {

			}
		},

		created() {
		}

	}
</script>