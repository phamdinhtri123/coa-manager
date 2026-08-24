(function () {
	'use strict';

	function normalize(value) {
		return (value || '').toString().toLowerCase().trim();
	}

	function filterManager(manager) {
		var search = normalize((manager.querySelector('[data-coam-search-input]') || {}).value);
		var cards = manager.querySelectorAll('[data-coam-card]');
		var visible = 0;

		cards.forEach(function (card) {
			var matchesSearch = !search || normalize(card.dataset.coamSearch).indexOf(search) !== -1;
			card.hidden = !matchesSearch;
			if (matchesSearch) {
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

	document.addEventListener('click', function (event) {
		if (event.target.matches('[data-coam-search-button]')) {
			filterManager(event.target.closest('.coam-manager'));
		}
	});

}());
