'use strict'

const path = require("path")
const createCsvWriter = require('csv-writer').createObjectCsvWriter;
var fs = require('fs');

class ScrappedCsvSaver {

  constructor(
    header_info, 
    scrapper_id, 
    filename_suffix = null, 
    file_to_continue_write = null
  ) {
    const path_to_save = path.join(
      __dirname, 
      '..', 
      '..', 
      'storage', 
      'scrapped', 
      scrapper_id)

    if (!fs.existsSync(path_to_save)){
        fs.mkdirSync(path_to_save);
    }

    this.filename = file_to_continue_write ?
                    file_to_continue_write :
                    (new Date).toISOString() + `_${scrapper_id}${filename_suffix}.csv`

    const full_path_to_save = `${path_to_save}/${this.filename}`

    console.log('PATH TO SAVE SCRAPPED - ', full_path_to_save)

    const headers = header_info.map(item => { 
      if (typeof item == 'object') {
        return item
      }
      return {id: item, title: item} 
    })

    this.headers_ids_list = headers.map(item => {
      return item.id
    })

    // if file exists append content to it
    const append = fs.existsSync(full_path_to_save)

    this.csvWriterVenue = createCsvWriter({
        path: full_path_to_save,
        header: headers,
        append
    });
  }

  getFilename() {
    return this.filename
  }

  async writeToCsv(info_to_save) {
    if (!Array.isArray(info_to_save)) {
      return;
    }

    let cloned_info_to_save = JSON.parse(JSON.stringify(info_to_save))

    for (let item of cloned_info_to_save) {
      for (let field_name in item) {
        if (!this.headers_ids_list.includes(field_name)) {
          delete item[field_name]
          continue
        }
        if (typeof item[field_name] == 'object') {
          item[field_name] = JSON.stringify(item[field_name])
        } 
        if (item[field_name] == 'null') {
          item[field_name] = null
        }
      }
    }

    await this.csvWriterVenue.writeRecords(cloned_info_to_save)
  }

}

module.exports = ScrappedCsvSaver