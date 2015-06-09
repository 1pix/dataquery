<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Register as Data Provider service
// Note that the subtype corresponds to the name of the database table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
	'dataquery',
	// Service type
	'dataprovider',
	// Service key
	'tx_dataquery_dataprovider',
	array(
		'title' => 'Data Query',
		'description' => 'Data Provider for Data Query',

		'subtype' => 'tx_dataquery_queries',

		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,

		'os' => '',
		'exec' => '',

		'className' => 'Tesseract\Dataquery\Component\DataProvider',
	)
);

// Register the dataquery cache table to be deleted when all caches are cleared
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_dataquery_cache'] = 'tx_dataquery_cache';

// Register a hook to clear the cache for a given page
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval']['tx_dataquery'] = 'Tesseract\Dataquery\Cache\CacheHandler->clearCache';

// Register a hook with datafilter to handle the extra field added by dataquery
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessReturnValue']['tx_dataquery'] = 'Tesseract\Dataquery\Hook\DataFilterHook';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['datafilter']['postprocessEmptyFilterCheck']['tx_dataquery'] = 'Tesseract\Dataquery\Hook\DataFilterHook';

// Register wizard validation method with generic BE ajax calls handler
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
	'dataquery::validate',
	'Tesseract\\Dataquery\\Ajax\\AjaxHandler->validate'
);
