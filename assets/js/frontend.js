(function () {
	'use strict';

	function normalize(value) {
		return (value || '').toString().toLowerCase().trim();
	}

	function filterManager(manager) {
		var search = normalize((manager.querySelector('[data-coam-search-input]') || {}).value);
		var category = normalize((manager.querySelector('[data-coam-category-filter]') || {}).value);
		var laboratory = normalize((manager.querySelector('[data-coam-laboratory-filter]') || {}).value);
		var cards = manager.querySelectorAll('[data-coam-card]');
		var visible = 0;

		cards.forEach(function (card) {
			var matchesSearch = !search || normalize(card.dataset.coamSearch).indexOf(search) !== -1;
			var matchesCategory = !category || normalize(card.dataset.coamCategory).split(/\s+/).indexOf(category) !== -1;
			var matchesLaboratory = !laboratory || normalize(card.dataset.coamLaboratory) === laboratory;
			var show = matchesSearch && matchesCategory && matchesLaboratory;
			card.hidden = !show;
			if (show) {
				visible += 1;
			}
		});

		var empty = manager.querySelector('[data-coam-empty]');
		if (empty) {
			empty.hidden = visible !== 0;
		}
	}

	document.addEventListener('input', function (event) {
		if (event.target.matches('[data-coam-search-input]')) {
			filterManager(event.target.closest('.coam-manager'));
		}
	});

	document.addEventListener('change', function (event) {
		if (event.target.matches('[data-coam-category-filter], [data-coam-laboratory-filter]')) {
			filterManager(event.target.closest('.coam-manager'));
		}
	});
}());
