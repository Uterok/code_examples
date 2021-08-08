<template>
	<div class="modal fade" id="portfolioModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content modal-card p-5">
				<h4 class="mb-5" v-if="item == null">Add new portfolio work</h4>

				<template v-if="item == null">
					<vue-dropzone
					class=" platform-input text-center dropzone mb-4"
					ref="myVueDropzone"
					id="dropzone"
					:options="dropzoneOptions"
					v-on:vdropzone-success="showSuccess"
					v-on:vdropzone-files-added="multiFiles"
					v-on:vdropzone-removed-file="removedFile"></vue-dropzone>
	
				</template>

				<template v-else>
					<div id="carouselExampleControls" class="d-flex carousel slide mb-4" data-ride="carousel" v-if="form.images.length > 1">
						<div class="row w-100">
							<div class="col-lg-2">
								<a class="carousel-control-prev text-black" href="#carouselExampleControls" role="button" data-slide="prev">
									<i class="fa fa-chevron-left fa-2x"></i>
								</a>
							</div>
							<div class="col-lg-8">
								<div class="carousel-inner">
									<div class="carousel-item" :class="{'active': index == 0}" v-for="(image, index) in form.images">
										<img class="d-block w-100 h-100" :src="image.link" alt="">
									</div>
								</div>
							</div>
							<div class="col-lg-2">
								<a class="carousel-control-next text-black" href="#carouselExampleControls" role="button" data-slide="next">
									<i class="fa fa-chevron-right fa-2x"></i>
								</a>
							</div>
						</div>
					</div>
					<img :src="form.images[0].link" alt="" class="mx-auto w-75" v-else>
				</template>

				<div class="mb-4">
					<p class="form-text text-dark-easy text-muted mb-2">Name of project</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 py-3">
						<input type="text" class="w-100" v-model="form.name">
					</div>
				</div>

				<div class="mb-4">
					<p class="form-text text-dark-easy text-muted mb-2">Skills</p>
					<div class="input-group select-group pl-4 py-2">
						<v-select class="col px-0" multiple label="name" v-model="form.skills" :options="skills"></v-select>
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-search text-dark-easy"></i>
							</div>
						</div>
					</div>
				</div>

				<div class="mb-5">
					<p class="form-text text-dark-easy text-muted mb-2">Description</p>
					<div class="input-group br-dark-easy text-dark-easy select-group pl-4 pr-2 py-3">
						<textarea rows="4" class="border-0 w-100" placeholder="Enter description about project" v-model="form.description"></textarea>
					</div>
				</div>

				<div class="d-flex justify-content-center">
					<button class="btn bg-easy easy-shadow text-uppercase text-white py-2 px-5 w-25" @click="create" v-if="item == null">Add</button>
					<template v-if="item">
						<button class="btn btn-transparent text-uppercase py-2 px-5 w-25" @click="deleteItem" v-if="item.user_id == auth.id && item !== null">delete</button>
					</template>
				</div>

			</div>
		</div>
	</div>
</div>
</template>
<script>
	import { mapState, mapActions } from 'vuex'
	import vSelect from 'vue-select'
	import vue2Dropzone from 'vue2-dropzone'

	export default {
		components: {
			'vue-dropzone': vue2Dropzone,
			vSelect
		},

		props: ['auth', 'item'],

		watch: {
			item(val) {
				if(val == null) {
					this.form = {
						images: []
					}
				} else {
					this.form = this.item
				}
			}
		},

		data() {
			return {
				image: false,
				form: {
					images: []
				},
				dropzoneOptions: {
					method: 'POST',
					addRemoveLinks: true,
					url: this.$root.default_api_prefix + '/files/portfolio',
					autoProcessQueue: false,
					headers: {
						delete: false
					},
					dictDefaultMessage: "Drop your file here"
				},
			}
		},

		computed: {
			...mapState({
				skills: state => state.skills.skills,
			})
		},

		methods: {
			...mapActions({
				getAuthUser: 'user/getAuthUser'
			}),
			multiFiles() {
				this.$refs.myVueDropzone.processQueue()
			},
			removedFile(file, error, xhr) {
				for (var i = 0; i < this.form.images.length; i++) {
					if(this.form.images[i].real_name == file.name) {
						this.removeImage(i)
					}
				}
			},
			deleteItem() {
				axios.delete(this.$root.default_api_prefix + '/users/' + this.auth.id + '/portfolio/' + this.item.id)
				.then(response => {
					Bus.$emit('notification', {text: 'Portfolio has been deleted'})
					this.getAuthUser()
					$('#portfolioModal').modal('hide')
				})
			},
			create() {
				axios.post(this.$root.default_api_prefix + '/users/' + this.auth.id + '/portfolio', this.form)
				.then(response => {
					this.getAuthUser()
					Bus.$emit('notification', {text: 'Portfolio has been created'})
					this.form = {
						images: []
					}
					$('#portfolioModal').modal('hide')
				})
			},
			removeImage(index) {
				this.form.images.splice(index, 1)
			},
			showSuccess(response) {
				let resp = JSON.parse(response.xhr.response)
				this.form.images.push(resp)
				this.image = true
			},
			clearDropzone() {
				this.$refs.myVueDropzone.removeAllFiles()
			},
		}

	}
</script>