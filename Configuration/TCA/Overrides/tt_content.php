<?php

// Register dataquery as a Data Provider
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider']['config']['allowed'] .= ',tx_dataquery_queries';
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider2']['config']['allowed'] .= ',tx_dataquery_queries';

// Add a wizard for adding a dataquery
$addDataqueryWizard = array(
	'type' => 'script',
	'title' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:wizards.add_dataquery',
	'script' => 'wizard_add.php',
	'module' => array(
		'name' => 'wizard_add'
	),
	'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/AddDataQueryWizard.png',
	'params' => array(
		'table' => 'tx_dataquery_queries',
		'pid' => '###CURRENT_PID###',
		'setValue' => 'append'
	)
);
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider']['config']['wizards']['add_dataquery'] = $addDataqueryWizard;
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider2']['config']['wizards']['add_dataquery'] = $addDataqueryWizard;
