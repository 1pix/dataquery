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
use Tesseract\Dataquery\Utility\DatabaseAnalyser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides routine to manipulate a query adding a fulltext segment in the query.
 *
 * @author Fabien Udriot (Cobweb) <support@cobweb.ch>
 * @author Francois Suter (Cobweb) <support@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class FulltextParser
{

    /**
     * @var string
     */
    protected $searchTerms = array();

    /**
     * @var DatabaseAnalyser
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
     * @var array List of allowed fulltext operators (see http://dev.mysql.com/doc/refman/5.6/en/fulltext-boolean.html)
     */
    static protected $fullTextOperators = array('+', '-', '~', '>', '<');

    /**
     * Constructor
     *
     * @return FulltextParser
     */
    public function __construct()
    {

        /** @var $analyser DatabaseAnalyser */
        $analyser = GeneralUtility::makeInstance(
                DatabaseAnalyser::class
    );
        $this->setAnalyser($analyser);
        $this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dataquery']);
    }

    /**
     * Retrieves full-text index fields for a given table.
     *
     * @param string $tableName
     */
    protected function retrieveIndexedFields($tableName)
    {
        $this->indexedFields = $this->analyser->getFields($tableName);
    }

    /**
     * Sets the analyser.
     *
     * Useful for unit tests.
     *
     * @param DatabaseAnalyser $analyser
     */
    public function setAnalyser($analyser)
    {
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
    public function setConfiguration($configuration)
    {
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
    public function parse($table, $index, $search, $isNaturalSearch, $isNegated)
    {
        $this->retrieveIndexedFields($table);
        if (array_key_exists($index, $this->indexedFields)) {
            $indexFields = $this->indexedFields[$index];
        } else {
            throw new InvalidQueryException(
                    sprintf('Table %s has no index "%s"', $table, $index),
                    1421769189
            );
        }
        $booleanMode = '';
        if ($isNaturalSearch) {
            $processedSearchTerms = addslashes($search);
        } else {
            $processedSearchTerms = $this->processSearchTerm($search);
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
    public function processSearchTerm($term)
    {

        $termsProcessed = array();

        // Handle double quote wrapping
        // Take all double-quoted strings and replace them with a ###EXTRACTED(number)### construct
        // These terms are not processed further
        // Terms within brackets are also not handled further
        $searches = array();
        $replacements = array();
        if (preg_match_all('/["(].+[")]/isU', $term, $matches)) {
            $counter = 1;
            foreach ($matches as $match) {
                $searches[] = $match[0];
                $replacements[] = '###EXTRACTED' . $counter . '###';
                $counter++;
            }
            $term = str_replace($searches, $replacements, $term);
        }

        // Now that double-quoted and brackets-wrapped strings have been extracted,
        // get each search term by splitting on spaces
        $terms = explode(' ', $term);
        foreach ($terms as $aTerm) {
            // Take extracted strings as is
            if (strpos($aTerm, '###EXTRACTED') === 0) {
                $termsProcessed[] = $aTerm;
            } elseif (!empty($aTerm)) {
                $operator = substr($aTerm, 0, 1);
                $wildcard = substr($aTerm, -1);
                if (in_array($operator, self::$fullTextOperators, true)) {
                    $aTerm = substr($aTerm, 1);
                } else {
                    $operator = '';
                }
                if ($wildcard === '*') {
                    $aTerm = substr($aTerm, 0, -1);
                } else {
                    $wildcard = '';
                }
                // Eliminate search terms which are too short (except if wildcard is used)
                if ($wildcard === '*' || strlen($aTerm) >= $this->configuration['fullTextMinimumWordLength']) {
                    $termsProcessed[] = $operator . addslashes($aTerm) . $wildcard;
                }
            }
        }
        // Assemble the processed string
        $processedSearchString = implode(' ', $termsProcessed);
        // If double-quoted or brackets-wrapped terms had been extracted, put them back
        if (count($searches) > 0) {
            // Escape every string before replacing it again
            $searches = array_map('addslashes', $searches);
            $processedSearchString = str_replace($replacements, $searches, $processedSearchString);
        }
        return $processedSearchString;
    }
}
