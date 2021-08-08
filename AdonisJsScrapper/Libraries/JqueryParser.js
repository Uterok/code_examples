'use strict'

class JqueryParser {
	findInArray(
		source_from,
		array_selector,
		to_find_selector,
		search_from_global = true
	) {
		const findQuery = function () {
			return search_from_global ? 
					source_from(array_selector) :
					source_from.find(array_selector);
					
		}

		const array_length = findQuery().length

		if (!array_length) {
			return null;
		}

		for (let i = 0; i < array_length; i++) {
			const array_item = findQuery().eq(i);

			if (!array_item.find(to_find_selector).length) {
				continue;
			}

			return array_item;
		}

		return null;
	}

	findInArrayByInnerValue(
		source_from,
		array_selector,
		to_find_selector,
		check_inner_elem_callback,
		{
			multiple_inner_elems = false
		} = {},
		search_from_global = true
	) {
		const findQuery = function () {
			return search_from_global ? 
					source_from(array_selector) :
					source_from.find(array_selector);
					
		}

		const array_length = findQuery().length

		if (!array_length) {
			return null;
		}

		for (let i = 0; i < array_length; i++) {
			const array_item = findQuery().eq(i);

			const find_for_selector = array_item.find(to_find_selector)

			if (!find_for_selector.length) {
				continue;
			}

			if (multiple_inner_elems) {
				const elem_first = find_for_selector.first();
				if ((typeof check_inner_elem_callback == 'function') && !check_inner_elem_callback(elem_first)) {
					continue;
				}
				return array_item;
			} else {
				let items = [];
				find_for_selector
					.each((i, elem) => {
						// let elem_parsed = elem;
						if ((typeof check_inner_elem_callback == 'function') && !check_inner_elem_callback(elem)) {
							return;
						}

						items.push(elem);
					})

				return items;
			}
		}

		return null;
	}
}


module.exports = JqueryParser