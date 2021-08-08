<template>
	<section>
		<h1 class="text-center mb-3 pt-5">Frequently Asked Questions</h1>
		<p class="text-center">Lorem ipsum dolor sit amet, consectetur adipisicing. Lorem ipsum dolor sit.</p>

		<div class="row">
			<div class="col-lg-4">
				<div class="card p-5">
					<h4 class="mb-4">Categories</h4>
					<h4 class="pointer" v-for="category in categoriesOfQuestions" @click="singleCategory = category">{{category.name}}</h4>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="card d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-4 help-header p-5">
						<div class="d-flex flex-column">
							<h3>{{singleCategory.name}}</h3>
							<p>{{singleCategory.description}}</p>
						</div>
						<button class="btn bg-easy text-white easy-shadow px-5 py-2 text-uppercase" data-toggle="modal" data-target="#contactModal">Contact Us</button>
					</div>
					
					<div class="help-card px-5">
						<div class="border-bottom border-dark mb-4 pb-4" v-for="(question, index) in singleCategory.platform_questions" v-if="singleCategory.platform_questions.length > 0">
							<a role="button" data-toggle="collapse" :data-target="'#question-' + question.id" aria-expanded="false" :aria-controls="'question-' + question.id" @click="question.show = !question.show">
								<div class="d-flex justify-content-between">
									<h4 class="mb-0">{{question.title}}</h4>
									<template v-if="question.platform_answers !== null">
										<img src="/images/platform/icons/minus.svg" width="20" alt="close button" v-if="question.show">
										<img src="/images/platform/icons/plus.svg" width="20" alt="open button" v-else>
									</template>
								</div>
							</a>
							<div>
								<div class="collapse" :id="'question-' + question.id"  v-if="question.platform_answers !== null">
									<div class="pt-4">{{question.platform_answers.description}}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
<script>

	import { mapState, mapActions } from 'vuex'
	export default {

		data() {
			return {
				questions: [],
				singleCategory: {},
			}
		},

		computed: {
			...mapState({
				auth: state => state.user.auth,
				categoriesOfQuestions: state => state.categoriesOfQuestions.categoriesOfQuestions,
			}),
		},

		mounted() {

		},

		methods: {
		
		},

		created() {
			this.singleCategory = this.categoriesOfQuestions[0]
		}

	}
</script>