<?php
namespace Tesseract\Dataquery\Utility;

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

use Tesseract\Dataquery\Parser\FulltextParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class containing some utility SQL methods.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
final class SqlUtility
{
    /**
     * @var FulltextParser Local instance of full text parser utility
     */
    static protected $fulltextParser = null;

    /**
     * Transforms a condition transmitted by data-filter to a real SQL segment.
     *
     * @throws \Tesseract\Tesseract\Exception\Exception
     * @param string $field
     * @param string $table
     * @param array $conditionData
     *              + operator: andgroup, orgroup, like, start, fulltext
     *              + value: the value given as input
     *              + negate: negate the expression
     * @return string
     */
    static public function conditionToSql($field, $table, $conditionData)
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = $GLOBALS['TYPO3_DB'];

        $condition = '';
        // If the value is special value "\all", all values must be taken,
        // so the condition is simply ignored
        if ($conditionData['value'] !== '\all') {
            // Some operators require a bit more handling
            // "in" values just need to be put within brackets
            if ($conditionData['operator'] === 'in') {
                // If the condition value is an array, use it as is
                // Otherwise assume a comma-separated list of values and explode it
                $conditionParts = $conditionData['value'];
                if (!is_array($conditionParts)) {
                    $conditionParts = GeneralUtility::trimExplode(',', $conditionData['value'], true);
                }
                $escapedParts = array();
                foreach ($conditionParts as $value) {
                    $escapedParts[] = $databaseConnection->fullQuoteStr($value, $table);
                }
                $condition = $field . (($conditionData['negate']) ? ' NOT' : '') . ' IN (' . implode(',',
                                $escapedParts) . ')';

                // "andgroup" and "orgroup" require more handling
                // The associated value is a list of comma-separated values and each of these values must be handled separately
                // Furthermore each value will be tested against a comma-separated list of values too, so the test is not so simple
            } elseif ($conditionData['operator'] === 'andgroup' || $conditionData['operator'] === 'orgroup') {
                // If the condition value is an array, use it as is
                // Otherwise assume a comma-separated list of values and explode it
                $values = $conditionData['value'];
                if (!is_array($values)) {
                    $values = GeneralUtility::trimExplode(',', $conditionData['value'], true);
                }
                $condition = '';
                $localOperator = 'OR';
                if ($conditionData['operator'] === 'andgroup') {
                    $localOperator = 'AND';
                }
                foreach ($values as $aValue) {
                    if (!empty($condition)) {
                        $condition .= ' ' . $localOperator . ' ';
                    }
                    $condition .= $databaseConnection->listQuery($field, $aValue, $table);
                }
                if ($conditionData['negate']) {
                    $condition = 'NOT (' . $condition . ')';
                }

                // If the operator is "like", "start" or "end", the SQL operator is always LIKE, but different wildcards are used
            } elseif ($conditionData['operator'] === 'like' || $conditionData['operator'] === 'start' || $conditionData['operator'] === 'end') {
                // Make sure values are an array
                $values = $conditionData['value'];
                if (!is_array($values)) {
                    $values = array($conditionData['value']);
                }
                // Loop on each value and assemble condition
                $condition = '';
                foreach ($values as $aValue) {
                    $aValue = $databaseConnection->escapeStrForLike($aValue, $table);
                    if (!empty($condition)) {
                        $condition .= ' OR ';
                    }
                    if ($conditionData['operator'] === 'start') {
                        $value = $aValue . '%';
                    } elseif ($conditionData['operator'] === 'end') {
                        $value = '%' . $aValue;
                    } else {
                        $value = '%' . $aValue . '%';
                    }
                    $condition .= $field . ' LIKE ' . $databaseConnection->fullQuoteStr($value, $table);
                }
                if ($conditionData['negate']) {
                    $condition = 'NOT (' . $condition . ')';
                }

                // Operator "fulltext" requires some special care, as a full MATCH() condition must be assembled
            } elseif ($conditionData['operator'] === 'fulltext' || $conditionData['operator'] === 'fulltext_natural') {
                $fulltextParser = self::getFulltextParserInstance();
                $fulltextParts = explode('.', $field);
                $condition = $fulltextParser->parse(
                        $table,
                        $fulltextParts[2],
                        $conditionData['value'],
                        ($conditionData['operator'] === 'fulltext_natural'),
                        $conditionData['negate']
                );

                // Other operators are handled simply
                // We just need to take care of special values: "\empty" and "\null"
            } else {
                $operator = $conditionData['operator'];
                // Make sure values are an array
                $values = $conditionData['value'];
                if (!is_array($values)) {
                    $values = array($conditionData['value']);
                }
                // Loop on each value and assemble condition
                $condition = '';
                foreach ($values as $aValue) {
                    if (!empty($condition)) {
                        $condition .= ' OR ';
                    }
                    // Special value "\empty" means evaluation against empty string
                    if ($conditionData['value'] === '\empty') {
                        $quotedValue = "''";

                        // Special value "\null" means evaluation against IS NULL or IS NOT NULL
                    } elseif ($conditionData['value'] === '\null') {
                        if ($operator === '=') {
                            $operator = 'IS';
                        }
                        $quotedValue = 'NULL';

                        // Normal value
                    } else {
                        $quotedValue = $databaseConnection->fullQuoteStr($aValue, $table);
                    }
                    $condition .= $field . ' ' . $operator . ' ' . $quotedValue;
                }
                if ($conditionData['negate']) {
                    $condition = 'NOT (' . $condition . ')';
                }
            }
        }
        return $condition;
    }

    /**
     * Returns an instance of Tx_Dataquery_Parser_Fulltext, which is created on demand.
     *
     * @return FulltextParser
     */
    static public function getFulltextParserInstance()
    {
        if (self::$fulltextParser === null) {
            self::$fulltextParser = GeneralUtility::makeInstance(
                    FulltextParser::class
            );
        }
        return self::$fulltextParser;
    }

    /**
     * Sets the fulltext parser instance.
     *
     * This is used for unit tests.
     *
     * @param FulltextParser $fulltextParser
     */
    static public function setFulltextParserInstance($fulltextParser)
    {
        self::$fulltextParser = $fulltextParser;
    }
}
