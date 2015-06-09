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
use Cobweb\Overlays\OverlayEngine;

/**
 * SQL parser class for extension "dataquery"
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class SqlParser {
	/**
	 * @var	array	List of all the main keywords accepted in the query
	 */
	static protected $tokens = array('INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'WHERE', 'GROUP BY', 'ORDER BY', 'LIMIT', 'OFFSET', 'MERGED');

	/**
	 * @var \Tesseract\Dataquery\Utility\QueryObject Structured type containing the parts of the parsed query
	 */
	protected $queryObject;

	/**
	 *
	 * @var	integer	Number of SQL function calls inside SELECT statement
	 */
	protected $numFunctions = 0;

	/**
	 * Parses a SQL query and extract structured information about it.
	 *
	 * @param string $query The SQL to parse
	 * @throws InvalidQueryException
	 * @return \Tesseract\Dataquery\Utility\QueryObject An object containing the parsed query information
	 */
	public function parseSQL($query) {
		$this->queryObject = GeneralUtility::makeInstance('Tesseract\\Dataquery\\Utility\\QueryObject');

		// First find the start of the SELECT statement
		$selectPosition = stripos($query, 'SELECT');
		if ($selectPosition === FALSE) {
			throw new InvalidQueryException('Missing SELECT keyword', 1272556228);
		}
		// Next find the position of the last FROM keyword
		// There may be more than one FROM keyword when some functions are used
		// (example: EXTRACT(YEAR FROM tstamp))
		// NOTE: sub-selects are not supported, but these could be a source
		// of additional FROMs
		$queryParts = preg_split('/\bFROM\b/', $query);
		// If the query was not split, FROM keyword is missing
		if (count($queryParts) == 1) {
			throw new InvalidQueryException('Missing FROM keyword', 1272556601);
		}
		$afterLastFrom = array_pop($queryParts);

		// Everything before the last FROM is the SELECT part
		// This is parsed last as we need information about any table aliases used in the query first
		$selectPart = implode(' FROM ', $queryParts);
		$selectedFields = trim(substr($selectPart, $selectPosition + 6));

		// Get all parts of the query after SELECT ... FROM, using the SQL keywords as tokens
		// The returned matches array contains the keywords matched (in position 2) and the string after each keyword (in position 3)
		$regexp = '/(' . implode('|', self::$tokens) . ')/';
		$matches = preg_split($regexp, $afterLastFrom, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
//\TYPO3\CMS\Core\Utility\DebugUtility::debug($regexp);
//\TYPO3\CMS\Core\Utility\DebugUtility::debug($query);
//\TYPO3\CMS\Core\Utility\DebugUtility::debug($matches, 'Matches');

		// The first position is the string that followed the main FROM keyword
		// Parse that information. It's important to do this first,
		// as we need to know the query' main table for later
		$fromPart = array_shift($matches);
		// NOTE: this may throw an Exception, but we let it bubble up
		$this->parseFromStatement($fromPart);

		// Fill the structure array, as suited for each keyword
		$i = 0;
		$numMatches = count($matches);
		while ($i < $numMatches) {
			$keyword = $matches[$i];
			$i++;
			$value = $matches[$i];
			$i++;
			switch ($keyword) {
				case 'INNER JOIN':
				case 'LEFT JOIN':
				case 'RIGHT JOIN':
					// Extract the JOIN type (INNER, LEFT or RIGHT)
					$joinType = strtolower(substr($keyword, 0, strpos($keyword,'JOIN') - 1));
					$theJoin = array();
					$theJoin['type'] = $joinType;
					// Separate the table from the join condition
					$parts = preg_split('/\bON\b/', $value);
					// Separate an alias from the table name
					$moreParts = GeneralUtility::trimExplode('AS', $parts[0]);
					$theJoin['table'] = trim($moreParts[0]);
					if (count($moreParts) > 1) {
						$theJoin['alias'] = trim($moreParts[1]);
					}
					else {
						$theJoin['alias'] = $theJoin['table'];
					}
					$this->queryObject->subtables[] = $theJoin['alias'];
					$this->queryObject->aliases[$theJoin['alias']] = $theJoin['table'];
					// Handle the "ON" part which may contain the non-SQL keyword "MAX"
					// This keyword is not used in the SQL query, but is an indication to the wrapper that
					// we want only a single record from this join
					if (count($parts) > 1) {
						$moreParts = GeneralUtility::trimExplode('MAX', $parts[1]);
						$theJoin['on'] = trim($moreParts[0]);
						if (count($moreParts) > 1) {
							$theJoin['limit'] = $moreParts[1];
						}
					}
					else {
						$theJoin['on'] = '';
					}
					if (!isset($this->queryObject->structure['JOIN'])) $this->queryObject->structure['JOIN'] = array();
					$this->queryObject->structure['JOIN'][$theJoin['alias']] = $theJoin;
					break;
				case 'WHERE':
					$this->queryObject->structure[$keyword][] = trim($value);
					break;
				case 'ORDER BY':
				case 'GROUP BY':
					$orderParts = explode(',', $value);
					foreach ($orderParts as $part) {
						$thePart = trim($part);
						$this->queryObject->structure[$keyword][] = $thePart;
							// In case of ORDER BY, perform additional operation to get field name and sort order separately
						if ($keyword == 'ORDER BY') {
							$finerParts = preg_split('/\s/', $thePart, -1, PREG_SPLIT_NO_EMPTY);
							$orderField = $finerParts[0];
							$orderSort = (isset($finerParts[1])) ? $finerParts[1] : 'ASC';
							$this->queryObject->orderFields[] = array('field' => $orderField, 'order' => $orderSort);
						}

					}
					break;
				case 'LIMIT':
					if (strpos($value, ',') !== FALSE) {
						$limitParts = GeneralUtility::trimExplode(',', $value, TRUE);
						$this->queryObject->structure['OFFSET'] = intval($limitParts[0]);
						$this->queryObject->structure[$keyword] = intval($limitParts[1]);
					} else {
						$this->queryObject->structure[$keyword] = intval($value);
					}
					break;
				case 'OFFSET':
					$this->queryObject->structure[$keyword] = intval($value);
					break;
			}
		}
		// Free some memory
		unset($matches);

		// Parse the SELECT part
		$this->parseSelectStatement($selectedFields);

		// Return the object containing the parsed query
		return $this->queryObject;
	}

	/**
	 * Parses the SELECT part of the statement and isolates each field in the selection.
	 *
	 * @param string $select The beginning of the SQL statement, between SELECT and FROM (both excluded)
	 * @throws InvalidQueryException
	 * @return void
	 */
	public function parseSelectStatement($select) {
		if (empty($select)) {
			throw new InvalidQueryException('Nothing SELECTed', 1280323976);
		}

		// Parse the SELECT part
		// First, check if the select string starts with "DISTINCT"
		// If yes, remove that and set the distinct flag to true
		$distinctPosition = strpos($select, 'DISTINCT');
		if ($distinctPosition === 0) {
			$this->queryObject->structure['DISTINCT'] = TRUE;
			$croppedString = substr($select, 8);
			$select = trim($croppedString);
		}
		// Next, parse the rest of the string character by character
		$stringLenth = strlen($select);
		$openBrackets = 0;
		$lastBracketPosition = 0;
		$currentField = '';
		$currentPosition = 0;
		$hasFunctionCall = FALSE;
		$hasWildcard = FALSE;
		for ($i = 0; $i < $stringLenth; $i++) {
			// Get the current character
			$character = $select[$i];
			// Count the position inside the current field
			// This is reset for each new field found
			$currentPosition++;
			switch ($character) {
				// An open bracket is the sign of a function call
				// Functions may be nested, so we count the number of open brackets
				case '(':
					$currentField .= $character;
					$openBrackets++;
					$hasFunctionCall = TRUE;
					break;

				// Decrease the open bracket count
				case ')':
					$currentField .= $character;
					$openBrackets--;
					// Store position of closing bracket (minus one), as we need the position
					// of the last one later for further processing
					$lastBracketPosition = $currentPosition - 1;
					break;

				// If the wildcard character appears outside of function calls,
				// take it into consideration. Otherwise not (it might be COUNT(*) for example)
				case '*':
					$currentField .= $character;
					if (!$hasFunctionCall) {
						$hasWildcard = TRUE;
					}
					break;

				// A comma indicates that we have reached the end of a field,
				// unless there are open brackets, in which case the comma is
				// a separator of function arguments
				case ',':
					// We are at the end of a field: add it to the list of fields
					// and reset some values
					if ($openBrackets == 0) {
						$this->parseSelectField(trim($currentField), $lastBracketPosition, $hasFunctionCall, $hasWildcard);
						$currentField = '';
						$hasFunctionCall = FALSE;
						$hasWildcard = FALSE;
						$currentPosition = 0;
						$lastBracketPosition = 0;

					// We're inside a function, keep the comma and keep the current character
					} else {
						$currentField .= $character;
					}
					break;

				// Nothing special, just add the current character to the current field's name
				default:
					$currentField .= $character;
					break;
			}
		}
		// Upon exit from the loop, save the last field found,
		// except if there's still an open bracket, in which case we have a syntax error
		if ($openBrackets > 0) {
			throw new InvalidQueryException('Bad SQL syntax, opening and closing brackets are not balanced', 1272954424);
		} else {
			$this->parseSelectField(trim($currentField), $lastBracketPosition, $hasFunctionCall, $hasWildcard);
		}
	}

	/**
	 * Parses one field from the SELECT part of the SQL query and analyzes its content.
	 *
	 * In particular it will expand the "*" wildcard to include
	 * all fields. It also keeps tracks of field aliases.
	 *
	 * @param string $fieldString The string to parse
	 * @param integer $lastBracketPosition The position of the last closing bracket in the string, if any
	 * @param boolean $hasFunctionCall True if a SQL function call was detected in the string
	 * @param boolean $hasWildcard Ttrue if the wildcard character (*) was detected in the string
	 * @return void
	 */
	protected function parseSelectField($fieldString, $lastBracketPosition = 0, $hasFunctionCall = FALSE, $hasWildcard = FALSE) {
		// Exit early if field string is empty
		if (empty($fieldString)) {
			return;
		}

		// If the string is just * (or possibly table.*), get all the fields for the table
		if ($hasWildcard) {
			// It's only *, set table as main table
			if ($fieldString === '*') {
				$table = $this->queryObject->mainTable;
				$alias = $table;

			// It's table.*, extract table name
			} else {
				$fieldParts = GeneralUtility::trimExplode('.', $fieldString, 1);
				$table = (isset($this->queryObject->aliases[$fieldParts[0]]) ? $this->queryObject->aliases[$fieldParts[0]] : $fieldParts[0]);
				$alias = $fieldParts[0];
			}
			if (!isset($this->queryObject->hasBaseFields[$alias])) {
				$this->queryObject->hasBaseFields[$alias] = array('uid' => FALSE, 'pid' => FALSE);
			}
			// Get all fields for the given table
			$fieldInfo = OverlayEngine::getAllFieldsForTable($table);
			$fields = array_keys($fieldInfo);
			// Add all fields to the query structure
			foreach ($fields as $aField) {
				if ($aField == 'uid') {
					$this->queryObject->hasBaseFields[$alias]['uid'] = TRUE;
				} elseif ($aField == 'pid') {
					$this->queryObject->hasBaseFields[$alias]['pid'] = TRUE;
				}
				$this->queryObject->structure['SELECT'][] = array(
					'table' => $table,
					'tableAlias' => $alias,
					'field' => $aField,
					'fieldAlias' => '',
					'function' => FALSE
				);
			}

		// Else, the field is some string, analyse it
		} else {

			// If there's an alias, extract it and continue parsing
			// An alias is indicated by a "AS" keyword after the last closing bracket if any
			// (brackets indicate a function call and there might be "AS" keywords inside them)
			$fieldAlias = '';
			if ($lastBracketPosition > strlen($fieldString)) {
			    $asPosition = FALSE;
			} else {
			    $asPosition = strpos($fieldString, ' AS ', $lastBracketPosition);
			}
			if ($asPosition !== FALSE) {
				$fieldAlias = trim(substr($fieldString, $asPosition + 4));
				$fieldString = trim(substr($fieldString, 0, $asPosition));
			}
			if ($hasFunctionCall) {
				$this->numFunctions++;
				$alias = $this->queryObject->mainTable;
				$table = (isset($this->queryObject->aliases[$alias]) ? $this->queryObject->aliases[$alias] : $alias);
				$field = $fieldString;
				// Function calls need aliases
				// If none was given, define one
				if (empty($fieldAlias)) {
					$fieldAlias = 'function_' . $this->numFunctions;
				}

			// There's no function call
			} else {

				// If there's a dot, get table name
				if (stristr($fieldString, '.')) {
					$fieldParts = GeneralUtility::trimExplode('.', $fieldString, 1);
					$table = (isset($this->queryObject->aliases[$fieldParts[0]]) ? $this->queryObject->aliases[$fieldParts[0]] : $fieldParts[0]);
					$alias = $fieldParts[0];
					$field = $fieldParts[1];

				// No dot, the table is the main one
				} else {
					$alias = $this->queryObject->mainTable;
					$table = (isset($this->queryObject->aliases[$alias]) ? $this->queryObject->aliases[$alias] : $alias);
					$field = $fieldString;
				}
			}

			// For fulltext search, create placeholder which is replaced later with the full MATCH() statement
			// (if necessary)
			if (strpos($field, 'fulltext:') !== FALSE || strpos($field, 'fulltext_natural:') !== FALSE) {
				$fulltextSearchParts = explode(':', $field);
				$field = 'fulltext.' . $fulltextSearchParts[1];
				// Create placeholder entry (to be filled later)
				// If no fulltext value is entered or the table has no fulltext index, the dummy value "1" will be used,
				// which is neutral to the query.
				$this->queryObject->fulltextSearchPlaceholders[$table . '.' . $field] = '1';
			}

			// Set the appropriate flag if the field is uid or pid
			// Initialize first, if not yet done
			if (!isset($this->queryObject->hasBaseFields[$alias])) {
				$this->queryObject->hasBaseFields[$alias] = array('uid' => FALSE, 'pid' => FALSE);
			}
			if ((empty($fieldAlias) && $field == 'uid') || (!empty($fieldAlias) && $fieldAlias == 'uid')) {
				$this->queryObject->hasBaseFields[$alias]['uid'] = TRUE;
			} elseif ((empty($fieldAlias) && $field == 'pid') || (!empty($fieldAlias) && $fieldAlias == 'pid')) {
				$this->queryObject->hasBaseFields[$alias]['pid'] = TRUE;
			}
			// Add field's information to query structure
			$this->queryObject->structure['SELECT'][] = array(
				'table' => $table,
				'tableAlias' => $alias,
				'field' => $field,
				'fieldAlias' => $fieldAlias,
				'function' => $hasFunctionCall
			);

			// If there's an alias for the field, store it in a separate array, for later use
			if (!empty($fieldAlias)) {
				if (!isset($this->queryObject->fieldAliases[$alias])) {
					$this->queryObject->fieldAliases[$alias] = array();
				}
				$this->queryObject->fieldAliases[$alias][$field] = $fieldAlias;
				// Keep track of which field the alias is related to
				// (this is used by the parser to map alias used in filters)
				// If the alias is related to a function, we store the function syntax as is,
				// otherwise we map the alias to the syntax table.field
				if ($hasFunctionCall) {
					$this->queryObject->fieldAliasMappings[$fieldAlias] = $field;
				} else {
					$this->queryObject->fieldAliasMappings[$fieldAlias] = $table . '.' . $field;
				}
			}
		}
	}

	/**
	 * Parses the FROM statement of the query,
	 * which may be comprised of a comma-separated list of tables.
	 *
	 * @param string $from The FROM statement
	 * @throws InvalidQueryException
	 * @return void
	 */
	public function parseFromStatement($from) {
		$fromTables = GeneralUtility::trimExplode(',', $from, TRUE);
		$numTables = count($fromTables);
		// If there's nothing in the string, thrown an exception
		if ($numTables == 0) {
			throw new InvalidQueryException('No table defined in query (FROM).', 1280323639);
		}

		for ($i = 0; $i < $numTables; $i++) {
			$tableName = $fromTables[$i];
			$tableAlias = $tableName;
			if (strpos($fromTables[$i], ' AS ') !== FALSE) {
				$tableParts = GeneralUtility::trimExplode(' AS ', $fromTables[$i], TRUE);
				$tableName = $tableParts[0];
				$tableAlias = $tableParts[1];
			}
			// Consider the first table to be the main table of the query,
			// i.e. the table to which all others are JOINed
			if ($i == 0) {
				$this->queryObject->structure['FROM']['table'] = $tableName;
				$this->queryObject->structure['FROM']['alias'] = $tableAlias;
				$this->queryObject->mainTable = $tableAlias;

			// Each further table in the FROM statement is registered
			// as being INNER JOINed
			} else {
				$this->queryObject->structure['JOIN'][$tableAlias] = array(
					'type' => 'inner',
					'table' => $tableName,
					'alias' => $tableAlias,
					'on' => ''
				);
				$this->queryObject->subtables[] = $tableAlias;
			}
			$this->queryObject->aliases[$tableAlias] = $tableName;
		}
	}
}
