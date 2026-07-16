document.addEventListener('DOMContentLoaded', function () {
	// enhanceoPopupCraftData is provided via wp_localize_script
	if (typeof enhanceoPopupCraftData === 'undefined') {
		return;
	}

	var popupId = enhanceoPopupCraftData.id;
	var delay = parseInt(enhanceoPopupCraftData.delay, 10);
	var cookieExpiry = parseInt(enhanceoPopupCraftData.cookie_expiry, 10);
	var closeOnOverlay = enhanceoPopupCraftData.close_on_overlay === true || enhanceoPopupCraftData.close_on_overlay === '1';
	var storageKey = 'popup_craft_closed_' + popupId;

	var wrapper = document.getElementById('popup-craft-wrapper-' + popupId);
	if (!wrapper) {
		return;
	}

	var lastActiveElement = null;

	/**
	 * Check if the user has already closed the popup and if it's still valid.
	 */
	function shouldShowPopup() {
		if (cookieExpiry === 0) {
			return true;
		}

		var closedData = localStorage.getItem(storageKey);
		if (!closedData) {
			return true;
		}

		var parsedData;
		try {
			parsedData = JSON.parse(closedData);
		} catch (e) {
			return true;
		}

		var now = new Date().getTime();
		if (now > parsedData.expiry) {
			localStorage.removeItem(storageKey);
			return true;
		}

		return false;
	}

	/**
	 * Set localStorage item with expiry.
	 */
	function setClosedCookie() {
		if (cookieExpiry === 0) {
			return;
		}

		var now = new Date().getTime();
		var expiryMs = cookieExpiry * 24 * 60 * 60 * 1000;
		
		var data = {
			closedAt: now,
			expiry: now + expiryMs
		};

		localStorage.setItem(storageKey, JSON.stringify(data));
	}

	/**
	 * Get all focusable elements inside the popup.
	 */
	function getFocusableElements() {
		var selectors = [
			'a[href]',
			'area[href]',
			'input:not([disabled])',
			'select:not([disabled])',
			'textarea:not([disabled])',
			'button:not([disabled])',
			'iframe',
			'object',
			'embed',
			'[tabindex="0"]',
			'[contenteditable]'
		];
		var elements = wrapper.querySelectorAll(selectors.join(','));
		return Array.prototype.slice.call(elements);
	}

	/**
	 * Focus trap keyboard listener.
	 */
	function handleKeyDown(e) {
		if (e.key === 'Tab') {
			var focusables = getFocusableElements();
			if (focusables.length === 0) {
				e.preventDefault();
				return;
			}

			var first = focusables[0];
			var last = focusables[focusables.length - 1];

			if (e.shiftKey) {
				// Shift + Tab (Backward)
				if (document.activeElement === first) {
					last.focus();
					e.preventDefault();
				}
			} else {
				// Tab (Forward)
				if (document.activeElement === last) {
					first.focus();
					e.preventDefault();
				}
			}
		}

		if (e.key === 'Escape') {
			e.preventDefault();
			hidePopup();
			setClosedCookie();
		}
	}

	/**
	 * Show the popup.
	 */
	function showPopup() {
		lastActiveElement = document.activeElement;
		wrapper.classList.add('is-visible');
		wrapper.setAttribute('aria-hidden', 'false');

		// Focus the first focusable element or the close button
		var focusables = getFocusableElements();
		if (focusables.length > 0) {
			// Find close button or first custom element
			var closeBtn = wrapper.querySelector('.popup-craft-close');
			if (closeBtn) {
				closeBtn.focus();
			} else {
				focusables[0].focus();
			}
		}

		// Enable key listeners for escape & focus trapping
		document.addEventListener('keydown', handleKeyDown);
	}

	/**
	 * Hide the popup.
	 */
	function hidePopup() {
		wrapper.classList.remove('is-visible');
		wrapper.setAttribute('aria-hidden', 'true');
		
		// Remove event listener
		document.removeEventListener('keydown', handleKeyDown);

		// Restore focus
		if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
			lastActiveElement.focus();
		}
	}

	// Initialize
	if (shouldShowPopup()) {
		if (delay > 0) {
			setTimeout(showPopup, delay);
		} else {
			setTimeout(showPopup, 50);
		}
	}

	// Event Listeners for Close
	var closeTriggers = wrapper.querySelectorAll('[data-popup-close]');
	closeTriggers.forEach(function (trigger) {
		trigger.addEventListener('click', function (e) {
			// If clicking on wrapper (overlay) itself, check closeOnOverlay option
			if (trigger.classList.contains('popup-craft-overlay') && !closeOnOverlay) {
				return;
			}
			e.preventDefault();
			hidePopup();
			setClosedCookie();
		});
	});
});
