'use strict'

const ScrapHandler = require('../ScrapHandler')

//models
const TheVendryVenue = use('App/Models/TheVendryVenue')

const scrapper_id = 'thevendry'
const DEFAULT_COUNTRY_TO_SCRAP = 'US'

const website_root_url = 'https://www.thevendry.co'
const search_api_root_url = 'https://www.thevendry.co/api/search'

const AVAILABLE_COUNTRIES = ['US']

class ScrapTheVendryHandler extends ScrapHandler {
	constructor(
		type,
		country_to_scrap,
		adonis_console
	) {
		super({
			type,
			scrapper_id,
			available_countries: (typeof AVAILABLE_COUNTRIES != 'undefined') ? AVAILABLE_COUNTRIES : null,
			country_to_scrap: country_to_scrap ? country_to_scrap : DEFAULT_COUNTRY_TO_SCRAP,
			adonis_console,
			venue_model: TheVendryVenue,
		});

		return this;
	}

	async processVenuesList() {
	    const url = 'venues';

	    this.scrapped_count = 0;

	    await this.processVenuesListPageData(
		  search_api_root_url, 
		  url, 
		  async (returned_content) => await this.parsePaginatedPageData(returned_content),
		  {
		    get_info_type: ScrapHandler.GET_INFO_TYPE_API,
		    get_info_options: {
		      request_options: {
		        method: 'get',
		        params: {
		          location: 'usa',
		          perPage: 500
		        }
		      }
		    }
		  }
		)
	}

	async processSingleVenuesFromLoadedList() {

		console.log('in processSingleVenuesFromLoadedList')

		await this.processLoadedVenuesLinks(
	    	async (...params) => await this.parseSingleVenueData(...params),
			{
				get_info_type: ScrapHandler.GET_INFO_TYPE_HTML,
				get_info_options: {
		            page_custom_handle_callback: async (nightmare) => {

		            	await nightmare
		            	.exists('div.events__wrapper div.events div.list__wrapper button.btn')
		            	.then(function (result) {
					        if (result) {
					        	console.log("Exists 1")
					            nightmare.click('div.events__wrapper div.events div.list__wrapper button.btn')
					            		 .wait('div.events__wrapper div.events div.list__wrapper button.btn.btn--hide');
					            // return nightmare;
					        } else {
					            console.log("Could not find selector 1")
					        }
					    })

					    await nightmare
		            	.exists('div.venue-content__wrapper div.container section.pro-collab div.list__wrapper button.btn')
		            	.then(function (result) {
					        if (result) {
					        	console.log("Exists 2")
					            nightmare.click('div.venue-content__wrapper div.container section.pro-collab div.list__wrapper button.btn')
					            		 .wait('div.venue-content__wrapper div.container section.pro-collab div.list__wrapper button.btn.btn--hide');
					        } else {
					            console.log("Could not find selector 2")
					        }
					    })
		            }
  				},

  				force_scrap_existed_callback: async (existed_venue) => {
  					return !existed_venue || !existed_venue.single_scrapped;
  				},
			}
		)
	}

	async parsePaginatedPageData(returned_content) {

	  	let curr_page = +(returned_content && returned_content.data && returned_content.data.pagination && returned_content.data.pagination.current)
	    let per_page = +(returned_content && returned_content.data && returned_content.data.pagination && returned_content.data.pagination.perPage)
	    let elems_count = +(returned_content && returned_content.data && returned_content.data.pagination && returned_content.data.pagination.count)
	    let total_pages = +(returned_content && returned_content.data && returned_content.data.pagination && returned_content.data.pagination.total)

	    this.scrapped_count += per_page;

	    const is_last_page = (curr_page >= total_pages)

	    const response = {
	  		next_page_link: !is_last_page ? 'venues' : null,
			get_info_options: {
				request_options: {
					method: 'get',
					params: {
						location: 'usa',
						perPage: 500,
						page: curr_page + 1
					}
				}
			},
			venues_list_info: []
	  	}

	    const venues_list = returned_content && returned_content.data && returned_content.data.businesses

	    if (Array.isArray(venues_list)) {
	    	for (const venue_info of venues_list) {
	    		if (!venue_info) {
	    			continue
	    		}

	    		const venue_location = venue_info.locations[0]

	    		const invite_token = venue_info.inviteToken
	    		const state = venue_location && venue_location.state
	    		const city = venue_location && venue_location.city
	    		let link_venue_name = `${invite_token.replace(/-[0-9]+$/, '')}-${city.toLowerCase().replace(' ', '-')}-${state.toLowerCase()}`

	    		response.venues_list_info.push({
	    			venue_id: venue_info.id,
	    			venue_link: `${website_root_url}/venue/${venue_info.id}/${link_venue_name}`,
	    			venue_description: null
	    		})

	    		let spaces_info = null

	    		if (Array.isArray(venue_info.spaces)) {
	    			spaces_info = []
	    			for (const single_space of venue_info.spaces) {
	    				if (!single_space) {
	    					continue
	    				}

	    				let space_images = null
	    				if (Array.isArray(single_space.photos)) {
	    					space_images = []
	    					for (const photo_info of single_space.photos) {
	    						if (!photo_info || !photo_info.sizes) {
	    							continue
	    						}
								space_images.push(photo_info.sizes)
	    					}
	    				}

		    			spaces_info.push({
		    				space_id: single_space.id,
		    				space_name: single_space.name,
		    				seated_capacity: single_space.seatingCapacity,
		    				standing_capacity: single_space.standingCapacity,
		    				space_images,
		    			})
		    		}
	    		}
	    		

	    		//save info into database
	    		const venue_info_save_to_db = {
	    			country: this.country_to_scrap,
	    			venue_id: venue_info.id,
	    			venue_name: venue_info.name,
	    			venue_address: venue_location && venue_location.name,
	    			venue_type: venue_info && venue_info.venue && venue_info.venue.venueType && venue_info.venue.venueType.name,
	    			website_url: venue_info.web,
	    			venue_description: venue_info.about,
	    			vibe: venue_info && venue_info.venue && venue_info.venue.vibe,
	    			spaces_info
	    		};

		     	console.log(venue_info_save_to_db.country, venue_info_save_to_db.venue_id)

	    		let venue_model = await TheVendryVenue
						      	.query()
						      	.where('country', '=', venue_info_save_to_db.country)
						      	.where('venue_id', '=', venue_info_save_to_db.venue_id)
						      	.first()

				if (venue_model) {
					venue_model.merge(venue_info_save_to_db)
					await venue_model.save()
				} else {
					await TheVendryVenue.create(venue_info_save_to_db)
				}
	    	}
	    }

	  	console.log(`get ${venues_list.length}(${this.scrapped_count}) links of ${per_page}(${elems_count})`)

	  	return response
	}

	async parseSingleVenueData(returned_content, venue_scrap_info, existed_venue) {
		const $ = returned_content;

		const header = $('div.header__title h1').text()

		const venue_image = $('div.page__container div.venue-page__wrapper div.overview__wrapper div.overview img')
								.attr('src')

		const venue_amenities = [];
		let correct_section = this.jquery_parser.findInArray(
			$,
			'div.about__wrapper div.about__info section',
			'h5:contains("Other Details")'
		);

		if (correct_section) {
			correct_section
			.find('div.txt div.check-item__container div.check-item')
			.each((i, elem) => {
				venue_amenities.push($(elem).find('span').text())
	      	})
		}

		const getEventsContainer = () => {
			return $(`div.events__wrapper`)
		}

		const suitable_for = [];
		if (getEventsContainer().length) {
			getEventsContainer()
				.find('div.events div.list__wrapper div.events__container div.event__wrapper')
				.each((i, elem) => {
					suitable_for.push($(elem).find('p.event__type').text())  
		      	})
		}

		const collaborators = {};

		$('div.venue-content__wrapper div.container section.pro-collab')
			.each((i, elem) => {
				const header = $(elem).find('h5').text()
				const collab_single = [];

				$(elem)
					.find('div.list__wrapper div.collab-list div.collab__wrapper')
					.each((i, inner_elem) => {
						collab_single.push({
							name: $(inner_elem).find('div.collab__container h6.collab__name').text(),
							preffered: !!$(inner_elem).find('div.collab__container div.collab__extra p.collab__preferred').length
						})
						
			      	})

			    collaborators[header] = collab_single 
	      	})

	    // return spaces info
	    const existed_venue_spaces_info = existed_venue && existed_venue.toJSON().spaces_info;
	    console.log('SPACES COUNT - ', existed_venue_spaces_info.length)
    	if (existed_venue_spaces_info && Array.isArray(existed_venue_spaces_info)) {
	    	for (const existed_space of existed_venue_spaces_info) {
	    		const space_link = `${venue_scrap_info.venue_link}/space/${existed_space.space_id}`
	    		console.log('SPACE - ', space_link)
	    		const space$ = await this.getHtmlContentFromUrl(
			      	space_link,
			      	{
						headless: false,
						show_dev_tool: true,
					}
		      	)

	    		// get fb options
		      	let fb_options

	    		let fb_section = this.jquery_parser.findInArray(
					space$,
					'div.space-page__left div.about__wrapper section',
					'h5:contains("F&B Options")'
				);

				if (fb_section) {
					fb_options = fb_section
					.find('div')
					.text()
				}

				// get space equipment
				let space_equipment = [];

				let eq_section = this.jquery_parser.findInArray(
					space$,
					'div.space-page__left div.about__wrapper section.amenities div.accordion',
					'div.accordion__header h5:contains("Equipment")'
				);

				if (eq_section) {
					eq_section
					.find('div.accordion__content ul li')
					.each((i, elem) => {
						space_equipment.push($(elem).find('div.check-item span:not(.question)').first().text())
			      	})
				}

				// get space features
				let space_features = [];

				let features_section = this.jquery_parser.findInArray(
					space$,
					'div.space-page__left div.about__wrapper section.amenities div.accordion',
					'div.accordion__header h5:contains("Features")'
				);

				if (features_section) {
					features_section
					.find('div.accordion__content ul li')
					.each((i, elem) => {
						space_features.push($(elem).find('div.check-item span:not(.question)').first().text())
			      	})
				}

				// get space frequent users
				let frequent_users = [];

				let fr_user_section = this.jquery_parser.findInArray(
					space$,
					'div.space-page__left div.about__wrapper section.amenities div.accordion',
					'div.accordion__header h5:contains("Frequent Uses")'
				);

				if (fr_user_section) {
					fr_user_section
					.find('div.accordion__content ul li')
					.each((i, elem) => {
						frequent_users.push($(elem).find('div.check-item span:not(.question)').first().text())
			      	})
				}

				existed_space.fb_options = fb_options;
				existed_space.space_equipment = space_equipment;
				existed_space.space_features = space_features;
				existed_space.frequent_users = frequent_users;
	    	}
	    }

	    const venue_to_return = {
			venue_images: venue_image ? [venue_image] : [],
			venue_amenities,
			suitable_for,
			collaborators,
			spaces_info: existed_venue_spaces_info,
			single_scrapped: true,
		}

		return venue_to_return
	}

	async processSavedVenuesExport() {

		await this.processExportSavedScrappedInfo(
			async (venue_to_handle, venue_scrap_info_item, headers_info) => {
				const h_info_venues = headers_info[ScrapHandler.DEFAULT_CSV_EXPORT_HEADERS_TYPE];
				const h_info_spaces = headers_info['spaces'];

				const venue_response = {};
				const spaces_response = [];

				for (const v_key in h_info_venues) {
					if (v_key.match(/collab/)) {
						if (
							venue_to_handle.collaborators &&
							(typeof venue_to_handle.collaborators == 'object') && 
							Object.keys(venue_to_handle.collaborators).length
						) {
							const collab_key = h_info_venues[v_key];
							venue_response[v_key] = '';
							const collab_info = venue_to_handle.collaborators[collab_key];
							if (Array.isArray(collab_info)) {
								venue_response[v_key] = collab_info.reduce(
									(acum, curr) => {
										const name = (typeof curr.name == 'string') ? curr.name.trim() : '';
										let str_to_show = `${name}${curr.preffered ? '(preffered)' : ''}`;
										return `${acum}${acum ? ',' : ''}${str_to_show}`
									},
									''
								)
							}
						}
					} else {
						venue_response[v_key] = venue_to_handle[v_key];
					}
				}
				venue_response.venue_id = venue_to_handle.venue_id;

				const spaces_info = venue_to_handle.spaces_info;

				if (Array.isArray(spaces_info)) {
					for (const space_info of spaces_info) {
						const space_info_to_export = {};
						for (const v_key in h_info_spaces) {
							space_info_to_export[v_key] = space_info[v_key];
						}
						space_info_to_export.venue_id = venue_to_handle.venue_id;
						spaces_response.push(space_info_to_export);
					}
				}

				const info_to_return = {
					[ScrapHandler.CSV_EXPORT_SAVER_NAME_DEFAULT]: venue_response,
					'spaces' : spaces_response
				}

				return info_to_return;
			},
			{
				multiple_additional_info: true
			}
		)
	}

	getVenueId(venue_item) {
		return venue_item.venue_id;
	}

}


module.exports = ScrapTheVendryHandler