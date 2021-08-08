'use strict'

const path = require("path")
const createCsvWriter = require('csv-writer').createObjectCsvWriter;
var fs = require('fs');

const axios = require('axios')
const pretty = require('pretty')

// browsers
const puppeteer = require('puppeteer')
const Nightmare = require('nightmare')

// html parsers
const cheerio = require('cheerio')
const JqueryParser = require('./JqueryParser')

const ScrappedCsvSaver = require('./ScrappedCsvSaver')

//models
const ScrapVenue = use('App/Models/ScrapVenue')

const google_api_key = 'AIzaSyBAKe4mFtRPjy_Fm8Sm_pAlI4lzK5RVDok'

class ScrapHandler {
	constructor(
		{
			type,
			scrapper_id,
			available_countries,
			country_to_scrap,
			adonis_console,
			browser,
			html_parser_options,
			venue_model,
			chunk_single_scrapping_count,
			venues_ids_unique_all_langs = true
		} = {}
	) {
		console.log('CREATE SCRAP HANDLER');

		this.type = type;
		this.scrapper_id = scrapper_id;
		this.available_countries = available_countries;
		this.country_to_scrap = country_to_scrap;
		this.console = adonis_console;
		this.browser = this.getBrowserToUse(browser, DEFAULT_BROWSER);
		this.html_parser = DEFAULT_HTML_PARSER;
		this.html_parser_options = html_parser_options;
		this.venue_model = venue_model;

		this.jquery_parser = new JqueryParser;

		this.opened_browser_page = null;

		this.google_api_key = google_api_key;
		this.axios = axios;
		this.pretty = pretty;
		this.chunk_single_scrapping_count = chunk_single_scrapping_count;
		this.venues_ids_unique_all_langs = venues_ids_unique_all_langs;
	}

	async startScrapping() {
		console.log('START SCRAPPING - ', this.type, this.country_to_scrap)

		if (!this.type) {
			this.console.error(`Scrapping type is not set!`);
		}
		
		if (
			Array.isArray(this.available_countries) && 
			!this.available_countries.includes(this.country_to_scrap)
		) {
			this.console.error(`${this.country_to_scrap} is not supported for this scrapper. Supported countries list: ${this.available_countries.join(', ')}`)
			return;
		}

		await this.startScrappingProcess();
	}

	async startScrappingProcess() {
		switch (this.type) {
			case ScrapHandler.TYPE_LIST:
				await this.processVenuesList();
				break;
			case ScrapHandler.TYPE_SINGLE:
				await this.processSingleVenuesFromLoadedList();
				break;
			case ScrapHandler.TYPE_EXPORT:
				await this.processSavedVenuesExport();
				break;
			default:
				if (typeof this.handleAdditionalScrappingTypes == 'function') {
					await this.handleAdditionalScrappingTypes(this.type);
				} else {
					this.console.error(`To handle not ordinary type ${this.type} declare async method handleAdditionalScrappingTypes(type) in scrapper handler class!`)
				}
				
				break;
		}
	}

	// SCRAPPING PROCESS FUNCTIONS
	parseHtmlContent(
		html_to_parse, 
		{
			parser_options
		} = {}
	) {
		const default_html_parser_options = 
			this.html_parser_options ? 
			this.html_parser_options : 
			{decodeEntities : false};
		parser_options = 
			parser_options ? 
			parser_options : 
			default_html_parser_options;
		const $ = cheerio.load(html_to_parse, parser_options);
		return $;
	}

	async getHtmlContentFromUrl(
		url, 
		{
			wait_for_selector = 'body', 
			get_html_from_selector = 'body',
			browser,
			headless = true,
			show_dev_tool = false,
			window_width = 1366,
			window_height = 768,
			page_custom_handle_callback,
			return_browser_page = false
		} = {},
		{
			parsed = true,
			parser_options,
		} = {}
	) {
		const browser_to_use = this.getBrowserToUse(browser, this.browser);
		try {
			let response;
			if (browser_to_use == ScrapHandler.BROWSER_PUPPETEER) {
				let browser_instance = await puppeteer.launch({ 
					headless,
					devtools: show_dev_tool
				})
				const page = await browser_instance.newPage()
				await page.setViewport({
					width: window_width,
				 	height: window_height,
				 });
				await page.goto(url)
				await page.waitForSelector(wait_for_selector)
				if (typeof page_custom_handle_callback == 'function') {
                	await page_custom_handle_callback(page)
                }
				
				// return page content or page instance
				if (return_browser_page) {
            		response = page
            	} else {
            		response = await page.evaluate((selector) => document.querySelector(selector).innerHTML, get_html_from_selector)
            	}

				browser_instance.close();
			} else if (browser_to_use == ScrapHandler.BROWSER_NIGHTMARE) {
				const nightmare_options = {show: !headless}
				if (show_dev_tool) {
					nightmare_options.openDevTools = {mode: 'detach'}
				}
				const nightmare = Nightmare(nightmare_options)
          		nightmare
  					.viewport(window_width, window_height)
                    .goto(url)
                    .wait(wait_for_selector)

                // let custom_response;
                if (typeof page_custom_handle_callback == 'function') {
                	await page_custom_handle_callback(nightmare)
                } 

            	// return page content or page instance
            	if (return_browser_page) {
            		response = nightmare
            	} else {
            		response = await nightmare
				            	.evaluate((selector) => document.querySelector(selector).innerHTML, get_html_from_selector)
                        		.end()
            	}
			}

			return (parsed && !return_browser_page) ? this.parseHtmlContent(response, {parser_options}) : response;
		} catch (e) {
		  console.error('error get html on url - ' + url)
		  console.error(e)
		  return null;
		}
	}

	async getContentFromApi(
		url,
		{
			request_options: {
				method,
				headers,
				params,
				data
			} = {}
		} = {},
		{
			parsed = false,
			parser_options,
		} = {}
	) {
		try {
			let response;
			const axios_params = {
				url,
				method: method ? method : 'get',
				headers,
				params,
				data
			}
			
			response = await axios(axios_params)

            const api_result = response && response.data
            return parsed ? this.parseHtmlContent(api_result, {parser_options}) : api_result;
        } catch (e) {
            console.error('error get api response on url - ' + url)
		  	console.error(e)
		  	return null;
        }
	}

	async processVenuesListPageData(
		root_url, 
		url, 
		parsePaginatedPageCallback, 
		{
			get_info_type,
			get_info_options,
			parser_options,
			return_parsed_html,
			full_next_page_link = false,
		} = {}
	) {
	  get_info_type = get_info_type ? get_info_type : ScrapHandler.GET_INFO_TYPE_HTML
	  let full_first_page_url = `${root_url}/${url}`;
	  let response_content

	  console.log('get info options - ', get_info_options)

	  if (get_info_type == ScrapHandler.GET_INFO_TYPE_HTML) {
		response_content = await this.getHtmlContentFromUrl(
	      	full_first_page_url, 
	      	get_info_options,
	  		{
	  			parsed: (typeof return_parsed_html == 'undefined') ? true : return_parsed_html,
	  			parser_options
	  		}
	      )
	  } else {
	  	  response_content = await this.getContentFromApi(
	      	full_first_page_url, 
	      	get_info_options,
	  		{
	  			parsed: (typeof return_parsed_html == 'undefined') ? false : return_parsed_html,
	  			parser_options
	  		}
	      )
	  }

      if (!response_content) {
          return null
      }

      let next_page_link = null
      do {
      	  if (next_page_link) {
      	  	let next_page_full_url;
      	  	if (full_next_page_link) {
      	  		next_page_full_url = next_page_link;
      	  	} else {
      	  		next_page_link = next_page_link.replace(/^\//, '')
      	  		next_page_full_url = `${root_url}/${next_page_link}`
      	  	}
      	  	
      	  	console.log('next_page_full_url - ', next_page_full_url)

      	  	if (get_info_type == ScrapHandler.GET_INFO_TYPE_HTML) {
	      	  	response_content = await this.getHtmlContentFromUrl(
	      	  		next_page_full_url,
	      	  		get_info_options,
	      	  		{
	      	  			parsed: return_parsed_html,
	      	  			parser_options
	      	  		}
	      	  	)
	      	} else {
	      		response_content = await this.getContentFromApi(
			      	full_first_page_url, 
			      	get_info_options,
			  		{
			  			parsed: return_parsed_html,
			  			parser_options
			  		}
			    )
	      	}
      	  }

      	  let processed = await parsePaginatedPageCallback(response_content)

      	  // check get info type and options
      	  get_info_type = processed.get_info_type ? processed.get_info_type : get_info_type
      	  get_info_options = processed.get_info_options ? processed.get_info_options : get_info_options

      	  // save receoved venues info into database
      	  const venues_list_info = processed.venues_list_info;
      	  for (const venue_info of venues_list_info) {
			const info_to_save = {
				scrapper: this.scrapper_id,
				country: this.country_to_scrap,
				venue_id: venue_info.venue_id,
				venue_link: venue_info.venue_link,
				description: venue_info.venue_description,
			}

			let scrapper_venue = await ScrapVenue
					      	.query()
					      	.where('scrapper', '=', this.scrapper_id)
					      	.where('country', '=', this.country_to_scrap)
					      	.where('venue_id', '=', info_to_save.venue_id)
					      	.first()

			if (scrapper_venue) {
				scrapper_venue.merge(info_to_save)
				await scrapper_venue.save()
			} else {
				await ScrapVenue.create(info_to_save)
			}
		  }
		  console.log(`saved all ${venues_list_info.length} venues links info`)


      	  next_page_link = processed.next_page_link
      	  
      } while (next_page_link)
  }

    async processLoadedVenuesLinks(
    	parseSinglePageCallback, 
		{
			get_info_type,
			get_info_options,
			parser_options,
			return_parsed_html,
			force_scrap_existed,
			force_scrap_existed_callback,
			fake_test_link,
			venue_link_suffix,
		} = {}
	) {
    	let scrapper_venues = (await ScrapVenue
	                    .query()
	                    .where('scrapper', '=', this.scrapper_id)
	                    .where('country', '=', this.country_to_scrap)
	                    .orderBy('id', 'asc')
	                    .fetch()
	                    )
	                      .toJSON()

	    let venues_count = scrapper_venues.length

	    let scrapped_count = 0;

	    console.log('VENUES COUNT TO SCRAP - ', venues_count)

	    const showScrappedCountInfo = async () => {
	    	console.log(`######### SCRAPPED ${scrapped_count}/${venues_count} #########`);
	    }

	    const scrapFork = async (fork_venues, fork_number) => {
	    	let count = 0;
	    	const fork_prefix = fork_number ? `FORK ${fork_number} ### ` : '';
	    	console.log(`${fork_prefix}venues count ${fork_venues.length}`)

	    	for (const venue_item of fork_venues) {
		      count++
		      let venue_id = this.getVenueId(venue_item)
		      let existed_venue_query = this.venue_model
		                            .query();

              if (!this.venues_ids_unique_all_langs) {
            	  existed_venue_query.where('country', '=', this.country_to_scrap)
              }
		                            
		      let existed_venue = await existed_venue_query
		                            	.where('venue_id', '=', venue_id)
		                            	.first()
		      
		      // if defined force scrap existed flag use it, if not check force scrap existed callback and check force scrap permission with it
		      const force_scrap_permission = (typeof force_scrap_existed != 'undefined') ? 
		      								 force_scrap_existed :
		      								 (
		      								 	(typeof force_scrap_existed_callback == 'function') ? 
		      								 	await force_scrap_existed_callback(existed_venue) :
		      								 	false
		      								 );

		      if (!force_scrap_permission && existed_venue) {
		        console.log(`${fork_prefix}venue ${existed_venue.venue_id} already loaded. skip scrapping`)
		        scrapped_count++;
		        continue
		      }

		      let response_content;

		      console.log(`${fork_prefix}TRY SCRAP ${count} venue - ${venue_item.venue_link}`)
			  const venue_link_to_request = fake_test_link ? fake_test_link : (venue_item.venue_link + (venue_link_suffix ? venue_link_suffix : ''))
			  console.log(`${fork_prefix}venue link to request - ${venue_link_to_request}`);
		      if (get_info_type == ScrapHandler.GET_INFO_TYPE_HTML) {
				response_content = await this.getHtmlContentFromUrl(
			      	venue_link_to_request,
			      	get_info_options,
			  		{
			  			parsed: (typeof return_parsed_html == 'undefined') ? true : return_parsed_html,
			  			parser_options
			  		}
			      )
			  } else {
			  	  response_content = await this.getContentFromApi(
			      	venue_link_to_request, 
			      	get_info_options,
			  		{
			  			parsed: (typeof return_parsed_html == 'undefined') ? false : return_parsed_html,
			  			parser_options
			  		}
			      )
			  }

			  let single_scrapped = await parseSinglePageCallback(response_content, venue_item, existed_venue, fork_prefix)

		      single_scrapped.country = this.country_to_scrap
		      single_scrapped.venue_id = venue_id

		      if (existed_venue) {
		        existed_venue.merge(single_scrapped)
		        await existed_venue.save()
		      } else {
		        await this.venue_model.create(single_scrapped)
		      }

		      console.log(`${fork_prefix}saved`)
		      scrapped_count++;
		      showScrappedCountInfo();
		    }
	    }

	    if (this.chunk_single_scrapping_count && (this.chunk_single_scrapping_count > 1)) {
	    	let promises = []
	        var i,j,c,temparray,chunk = this.chunk_single_scrapping_count;
	        for (i=0,j=scrapper_venues.length, c=0; i<j; i+=chunk,c++) {
	            temparray = scrapper_venues.slice(i,i+chunk);
	            promises.push(scrapFork(temparray, c+1))
	        }

	        console.log(`SPLITTED TO ${c} FORKS. ${this.chunk_single_scrapping_count} PER FORK.`)

	        await Promise.all(promises)
	        console.log('ALL FORKS FINISHED')
	    } else {
	    	await scrapFork(scrapper_venues, 0)
	    	console.log('ALL FINISHED WITHOUT FORKING')
	    }
    }

	getSupportedBrowsersList()
	{
		return [
			ScrapHandler.BROWSER_PUPPETEER,
			ScrapHandler.BROWSER_NIGHTMARE
		];
	}

	getBrowserToUse(browser, default_browser)
	{
		default_browser = default_browser ? default_browser : DEFAULT_BROWSER;
		return this.getSupportedBrowsersList().includes(browser) ? browser : default_browser;
	}

	getScrappingVenuesList(scrapper_id, country_to_scrap) {
		return require(`../../storage/scrapping_info/${this.scrapper_id}/venues_list_${this.country_to_scrap}.json`);
	}

	getCsvExportHeaders(headers_type, with_keys = true) {
		const headers_info = require(`../../storage/scrapping_info/${this.scrapper_id}/csv_export_headers.json`);

		if (!headers_info) {
			return null;
		}

		if (headers_type) {
			headers_type = headers_info[headers_type] ? headers_type : ScrapHandler.DEFAULT_CSV_EXPORT_HEADERS_TYPE;
		}

		const headers_info_to_return = (headers_type && headers_info[headers_type]) ? 
										headers_info[headers_type]:
										headers_info;

		const response = with_keys ? headers_info_to_return : Object.values(headers_info_to_return);
		
		return response;
	}

	async getGooglePlacesNearbyCoords(location, radius = 500, type = null) {
		if (!location) {
			return null;
		}

	    try {
		  const url = `https://maps.googleapis.com/maps/api/place/nearbysearch/json`;

		  const params_to_req = {
	            key: this.google_api_key,
	            location,
	            radius,
	            type,
	            language:'en'
	          }

	      let response = (await this.axios.get(
	        url ,
	        {
	          params: params_to_req
	        }
	      )).data

	      if (response.status != 'OK') {
	        console.error('Error while request google place info on address try - ' + url, params_to_req)
	        console.error(response, response.error_message)
	        return null
	      }

	      return response && response['results'];
	    } catch (e) {
	      console.error('Error while request google place info on address catch - ' + url, params_to_req);
	      console.error(e)
	      return null
	    }
	  }

	  async getScrapVenueInfo(venue_id = null, to_json = false) {
		let scrapper_venue_query = ScrapVenue
								      	.query()
								      	.where('scrapper', '=', this.scrapper_id)
								      	.where('country', '=', this.country_to_scrap);

		let scrapper_venue;

		if (venue_id) {
			scrapper_venue = await scrapper_venue_query
				.where('venue_id', '=', info_to_save.venue_id)
				.first()
		} else {
			scrapper_venue = await scrapper_venue_query
				.fetch();
		}

		if (to_json) {
			scrapper_venue = scrapper_venue.toJSON();
		}

		return scrapper_venue;			      	
	  }

	  async getSavedScrappedVenues(venue_id = null, to_json = false) {
		let saved_venue_query = this.venue_model
								      	.query()
								      	.where('country', '=', this.country_to_scrap);

		let saved_venue;

		if (venue_id) {
			saved_venue = await saved_venue_query
				.where('venue_id', '=', info_to_save.venue_id)
				.first()
		} else {
			saved_venue = await saved_venue_query
				.fetch();
		}

		if (to_json) {
			saved_venue = saved_venue.toJSON();
		}

		return saved_venue;			      	
	  }

	  transformHeadersInfoToCsvExport(headers_type) {
	  	return Object.entries(this.getCsvExportHeaders(headers_type))
					   .map(item => {
						   	return {
						   		id: item[0],
						   		title: item[1]
						   	};
					   });
	  }

	  async processExportSavedScrappedInfo(
	  	main_info_save_get_data_callback,
	  	{
	  		custom_save_callback,
	  		main_info_to_save_name,
	  		multiple_additional_info
	  	}
	  ) {

	    const csv_savers_list = {};

	  	let venues = await this.getSavedScrappedVenues(null, true);

	    let venues_scrap_info = await this.getScrapVenueInfo(null, true);

	    let count = 0;

	    for (let venue_to_handle of venues) {
	    	this.console.info(`Try export venue - ${++count}`)
			if ((!venue_to_handle.venue_id) || (typeof venue_to_handle.venue_id != 'string')) {
				this.console.info('Cant save venue link - ')
				this.console.info(!venue_to_handle.venue_id)
				this.console.info(typeof venue_to_handle.venue_id != 'string')
				continue
			}

			const venue_scrap_info_item = venues_scrap_info.find(item => {
				return (item.country == this.country_to_scrap) && 
					   (item.venue_id == this.getVenueId(venue_to_handle));
			});

			const info_to_export = await main_info_save_get_data_callback(
				venue_to_handle, 
				venue_scrap_info_item,
				this.getCsvExportHeaders()
			);

			const checkAdditionalCsvSaver = (name) => {
				if (!csv_savers_list[name]) {
					csv_savers_list[name] = new ScrappedCsvSaver (
						this.transformHeadersInfoToCsvExport(name), 
						this.scrapper_id, 
						`_${name}_${this.country_to_scrap}`
					)
				}
			}

			const checkInfoToExport = (info) => {
				if ((typeof info == 'object')) {
					return Array.isArray(info) ?
								info:
								[info];
				}
			}

			if (info_to_export) {
				if (multiple_additional_info) {
					for (const add_name in info_to_export) {
						checkAdditionalCsvSaver(add_name);
						const mult_info_to_export = checkInfoToExport(info_to_export[add_name]);
						if (mult_info_to_export && mult_info_to_export.length) {
							await csv_savers_list[add_name].writeToCsv(mult_info_to_export);
						}
					}
				} else {
					main_info_to_save_name = main_info_to_save_name ? 
														main_info_to_save_name : 
														ScrapHandler.CSV_EXPORT_SAVER_NAME_DEFAULT;
					checkAdditionalCsvSaver(main_info_to_save_name);
					await csv_savers_list[main_info_to_save_name].writeToCsv(checkInfoToExport(info_to_export))
				}
			}
	    }

	    if (typeof custom_save_callback == 'function') {
	    	custom_save_callback(venues, venues_scrap_info);
	    }
	  }
}

ScrapHandler.BROWSER_PUPPETEER = 'puppeteer';
ScrapHandler.BROWSER_NIGHTMARE = 'nightmare';

ScrapHandler.HTML_PARSER_CHEERIO = 'cheerio';

ScrapHandler.GET_INFO_TYPE_API = 'api';
ScrapHandler.GET_INFO_TYPE_HTML = 'html';

// scrapper usage types
ScrapHandler.TYPE_LIST = 'list'; // scrap venues list(links or info about many venues in one request)
ScrapHandler.TYPE_SINGLE = 'single'; // scrap single venue info
ScrapHandler.TYPE_EXPORT = 'export'; // export venues info

ScrapHandler.DEFAULT_COUNTRY_TO_SCRAP = 'US';

ScrapHandler.DEFAULT_CSV_EXPORT_HEADERS_TYPE = 'venues';

ScrapHandler.CSV_EXPORT_SAVER_NAME_DEFAULT = 'venues';

//constants
const DEFAULT_BROWSER = ScrapHandler.BROWSER_NIGHTMARE;
const DEFAULT_HTML_PARSER = ScrapHandler.HTML_PARSER_CHEERIO;
const DEFAULT_GET_INFO_TYPE = ScrapHandler.GET_INFO_TYPE_HTML;

module.exports = ScrapHandler