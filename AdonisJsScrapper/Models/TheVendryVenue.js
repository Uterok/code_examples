'use strict'

/** @type {typeof import('@adonisjs/lucid/src/Lucid/Model')} */
const Model = use('Model')

class TheVendryVenue extends Model {

	getVenueImages (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setVenueImages (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

	getVenueAmenities (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setVenueAmenities (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

	getSuitableFor (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setSuitableFor (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

	getCollaborators (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setCollaborators (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

	getSpacesInfo (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setSpacesInfo (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}
}

module.exports = TheVendryVenue
