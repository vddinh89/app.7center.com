/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

onDocumentReady((event) => {
	
	/* Handle the locations modal */
	let locationsModal = null;
	const browseLocationsEl = document.getElementById('browseLocations');
	if (isDefined(browseLocationsEl) && browseLocationsEl != null) {
		locationsModal = new bootstrap.Modal(browseLocationsEl, {});
	}
	
	/* When trying to open the city select box, open the locations modal */
	$(document).on('select2:opening', '#cityId', function (e) {
		e.preventDefault();
		
		$('#modalTriggerName').val('select');
		if (locationsModal != null) {
			locationsModal.show(this); /* The browseLocations() function will be fired */
		}
		
		return false;
	});
	
	/* Retrieve selected city and its admin. division */
	selectCity();
	
	/* When clicking on a city link, add the city in the select box option and select it (without fire the HTML link) */
	$(document).on('click', '#browseLocations .is-city', function (e) {
		let modalTriggerName = $('#modalTriggerName').val();
		
		if (modalTriggerName === 'select') {
			e.preventDefault();
			
			selectCity(this);
			if (locationsModal != null) {
				locationsModal.hide();
			}
			
			return false;
		}
	});
	
});

/**
 * Click on a city link to select it (to add it in the city's select box)
 *
 * @param jsThis
 * @returns {boolean}
 */
function selectCity(jsThis = null) {
	/* Check required variables */
	if (typeof languageCode === 'undefined' || typeof countryCode === 'undefined') {
		return false;
	}
	
	/* Location's modal fields */
	const $adminTypeEl = $('#modalAdminType');
	const $adminCodeEl = $('#modalAdminCode');
	
	/* Main form fields */
	const $selectedAdminTypeEl = $('#selectedAdminType');
	const $selectedAdminCodeEl = $('#selectedAdminCode');
	const $selectedCityIdEl = $('#selectedCityId');
	const $selectedCityNameEl = $('#selectedCityName');
	const $cityIdEl = $('#cityId');
	
	if (
		!isDefined($adminTypeEl)
		|| !isDefined($adminCodeEl)
		|| !isDefined($selectedAdminTypeEl)
		|| !isDefined($selectedAdminCodeEl)
		|| !isDefined($selectedCityIdEl)
		|| !isDefined($selectedCityNameEl)
		|| !isDefined($cityIdEl)
	) {
		return false;
	}
	
	let adminType, adminCode, cityId, cityName;
	
	if (isDefined(jsThis) && jsThis !== null) {
		const thisEl = $(jsThis);
		
		adminType = thisEl.data('adminType');
		adminCode = thisEl.data('adminCode');
		cityId = thisEl.data('id');
		cityName = thisEl.data('name');
		
		if (!isEmpty(cityId) && !isEmpty(cityName)) {
			$cityIdEl.empty().append('<option value="' + cityId + '">' + cityName + '</option>').val(cityId).trigger('change');
			
			$adminTypeEl.val(adminType);
			$adminCodeEl.val(adminCode);
			$selectedAdminTypeEl.val(adminType);
			$selectedAdminCodeEl.val(adminCode);
			$selectedCityIdEl.val(cityId);
			$selectedCityNameEl.val(cityName);
			
			$adminTypeEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			$adminCodeEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			$selectedAdminTypeEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			$selectedAdminCodeEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			$selectedCityIdEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			$selectedCityNameEl[0].dispatchEvent(new Event('input', {bubbles: true}));
			
			/* Update the modal form */
			if (!isEmpty(adminType) && !isEmpty(adminCode)) {
				const urlEl = $('#modalUrl');
				if (isDefined(urlEl)) {
					let url = `${siteUrl}/browsing/locations/${strToLower(countryCode)}/admins/${adminType}/${adminCode}/cities`;
					urlEl.val(url);
				}
			}
			const queryEl = $('#modalQuery');
			if (isDefined(queryEl)) {
				queryEl.val('');
			}
		} else {
			let error = 'Error: Impossible to select the city.';
			jsAlert(error, 'error', false, true);
		}
	} else {
		$adminTypeEl.val($selectedAdminTypeEl.val());
		$adminCodeEl.val($selectedAdminCodeEl.val());
		cityId = $selectedCityIdEl.val();
		cityName = $selectedCityNameEl.val();
		
		if (!isEmpty(cityId) && !isEmpty(cityName)) {
			$cityIdEl.empty().append('<option value="' + cityId + '">' + cityName + '</option>').val(cityId).trigger('change');
		}
	}
}
