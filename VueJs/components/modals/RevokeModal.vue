<template>
	<div class="modal fade" id="revokeModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content p-5">
				<div class="d-flex justify-content-center">
					<img src="" alt="" width="80" height="80" class="bg-secondary rounded-circle">
				</div>

				<p class="text-center">Are you shure you want to revoke {{name}}?</p>

				<div class="d-flex justify-content-between">
					<button class="btn btn-transparent py-1 px-5" @click="revokeProject">Revoke</button>
					<button class="btn btn-transparent py-1 px-5" data-dismiss="modal" aria-label="Close" ref="closeModal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</template>
<script>

	export default {

		props: ['name'],

		methods: {
			revokeProject() {
				this.$emit('revoke')
			},
			close() {
				this.$refs.closeModal.click()
			}
		}

	}
</script>