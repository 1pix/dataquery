<?php
// $Id$

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Define main TCA for table tx_dataquery_queries
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_dataquery_queries');

// Register sprite icon for dataquery table
$extensionRelativePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);
$icon = array(
	'query' => $extensionRelativePath . 'Resources/Public/Icons/DataQuery.png'
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
	$icon,
	$_EXTKEY
);

// Add context sensitive help (csh) for this table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_dataquery_queries', 'EXT:' . $_EXTKEY . '/locallang_csh_txdataqueryqueries.xml');

// Register dataquery as a Data Provider
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider']['config']['allowed'] .= ',tx_dataquery_queries';
$GLOBALS['TCA']['tt_content']['columns']['tx_displaycontroller_provider2']['config']['allowed'] .= ',tx_dataquery_queries';

// Add a wizard for adding a dataquery
$addDataqueryWizard = array(
	'type' => 'script',
	'title' => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:wizards.add_dataquery',
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

// Add context sensitive help (csh) for the added field
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_datafilter_filters', 'EXT:' . $_EXTKEY . '/locallang_csh_txdatafilterfilters.xml');
