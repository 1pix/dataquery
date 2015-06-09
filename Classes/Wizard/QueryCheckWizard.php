<?php
namespace Tesseract\Dataquery\Wizard;

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

use TYPO3\CMS\Backend\Form\FormEngine;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Wizard for checking the validity and the results of a SQL query.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class QueryCheckWizard {

	/**
	 * Renders the wizard itself.
	 *
	 * @param array $fieldParameters Parameters of the field
	 * @param FormEngine $formObject Calling object
	 * @return string HTML for the wizard
	 */
	public function render($fieldParameters, FormEngine $formObject) {
		// Get the id attribute of the field tag
		preg_match('/id="(.+?)"/', $fieldParameters['item'], $matches);

		/** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
		$pageRenderer = $GLOBALS['SOBE']->doc->getPageRenderer();
		// Add specific CSS
		$pageRenderer->addCssFile(
			ExtensionManagementUtility::extRelPath('dataquery') . 'Resources/Public/Styles/CheckWizard.css'
		);
		// Load the necessary JavaScript
		$pageRenderer->addJsFile(
			ExtensionManagementUtility::extRelPath('dataquery') . 'Resources/Public/JavaScript/CheckWizard.js'
		);
		// Load some localized labels, plus the field's id
		$pageRenderer->addJsInlineCode(
			'tx_dataquery_wizard',
			'var TX_DATAQUERY = {
				fieldId : "' . $matches[1] . '",
				labels : {
					"debugTab" : "' . $GLOBALS['LANG']->sL('LLL:EXT:dataquery/locallang.xml:wizard.check.debugTab') . '",
					"previewTab" : "' . $GLOBALS['LANG']->sL('LLL:EXT:dataquery/locallang.xml:wizard.check.previewTab') . '",
					"validateButton" : "' . $GLOBALS['LANG']->sL('LLL:EXT:dataquery/locallang.xml:wizard.check.validateButton') . '"
				}
			};'
		);
		// First of all render the button that will show/hide the rest of the wizard
		$wizard = '';
		// Assemble the base HTML for the wizard
		$wizard .= '<div id="tx_dataquery_wizardContainer"></div>';
		return $wizard;
	}
}
