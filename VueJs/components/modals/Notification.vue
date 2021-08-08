<template>
	<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content p-5">
				<div class="d-flex justify-content-center mb-3">
					<img :src="modal.image" alt="" width="80" height="80" class="bg-secondary rounded-circle">
				</div>
				
				<slot></slot>
				
				<p class="text-center">{{modal.text}}</p>

				<div class="d-flex justify-content-center">
					<button class="btn btn-transparent py-2 px-5" data-dismiss="modal" aria-label="Close" ref="closeModal">OK</button>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	export default {

		props: [],

		data() {
			return {
				modal: {
					text: null,
					image: null
				}
			}
		},

		mounted() {
			Bus.$on('notification', data => {
				this.modal = data
				$('#notificationModal').modal('show')
			})
		},

		methods: {
			delProject() {
				this.$emit('delete')
			},
			close() {
				this.$refs.closeModal.click()
			}
		}

	}
</script>