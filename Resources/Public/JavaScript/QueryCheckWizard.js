/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/Dataquery/QueryCheckWizard
 * JS for the query check wizard
 */
define(['jquery'], function($) {
	'use strict';

	var QueryCheckWizard = {
	};

	/**
	 * Activates the wizard which check the current SQL entered in the SQL query field.
	 */
	QueryCheckWizard.activateWizard = function() {
		var wizardContainer = $('#tx_dataquery_wizard_container');
		// On click, call the query check by AJAX
		wizardContainer.on('click', 'input[type="button"]', function() {
			var textarea = $(this).parents('.form-wizards-wrap').find('.form-wizards-element textarea');
			$.ajax({
				url: TYPO3.settings.ajaxUrls['tx_dataquery_querycheckwizard'],
				data: {
					query: textarea.val()
				},
				// Display the validation result
				success: function (data, status, xhr) {
					var resultContainer = $('#tx_dataquery_wizard_check_result');
					resultContainer.html(data);
				}
			});
		});
	};

	/**
	 * Initialize this module
	 */
	$(function() {
		QueryCheckWizard.activateWizard();
	});

	return QueryCheckWizard;
});

