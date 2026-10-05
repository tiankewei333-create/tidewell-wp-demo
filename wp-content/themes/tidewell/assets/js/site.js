(() => {
	const toggle = document.querySelector('.tw-nav-toggle');
	const nav = document.getElementById('tw-nav');
	if (!toggle || !nav) return;

	const mobile = window.matchMedia('(max-width: 1023px)');

	const setOpen = (open) => {
		toggle.setAttribute('aria-expanded', String(open));
		nav.classList.toggle('is-open', open);
		document.body.classList.toggle('tw-nav-open', open);
	};

	toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && nav.classList.contains('is-open')) {
			setOpen(false);
			toggle.focus();
		}
	});

	mobile.addEventListener('change', () => setOpen(false));

	// Submenus: a separate toggle button keeps the parent link clickable.
	nav.querySelectorAll('.menu-item-has-children').forEach((item, index) => {
		const link = item.querySelector(':scope > a');
		const submenu = item.querySelector(':scope > .sub-menu');
		if (!link || !submenu) return;

		submenu.id = submenu.id || `tw-submenu-${index}`;
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'tw-submenu-toggle';
		button.setAttribute('aria-expanded', 'false');
		button.setAttribute('aria-controls', submenu.id);
		button.innerHTML = `<span class="screen-reader-text">Show ${link.textContent.trim()} submenu</span>`;
		link.after(button);

		const setSub = (open) => {
			button.setAttribute('aria-expanded', String(open));
			item.classList.toggle('is-open', open);
		};

		button.addEventListener('click', () => setSub(button.getAttribute('aria-expanded') !== 'true'));
		item.addEventListener('focusout', (event) => {
			if (!mobile.matches && !item.contains(event.relatedTarget)) setSub(false);
		});
		item.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && item.classList.contains('is-open')) {
				event.stopPropagation();
				setSub(false);
				button.focus();
			}
		});
	});
})();
