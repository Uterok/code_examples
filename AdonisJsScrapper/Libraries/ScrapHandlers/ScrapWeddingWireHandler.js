'use strict'

const ScrapHandler = require('../ScrapHandler')

//models
const WeddingWireVenue = use('App/Models/WeddingWireVenue')

const scrapper_id = 'weddingwire'
const DEFAULT_COUNTRY_TO_SCRAP = 'US'

const website_root_url = {
	'US' : 'https://www.weddingwire.com',
	'CA' : 'https://www.weddingwire.ca/',
}

const AVAILABLE_COUNTRIES = ['US', 'CA']

class ScrapWeddingWireHandler extends ScrapHandler {
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
			venue_model: WeddingWireVenue,
			venues_ids_unique_all_langs: false,
			chunk_single_scrapping_count: 510,
		});

		this.page_parts = {};

		return this;
	}

	async processVenuesList() {
	    this.current_city_link;
	    this.scrapped_count;

	    const cities_list = this.getScrappingVenuesList()

	    for (const city_link of cities_list) {
	      	console.log(city_link)
	      	this.current_city_link = city_link
	        // reset scrapped count on each link to show right statistic on each pagonatable request
	        this.scrapped_count = 0;
	      	// continue
	      	await this.processVenuesListPageData(
	  			website_root_url[this.country_to_scrap], 
	  			city_link, 
	  			async (parsed_html) => await this.parsePaginatedPageData(parsed_html),
	  			{
	  				get_info_type: ScrapHandler.GET_INFO_TYPE_HTML,
	  				get_info_options: {
	  					wait_for_selector: 'div.directory-view-mode-container',
	  				},
	  				full_next_page_link: true,
	  			}
	  		)
	    }
	}

	async processSingleVenuesFromLoadedList() {

		console.log('in processSingleVenuesFromLoadedList')

		await this.processLoadedVenuesLinks(
	    	async (...params) => await this.parseSingleVenueData(...params),
			{
				get_info_type: ScrapHandler.GET_INFO_TYPE_HTML,
				get_info_options: {
  					wait_for_selector: 'h1.storefrontHeader__title',
  				},
  				force_scrap_existed_callback: async (existed_venue) => {
  					return !existed_venue || !existed_venue.single_scrapped;
  				},
			}
		)
	}

	async parsePaginatedPageData(returned_content) {
	  	const $ = returned_content;

	  	const getPaginationPanel = function() {
	  		return $(`ul.pagination`)
	  	}

	  	const getVenuesList = function() {
	  		return $(`div.app-ec-list.app-internal-tracking-page.gtm-impression-list div[data-track-l="s-list"]`)
	  	}

	  	let per_page = getVenuesList().length							  
	    let elems_count = +($('div.directory-view-mode-container span')
	    				  .first()
	    				  .text()
	    				  .replace(/[^0-9]+/g, ''))

	    this.scrapped_count += per_page;

	    const curr_page = +(getPaginationPanel()
	  							  .find('span.pagination__page.pagination__page--active')
	  							  .text())
	  	console.log('curr page - ', curr_page);
	  	console.log(curr_page, per_page, elems_count);

	  	const is_last_page = !getPaginationPanel()
	  							  .find('a.pagination__page.pagination__next')
	  							  .length

	  	const next_link = getPaginationPanel()
	  							  .find('a.pagination__page.pagination__next')
	  							  .attr('href')
	  	console.log('next link - ', next_link, is_last_page)
	  	const response = {
	  		next_page_link: !is_last_page ? next_link : null,
			venues_list_info: []
	  	}

	  	const items_count_on_page = per_page;
	  	console.log('count on page - ', items_count_on_page)

	  	for (let i = 0; i < items_count_on_page; i++) {
	  		let single_page_link = getVenuesList()
	  								.eq(i)
	  								.find('div div.directory-item-content a.item-title')
	                               	.attr('href')

	        let venue_id = +(getVenuesList()
	                               .eq(i) 
	                               .attr('id')
	                               .replace(/[^0-9]+/g, ''))                

	        console.log('single - ', single_page_link, venue_id)

	    	response.venues_list_info.push({
				venue_id: +venue_id,
				venue_link: single_page_link,
				venue_description: null
			})
	  	}

	  	console.log(`get ${per_page}(${this.scrapped_count}) links of ${per_page}(${elems_count})`)

	  	return response
	}

	async parseSingleVenueData(returned_content, venue_scrap_info, existed_venue) {
		const $ = returned_content;
		// console.log(returned_content);

		const venue_name = $('h1.storefrontHeader__title').text();
		let venue_address = $('div.storefrontHeaderOnepage__address').html()

		venue_address = this.pretty(venue_address)
							.replace(/(\r\n|\n|\r)/gm," ")
							.replace(/<!-- \/?APP_SHOW_LOCATION_GEO -->/gm," ")
							.replace(/<.+>/gm," ")
		venue_address = (typeof venue_address == 'string') ? venue_address.trim() : null;

		const website_url = $('div.storefrontHeaderOnepage__address span.app-profile-info-website.storefrontHeaderOnepage__infoItem.pointer')
								.attr('data-url');

		let venue_capacity;
		if (this.country_to_scrap == DEFAULT_COUNTRY_TO_SCRAP) {
			venue_capacity = this.jquery_parser.findInArray(
									$,
									'span.storefrontSummary__text',
									'i.svgIcon__guests'
								);
			venue_capacity = venue_capacity ? venue_capacity.text() : null;
			venue_capacity = venue_capacity ? venue_capacity.trim() : null;
		} else {
			venue_capacity = this.jquery_parser.findInArray(
									$,
									'div.storefrontSummary__item',
									'i.svgIcon__guests'
								);

			venue_capacity = venue_capacity ? venue_capacity.html() : null;
			venue_capacity = (typeof venue_capacity == 'string') ? venue_capacity.replace(/<.*>/gm, '').replace(/ {2,}/gm, ' ').trim() : null;
		}

		let venue_pricing;

		if (this.country_to_scrap == DEFAULT_COUNTRY_TO_SCRAP) {
			venue_pricing = this.jquery_parser.findInArray(
									$,
									'span.storefrontSummary__text',
									'i.svgIcon__pricing'
								); 
			venue_pricing = venue_pricing ? venue_pricing.text() : null;
			venue_pricing = venue_pricing ? venue_pricing.trim() : null;
		} else {
			venue_pricing = this.jquery_parser.findInArray(
									$,
									'div.storefrontSummary__item',
									'i.svgIcon__pricing'
								); 

		 	venue_pricing = venue_pricing ? venue_pricing.html() : null;
			venue_pricing = (typeof venue_pricing == 'string') ? venue_pricing.replace(/<.*>/gm, '').replace(/ {2,}/gm, ' ').trim() : null;
		}
		

		let venue_description =	(this.country_to_scrap == DEFAULT_COUNTRY_TO_SCRAP) ?
		 						$('div.storefrontInfo__text.post.app-translation-container').html() :
		 						$('div.storefront__description.post').html()

		venue_description = venue_description ? this.pretty(venue_description) : null;

		const venue_images = [];

		$('div.app-slider-container.swiper-container.swiper-container-initialized.swiper-container-horizontal div.swiper-wrapper figure.swiper-slide')
			.each((i, elem) => {
				const img_src = $(elem).find('img').attr('src');
				if (typeof img_src == 'string') {
					venue_images.push(img_src)
				}
			})

		const venue_faq = {};
		

		$('div#faqs ul.storefront-faqs li')
			.each((i, elem) => {
				let question = $(elem).find('span').text()
				if (typeof question == 'string') {
					question = question.trim()
					venue_faq[question] = null;

					let is_single = $(elem).find('div.pure-u').length
					if (is_single) {
						let answer_single = $(elem).find('div.pure-u').text();
						if(typeof answer_single == 'string') {
							venue_faq[question] = answer_single
						}
					} else {
						venue_faq[question] = [];
						$(elem).find('div.pure-u-1-2.storefront-faqs__check')
							.each((i, inner_elem) => {
								let answer_elem = $(inner_elem).find('div').text();
								if(typeof answer_elem == 'string') {
									venue_faq[question].push(answer_elem)
								}
							})
					}
				}
			})

		const preffered_vendors = {};

		$('div.app-swiper-endorsement.swiper-container.storefrontEndorsementSlider.swiper-container-initialized.swiper-container-horizontal div.swiper-wrapper div.swiper-slide')
			.each((i, elem) => {
				let category = $(elem).find('span.storefrontEndorsement__categ').text()
				if (typeof category == 'string') {
					if (!preffered_vendors[category]) {
						preffered_vendors[category] = []
					}

					const link = $(elem).find('a.storefrontEndorsement__title').attr('href');
					let title = $(elem).find('a.storefrontEndorsement__title').text();
					title = title ? title.trim() : null;

					preffered_vendors[category].push({
						link,
						title
					});
				}
			})

	    const venue_to_return = {
			venue_name,
			venue_address,
			website_url,
			venue_capacity,
			venue_pricing,
			venue_description,
			venue_images,
			venue_faq,
			preffered_vendors,
			single_scrapped: true,
		}

		return venue_to_return
	}

	async getGooglePlaceInfo(location_request_href) {
		if (!location_request_href) {
			return null;
		}

	    const addr_str = this.getGooglePlaceAddressFromReqStr(location_request_href)

	    try {
		  const url = `https://maps.googleapis.com/maps/api/place/findplacefromtext/json`;

		  const params_to_req = {
	            key: this.google_api_key,
	            input: addr_str,
	            inputtype: 'textquery',
	            fields: 'formatted_address,place_id',
	            language:'en'
	          }

	    const get_locations_url = `${url}?key=${params_to_req.key}&input=${params_to_req.input}&inputtype=${params_to_req.inputtype}&fields=${params_to_req.fields}&language=${params_to_req.language}`;
	    console.log('get location address - ', params_to_req.input);

	      let response = (await this.axios.get(
	        get_locations_url,
	        {
	          params: params_to_req
	        }
	      )).data

	      if (response.status != 'OK') {
	        console.error('Error while request google place info on address try - ' + addr_str)
	        console.error(response, response.error_message)
	        return null
	      }

	      return response && response['candidates'] && response['candidates'][0]
	    } catch (e) {
	      console.error('Error while request google place info on address catch - ' + addr_str);
	      console.error(e)
	      return null
	    }
	  }

	getGooglePlaceAddressFromReqStr(req_string) {
		if (!req_string) {
		  return null
		}
		const found = req_string.match(/q=([^&#]*)/)

		return found && found[1]
	}

	getLatLngFromReqStr(req_string) {
		if (!req_string) {
		  return null
		}
		const found = req_string.match(/coord=([^&#]*)/)

		return found && found[1]
	}

	async processSavedVenuesExport() {

		await this.processExportSavedScrappedInfo(
			async (venue_to_handle, venue_scrap_info_item, headers_info) => {
				const h_info_venues = headers_info[ScrapHandler.DEFAULT_CSV_EXPORT_HEADERS_TYPE];

				const venue_response = {};

				for (const v_key in h_info_venues) {
					if (v_key.match(/faq/)) {
						const items_field_db = 'venue_faq';
						if (
							venue_to_handle[items_field_db] &&
							(typeof venue_to_handle[items_field_db] == 'object') && 
							Object.keys(venue_to_handle[items_field_db]).length
						) {
							const items_key = h_info_venues[v_key];
							venue_response[v_key] = '';
							const items_info = venue_to_handle[items_field_db][items_key];
							venue_response[v_key] = items_info;
						}
					} else if (v_key.match(/pref_vend/)) {
						const items_field_db = 'preffered_vendors';
						if (
							venue_to_handle[items_field_db] &&
							(typeof venue_to_handle[items_field_db] == 'object') && 
							Object.keys(venue_to_handle[items_field_db]).length
						) {
							const items_key = h_info_venues[v_key];
							venue_response[v_key] = '';
							const items_info = venue_to_handle[items_field_db][items_key];
							venue_response[v_key] = items_info;
						}
					} else {
						venue_response[v_key] = venue_to_handle[v_key];
					}
				}
				venue_response.venue_id = venue_to_handle.venue_id;

				return venue_response;
			},
			{
				multiple_additional_info: false
			}
		)
	}

	getVenueId(venue_item) {
		return venue_item.venue_id;
	}

}


module.exports = ScrapWeddingWireHandler