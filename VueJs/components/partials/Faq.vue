<template>
	<section class="container">

		<div class="row pt-4">
			<div class="col-12">
				<div class="breadcrumb mb-4">
						<ul class="pl-0">
							<li 
							v-for="(breadcrumb, index) in $root.breadcrumbList"
							:key="index"
							v-on:click="$root.RouteTo(index)"
							:class="{'linked pointer': !!breadcrumb.link}">
							{{breadcrumb.name}}
						</li>
					</ul>
			</div>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-4">
				<div class="card p-5">
					<h4 class="mb-4">Categories</h4>
					<h4 class="pointer" v-for="category in categoriesOfQuestions" @click="openQuestionList(category)">{{category.name}}</h4>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="card p-5 help-card d-flex flex-column mb-4" v-for="category in categoriesOfQuestions" v-if="category.platform_questions.length > 0">
					<div class="d-flex justify-content-between align-items-start mb-4 help-header">
						<div class="d-flex flex-column">
							<h3>{{category.name}}</h3>
							<p class="text-dark-easy">{{category.description}}</p>
						</div>
						<!-- <button class="btn btn-transparent px-4 py-1 text-uppercase" data-toggle="modal" data-target="#contactModal">Contact Us</button> -->
					</div>

					<div class="question-item mb-4 pb-4" v-for="(question, index) in category.platform_questions">
						<a role="button" data-toggle="collapse" :data-target="'#question-' + question.id" aria-expanded="false" :aria-controls="'question-' + question.id" @click="question.show = !question.show">
							<div class="d-flex justify-content-between">
								<h4 class="mb-0">{{question.title}}</h4>
								<template v-if="question.platform_answers !== null">
									<img src="/images/platform/icons/minus.svg" width="20" alt="close button" v-if="question.show">
									<img src="/images/platform/icons/plus.svg" width="20" alt="open button" v-else>
								</template>
							</div>
						</a>
						<div v-if="question.platform_answers !== null">
							<div class="collapse" :id="'question-' + question.id">
								<div class="pt-4 text">{{question.platform_answers.description}}</div>
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
			openQuestionList(category) {
				this.singleCategory = category
			}
		},

		created() {

		}

	}
</script>