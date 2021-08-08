<template>
	<section>
		<div class="col-12 d-flex justify-content-center mb-4 pt-5 filter-group">
			<a class="px-5 text-center" v-for="tab in tabs" :class="{'active br-easy': activeTable == tab.type}" role="button" @click="activeTable = tab.type" v-if="auth.role_name == tab.role_name || tab.role_name == 'all'">{{tab.label}}</a>
		</div>
		<div class="card-body chart-body table-responsive">
			<div class="list-card">
				<vuetable
				ref="vuetable"
				class="table-hover"
				:api-url="$root.default_api_prefix + '/users/' + auth.id + '/' + tableUrl + '?type=' + activeTable + '&per_page=' + paginateData.per_page"
				:fields="fields"
				data-path="data"
				pagination-path=""
				:http-options="vuetable_http_options"
				@vuetable:load-success="loadedData"
				:per-page="paginateData.per_page">
				<template slot="name" slot-scope="props">
					<p class="mb-0">
						<template v-if="auth.role_name == 3">
							<template v-if="props.rowData.job">
								{{props.rowData.job.name}}
							</template>
						</template>
						<template v-else>
							{{props.rowData.name}}
						</template>
					</p>
				</template>
				<template slot="category" slot-scope="props">
					<p class="mb-0">
						<template v-if="auth.role_name == 3">
							<template v-if="props.rowData.job">
								<template v-if="props.rowData.job.category">
									{{props.rowData.job.category.name}}
								</template>
							</template>
						</template>
						<template v-else>
							<template v-if="props.rowData.category">
								{{props.rowData.category.name}}
							</template>
						</template>
					</p>
				</template>
				<template slot="date" slot-scope="props">
					<p class="mb-0">
						<template v-if="auth.role_name == 3">
							<template v-if="props.rowData.job">
								{{ dateFormat(props.rowData.job.created_at) }}
							</template>
						</template>
						<template v-else>
							{{ dateFormat(props.rowData.created_at) }}
						</template>
					</p>
				</template>
				<template slot="job_type" slot-scope="props">
					<p class="mb-0">
						<template v-if="auth.role_name == 3">
							<template v-if="props.rowData.job">
								<template v-if="props.rowData.job.pay_type">
									{{props.rowData.job.pay_type.name}}
								</template>
							</template>
						</template>
						<template v-else>
							<template v-if="props.rowData.pay_type">
								{{props.rowData.pay_type.name}}
							</template>
						</template>
					</p>
				</template>
				<template slot="bids" slot-scope="props">
					<p class="mb-0">
						<template v-if="props.rowData.count_bids == null">
							0
						</template>
						<template v-else>
							{{props.rowData.count_bids}}
						</template>
					</p>
				</template>
				<template slot="pay_amount" slot-scope="props">
					<p class="mb-0 text-easy">$
						<template v-if="auth.role_name == 3">
							<template v-if="props.rowData.job">
								{{props.rowData.job.pay_amount}}
							</template>
						</template>
						<template v-else>
							{{props.rowData.pay_amount}}
						</template>
					</p>
				</template>
				<template slot="actions" slot-scope="props">
					<div class="table-button-container">
						<template v-if="auth.role_name == 3">
							<router-link class="btn btn-transparent text-capitalize mr-2 py-2" :to="{ name: 'view-offerform', params: {offer_id: props.rowData.id, project_id: props.rowData.job_id}}" tag="a" v-if="activeTable == 'my_offers'">
								show offer
							</router-link>
							<router-link class="btn btn-transparent mr-2 py-2" :to="{ name: 'project', params: {id: props.rowData.job_id}}" tag="a" v-else>
								{{lang.buttons.view}}
							</router-link>
						</template>
						<router-link class="btn btn-transparent mr-2 py-2" :to="{ name: 'project', params: {id: props.rowData.id}}" tag="a" v-else>
							{{lang.buttons.view}}
						</router-link>
						
						<button class="btn btn-transparent py-2" data-toggle="modal" data-target="#deleteModal" @click="stateItem(props.rowData)" v-if="auth.role_name == 4 && activeTable == 'open'">{{lang.buttons.delete}}</button>
						<template v-if="auth.role_name == 3">
							<button class="btn btn-transparent py-2" @click="declineOffer(props.rowData)" v-if="activeTable == 'my_offers'">Decline</button>
							<button class="btn btn-transparent py-2" @click="revoke(props.rowData)" v-if="activeTable == 'request'">Revoke</button>
						</template>
						
					</div>
				</template>
			</vuetable>
			</div>

		<pagination
		v-if="paginateData.last_page !== 1"
		:pagination="paginateData"
		:per_page="5"
		:offset="3"
		@paginate="onChangePage"></pagination>

	</div>

	<delete-modal :name="'project'" @delete="deleted" ref="deleteModal"></delete-modal>

</section>
</template>
<script>
	import DeleteModal from '../../modals/DeleteModal.vue'
	import Vuetable from 'vuetable-2/src/components/Vuetable.vue'
	import Pagination from '../../layouts/Paginate.vue'

	export default {
		components: {
			DeleteModal,
			Vuetable,
			Pagination,
		},

		props: {
			lang: {},
			auth: {
				type: Object,
				default: function () { return {} }
			}
		},

		data() {
			return {
				tableUrl: 'myjobs',
				paginateData: {
					current_page: 1,
					per_page: 5,
				},
				tabs: [
					{
						type: 'open',
						label: 'open',
						role_name: 4
					},
					{
						type: 'request',
						label: 'my bids',
						role_name: 3
					},
					{
						type: 'my_offers',
						label: 'my offers',
						role_name: 3
					},
					{
						type: 'in_progress',
						label: 'in progress',
						role_name: 'all'
					},
					{
						type: 'done',
						label: 'done',
						role_name: 'all'
					},
					{
						type: 'declined',
						label: 'declined',
						role_name: 3
					},
					{
						type: 'archived',
						label: 'Archived',
						role_name: 'all'
					}
				],
				item: {
					name: '',
					category: {
						name: ''
					},
					pay_type: {
						name: ''
					},
					currency: {
						name: ''
					},
					author: {
						name: ''
					},
					skills: [{
						name: ''
					}]
				},
				activeTable: 'open',
				fields: [
				{
					name: '__slot:name',
					title: 'Name of project'
				},
				{
					name: '__slot:date',
					title: 'Date'
				},
				{
					name: '__slot:category',
					title: 'Category'
				},
				{
					name: '__slot:job_type',
					title: 'Job Type'
				},
				{
					name: '__slot:pay_amount',
					title: 'Budget'
				},
				{
					name: '__slot:bids',
					title: 'Bids'
				},
				{
					name: '__slot:actions',
					title: '',
					titleClass: 'text-right',
					dataClass: 'text-right'
				}],
				css: {
					table: {
						tableClass: 'vuetable ui blue selectable celled stackable attached table',
						ascendingIcon: 'glyphicon glyphicon-chevron-up',
						descendingIcon: 'glyphicon glyphicon-chevron-down'
					},
					pagination: {
						infoClass: 'pull-left',
						wrapperClass: 'vuetable-pagination text-center',
						activeClass: 'btn btn-primary text-white',
						disabledClass: 'disabled',
						pageClass: 'btn btn-border',
						linkClass: 'btn btn-border',
						icons: {
							first: '',
							prev: '',
							next: '',
							last: '',
						}
					}
				},
				vuetable_http_options : {
					headers : axios.defaults.headers.common
				}

			}
		},

		watch: {
			auth() {
				this.updateTable()
			},
			activeTable(val, oldVal) {
				if(this.auth.role_name == 3) {
					this.fields[5] = {
						name: 'job.author.name',
						title: 'Employers'
					}
				} else {
					if(val == 'open') {
						this.fields[5] = {
							name: '__slot:bids',
							title: 'Bids'
						}
					} else {
						this.fields[5] = {
							name: 'freelancers',
							title: 'Freelancers'
						}
					}
				}
				Vue.nextTick(() => this.$refs.vuetable.normalizeFields());
			}
		},

		mounted() {

		},

		methods: {
			updateTable() {
				if(this.auth.role_name == 4) {
					this.activeTable = 'open'
					this.tableUrl = 'myjobs'
				} else {
					this.activeTable = 'request'
					this.tableUrl = 'mybids'
				}
				axios.get(this.$root.default_api_prefix + '/users/' + this.auth.id + '/' + this.tableUrl + '?type=' + this.activeTable + '&per_page=' + this.paginateData.per_page + '&page=' + this.paginateData.current_page)
				.then(response => {
					response.data.per_page = Number(response.data.per_page)
					this.paginateData = response.data
				})
				this.$refs.vuetable.refresh();
			},
			loadedData(response) {
				response.data.per_page = Number(response.data.per_page)
				this.paginateData = response.data
			},
			dateFormat(date) {
				return date.substring(0,10)
			},
			stateItem(item) {
				this.item = item
			},
			deleted() {
				axios.delete(this.$root.default_api_prefix + '/jobs/' + this.item.id)
				.then(response => {
					this.$refs.vuetable.reload();
					this.$refs.deleteModal.close();
				})
				.catch(error => {
					this.$swal('Error',
						'',
						'error',
						);
				})
			},
			declineOffer(item) {
				axios.delete(this.$root.default_api_prefix + '/offers/' + item.id + '/declineoffer')
				.then(response => {
					Bus.$emit('notification', {text: 'This Offer was declined'})
					this.$refs.vuetable.refresh();
				})
				.catch(error => {
					this.$swal('Error',
						'',
						'error',
						);		
				})
			},
			revoke(jobBid) {
				let data = {
					revoked: 1
				}
				axios.delete(this.$root.default_api_prefix + '/users/' + this.auth.id + '/bids/' + jobBid.id + '/revoke', data)
				.then(response => {
					Bus.$emit('notification', {text: 'Your Bid was revoked'})
					this.$refs.vuetable.refresh();
				})
				.catch(error => {
					this.$swal('Error',
						'',
						'error',
						);
				})
			},
			onPaginationData(paginationData) {
				this.$refs.pagination.setPaginationData(paginationData);
			},
			onChangePage(page) {
				this.$refs.vuetable.changePage(this.paginateData.current_page);
			}
		},

		created() {
			this.updateTable()
		}

	}
</script>
