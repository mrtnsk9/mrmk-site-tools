(function () {
	'use strict';

	var search = document.getElementById('mrmk-st-feature-search');
	var statusFilter = document.getElementById('mrmk-st-feature-status');
	var categoryFilter = document.getElementById('mrmk-st-feature-category');
	var sortSelect = document.getElementById('mrmk-st-feature-sort');
	var grid = document.querySelector('.mrmk-st-grid');
	var cards = Array.prototype.slice.call(document.querySelectorAll('[data-feature-card]'));
	var results = document.querySelector('[data-search-results]');
	var noResults = document.querySelector('[data-no-results]');

	if (!search || !results) {
		return;
	}

	function updateSearch() {
		var query = search.value.trim().toLocaleLowerCase();
		var status = statusFilter ? statusFilter.value : 'all';
		var category = categoryFilter ? categoryFilter.value : '';
		var sort = sortSelect ? sortSelect.value : 'name';
		var visible = 0;
		var enabledCount = 0;

		cards.sort(function (a, b) {
			var aEnabled = a.querySelector('.mrmk-st-toggle__input').checked;
			var bEnabled = b.querySelector('.mrmk-st-toggle__input').checked;

			if (sort === 'enabled' && aEnabled !== bEnabled) {
				return aEnabled ? -1 : 1;
			}

			return a.getAttribute('data-name').toLocaleLowerCase()
				.localeCompare(b.getAttribute('data-name').toLocaleLowerCase());
		});

		cards.forEach(function (card) {
			var enabled = card.querySelector('.mrmk-st-toggle__input').checked;
			var matchesSearch = card.getAttribute('data-search').toLocaleLowerCase().indexOf(query) !== -1;
			var matchesStatus = status === 'all' || (status === 'enabled' ? enabled : !enabled);
			var matchesCategory = !category || card.getAttribute('data-category') === category;
			var matches = matchesSearch && matchesStatus && matchesCategory;

			if (grid) {
				grid.appendChild(card);
			}

			card.hidden = !matches;
			if (matches) {
				visible += 1;
			}
			if (enabled) {
				enabledCount += 1;
			}
		});

		var template = results.getAttribute('data-results-template');
		results.textContent = template
			.replace('%1$d', visible)
			.replace('%2$d', cards.length)
			.replace('%3$d', enabledCount);

		if (noResults) {
			noResults.hidden = visible !== 0;
		}
	}

	search.addEventListener('input', updateSearch);
	[statusFilter, categoryFilter, sortSelect].forEach(function (control) {
		if (control) {
			control.addEventListener('change', updateSearch);
		}
	});
	search.addEventListener('keydown', function (event) {
		// Prevents form submit on enter press.
		if (event.key === 'Enter' && !event.isComposing) {
			event.preventDefault();
		}
	});
	updateSearch();

	document.querySelectorAll('.mrmk-st-toggle__input').forEach(function (toggle) {
		toggle.addEventListener('change', function () {
			var card = toggle.closest('[data-feature-card]');
			var status = card.querySelector('[data-feature-state]');

			card.classList.toggle('is-enabled', toggle.checked);
			status.textContent = toggle.checked
				? status.getAttribute('data-enabled-text')
				: status.getAttribute('data-disabled-text');

			updateSearch();
		});
	});
}());
