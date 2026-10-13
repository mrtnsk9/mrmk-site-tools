(function () {
	'use strict';

	var search = document.getElementById('mrmk-st-feature-search');
	var cards = Array.prototype.slice.call(document.querySelectorAll('[data-feature-card]'));
	var results = document.querySelector('[data-search-results]');
	var noResults = document.querySelector('[data-no-results]');

	if (!search || !results) {
		return;
	}

	function updateSearch() {
		var query = search.value.trim().toLocaleLowerCase();
		var visible = 0;

		cards.forEach(function (card) {
			var matches = card.getAttribute('data-search').toLocaleLowerCase().indexOf(query) !== -1;
			card.hidden = !matches;
			if (matches) {
				visible += 1;
			}
		});

		var template = results.getAttribute('data-results-template');
		results.textContent = template.replace('%1$d', visible).replace('%2$d', cards.length);

		if (noResults) {
			noResults.hidden = visible !== 0;
		}
	}

	search.addEventListener('input', updateSearch);
	updateSearch();

	document.querySelectorAll('.mrmk-st-toggle__input').forEach(function (toggle) {
		toggle.addEventListener('change', function () {
			var card = toggle.closest('[data-feature-card]');
			var status = card.querySelector('[data-feature-state]');

			card.classList.toggle('is-enabled', toggle.checked);
			status.textContent = toggle.checked
				? status.getAttribute('data-enabled-text')
				: status.getAttribute('data-disabled-text');
		});
	});
}());
