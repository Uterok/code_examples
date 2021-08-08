import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import Vuelidate from 'vuelidate';
import VueSwal from 'vue-swal';

import router from './routes';
import store from './store/index';
import 'es6-promise/auto';

window.Vue = Vue;
window.Bus = new Vue;

Vue.use(VueRouter);
Vue.use(Vuelidate);
Vue.use(VueSwal);

require('./bootstrap');

// Layouts
Vue.component('platform-header', require('./components/layouts/Header.vue'));
Vue.component('platform-footer', require('./components/layouts/Footer.vue'));

// Loader
Vue.component('clip-loader', require('vue-spinner/src/ClipLoader.vue'));

//set authorization object
import {AppAuthentication} from './authUser.js';
window.auth = new AppAuthentication(  axios,
{
  register_url: '/api/v1/register',
  login_url: '/api/v1/login',
  refresh_url: '/api/v1/refresh',
  check_is_user_auth_url: '/api/v1/isuserauth',
  logout_url : '/api/v1/logout'
},
null,
window.Bus);
Vue.prototype.$auth = window.auth;

// Pages

// Modals
Vue.component('login-modal', require('./components/modals/Login.vue'));
Vue.component('register-modal', require('./components/modals/Register.vue'));
Vue.component('contact', require('./components/modals/Contact.vue'));
Vue.component('notification', require('./components/modals/Notification.vue'));


import { mapState, mapActions, mapMutations } from 'vuex'

const app = new Vue({
	el: '#app',
	router,
	store,

  data() {
    return {
      color: '#ff8787',
      breadcrumbList: [],
      platforms: [
      {
        name: 'main',
        color: '#ff8787'
      },
      {
        name: 'tribunal',
        color: '#CC3333'
      },
      {
        name: 'task',
        color: '#336699'
      },
      {
        name: 'wallet',
        color: '#8C7DD1'
      }
      ],
      notAuthRoutes: ['home', 'projects', 'project', 'users', 'user', 'terms', 'faq']
    }
  },

  methods: {
    ...mapActions({
      getUsers: 'user/getUsers',
      getAuth: 'user/getAuthUser',
      getProjects: 'projects/getProjects',
      getCategories: 'categories/getCategories',
      getSkills: 'skills/getSkills',
      getPayTypes: 'paytypes/getPayTypes',
      getExpLevels: 'experience_lvls/getExpLevels',
      getCurrencies: 'currencies/getCurrencies',
      getCategoriesOfQuestions: 'categoriesOfQuestions/getCategoriesOfQuestions',
      getLocations: 'locations/getLocations',
      getCountries: 'countries/getCountries',
      getDurations: 'durations/getDurations',
    }),
    ...mapMutations({
      logoutAuth: 'user/logout',
    }),
    RouteTo(index) {
      if(this.breadcrumbList[index].link) {
        this.$router.push({name: this.breadcrumbList[index].name})
      }
    },
    scrollToTop() {
      window.scroll({ top: 0, left: 0, behavior: 'smooth' })
    },
    setAuthToken(access_token) {
      window.Echo.connector.options.auth.headers['Authorization'] = access_token;
    },
    //set unchanged urls which will be used in components
    setUrls() {
      //set default api prefix
      this.$root.default_api_prefix = `/api/v${document.head.querySelector('meta[name="api"]').content}`;
      this.$root.files_upload_url = `${this.$root.default_api_prefix}/files`;
    },
    setRolesInfo() {
      this.$root.role_admin_id = 1;
      this.$root.role_investor_id = 2;
      this.$root.role_freelancer_id = 3;
      this.$root.role_employer_id = 4;
      this.$root.role_agency_id = 5;

      this.$root.role_admin_name = 'admin';
      this.$root.role_investor_name = 'investor';
      this.$root.role_freelancer_name = 'freelancer';
      this.$root.role_employer_name = 'employer';
      this.$root.role_agency_name = 'agency';
    },
    setRoleCheckMethods() {
      this.$root.isUserAdmin = (user) => {
        return user && (user.role_name == this.$root.role_admin_id);
      };
      this.$root.isUserFreelancer = (user) => {
        return user && (user.role_name == this.$root.role_freelancer_id);
      };
      this.$root.isUserEmployer = (user) => {
        return user && (user.role_name == this.$root.role_employer_id);
      };
      this.$root.isUserAgency = (user) => {
        return user && (user.role_name == this.$root.role_agency_id);
      };
    },
    setProfileInfoMethods() {
      this.$root.userImage = (user) => {
        if(user.role_name == 3) {
          return (user.profile && user.profile.image) ? user.profile.image : '/images/platform/icons/freelancer_avatar.svg';
        } else {
          return (user.profile && user.profile.image) ? user.profile.image : '/images/platform/icons/employer_avatar.svg';
        }
      };
    },
    checkPlatform() {
      for (var i = 0; i < this.platforms.length; i++) {
        if(this.platforms[i].name == this.$route.meta.platformname) {
          $('body').addClass(this.platforms[i].name)
          this.color = this.platforms[i].color
        } else {
          $('body').removeClass(this.platforms[i].name)
        }
      }
    }
  },

  watch: {
    '$route'() {
      this.breadcrumbList = this.$route.meta.breadcrumb
      this.checkPlatform()
    }
  },

  mounted() {
  },

  created() {
    this.getUsers()
    this.getSkills()
    this.getPayTypes()
    this.getExpLevels()
    this.getCurrencies()
    this.getCategories()
    this.getProjects()
    this.getCategoriesOfQuestions()
    this.getLocations()
    this.getCountries()
    this.getDurations()
    this.getAuth()

    //set logout callback
    this.$root.logout = () => {
      let home = true
      for (var i = 0; i < this.notAuthRoutes.length; i++) {
        if(this.$route.name == this.notAuthRoutes[i]) {
          home = false
        }
      }
      if(home) {
        this.$router.push({name: 'home'});
      }
      this.logoutAuth()
    };

    this.breadcrumbList = this.$route.meta.breadcrumb

    this.checkPlatform();

    this.setUrls();
    this.setRoleCheckMethods();
    this.setProfileInfoMethods();
    this.setRolesInfo();

    //add logout callback to auth
    this.$auth.setLogoutCallback(this.$root.logout);

    //set auth token(for laravel echo)
    this.setAuthToken(axios.defaults.headers.common['Authorization']);
    //on authentication token changed change token in laravel Echo
    window.Bus.$on('access-token-changed', (access_token) => this.setAuthToken(`Bearer ${access_token}`));
  }


});

window.auth.setApp(app);
