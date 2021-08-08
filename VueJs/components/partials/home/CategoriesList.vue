<template>
	<div class="row">
		<div class="col-lg-3" v-for="category in categories">
			<div class="list-card px-5 py-4 h-100">
				<img src="/images/platform/icons/Design.svg" alt="" class="w-100 pointer mb-3" @click="filterCategory(category.name)">
				<h4 class="text-center mb-0">{{category.name}}</h4>
			</div>
		</div>
	</div>
</template>
<script>
	export default {

		props: {
			categories: {
				type: Array,
				default: function () { return [] }
			}
		},

		data() {
			return {

			}
		},

		computed: {

		},

		mounted() {

		},

		methods: {
			filterCategory(category) {
				this.$router.push({name: 'projects', params: {category: category}})
			}
		},

		created() {

		}

	}
</script>