'use strict'

const { Command } = require('@adonisjs/ace')

class ScrappingProcess extends Command {
  static get signature () {
    return `
    	scrapping:process
    	{scrapper: Scrapper name}
    	{type: Scrapping type}
    	{country? : Country code to scrap}`
  }

  static get description () {
    return 'Tell something helpful about this command'
  }

  async handle (args, options) {
    this.info('Start scrapping')

    const ScrapperClass = require(`../Libraries/ScrapHandlers/Scrap${args.scrapper}Handler`)

    await (new ScrapperClass(args.type, args.country, this)).startScrapping()

    process.exit(0)
  }
}

module.exports = ScrappingProcess
