'use strict'

/** @type {import('@adonisjs/lucid/src/Schema')} */
const Schema = use('Schema')

class CreateWeddingWireVenuesSchema extends Schema {
  up () {
    this.create('wedding_wire_venues', (table) => {
      table.increments()

      table.string('country')
      table.string('venue_id')

      table.text('venue_name')
      table.text('venue_address')
      table.text('venue_pricing')
      table.json('venue_images')
      table.text('venue_description')
      table.text('venue_capacity')
      table.json('venue_faq')
      table.json('preffered_vendors')
      table.string('website_url')
      table.boolean('single_scrapped').defaultTo(false)

      table.timestamps()
    })
  }

  down () {
    this.drop('wedding_wire_venues')
  }
}

module.exports = CreateWeddingWireVenuesSchema
