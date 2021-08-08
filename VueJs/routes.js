import VueRouter from 'vue-router';

//middlewares
let authMiddleware = async function(to, from, next) {
	window.auth.checkAuthUser().then(
		success => {
			console.log('success user check success111');
			next();			
		},
		error => {

			$('#loginModal').modal('show')
			console.error("user check error111");
		});
}

let routes = [
{
	path: '/',
	component: require('./components/partials/home/Home.vue'),
	name: 'home',
	meta: {
		platformname: 'main',
		breadcrumb: [{name: 'home'}]
	}
},
	// projects
	{
		path: '/projects',
		component: require('./components/partials/projects/Projects.vue'),
		name: 'projects',
		meta: {
			platformname: 'main',
			breadcrumb: [
				{name: 'home', link: 'home'},
				{name: 'projects'}
			]
		}
	},
	{
		path: '/projects/:id',
		component: require('./components/partials/projects/Project.vue'),
		name: 'project',
		meta: {
			platformname: 'main',
			breadcrumb: [
				{name: 'home', link: 'home'},
				{name: 'projects', link: 'projects'}
			]
		}
	},
	// users
	{
		path: '/users',
		component: require('./components/partials/users/Users.vue'),
		name: 'users',
		meta: {
			platformname: 'main',
			breadcrumb: [
				{name: 'home', link: 'home'},
				{name: 'users'}
			]
		}
	},
	{
		path: '/users/:id',
		component: require('./components/partials/users/User.vue'),
		name: 'user',
		meta: {
			platformname: 'main',
			breadcrumb: [
				{name: 'home', link: 'home'},
				{name: 'users', link: 'users'}
			]
		}
	},
	// Profile
	{
		path: '/profile',
		component: require('./components/partials/profile/Main.vue'),
		name: 'profilemain',
		meta: {
			platformname: 'main',
		},
		beforeEnter: (to, from, next) => {
			authMiddleware(to, from, next);
		},
		children: [
		{ 
			path: '/',
			component: require('./components/partials/profile/Profile.vue'), 
			name: 'profileinfo',
			meta: {
				platformname: 'main'
			}
		},
		{
			path: 'create-project',
			component: require('./components/partials/projects/CreateProject.vue'),
			name: 'new-project',
			meta: {
				platformname: 'main'
			}
		},
		{
			path: 'create-project/:project_id',
			component: require('./components/partials/projects/CreateProject.vue'),
			name: 'same-project',
			meta: {
				platformname: 'main'
			}
		},
		{
			path: 'my-projects/:id',
			component: require('./components/partials/projects/CreateProject.vue'),
			name: 'view-project',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'my-projects',
			component: require('./components/partials/profile/MyProjects.vue'),
			name: 'my_projects',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'offerform/:project_id',
			component: require('./components/partials/employer/CreateOffer.vue'),
			name: 'offerform',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'projects/:project_id/offerform/:offer_id',
			component: require('./components/partials/employer/CreateOffer.vue'),
			name: 'view-offerform',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'reviews',
			component: require('./components/partials/profile/Reviews.vue'),
			name: 'reviews',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'chats',
			component: require('./components/partials/profile/Chat.vue'),
			name: 'profilechats',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'help',
			component: require('./components/partials/profile/Help.vue'),
			name: 'profilehelp',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'settings',
			component: require('./components/partials/profile/Settings.vue'),
			name: 'settings',
			meta: {
				platformname: 'main'
			}
		},
		{ 
			path: 'offers/:start_offer/milestone/:project_id',
			component: require('./components/partials/freelancer/FormBid.vue'),
			name: 'milestone',
			meta: {
				platformname: 'main'
			}
		},
		{
			path: 'projects/:project_id/formbid',
			component: require('./components/partials/freelancer/FormBid.vue'),
			name: 'formbid',
			meta: {
				platformname: 'main'
			}
		},
		]
	},
	// Tribunal
	{
		path: '/tribunal',
		component: require('./components/partials/tribunal/Main.vue'),
		name: 'tribunal',
		meta: {
			platformname: 'tribunal'
		},
		beforeEnter: (to, from, next) => {
			authMiddleware(to, from, next);
		},
		children: [
			{
				path: 'disputes',
				component: require('./components/partials/projects/Projects.vue'),
				name: 'disputes',
				meta: {
					platformname: 'tribunal'
				}
			},
			{
				path: 'my-disputes',
				component: require('./components/partials/profile/MyProjects.vue'),
				name: 'my-disputes',
				meta: {
					platformname: 'tribunal'
				}
			},
			{
				path: 'disputes/:id',
				component: require('./components/partials/projects/Project.vue'),
				name: 'dispute',
				meta: {
					platformname: 'tribunal',
					breadcrumb: [
						{name: 'disputes', link: 'disputes'}
					]
				}
			},
			{ 
				path: 'chats',
				component: require('./components/partials/profile/Chat.vue'),
				name: 'tribunalchats',
				meta: {
					platformname: 'tribunal'
				}
			},
			{
				path: 'help',
				component: require('./components/partials/profile/Help.vue'),
				name: 'tribunalhelp',
				meta: {
					platformname: 'tribunal'
				}
			},
			{
				path: 'settings',
				component: require('./components/partials/profile/Settings.vue'),
				name: 'tribunalsettings',
				meta: {
					platformname: 'tribunal'
				}
			},
		]
	},
	// Task
	{
		path: '/task',
		component: require('./components/partials/task/Main.vue'),
		name: 'task',
		meta: {
			platformname: 'task'
		},
		beforeEnter: (to, from, next) => {
			authMiddleware(to, from, next);
		},
		children: [
			{
				path: 'my-tasks',
				component: require('./components/partials/profile/MyProjects.vue'),
				name: 'my-tasks',
				meta: {
					platformname: 'task'
				}
			},
			{ 
				path: 'chats',
				component: require('./components/partials/profile/Chat.vue'),
				name: 'taskchats',
				meta: {
					platformname: 'task'
				}
			},
			{ 
				path: 'help',
				component: require('./components/partials/profile/Help.vue'),
				name: 'taskhelp',
				meta: {
					platformname: 'task'
				}
			},
			{ 
				path: 'settings',
				component: require('./components/partials/profile/Settings.vue'),
				name: 'tasksettings',
				meta: {
					platformname: 'task'
				}
			},
		]
	},
	// Wallet
	{
		path: '/wallet',
		component: require('./components/partials/wallet/Main.vue'),
		name: 'wallet',
		meta: {
			platformname: 'wallet'
		},
		beforeEnter: (to, from, next) => {
			authMiddleware(to, from, next);
		},
		children: [
			{ 
				path: 'help',
				component: require('./components/partials/profile/Help.vue'),
				name: 'wallethelp',
				meta: {
					platformname: 'wallet'
				}
			},
			{ 
				path: 'settings',
				component: require('./components/partials/profile/Settings.vue'),
				name: 'walletsettings',
				meta: {
					platformname: 'wallet'
				}
			},
		]
	},
	// terms
	{
		path: '/terms',
		component: require('./components/partials/Terms.vue'),
		name: 'terms',
		meta: {
			platformname: 'main'
		}
	},
	// faq
	{
		path: '/faq',
		component: require('./components/partials/Faq.vue'),
		name: 'faq',
		meta: {
			platformname: 'main',
			breadcrumb: [
				{name: 'home', link: 'home'},
				{name: 'FAQ'}
			]
		}
	},
	];



	let router = new VueRouter ({
		routes,
		mode: 'history',
		linkActiveClass: 'active'
	});


router.afterEach((to, from) => {
	window.scroll({ top: 0, left: 0, behavior: 'smooth' })
});




export default router;