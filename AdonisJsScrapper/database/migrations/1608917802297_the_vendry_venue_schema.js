'use strict'

/** @type {import('@adonisjs/lucid/src/Schema')} */
const Schema = use('Schema')

class TheVendryVenueSchema extends Schema {
  up () {
    this.create('the_vendry_venues', (table) => {
      table.increments()
      table.string('country')
      table.string('venue_id').unique()

      table.string('venue_name')
      table.string('venue_address')
      table.string('venue_type')
      table.json('venue_images')
      table.string('website_url')
      table.text('venue_description')
      table.json('venue_amenities')
      table.json('suitable_for')
      table.string('vibe')
      table.json('collaborators')
      table.json('spaces_info')
      table.boolean('single_scrapped').defaultTo(false)

      table.timestamps()
    })
  }

  down () {
    this.drop('the_vendry_venues')
  }
}

module.exports = TheVendryVenueSchema
