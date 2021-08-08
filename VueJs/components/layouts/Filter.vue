<template>
	<div>
		<div class="row filter-group px-3 pt-3" v-if="headerType">
			<a href="##" :class="{active: filter == activeFilter}" v-for="(filter, index) in filters" @click="sortByType(filter)">{{filter}}</a>
		</div>
		<div class="list-card my-5">
			<div class="breadcrumb mb-4" v-if="$route.meta.platformname == 'main'">
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
			<div class="row align-items-center">
				<div class="flex-grow-1 px-3">
					<div class="input-group select-group pl-4">
						<input type="text" class="form-control h-44" :placeholder="placeholder" v-model="form.search">
						<div class="input-group-prepend">
							<div class="input-group-text pr-4">
								<i class="fa fa-search text-dark-easy pointer" @click="filter"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="d-flex justify-content-end align-items-center pl-2 pr-5">
					<button class="btn bg-easy text-white px-4 py-2 text-uppercase fw-300 h-44" type="button" data-toggle="collapse" data-target="#filter" aria-expanded="false" aria-controls="filter" @click="toggleHiddablePanel">
						<i class="fa fa-sliders mx-1"></i>
						Filters
					</button>
				</div>
				<div class="d-flex justify-content-end align-items-center px-3 w-25">
					<h5 class="mb-0 mr-3 w-50">Sort by:</h5>
					<button class="btn btn-transparent px-4 py-2 text-uppercase h-44">Rating</button>
				</div>
			</div>

			<div class="row mt-5" v-if="tags.length > 0">
				<div class="col-12 d-flex align-items-start flex-wrap">
					<span class="btn bg-light-easy text btn-bold mr-3 mb-3 fw-300 d-flex align-items-center" v-for="(tag, index) in tags">
						{{tag.label}}
						<button class="btn btn-transparent border-0 px-0" @click="removeTag(tag, index)">
							<img src="/images/platform/icons/close.svg" alt="" width="10" height="10" class="ml-2">
						</button>
					</span>
					<a role="button" class="text-easy text-underline pt-1 border-0 mb-3" @click="clearAll" v-if="tags.length > 0">Clear All</a>
				</div>
			</div>

		</div>

		<div class="collapse" id="filter" ref="filterBody">

			<div class="list-card">

			<div class="row justify-content-end">
				<button type="button" class="close" data-toggle="collapse" data-target="#filter" aria-expanded="false" aria-controls="filter"  @click="toggleHiddablePanel">
					<img src="/images/platform/icons/close.svg" alt="">
				</button>
			</div>

			<div class="row mb-5">
				<div class="col-lg-4">
					<div class="form-group">
						<label class="btn-bold mb-3" for="categories-filter">Categories</label>
						<div class="input-group select-group pl-4 py-1">
							<v-select class="col px-0 hide-tags" multiple label="name" v-model.trim="form.categories" :options="categories" id="categories-filter" placeholder="Choose your category"></v-select>
							<div class="input-group-prepend">
								<div class="input-group-text pr-4">
									<i class="fa fa-search text-dark-easy"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="form-group">
						<label class="btn-bold mb-3" for="skills-filter">Skills</label>
						<div class="input-group select-group pl-4 py-1">
							<v-select class="col px-0 hide-tags" multiple label="name" v-model.trim="form.skills" :options="skills" id="skills-filter" placeholder="Choose your skills"></v-select>
							<div class="input-group-prepend">
								<div class="input-group-text pr-4">
									<i class="fa fa-search text-dark-easy"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="form-group">
						<label class="btn-bold mb-3" for="location-filter">Location</label>
						<div class="input-group select-group pl-4 py-1">
							<v-select class="col px-0" label="name" v-model.trim="form.location" :options="locations" id="location-filter" placeholder="Choose your location"></v-select>
							<div class="input-group-prepend">
								<div class="input-group-text pr-4">
									<i class="fa fa-search text-dark-easy"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row" :class="{'mb-5': loaded_meta && showFilterElement('hourly_rate') || loaded_meta && showFilterElement('budget')}">
				<div class="col-lg-4" v-if="showFilterElement('experience_lvl')">
					<label class="btn-bold mb-3">{{getFilterTitle('experience_lvl', 'Experience Level')}}</label>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="experience_lvl">Any Experience Level {{getMetaInfo(meta.experience_lvl, 0)}}
						  <input type="checkbox" id="experience_lvl" v-model="form.experience_lvl.any">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="entry_lvl">Entry Level {{getMetaInfo(meta.experience_lvl, 1)}}
						  <input type="checkbox" id="entry_lvl" v-model="form.experience_lvl.entry">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="intermediate">Intermediate {{getMetaInfo(meta.experience_lvl, 2)}}
						  <input type="checkbox" id="intermediate" v-model="form.experience_lvl.intermediate">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="expert">Expert {{getMetaInfo(meta.experience_lvl, 3)}}
						  <input type="checkbox" id="expert" v-model="form.experience_lvl.expert">
						  <span class="checkmark"></span>
						</label>
					</div>
				</div>

				<div class="col-lg-4" v-if="showFilterElement('client_rating')">
					<label class="btn-bold mb-3">{{getFilterTitle('client_rating', 'Client rating')}}</label>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="any_job">Any Rating {{getMetaInfo(meta.client_rating, 0)}}
						  <input type="checkbox" id="any_job" v-model="form.client_rating.any">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="more_80">More than 80 {{getMetaInfo(meta.client_rating, 1)}}
						  <input type="checkbox" id="more_80" v-model="form.client_rating.more_80">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="more_90">More than 90 {{getMetaInfo(meta.client_rating, 2)}}
						  <input type="checkbox" id="more_90" v-model="form.client_rating.more_90">
						  <span class="checkmark"></span>
						</label>
					</div>
				</div>

				<div class="col-lg-4" v-if="showFilterElement('freelancer_type')">
					<label class="btn-bold mb-3">{{getFilterTitle('freelancer_type', 'Freelancer Type')}}</label>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="all_types">All types {{getMetaInfo(meta.freelancer_type, 0)}}
						  <input type="checkbox" id="all_types" v-model="form.freelancer_type.any">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="independent_freelancer">Independent freelancer {{getMetaInfo(meta.freelancer_type, 1)}}
						  <input type="checkbox" id="independent_freelancer" v-model="form.freelancer_type.independent">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="d-flex align-items-center mb-2">
						<label class="checkgroup" for="agency">Agency freelancers {{getMetaInfo(meta.freelancer_type, 2)}}
						  <input type="checkbox" id="agency" v-model="form.freelancer_type.agency">
						  <span class="checkmark"></span>
						</label>
					</div>
				</div>
			</div>

			<div class="row mb-4" v-if="loaded_meta && showFilterElement('hourly_rate') || loaded_meta && showFilterElement('budget')">

				<div class="col-lg-4" v-if="loaded_meta && showFilterElement('hourly_rate')">
					<label class="btn-bold mb-3">{{getFilterTitle('hourly_rate', 'Hourly Rate')}}</label>
					<div class="d-flex mb-2">
						<input class="py-0 pl-2 slider-counter" type="number" v-model.number="form.hourly_rate[0]" :min="hourlyRateMetaMin" :max="hourlyRateMetaMax">
						<span class="px-2">-</span>
						<input class="py-0 pl-2 slider-counter" type="number" v-model.number="form.hourly_rate[1]" :min="hourlyRateMetaMin" :max="hourlyRateMetaMax">
					</div>
					<vue-slider v-bind="vue_slider_hourly_options" v-model="form.hourly_rate" ref="slider1"></vue-slider>
				</div>

				<div class="col-lg-4" v-if="loaded_meta && showFilterElement('budget')">
					<label class="btn-bold mb-3">{{getFilterTitle('hourly_rate', 'Budget')}}</label>
					<div class="d-flex mb-2">
						<input class="py-0 pl-2 slider-counter" type="number" v-model.number="form.budget[0]" :min="budgetMetaMin" :max="budgetMetaMax">
						<span class="px-2">-</span>
						<input class="py-0 pl-2 slider-counter" type="number" v-model.number="form.budget[1]" :min="budgetMetaMin" :max="budgetMetaMax">
					</div>
					<vue-slider v-bind="vue_slider_budget_options" v-model="form.budget" ref="slider2"></vue-slider>
				</div>
			</div>
		</div>

		</div>
	</div>
</template>
<script>
	import vSelect from 'vue-select'
	import vueSlider from 'vue-slider-component'
	import { mapState, mapActions } from 'vuex'
	export default {
		components: {
			vSelect,
			vueSlider
		},

		props: ['headerType', 'skill', 'category', 'location', 'placeholder', 'meta', 'settings'],

		data() {
			return {
				sorts: ['RATING', 'DATE'],
				tags: [],
				show: false, //is hiddable filtes shown
				loaded_meta: false, //is meta data loaded
				send_request_perm: true, //is can send filter request
				skillCount: 0,
				categoryCount: 0,
				form: this.getClearFormObj(),
				vue_slider_hourly_options: {
					min: 0,
					max: 10000,
					width: 'auto',
					height: 8,
					dotSize: 18,
					interval: 1,
					speed: 0.3,
					tooltip: false,
					bgStyle: {
					  "backgroundColor": this.$root.color
					},
					processStyle: {
					  "backgroundColor": this.$root.color
					},
					sliderStyle: [
					  {
					    "backgroundColor": this.$root.color
					  },
					  {
					    "backgroundColor": this.$root.color
					  }
					]
				},
				vue_slider_budget_options: {
					min: 0,
					max: 10000,
					width: 'auto',
					height: 8,
					dotSize: 18,
					interval: 1,
					speed: 0.3,
					tooltip: false,
					bgStyle: {
					  "backgroundColor": this.$root.color
					},
					processStyle: {
					  "backgroundColor": this.$root.color
					},
					sliderStyle: [
					  {
					    "backgroundColor": this.$root.color
					  },
					  {
					    "backgroundColor": this.$root.color
					  }
					]
				},
				activeFilter: 'freelancers',
				filters: ['freelancers', 'company', 'employers'],
				languages: ['en', 'fr'],
				filters: {},
				delay_filter_timer: null,
			}
		},

		watch: {
			settings (val, oldVal) {
				this.clearAll();
			},
			show (val) {
		    	if (val) {
		    		this.$nextTick(() => {if (this.$refs.slider1) this.$refs.slider1.refresh()});
		    		this.$nextTick(() => {if (this.$refs.slider2) this.$refs.slider2.refresh()});
		      }
		    },
			//if tags array empty after clear set filter request permission to true
			'tags.length'(val, oldVal) {
				if (!val && oldVal) {
					this.send_request_perm = true;
					this.$emit('filter-on-change-hiddable');
				}
			},
			filters(val, oldVal) {
				if (!Object.keys(val).length && !this.send_request_perm) {
					this.send_request_perm = true;
					this.$emit('filter-on-change-hiddable');
				}
			},
			//meta watchers
			meta(val, oldVal) {
				// console.log('OBJ KEYS', Object.keys(val), Object.keys(oldVal));
				if (Object.keys(val).length && !Object.keys(oldVal).length) {
					Vue.set(this, 'loaded_meta', true);
				} else if (!Object.keys(val).length) {
					Vue.set(this, 'loaded_meta', false);
				}
			},
			'meta.hourly_rate'(val, oldVal) {
				if (this.form.hourly_rate[0] < +val[0]) 
				{
					Vue.set(this.form.hourly_rate, 0, +val[0]);
				}
				if (this.form.hourly_rate[1] > +val[1]) Vue.set(this.form.hourly_rate, 1, +val[1]);
				this.vue_slider_hourly_options.min = +val[0];
				this.vue_slider_hourly_options.max = +val[1];
			},
			'meta.budget'(val, oldVal) {
				if (this.form.budget[0] < +val[0]) Vue.set(this.form.budget, 0, +val[0]);
				if (this.form.budget[1] > +val[1]) Vue.set(this.form.budget, 1, +val[1]);
				this.vue_slider_budget_options.min = +val[0];
				this.vue_slider_budget_options.max = +val[1];
			},
			//changing watchers
			'form.search'(newVal, oldVal) {
				if (newVal) {
					this.filters.search = newVal;
				} else {
					delete this.filters.search;
				}
			},
			'form.categories'(val, oldVal) {
				if(this.form.categories.length > 0) {
					let category_id = this.form.categories.length - 1;
					if((this.form.categories.length - this.categoryCount) >= 1) {
						this.tags.push({
							id: this.form.categories[category_id].id,
							label: this.form.categories[category_id].name,
							type: 'category'
						})
						this.addToFilterArray(this.filters, 'categories', this.form.categories[category_id].id);
						this.categoryCount ++
					} else {
						this.categoryCount --
					}
				} else {
					if (this.categoryCount) {
						this.categoryCount = 0;
					}
				}
				this.$emit('filter-on-change-hiddable');
				this.filter()
			},
			'form.skills'(newVal, oldVal){
				if(this.form.skills.length > 0) {
					let skill_id = this.form.skills.length - 1;
					if((this.form.skills.length - this.skillCount) >= 1) {
						this.tags.push({
							id: this.form.skills[skill_id].id,
							label: this.form.skills[skill_id].name,
							type: 'skill'
						})
						this.addToFilterArray(this.filters, 'skills', this.form.skills[skill_id].id);
						this.skillCount ++
					} else {
						this.skillCount --
					}
				} else {
					if (this.skillCount) {
						this.skillCount = 0;
					}
				}
				this.$emit('filter-on-change-hiddable');
				this.filter()
			},
			'form.location'(newVal, oldVal) {
				this.filters.location = newVal.country_code;
				this.filter();
			},

			//experience level watchers
			'form.experience_lvl.any': function(newVal, oldVal){
				let field_name = 'experience_lvl.any';
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'All experience lvl',
						type: 'checkbox'
					})
					this.form.experience_lvl.entry = false;
					this.form.experience_lvl.intermediate = false;
					this.form.experience_lvl.expert = false;
					delete this.filters.experience_lvl;
				} else {
					this.deleteTagByFieldName(field_name);
				}
				if (!(
					this.form.experience_lvl.entry ||
					this.form.experience_lvl.intermediate ||
					this.form.experience_lvl.expert
				)) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.experience_lvl.entry': function(newVal, oldVal){
				let field_name = 'experience_lvl.entry';
				let id = 1;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Entry lvl',
						type: 'checkbox'
					})
					this.form.experience_lvl.any = false;
					this.addToFilterArray(this.filters, 'experience_lvl', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('experience_lvl', id);
				}
				if (!this.form.experience_lvl.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.experience_lvl.intermediate': function(newVal, oldVal){
				let field_name = 'experience_lvl.intermediate';
				let id = 2;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Intermediate',
						type: 'checkbox'
					})
					this.form.experience_lvl.any = false;
					this.addToFilterArray(this.filters, 'experience_lvl', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('experience_lvl', id);
				}
				if (!this.form.experience_lvl.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.experience_lvl.expert': function(newVal, oldVal){
				let field_name = 'experience_lvl.expert';
				let id = 3;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Expert',
						type: 'checkbox'
					})
					this.form.experience_lvl.any = false;
					this.addToFilterArray(this.filters, 'experience_lvl', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('experience_lvl', id);
				}
				if (!this.form.experience_lvl.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},

			//client rating watchers
			'form.client_rating.any': function(newVal, oldVal){
				let field_name = 'client_rating.any';
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Any job',
						type: 'checkbox'
					})
					this.form.client_rating.more_80 = false;
					this.form.client_rating.more_90 = false;
					delete this.filters.client_rating;
				} else {
					this.deleteTagByFieldName(field_name);
				}
				if (!(
					this.form.client_rating.more_80 ||
					this.form.client_rating.more_90
				)) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.client_rating.more_80': function(newVal, oldVal){
				let field_name = 'client_rating.more_80';
				let id = 1;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'More 80',
						type: 'checkbox'
					})
					this.form.client_rating.any = false;
					this.addToFilterArray(this.filters, 'client_rating', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('client_rating', id);
				}
				if (!this.form.client_rating.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.client_rating.more_90': function(newVal, oldVal){
				let field_name = 'client_rating.more_90';
				let id = 2;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'More 90',
						type: 'checkbox'
					})
					this.form.client_rating.any = false;
					this.addToFilterArray(this.filters, 'client_rating', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('client_rating', id);
				}
				if (!this.form.client_rating.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},

			//freelancer type watchers
			'form.freelancer_type.any': function(newVal, oldVal){
				let field_name = 'freelancer_type.any';
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'All types',
						type: 'checkbox'
					})
					this.form.freelancer_type.independent = false;
					this.form.freelancer_type.agency = false;
					delete this.filters.freelancer_type;
				} else {
					this.deleteTagByFieldName(field_name);
				}
				if (!(
					this.form.freelancer_type.independent ||
					this.form.freelancer_type.agency
				)) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.freelancer_type.independent': function(newVal, oldVal){
				let field_name = 'freelancer_type.independent';
				let id = 1;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Independent freelancer',
						type: 'checkbox'
					})
					this.form.freelancer_type.any = false;
					this.addToFilterArray(this.filters, 'freelancer_type', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('freelancer_type', id);
				}
				if (!this.form.freelancer_type.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.freelancer_type.agency': function(newVal, oldVal){
				let field_name = 'freelancer_type.agency';
				let id = 2;
				if(newVal == true) {
					this.tags.push({
						field_name: field_name,
						label: 'Agency',
						type: 'checkbox'
					})
					this.form.freelancer_type.any = false;
					this.addToFilterArray(this.filters, 'freelancer_type', id);
				} else {
					this.deleteTagByFieldName(field_name);
					this.clearFilterCheckboxById('freelancer_type', id);
				}
				if (!this.form.freelancer_type.any) {
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.hourly_rate': function(newVal, oldVal){
				//if filters shown and user dont disable this element in settings
				if (this.show && this.showFilterElement('hourly_rate')) {
					this.filters.hourly_rate = newVal;
					this.$emit('filter-on-change-hiddable');
				}
			},
			'form.budget': function(newVal, oldVal){
				if (this.show && this.showFilterElement('budget')) {
					this.filters.budget = newVal;
					this.$emit('filter-on-change-hiddable');
				}
			},
		},

		computed: {
			...mapState({
				skills: state => state.skills.skills,
				categories: state => state.categories.categories,
				locations: state => state.locations.locations,
			}),
			hourlyRateMetaMin() {
				return (this.meta && this.meta.hourly_rate) ? +this.meta.hourly_rate[0] : this.getDefaultSliderValues()[0];
			},
			hourlyRateMetaMax() {
				return (this.meta && this.meta.hourly_rate) ? +this.meta.hourly_rate[1] : this.getDefaultSliderValues()[1];
			},
			budgetMetaMin() {
				return (this.meta && this.meta.budget) ? +this.meta.budget[0] : this.getDefaultSliderValues()[0];
			},
			budgetMetaMax() {
				return (this.meta && this.meta.budget) ? +this.meta.budget[1] : this.getDefaultSliderValues()[1];
			},
		},

		mounted() {
			
		},

		methods: {
			showFilterElement(filter_name) {
				return !(this.settings && this.settings[filter_name] && this.settings[filter_name].hide);
			},
			getFilterTitle(filter_name, default_title) {
				let title = (this.settings && this.settings[filter_name]) ? this.settings[filter_name].title : null;
				return title ? title : default_title;
			},
			checkUnik(array, item) {
				for (var i = 0; i < array.length; i++) {
					if(array[i].id == item.id) {
						return false
					}
				}
			return true
			},
			getClearFormObj() {
				return {
					search: null,
					categories: [],
					skills: [],
					location: null,
					experience_lvl: {
						any: false,
						entry: false,
						intermediate: false,
						expert: false,
					},
					client_rating: {
						any: false,
						more_80: false,
						more_90: false,
					},
					freelancer_type: {
						any: false,
						independent: false,
						agency: false,
					},
					hourly_rate: this.getDefaultSliderValues(),
					budget: this.getDefaultSliderValues(),
				}
		},
			clearAllTags() {
				this.send_request_perm = false;
				for (let index in this.tags) {
					this.removeTag(this.tags[index], index);
				}
			},
			clearAll() {
				this.send_request_perm = false;
				this.form = this.getClearFormObj();
				Vue.set(this, 'filters', {});

				this.tags = []
				this.skillCount = 0
				this.categoryCount = 0
				this.$emit('clean-location');
				this.$emit('clean-props');
			},
			removeTag(tag, index) {
				if(tag.type == 'checkbox') {
					this.set(this.form, tag.field_name, false);
				} else {
					this.tags.splice(index, 1)
					if(tag.type == 'skill') {
						this.form.skills.splice([this.form.skills.findIndex(x => x.name === tag.label)], 1);
						this.filters.skills.splice([this.filters.skills.indexOf(tag.id)], 1);
						if (!this.filters.skills.length) {
							delete this.filters.skills;
						}
					} else {
						this.form.categories.splice([this.form.categories.findIndex(x => x.name === tag.label)], 1)
						this.filters.categories.splice([this.filters.categories.indexOf(tag.id)], 1);
						if (!this.filters.categories.length) {
							delete this.filters.categories;
						}
					}
				}
			},
			filter() {
				if(!this.headerType) {
					delete this.form.type
				}

				let filters = this.filters;

				/*filter on the last filter element change change in a some milliseconds(need when clear many filter elements
				 in one time and each their watchers call this function many times but we need send filter request
				 only if all filter elements will be cleared. Or when slider change filter values very fast and it generates
				 requests but we need one when user stop slide)
				*/
				if (this.delay_filter_timer) clearTimeout(this.delay_filter_timer);

				this.delay_filter_timer = setTimeout(() => {
					this.$emit('get-filtered', filters);
				}, 500);
			},
			toggleHiddablePanel() {
				Vue.set(this, 'show', !this.show);
			},
			sortByType(filter) {
				this.activeFilter = filter
				this.form.type = filter
			},
			addToFilterArray(filter, array, value) {
				if (filter[array] === undefined) {
					filter[array] = [];
				}
				if (filter[array].indexOf(value) < 0) {
					filter[array].push(value);
				}
			},
			getNestedFieldsFromStr(str) {
				return str.split('.');
			},
			set(obj, path, value) {
		    var schema = obj;  // a moving reference to internal objects within obj
		    var pList = path.split('.');
		    var len = pList.length;
		    for(var i = 0; i < len-1; i++) {
		        var elem = pList[i];
		        if( !schema[elem] ) schema[elem] = {}
		        schema = schema[elem];
		    }

		    schema[pList[len-1]] = value;
			},
			deleteTagByFieldName(field_name) {
				if (this.tags.length) {
					this.tags.splice([this.tags.findIndex(x => x.field_name && (x.field_name === field_name))], 1);
				}
			},
			clearFilterCheckboxById(obj_name, id) {
				if(this.filters[obj_name] !== undefined) {
					this.filters[obj_name].splice([this.filters[obj_name].indexOf(id)], 1);
					if(!this.filters[obj_name].length) {
						delete this.filters[obj_name];
					}
				}
			},
			getDefaultSliderValues() {
				return [360, 720];
			},
			getMetaInfo(info, index) {
				let result = '';
				if (info) {
					result = `(${info[index]})`;
				}

				return result;
			},
			pushToFilter(type, instance) {
				let filter = this.form[type];
				if (filter.findIndex(x => x.id == instance.id) < 0) {
					filter.push(instance);
				}
			},

			//events handlers
			onFilterOnChangeHiddable() {
				if (this.show && this.send_request_perm) {
					this.filter();
				}
			}
		},

		created() {
			this.$on('filter-on-change-hiddable', () => this.onFilterOnChangeHiddable());
		}

	}
</script>