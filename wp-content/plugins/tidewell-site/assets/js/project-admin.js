jQuery(($) => {
	$('.tw-media-field').each(function () {
		const field = $(this);
		const input = field.find('input[type="hidden"]');
		const preview = field.find('.tw-media-preview');
		const multiple = field.data('multiple') === 1;
		let frame;

		field.on('click', '.tw-media-pick', (event) => {
			event.preventDefault();
			if (!frame) {
				frame = wp.media({
					title: multiple ? 'Choose gallery photos' : 'Choose the "before" photo',
					library: { type: 'image' },
					multiple: multiple ? 'add' : false,
				});
				frame.on('open', () => {
					const selection = frame.state().get('selection');
					input
						.val()
						.split(',')
						.filter(Boolean)
						.forEach((id) => selection.add(wp.media.attachment(id)));
				});
				frame.on('select', () => {
					const items = frame.state().get('selection').toJSON();
					input.val(items.map((item) => item.id).join(','));
					preview.empty();
					items.forEach((item) => {
						const src = item.sizes?.thumbnail?.url || item.url;
						preview.append($('<img>', { src, alt: '', width: 80, height: 80 }));
					});
				});
			}
			frame.open();
		});

		field.on('click', '.tw-media-clear', (event) => {
			event.preventDefault();
			input.val('');
			preview.empty();
		});
	});
});
