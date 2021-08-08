'use strict'

/** @type {typeof import('@adonisjs/lucid/src/Lucid/Model')} */
const Model = use('Model')

class WeddingWireVenue extends Model {

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

	getVenueFaq (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setVenueFaq (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

	getPrefferedVendors (val) {
    	try {
			return JSON.parse(val)
		} catch (e) {
			return val
		}
	}

	setPrefferedVendors (val) {
		return (!val || (typeof val == 'string')) ? val : JSON.stringify(val)
	}

}

module.exports = WeddingWireVenue
