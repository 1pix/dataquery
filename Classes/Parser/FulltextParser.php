<?php
namespace Tesseract\Dataquery\Parser;

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

use Tesseract\Dataquery\Exception\InvalidQueryException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides routine to manipulate a query adding a fulltext segment in the query.
 *
 * @author Fabien Udriot (Cobweb) <support@cobweb.ch>
 * @author Francois Suter (Cobweb) <support@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class FulltextParser {

	/**
	 * @var string
	 */
	protected $searchTerms = array();

	/**
	 * @var \Tesseract\Dataquery\Utility\DatabaseAnalyser
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
	 * @return FulltextParser
	 */
	public function __construct() {

		/** @var $analyser \Tesseract\Dataquery\Utility\DatabaseAnalyser */
		$analyser = GeneralUtility::makeInstance('Tesseract\\Dataquery\\Utility\\DatabaseAnalyser');
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
	 * @param \Tesseract\Dataquery\Utility\DatabaseAnalyser $analyser
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
	 * @throws InvalidQueryException
	 */
	public function parse($table, $index, $search, $isNaturalSearch, $isNegated) {
		$this->retrieveIndexedFields($table);
		if (isset($this->indexedFields[$index])) {
			$indexFields = $this->indexedFields[$index];
		} else {
			throw new InvalidQueryException(
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
			throw new InvalidQueryException(
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
