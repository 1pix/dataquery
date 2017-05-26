<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// Define main TCA for table tx_dataquery_queries
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_dataquery_queries');

// Register sprite icon for dataquery table
/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
        'tx_dataquery-dataquery',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        [
            'source' => 'EXT:dataquery/Resources/Public/Icons/DataQuery.png'
        ]
);

// Add context sensitive help (csh) for this table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_dataquery_queries',
        'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh_txdataqueryqueries.xlf'
);

// Add context sensitive help (csh) for the added field
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_datafilter_filters',
        'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh_txdatafilterfilters.xlf'
);
