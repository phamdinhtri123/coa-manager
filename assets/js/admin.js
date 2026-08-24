(function ($) {
	'use strict';

	$(function () {
		if ($.fn.wpColorPicker) {
			$('.coam-color-field').wpColorPicker();
		}

		var frame;
		$('.coam-select-file').on('click', function (event) {
			event.preventDefault();
			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Select COA PDF',
				button: { text: 'Use this PDF' },
				library: { type: 'application/pdf' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				$('#coam-file-id').val(attachment.id);
				$('#coam-file-url').val(attachment.url);
				$('input[name="_coam_file_source"][value="media"]').prop('checked', true);
			});

			frame.open();
		});

		$('.coam-clear-file').on('click', function (event) {
			event.preventDefault();
			$('#coam-file-id, #coam-file-url').val('');
		});
	});
}(jQuery));
