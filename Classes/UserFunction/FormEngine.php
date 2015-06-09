<?php
namespace Tesseract\Dataquery\UserFunction;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Displays a custom field in the BE to check for fulltext indices and
 * provide useful hints.
 *
 * @author Fabien Udriot (Cobweb) <support@cobweb.ch>
 * @author Francois Suter (Cobweb) <support@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class FormEngine {

	/**
	 * @var \Tesseract\Dataquery\Utility\DatabaseAnalyser
	 */
	protected $analyser;

	/**
	 * @var \Tesseract\Dataquery\Parser\SqlParser
	 */
	protected $sqlParser;

	/**
	 * @var \TYPO3\CMS\Lang\LanguageService
	 */
	protected $language;

	/**
	 * Stores the main table of the SQL query
	 * @var string
	 */
	protected $table;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->language = $GLOBALS['LANG'];
		$this->analyser = GeneralUtility::makeInstance('Tesseract\\Dataquery\\Utility\\DatabaseAnalyser');
		$this->sqlParser = GeneralUtility::makeInstance('Tesseract\\Dataquery\\Parser\\SqlParser');
	}

	/**
	 * This method format a message regarding FULLTEXT indexes in the database towards a BE user.
	 *
	 * @param array $parameters Properties of the field being modified
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject Back-reference to the calling object
	 * @return string
	 */
	public function renderFulltextIndices($parameters, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		$output = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_index_or_missing_table');

		if (empty($parameters['row']['sql_query'])) {
			$output = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_query');
		} else {

			// Fetch the query parts
			try {
				$query = $this->sqlParser->parseSQL($parameters['row']['sql_query']);

				if (!empty($query->structure['FROM']['table'])) {
					$this->table = $query->structure['FROM']['table'];
					if ($this->analyser->hasIndex($this->table)) {
						$output = $this->getMessageOk();
					} else {
						$output = $this->getMessageNoIndexFound();
					}
				}
			}
			catch (\Exception $e) {
				// Nothing to do, the default message will do fine
			}
		}
		return $output;
	}

	/**
	 * Formats a message for the BE displaying all possible FULLTEXT index to the BE User.
	 *
	 * @return string
	 */
	protected function getMessageOk() {
		$fields = $this->analyser->getFields($this->table);
		$output = '';
		foreach ($fields as $index => $indexedFields) {
			$output .= sprintf(
				'%s <strong>fulltext:%s AS foo</strong>',
				$this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.syntax_for_query'),
				$index
			);
			$output .= sprintf(
				'<br/>' . $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.indexed_fields') . '<br/>',
				$index,
				$indexedFields
			);
			$output = '<div style="margin-bottom: 10px">' . $output . '</div>';
		}
		return '<div>' . $output . '</div>';
	}

	/**
	 * Formats a message for the BE when no FULLTEXT index is found against a table.
	 *
	 * @return string
	 */
	protected function getMessageNoIndexFound() {
		$outputs = array();

		$string = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_index');
		$outputs[] = sprintf($string, $this->table);

		$tables = $this->analyser->getTables();
		if (!empty($tables)) {
			$listOfTables = implode(', ', $tables);
			$string = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.tables_list');
			$outputs[] = sprintf($string, $listOfTables);
		} else {
			$outputs[] = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_table_found');
		}
		return implode('<br>', $outputs);
	}
}
