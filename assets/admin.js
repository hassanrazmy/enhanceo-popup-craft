jQuery(document).ready(function($){
	if ( $('.popup-craft-color-picker').length ) {
		$('.popup-craft-color-picker').wpColorPicker();
	}

	// ── Instant client-side category filter ──────────────────────────────────
	var $filterBtns = $('.elevoire-filter-btn');
	var $cards      = $('.elevoire-template-card');

	if ( $filterBtns.length ) {
		$filterBtns.on('click', function() {
			var filter = $(this).data('filter');

			// Update active state
			$filterBtns.removeClass('active');
			$(this).addClass('active');

			// Show / hide cards
			if ( 'all' === filter ) {
				$cards.show();
			} else {
				$cards.each(function() {
					$( this )[ $(this).data('category') === filter ? 'show' : 'hide' ]();
				});
			}
		});
	}

	// Live Preview Lightbox setup
	var $lightbox = $('#elevoire-preview-lightbox');
	var $lightboxBox = $('#elevoire-lightbox-container-box');
	var $lightboxContent = $('#elevoire-lightbox-inner-content');

	if ( $lightbox.length && typeof elevoireTemplatesData !== 'undefined' ) {
		
		$('.elevoire-preview-btn').on('click', function(e) {
			e.preventDefault();
			var templateId = $(this).data('template-id');
			var template = elevoireTemplatesData[templateId];

			if (!template) {
				return;
			}

			// Clear previous modifiers
			$lightbox.removeClass('preview-type-center_modal preview-type-top_banner preview-type-bottom_banner');
			
			// Add placement modifier
			var type = template.settings.type || 'center_modal';
			$lightbox.addClass('preview-type-' + type);

			// Apply inline styles to mimic settings
			var bg = template.settings.bg_color || '#ffffff';
			var text = template.settings.text_color || '#1e293b';
			var radius = template.settings.border_radius || 12;

			$lightboxBox.css({
				'--preview-bg': bg,
				'--preview-text': text,
				'--preview-radius': radius + 'px'
			});

			// Populate HTML
			$lightboxContent.html(template.content);

			// Show lightbox
			$lightbox.show();
		});

		// Close events
		$('#elevoire-lightbox-close-btn, #elevoire-lightbox-close-overlay').on('click', function(e) {
			e.preventDefault();
			$lightbox.hide();
			$lightboxContent.empty();
		});

		// Close on escape
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $lightbox.is(':visible')) {
				$lightbox.hide();
				$lightboxContent.empty();
			}
		});
	}
});
