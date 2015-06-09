<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Add SQL field to datafilter
$tempColumns = array(
	'tx_dataquery_sql' => array(
		'exclude' => TRUE,
		'label' => 'LLL:EXT:dataquery/locallang_db.xml:tx_datafilter_filters.tx_dataquery_sql',
		'config' => array(
			'type' => 'text',
			'cols' => '30',
			'rows' => '8',
		)
	)
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_datafilter_filters',
	$tempColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_datafilter_filters',
	'--div--;LLL:EXT:dataquery/locallang_db.xml:sql, tx_dataquery_sql'
);
