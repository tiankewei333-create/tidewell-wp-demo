(() => {
	document.querySelectorAll('[data-tw-portfolio]').forEach(initPortfolio);
	document.querySelectorAll('[data-tw-ba]').forEach(initBeforeAfter);

	function initPortfolio(root) {
		const filters = [...root.querySelectorAll('.tw-filter')];
		const cards = [...root.querySelectorAll('.tw-card')];
		const status = root.querySelector('[data-tw-status]');
		const dialog = root.querySelector('.tw-lightbox');

		filters.forEach((link) =>
			link.addEventListener('click', (event) => {
				event.preventDefault();
				applyFilter(link);
				const url = new URL(window.location.href);
				if (link.dataset.filter) url.searchParams.set('project_type', link.dataset.filter);
				else url.searchParams.delete('project_type');
				history.replaceState(null, '', url);
			})
		);

		function applyFilter(link) {
			const slug = link.dataset.filter;
			filters.forEach((f) => f.removeAttribute('aria-current'));
			link.setAttribute('aria-current', 'true');
			let shown = 0;
			cards.forEach((card) => {
				const match = !slug || card.dataset.types.split(' ').includes(slug);
				card.hidden = !match;
				if (match) shown++;
			});
			if (status) {
				const label = slug ? ` in ${link.firstChild.textContent.trim()}` : '';
				status.textContent = `Showing ${shown} ${shown === 1 ? 'project' : 'projects'}${label}.`;
			}
		}

		if (!dialog || typeof dialog.showModal !== 'function') return;

		const img = dialog.querySelector('.tw-lightbox__img');
		const caption = dialog.querySelector('.tw-lightbox__caption');
		let current = -1;
		const visibleTriggers = () =>
			cards.filter((card) => !card.hidden).map((card) => card.querySelector('[data-tw-lightbox]'));

		root.addEventListener('click', (event) => {
			const trigger = event.target.closest('[data-tw-lightbox]');
			if (trigger) open(visibleTriggers().indexOf(trigger));
		});

		function open(index) {
			const list = visibleTriggers();
			if (!list.length) return;
			current = (index + list.length) % list.length;
			const trigger = list[current];
			img.src = trigger.dataset.full;
			img.alt = trigger.dataset.caption;
			caption.textContent = trigger.dataset.caption;
			if (!dialog.open) dialog.showModal();
		}

		dialog.querySelector('[data-tw-prev]').addEventListener('click', () => open(current - 1));
		dialog.querySelector('[data-tw-next]').addEventListener('click', () => open(current + 1));
		dialog.querySelector('[data-tw-close]').addEventListener('click', () => dialog.close());
		dialog.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowLeft') open(current - 1);
			if (event.key === 'ArrowRight') open(current + 1);
		});
		dialog.addEventListener('click', (event) => {
			if (event.target === dialog) dialog.close();
		});
		dialog.addEventListener('close', () => {
			visibleTriggers()[current]?.focus();
		});
	}

	function initBeforeAfter(root) {
		const range = root.querySelector('.tw-ba__range');
		const update = () => root.style.setProperty('--pos', `${range.value}%`);
		range.addEventListener('input', update);
		update();
	}
})();
