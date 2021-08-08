<template>
	<div class="text-center d-flex justify-content-between list-card">
		<div class="d-flex align-items-center" v-if="pagination.current_page">
			<label for="per_page" class="mb-0 pr-4">Result per page</label>
			<select name="per_page" id="per_page" class="p-2" v-model.number="pagination_per_page" @change="perPage">
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="15">15</option>
				<option value="20">20</option>
			</select>
		</div>
		<div>
			<template v-for="page in pagesNumber">
				<a href="javascript:void(0)" class="btn mr-2 py-1 px-3" :class="[ page == pagination.current_page ? 'bg-light-easy' : '']" v-on:click.prevent="changePage(page)">{{ page }}</a>
			</template>
		</div>
	</div>
</template>
<script>
	export default{
		props: {
			pagination: {
				type: Object,
				required: true
			},
			per_page: {
				type: Number,
				default: 5
			},
			offset: {
				type: Number,
				default: 4
			}
		},
		data() {
			return {
				pagination_per_page: null
			}
		},
		computed: {
			pagesNumber() {
				if (!this.pagination.to) {
					return [];
				}
				let from = this.pagination.current_page - this.offset;
				if (from < 1) {
					from = 1;
				}
				let to = from + (this.offset * 2) - 1;
				if (to >= this.pagination.last_page) {
					to = this.pagination.last_page;
				}
				let pagesArray = [];
				for (let page = from; page <= to; page++) {
					pagesArray.push(page);
				}
				return pagesArray;
			}
		},
		methods : {
			perPage() {
				this.pagination.per_page = this.pagination_per_page;
				this.$emit('paginate');
			},
			changePage(page) {
				if( page > 0 && page <= this.pagination.last_page ) {
					this.pagination.current_page = page;
					this.$emit('paginate');
				}
			}
		},
		created() {
			this.pagination_per_page = this.per_page
		}
	}
</script>