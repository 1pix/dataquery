<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012-2015 Fabien Udriot (Cobweb) <fudriot@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Displays a custom field in the BE to check for fulltext indices and
 * provide useful hints.
 *
 * @author Fabien Udriot (Cobweb) <support@cobweb.ch>
 * @author Francois Suter (Cobweb) <support@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class Tx_Dataquery_Userfunc_FormEngine {

	/**
	 * @var Tx_Dataquery_Utility_DatabaseAnalyser
	 */
	protected $analyser;

	/**
	 * @var tx_dataquery_sqlparser
	 */
	protected $sqlParser;

	/**
	 * @var language
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
		$this->analyser = t3lib_div::makeInstance('Tx_Dataquery_Utility_DatabaseAnalyser');
		$this->sqlParser = t3lib_div::makeInstance('tx_dataquery_sqlparser');
	}

	/**
	 * This method format a message regarding FULLTEXT indexes in the database towards a BE user.
	 *
	 * @param array $parameters Properties of the field being modified
	 * @param t3lib_TCEforms $parentObject Back-reference to the calling object
	 * @return string
	 */
	public function renderFulltextIndices($parameters, t3lib_TCEforms $parentObject) {
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
			catch (Exception $e) {
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

		$string = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_index');
		$outputs[] = sprintf($string, $this->table);

		$tables = $this->analyser->getTables();
		if (!empty($tables)) {
			$listOfTables = implode(', ', $tables);
			$string = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.tables_list');
			$outputs[] = sprintf($string, $listOfTables);
		} else {
			$outputs = $this->language->sL('LLL:EXT:dataquery/locallang_db.xlf:fulltext.no_table_found');
		}
		return implode('<br>', $outputs);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['dataquery/Classes/Userfunc/FormEngine.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['dataquery/Classes/Userfunc/FormEngine.php']);
}
?>