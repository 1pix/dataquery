<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return array(
        'ctrl' => array(
                'title' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries',
                'label' => 'title',
                'descriptionColumn' => 'description',
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'versioningWS' => true,
                'origUid' => 't3_origuid',
                'default_sortby' => 'ORDER BY title',
                'delete' => 'deleted',
                'enablecolumns' => array(
                        'disabled' => 'hidden',
                ),
                'searchFields' => 'title,description,sql_query',
                'typeicon_classes' => array(
                        'default' => 'tx_dataquery-dataquery'
                ),
                'dividers2tabs' => 1,
        ),
        'interface' => array(
                'showRecordFieldList' => 'hidden,title,description,sql_query,cache_duration,ignore_enable_fields,ignore_time_for_tables,ignore_disabled_for_tables,ignore_fegroup_for_tables,ignore_language_handling,skip_overlays_for_tables,get_versions_directly'
        ),
        'columns' => array(
                't3ver_label' => array(
                        'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
                        'config' => array(
                                'type' => 'input',
                                'size' => '30',
                                'max' => '30',
                        )
                ),
                'hidden' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
                        'config' => array(
                                'type' => 'check',
                                'default' => '0'
                        )
                ),
                'title' => array(
                        'exclude' => 0,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.title',
                        'config' => array(
                                'type' => 'input',
                                'size' => '30',
                                'eval' => 'required,trim',
                        )
                ),
                'description' => array(
                        'exclude' => 0,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.description',
                        'config' => array(
                                'type' => 'text',
                                'cols' => '30',
                                'rows' => '4',
                        )
                ),
                'sql_query' => array(
                        'exclude' => 0,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.sql_query',
                        'config' => array(
                                'type' => 'text',
                                'fieldWizard' => [
                                        'queryCheck' => [
                                                'renderType' => 'dataqueryCheckWizard'
                                        ]
                                ],
                                'cols' => '30',
                                'rows' => '8'
                        )
                ),
                'fulltext_indices' => array(
                        'exclude' => 0,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.fulltext_indices',
                        'config' => array(
                                'type' => 'user',
                                'userFunc' => 'Tesseract\Dataquery\UserFunction\FormEngine->renderFulltextIndices',
                        )
                ),
                'cache_duration' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.cache_duration',
                        'config' => array(
                                'type' => 'input',
                                'size' => 20,
                                'default' => 0,
                                'eval' => 'int',
                        )
                ),
                'ignore_enable_fields' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_enable_fields',
                        'config' => array(
                                'type' => 'radio',
                                'default' => 0,
                                'items' => array(
                                        array(
                                                'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_enable_fields.I.0',
                                                '0'
                                        ),
                                        array(
                                                'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_enable_fields.I.1',
                                                '1'
                                        ),
                                        array(
                                                'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_enable_fields.I.2',
                                                '2'
                                        ),
                                ),
                        )
                ),
                'ignore_time_for_tables' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_time_for_tables',
                        'config' => array(
                                'type' => 'input',
                                'size' => 255,
                                'default' => '*',
                        )
                ),
                'ignore_disabled_for_tables' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_disabled_for_tables',
                        'config' => array(
                                'type' => 'input',
                                'size' => 255,
                                'default' => '*',
                        )
                ),
                'ignore_fegroup_for_tables' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_fegroup_for_tables',
                        'config' => array(
                                'type' => 'input',
                                'size' => 255,
                                'default' => '*',
                        )
                ),
                'ignore_language_handling' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_language_handling',
                        'config' => array(
                                'type' => 'check',
                                'default' => 0,
                                'items' => array(
                                        array(
                                                'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.ignore_language_handling.I.0',
                                                ''
                                        ),
                                ),
                        )
                ),
                'skip_overlays_for_tables' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.skip_overlays_for_tables',
                        'config' => array(
                                'type' => 'input',
                                'size' => 255,
                        )
                ),
                'get_versions_directly' => array(
                        'exclude' => 1,
                        'label' => 'LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.get_versions_directly',
                        'config' => array(
                                'type' => 'input',
                                'size' => 255,
                        )
                ),
        ),
        'types' => array(
                '0' => array(
                        'showitem' => 'hidden, title;;1, sql_query, fulltext_indices,
							--div--;LLL:EXT:dataquery/Resources/Private/Language/locallang_db.xlf:tx_dataquery_queries.tab.advanced, cache_duration, ignore_enable_fields;;2, ignore_language_handling;;3, get_versions_directly'
                )
        ),
        'palettes' => array(
                '1' => array('showitem' => 'description'),
                '2' => array('showitem' => 'ignore_time_for_tables, --linebreak--, ignore_disabled_for_tables, --linebreak--, ignore_fegroup_for_tables'),
                '3' => array('showitem' => 'skip_overlays_for_tables')
        )
);
