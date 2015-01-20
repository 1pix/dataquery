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
 * Provides routine to manipulate a query adding a fulltext segment in the query.
 *
 * @author Fabien Udriot (Cobweb) <support@cobweb.ch>
 * @author Francois Suter (Cobweb) <support@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class Tx_Dataquery_Parser_Fulltext {

	/**
	 * @var string
	 */
	protected $searchTerms = array();

	/**
	 * @var Tx_Dataquery_Utility_DatabaseAnalyser
	 */
	protected $analyser;

	/**
	 * @var array
	 */
	protected $indexedFields = array();

	/**
	 * Unserialized extension configuration
	 * @var array
	 */
	protected $configuration;

	/**
	 * Constructor
	 *
	 * @param string $tableName: the main table name
	 * @return Tx_Dataquery_Parser_Fulltext
	 */
	public function __construct() {

		/** @var $analyser Tx_Dataquery_Utility_DatabaseAnalyser */
		$analyser = t3lib_div::makeInstance('Tx_Dataquery_Utility_DatabaseAnalyser');
		$this->setAnalyser($analyser);
		$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dataquery']);
	}

	/**
	 * Retrieves full-text index fields for a given table.
	 *
	 * @param string $tableName
	 */
	protected function retrieveIndexedFields($tableName) {
		$this->indexedFields = $this->analyser->getFields($tableName);
	}

	/**
	 * Sets the analyser.
	 *
	 * Useful for unit tests.
	 *
	 * @param Tx_Dataquery_Utility_DatabaseAnalyser $analyser
	 */
	public function setAnalyser($analyser) {
		$this->analyser = $analyser;
	}

	/**
	 * Sets the extension configuration.
	 *
	 * Useful for unit tests.
	 *
	 * @param array $configuration Extension configuration
	 * @return void
	 */
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Parses the query. If a placeholder "fulltext:foo" is found, then replace with a MATCH / AGAINST expression.
	 *
	 * @param string $table Name of the table to search
	 * @param string $index Name of the fulltext index to use
	 * @param string $search Search string
	 * @param boolean $isNaturalSearch TRUE if fulltext search should be in natural mode
	 * @param boolean $isNegated TRUE if condition should be negated
	 * @return string SQL MATCH() condition
	 * @throws tx_tesseract_exception
	 */
	public function parse($table, $index, $search, $isNaturalSearch, $isNegated) {
		$this->retrieveIndexedFields($table);
		if (isset($this->indexedFields[$index])) {
			$indexFields = $this->indexedFields[$index];
		} else {
			throw new tx_tesseract_exception(
				sprintf('Table %s has no index "%s"', $table, $index),
				1421769189
			);
		}
		// Search terms from a query string will be urlencode'd
		$processedSearchTerms = urldecode($search);
		$booleanMode = '';
		if (!$isNaturalSearch) {
			$processedSearchTerms = $this->processSearchTerm($processedSearchTerms);
			$booleanMode = ' IN BOOLEAN MODE';
		}
		if (empty($processedSearchTerms)) {
			throw new tx_tesseract_exception(
				'Empty fulltext search condition',
				1423068811
			);
		}
		$baseCondition = "MATCH(%s) AGAINST('%s'%s)";
		if ($isNegated) {
			$baseCondition = 'NOT ' . $baseCondition;
		}
		$condition = sprintf($baseCondition, $indexFields, $processedSearchTerms, $booleanMode);
		return $condition;
	}

	/**
	 * Processes the search term.
	 *
	 * @param string $term Search term
	 * @return string
	 */
	public function processSearchTerm($term) {

		$termsProcessed = array();

		// Handle double quote wrapping
		if (preg_match_all('/".+"/isU', $term, $matches)) {

			foreach ($matches as $match) {
				$searchedCharacters = array(
					'"',
					' '
				);
				$replacedCharacters = array(
					'',
					'###'
				);
				$search = $match;
				$replace = str_replace($searchedCharacters, $replacedCharacters, $match);
				$term = str_replace($search, $replace, $term);
			}
		}

		$terms = explode(' ', $term);
		foreach ($terms as $term) {
			if (!empty($term)) {
				// Handle exclusion of term
				$logic = '+';
				if (substr($term, 0, 1) == '-') {
					$term = substr($term, 1);
					$logic = '-';
				}
				if (strlen($term) >= $this->configuration['fullTextMinimumWordLength']) {
					$termProcessed = str_replace('###', ' ', addslashes($term));
					$termsProcessed[] = sprintf('%s"%s"', $logic, $termProcessed);
				}
			}
		}
		return implode(' ', $termsProcessed);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['dataquery/Classes/Parser/Fulltext.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['dataquery/Classes/Parser/Fulltext.php']);
}
?>