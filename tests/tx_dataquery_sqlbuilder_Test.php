<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-2015 Francois Suter <typo3@cobweb.ch>
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
 * Testcase for the Data Query query builder
 *
 * @author		Francois Suter <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_dataquery
 */
abstract class tx_dataquery_sqlbuilder_Test extends tx_phpunit_testcase {

	/**
	 * @var	string	Base SQL condition to apply to tt_content table
	 */
	protected $baseConditionForTable = '###MINIMAL_CONDITION### AND ###TABLE###.hidden=0 AND ###TABLE###.starttime<=###NOW### AND (###TABLE###.endtime=0 OR ###TABLE###.endtime>###NOW###)###GROUP_CONDITION###';

	/**
	 * @var string Absolute minimal condition applied to all TYPO3 requests, even in workspaces
	 */
	protected $minimalConditionForTable = '###TABLE###.deleted=0 AND ###TABLE###.t3ver_state<=0 AND ###TABLE###.pid<>-1';

	/**
	 * @var string Condition on user groups found inside the base condition
	 */
	protected $groupsConditionForTable = ' AND (###TABLE###.fe_group=\'\' OR ###TABLE###.fe_group IS NULL OR ###TABLE###.fe_group=\'0\' OR FIND_IN_SET(\'0\',###TABLE###.fe_group) OR FIND_IN_SET(\'-1\',###TABLE###.fe_group))';

	/**
	 * @var	string	Language-related SQL condition to apply to tt_content table
	 */
	protected $baseLanguageConditionForTable = '(###TABLE###.sys_language_uid IN (0,-1))';

	/**
	 * @var	string	Base workspace-related SQL condition to apply to tt_content table
	 */
	protected $baseWorkspaceConditionForTable = '(###TABLE###.t3ver_oid = \'0\') ';

	/**
	 * @var	string	Additional workspace-related SQL condition to apply to tt_content table
	 */
	protected $additionalWorkspaceConditionForTable = '';

	/**
	 * @var	string	Full SQL condition (for tt_content) to apply to all queries. Will be based on the above components.
	 */
	protected $fullConditionForTable = '(###BASE_CONDITION###) AND ###LANGUAGE_CONDITION### AND ###WORKSPACE_CONDITION###';

	/**
	 * @var string Full condition is different for pages table, because language handling is delegated to separate table
	 */
	protected $conditionForPagesTables = '(###BASE_CONDITION###) AND ###WORKSPACE_CONDITION###';

	/**
	 * @var	string	Full SQL condition except for languages
	 */
	protected $noLanguagesConditionForTable = '(###BASE_CONDITION###) AND ###WORKSPACE_CONDITION###';

	/**
	 * @var	array	some default data configuration from the record
	 */
	protected $settings;

	/**
	 * @var	array	fields that must be added to the SELECT clause in some conditions
	 */
	protected $additionalFields = array();

	/**
	 * @var Tx_Phpunit_Framework
	 */
	protected $testingFramework;

	/** @var tx_dataquery_parser */
	protected $sqlParser;

	/**
	 * Sets up the test environment.
	 *
	 * @return void
	 */
	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('tx_dataquery');
		$this->testingFramework->createFakeFrontEnd();

		$this->settings = array(
			'ignore_language_handling' => FALSE,
			'ignore_enable_fields' => 0,
			'ignore_time_for_tables' => '*',
			'ignore_disabled_for_tables' => '*',
			'ignore_fegroup_for_tables' => '*',
		);

		// Get a minimal instance of tx_dataquery_wrapper for passing to the parser as a back-reference
		/** @var $dataQueryWrapper tx_dataquery_wrapper */
		$dataQueryWrapper = t3lib_div::makeInstance('tx_dataquery_wrapper');
		/** @var $controller tx_displaycontroller */
		$controller = t3lib_div::makeInstance('tx_displaycontroller');
		$dataQueryWrapper->setController($controller);
		$this->sqlParser = t3lib_div::makeInstance('tx_dataquery_parser', $dataQueryWrapper);
	}

	/**
	 * Cleans up the test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/**
	 * Replaces all the markers found in the conditions.
	 *
	 * @param string $condition The condition to parse for markers
	 * @param string $table The name of the table to use (the default is tt_content, which is used in most tests)
	 * @return string The parsed condition
	 */
	public function finalizeCondition($condition, $table = 'tt_content') {
		$parsedCondition = $condition;
		// Replace the base condition marker
		$parsedCondition = str_replace(
			'###BASE_CONDITION###',
			$this->baseConditionForTable,
			$parsedCondition
		);
		// Replace the minimal condition marker (which may have been inside the ###BASE_CONDITION### marker)
		$parsedCondition = str_replace(
			'###MINIMAL_CONDITION###',
			$this->minimalConditionForTable,
			$parsedCondition
		);
		// Replace the group condition marker (which may have been inside the ###BASE_CONDITION### marker)
		$parsedCondition = str_replace(
			'###GROUP_CONDITION###',
			$this->groupsConditionForTable,
			$parsedCondition
		);
		// Replace the language condition marker
		$parsedCondition = str_replace(
			'###LANGUAGE_CONDITION###',
			$this->baseLanguageConditionForTable,
			$parsedCondition
		);
		// Replace the workspace condition marker
		$parsedCondition = str_replace(
			'###WORKSPACE_CONDITION###',
			$this->baseWorkspaceConditionForTable,
			$parsedCondition
		);
		// Replace the additional workspace condition marker
		$parsedCondition = str_replace(
			'###ADDITIONAL_WORKSPACE_CONDITION###',
			$this->additionalWorkspaceConditionForTable,
			$parsedCondition
		);
		// Replace table marker by table name
		$parsedCondition = str_replace(
			'###TABLE###',
			$table,
			$parsedCondition
		);
		// Replace time marker by time used for starttime and endtime enable fields
		// This is done last because it is "contained" in other markers
		$parsedCondition = str_replace(
			'###NOW###',
			$GLOBALS['SIM_ACCESS_TIME'],
			$parsedCondition
		);

		return $parsedCondition;
	}

	/**
	 * Parses and rebuilds a simple SELECT query.
	 *
	 * @test
	 */
	public function selectQuerySimple() {
		// Replace markers in the condition
		$condition = $this->finalizeCondition($this->fullConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $condition;

		$query = 'SELECT uid,header FROM tt_content';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Parses and rebuilds a simple SELECT query with an alias for the table name.
	 *
	 * @test
	 */
	public function selectQuerySimpleWithTableAlias() {
		// Replace markers in the condition
		$condition = $this->finalizeCondition($this->fullConditionForTable);
		// Replace table name by its alias
		$condition = str_replace('tt_content', 'c', $condition);
		$additionalSelectFields = $this->prepareAdditionalFields('c');
		$expectedResult = 'SELECT c.uid, c.header, c.pid, c.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS c WHERE ' . $condition;

		$query = 'SELECT uid,header FROM tt_content AS c';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Parses and rebuilds a SELECT query with an id list.
	 *
	 * @test
	 */
	public function selectQueryWithIdList() {
		// Replace markers in the condition
		$condition = $this->finalizeCondition($this->fullConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $condition. 'AND (tt_content.uid IN (1,12)) ';

		$query = 'SELECT uid,header FROM tt_content';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		// Add the id list
		// NOTE: "pages_3" is expected to be ignored, as the "pages" table is not being queried
		$this->sqlParser->addIdList('1,tt_content_12,pages_3');
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Parses and rebuilds a SELECT query with an id list.
	 *
	 * @test
	 */
	public function selectQueryWithUidAsAliasAndDistinct() {
		// Replace markers in the condition
		$condition = $this->finalizeCondition($this->noLanguagesConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT DISTINCT tt_content.CType AS uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $condition;

		$query = 'SELECT DISTINCT CType AS uid FROM tt_content';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Parses and rebuilds a SELECT query with RAND() ordering.
	 *
	 * RAND() ordering is not handled like other order conditions.
	 *
	 * @test
	 */
	public function selectQueryWithRand() {
		// Replace markers in the condition
		$condition = $this->finalizeCondition($this->fullConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $condition . 'ORDER BY RAND() ';

		$query = 'SELECT uid, header FROM tt_content ORDER BY RAND()';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Provides filters for testing query with filters.
	 *
	 * Some filters are arbitrarily negated, to test the building of negated conditions
	 * Also provides the expected interpretation of the filter.
	 *
	 * @return array
	 */
	public function filterProvider() {
		$filters = array(
			'like foo' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'header',
							'conditions' => array(
								0 => array(
									'operator' => 'like',
									'value' => array(
										'foo',
										'bar'
									),
									'negate' => FALSE
								)
							)
						),
					),
				),
				'condition' => '((tt_content.header LIKE \'%foo%\' OR tt_content.header LIKE \'%bar%\'))'
			),
			'interval' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'conditions' => array(
								0 => array(
									'operator' => '>',
									'value' => 10,
									'negate' => FALSE
								),
								1 => array(
									'operator' => '<=',
									'value' => 50,
									'negate' => FALSE
								)
							)
						),
					)
				),
				'condition' => '((tt_content.uid > \'10\') AND (tt_content.uid <= \'50\'))'
			),
			'not in' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'conditions' => array(
								0 => array(
									'operator' => 'in',
									'value' => array(1, 2, 3),
									'negate' => TRUE
								)
							)
						),
					)
				),
				'condition' => '((tt_content.uid NOT IN (\'1\',\'2\',\'3\')))'
			),
			'not orgroup' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'fe_group',
							'conditions' => array(
								0 => array(
									'operator' => 'orgroup',
									'value' => '1,2,3',
									'negate' => TRUE
								)
							)
						),
					)
				),
				'condition' => '((NOT (FIND_IN_SET(\'1\',tt_content.fe_group) OR FIND_IN_SET(\'2\',tt_content.fe_group) OR FIND_IN_SET(\'3\',tt_content.fe_group))))'
			),
			'combined with AND' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'header',
							'conditions' => array(
								0 => array(
									'operator' => 'like',
									'value' => array(
										'foo',
										'bar'
									),
									'negate' => FALSE
								)
							)
						),
						1 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'conditions' => array(
								0 => array(
									'operator' => '>',
									'value' => 10,
									'negate' => FALSE
								),
								1 => array(
									'operator' => '<=',
									'value' => 50,
									'negate' => FALSE
								)
							)
						)
					),
					'logicalOperator' => 'AND'
				),
				'condition' => '((tt_content.header LIKE \'%foo%\' OR tt_content.header LIKE \'%bar%\')) AND ((tt_content.uid > \'10\') AND (tt_content.uid <= \'50\'))'
			),
			'combined with OR' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'header',
							'conditions' => array(
								0 => array(
									'operator' => 'like',
									'value' => array(
										'foo',
										'bar'
									),
									'negate' => FALSE
								)
							)
						),
						1 => array(
							'table' => 'tt_content',
							'field' => 'uid',
							'conditions' => array(
								0 => array(
									'operator' => '>',
									'value' => 10,
									'negate' => FALSE
								),
								1 => array(
									'operator' => '<=',
									'value' => 50,
									'negate' => FALSE
								)
							)
						)
					),
					'logicalOperator' => 'OR'
				),
				'condition' => '((tt_content.header LIKE \'%foo%\' OR tt_content.header LIKE \'%bar%\')) OR ((tt_content.uid > \'10\') AND (tt_content.uid <= \'50\'))'
			),
			'filter on alias' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'year',
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => 2010,
									'negate' => FALSE
								)
							)
						)
					)
				),
				'condition' => '((FROM_UNIXTIME(tstamp, \'%Y\') = \'2010\'))'
			),
			'special value null' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'image',
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => '\null',
									'negate' => TRUE
								)
							)
						)
					)
				),
				'condition' => '((NOT (tt_content.image IS NULL)))'
			),
			'special value empty' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'header',
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => '\empty',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'condition' => '((tt_content.header = \'\'))'
			),
			// NOTE: a filter with "all" does not get applied (no matter the operator)
			'special value all' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'bodytext',
							'conditions' => array(
								0 => array(
									'operator' => '=',
									'value' => '\all',
									'negate' => FALSE
								),
								1 => array(
									'operator' => 'like',
									'value' => '\all',
									'negate' => FALSE
								),
								2 => array(
									'operator' => 'in',
									'value' => '\all',
									'negate' => FALSE
								),
								3 => array(
									'operator' => 'andgroup',
									'value' => '\all',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'condition' => ''
			),
			// NOTE: void filters do not get applied
			'void filter' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'month',
							'void' => TRUE,
							'conditions' => array(
								0 => array(
									'operator' => '>',
									'value' => 3,
									'negate' => FALSE
								)
							)
						)
					)
				),
				'condition' => ''
			),
			'ordering' => array(
				'filter' => array(
					'filters' => array(),
					'orderby' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'starttime',
							'order' => 'desc'
						),
						1 => array(
							'table' => 'tt_content',
							'field' => 'year',
							'order' => 'asc',
							// Test forcing the ordering  on the source (impact on language test)
							'engine' => 'source'
						)
					)
				),
				'condition' => 'ORDER BY tt_content.starttime desc, tt_content.year asc',
				'sqlCondition' => FALSE
			),
			'random ordering' => array(
				'filter' => array(
					'filters' => array(),
					'orderby' => array(
						1 => array(
							'table' => '',
							'field' => '',
							'order' => 'RAND'
						)
					)
				),
				'condition' => 'ORDER BY RAND()',
				'sqlCondition' => FALSE
			),
			// Filter limits are not applied explicitly
			'limit' => array(
				'filter' => array(
					'filters' => array(),
					'limit' => array(
						'max' => 20,
						'offset' => 2
					),
				),
				'condition' => ''
			),
		);
		return $filters;
	}

	/**
	 * Parses and rebuilds a SELECT query with a filter.
	 *
	 * @param array $filter Filter configuration
	 * @param string $condition Interpreted condition
	 * @param boolean $isSqlCondition TRUE if the filter applies as a SQL WHERE condition, FALSE otherwise
	 * @test
	 * @dataProvider filterProvider
	 */
	public function selectQueryWithFilter($filter, $condition, $isSqlCondition = TRUE) {
		// Replace markers in the condition
		$generalCondition = $this->finalizeCondition($this->fullConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, FROM_UNIXTIME(tstamp, \'%Y\') AS year, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $generalCondition;
		// Add the filter's condition if not empty
		if (!empty($condition)) {
			if ($isSqlCondition) {
				$expectedResult .= 'AND (' . $condition . ') ';
			} else {
				$expectedResult .= $condition . ' ';
			}
		}

		$query = 'SELECT uid,header, FROM_UNIXTIME(tstamp, \'%Y\') AS year FROM tt_content';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$this->sqlParser->addFilter($filter);
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Provides filters for testing query with filters.
	 *
	 * Some filters are arbitrarily negated, to test the building of negated conditions
	 * Also provides the expected interpretation of the filter.
	 *
	 * @return array
	 */
	public function fulltextFilterProvider() {
		$filters = array(
			// Boolean mode, one word valid, one word ignore
			'fulltext, one valid word, one invalid word' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									// "bar" should be ignored, as it is below minimum word length
									'value' => 'foox bar',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '(MATCH(tt_content.header,tt_content.bodytext) AGAINST(\'+"foox"\' IN BOOLEAN MODE))'
			),
			// Boolean mode, one word included, one word excluded
			'fulltext, one word included, one word excluded' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									'value' => 'foox -barz',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '(MATCH(tt_content.header,tt_content.bodytext) AGAINST(\'+"foox" -"barz"\' IN BOOLEAN MODE))'
			),
			// Boolean mode with quoted string
			'fulltext, quoted string' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									'value' => '"go for foox"',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '(MATCH(tt_content.header,tt_content.bodytext) AGAINST(\'+"go for foox"\' IN BOOLEAN MODE))'
			),
			// Boolean mode, negated condition
			'fulltext, negated condition' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									// "bar" should be ignored, as it is below minimum word length
									'value' => 'foox',
									'negate' => TRUE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '(NOT MATCH(tt_content.header,tt_content.bodytext) AGAINST(\'+"foox"\' IN BOOLEAN MODE))'
			),
			// Natural mode
			'fulltext natural' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext_natural',
									'value' => 'foo bar',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '(MATCH(tt_content.header,tt_content.bodytext) AGAINST(\'foo bar\'))'
			),
			// Empty search words
			'fulltext, empty search' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									'value' => '',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '1'
			),
			// Empty search words in natural mode
			'fulltext natural, empty search' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext_natural',
									'value' => '',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'SEARCH',
				'fulltextCondition' => '1'
			),
			// Invalid index
			'fulltext, invalid index' => array(
				'filter' => array(
					'filters' => array(
						0 => array(
							'table' => 'tt_content',
							'field' => 'score',
							'conditions' => array(
								0 => array(
									'operator' => 'fulltext',
									// "bar" should be ignored, as it is below minimum word length
									'value' => 'foox bar',
									'negate' => FALSE
								)
							)
						)
					)
				),
				'index' => 'WEIRD',
				'fulltextCondition' => '1'
			),
		);
		return $filters;
	}

	/**
	 * Parses and rebuilds a SELECT query with a filter.
	 *
	 * @param array $filter Filter configuration
	 * @param string $index Name of the fulltext index
	 * @param string $fulltextCondition Interpreted condition
	 * @test
	 * @dataProvider fulltextFilterProvider
	 */
	public function selectQueryWithFulltextFilter($filter, $index, $fulltextCondition) {
		/** @var Tx_Dataquery_Parser_Fulltext $fulltextParser */
		$fulltextParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Dataquery_Parser_Fulltext');
		/** @var Tx_Dataquery_Utility_DatabaseAnalyser $databaseAnalyser */
		$databaseAnalyser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Dataquery_Utility_DatabaseAnalyser');
		// Set "fake" fulltext indices to ensure proper running of unit test
		$databaseAnalyser->setIndices(
			'SEARCH',
			'tt_content',
			'tt_content.header,tt_content.bodytext'
		);
		$fulltextParser->setAnalyser($databaseAnalyser);
		// Set "fake" configuration extension to ensure proper running of unit test
		$fulltextParser->setConfiguration(
			array(
				'fullTextMinimumWordLength' => 4
			)
		);
		tx_dataquery_SqlUtility::setFulltextParserInstance($fulltextParser);
		// Replace markers in the condition
		$generalCondition = $this->finalizeCondition($this->fullConditionForTable);
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, ' . $fulltextCondition . ' AS score, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ';
		$expectedResult .= $generalCondition;
		if ($fulltextCondition !== '1') {
			$expectedResult .= 'AND ((' . $fulltextCondition . ')) ';
		}

		$query = 'SELECT uid,header,fulltext:' . $index . ' AS score FROM tt_content';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$this->sqlParser->addFilter($filter);
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult, '***Expected***' . $expectedResult . '***Actual***' . $actualResult);
	}

	/**
	 * Provides various setups for all ignore flags.
	 *
	 * Also provides the corresponding expected WHERE clauses.
	 *
	 * @return array
	 */
	public function ignoreSetupProvider() {
		$setup = array(
			'ignore nothing' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '0',
					'ignore_time_for_tables' => '',
					'ignore_disabled_for_tables' => 'pages',
					// Tests that this is *not* ignore, because global ignore flag is 0
					'ignore_fegroup_for_tables' => 'tt_content'
				),
				'condition' => $this->fullConditionForTable
			),
			// Ignore all enable fields (detailed settings should be irrelevant)
			'ignore all' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '1',
					'ignore_time_for_tables' => '',
					'ignore_disabled_for_tables' => 'pages',
					'ignore_fegroup_for_tables' => 'tt_content'
				),
				'condition' => '###LANGUAGE_CONDITION### AND ###WORKSPACE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
			),
			// Ignore select enable fields, take 1: ignore all fields for all tables
			'ignore selected - all for all tables' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '2',
					'ignore_time_for_tables' => '*',
					'ignore_disabled_for_tables' => '*',
					'ignore_fegroup_for_tables' => '*'
				),
				'condition' => '(###MINIMAL_CONDITION###) AND ###LANGUAGE_CONDITION### AND ###WORKSPACE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
			),
			// Ignore select enable fields, take 2: ignore all fields for all tables
			// NOTE: should be the same as previous one since the only table in the query is tt_content
			'ignore selected - all for tt_content' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '2',
					'ignore_time_for_tables' => 'tt_content',
					'ignore_disabled_for_tables' => 'tt_content',
					'ignore_fegroup_for_tables' => 'tt_content'
				),
				'condition' => '(###MINIMAL_CONDITION###) AND ###LANGUAGE_CONDITION### AND ###WORKSPACE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
			),
			// Ignore select enable fields, take 3: ignore time fields for all tables and hidden field for tt_content
			'ignore selected - time and disabled for tt_content' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '2',
					'ignore_time_for_tables' => '*',
					// Weird but valid value (= tt_content)
					'ignore_disabled_for_tables' => ', tt_content',
					// Irrelevant, table "pages" is not in query
					'ignore_fegroup_for_tables' => 'pages'
				),
				'condition' => '(###MINIMAL_CONDITION######GROUP_CONDITION###) AND ###LANGUAGE_CONDITION### AND ###WORKSPACE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
			),
			// Ignore select enable fields, take 4: no tables defined at all, so nothing is ignore after all
			'ignore selected - ignore nothing after all' => array(
				'ignore_setup' => array(
					'ignore_enable_fields' => '2',
					'ignore_time_for_tables' => '',
					'ignore_disabled_for_tables' => '',
					'ignore_fegroup_for_tables' => ''
				),
				'condition' => $this->fullConditionForTable
			),
		);
		return $setup;
	}

	/**
	 * Parses and rebuilds a simple SELECT with various values of ignored enable fields.
	 *
	 * @param array $ignoreSetup Array with mechanisms to ignore
	 * @param string $condition Expected condition
	 * @test
	 * @dataProvider ignoreSetupProvider
	 */
	public function selectQueryAddTypo3MechanismsWithIgnoreEnableFields($ignoreSetup, $condition) {
		$testCondition = $condition;
		// Replace markers in the condition
		$condition = $this->finalizeCondition($condition);
		// Add extra fields, as needed
		$additionalSelectFields = $this->prepareAdditionalFields('tt_content');
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, tt_content.pid, tt_content.sys_language_uid' . $additionalSelectFields . ' FROM tt_content AS tt_content WHERE ' . $condition;

		$query = 'SELECT uid,header FROM tt_content';
		$this->sqlParser->parseQuery($query);
		// Assemble the settings and rebuild the query
		$settings = array_merge($this->settings, $ignoreSetup);
		$this->sqlParser->setProviderData($settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * Parses and rebuilds a SELECT query with an explicit JOIN and fields forced to another table
	 *
	 * @test
	 */
	public function selectQueryWithJoin() {
		// Replace markers in the condition
		$conditionForTtContent = $this->finalizeCondition($this->fullConditionForTable);
		$conditionForPages = $this->finalizeCondition(
			$this->conditionForPagesTables,
			'pages'
		);
		$additionalSelectFieldsForTtContent = $this->prepareAdditionalFields('tt_content');
		$additionalSelectFieldsForPages = $this->prepareAdditionalFields('pages', FALSE);
		$expectedResult = 'SELECT tt_content.uid, tt_content.header, pages.title AS tt_content$title, tt_content.pid, pages.uid AS pages$uid, pages.pid AS pages$pid, tt_content.sys_language_uid' . $additionalSelectFieldsForTtContent . $additionalSelectFieldsForPages . ' FROM tt_content AS tt_content INNER JOIN pages AS pages ON pages.uid = tt_content.pid AND ' . $conditionForPages . 'WHERE ' . $conditionForTtContent;

		$query = 'SELECT uid,header,pages.title AS tt_content.title FROM tt_content INNER JOIN pages ON pages.uid = tt_content.pid';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult, '***Expected***'.$expectedResult.'***Actual***'.$actualResult.'***');
	}

	/**
	 * Parses and rebuilds a SELECT query with an implicit JOIN and filters applying to both tables,
	 * including one forced to main.
	 *
	 * @test
	 */
	public function selectQueryWithJoinAndFilter() {
		// Replace markers in the conditions
		$conditionForTtContent = $this->finalizeCondition($this->fullConditionForTable);
		$conditionForPages = $this->finalizeCondition(
			$this->conditionForPagesTables,
			'pages'
		);
		$additionalSelectFieldsForTtContent = $this->prepareAdditionalFields('tt_content');
		$additionalSelectFieldsForPages = $this->prepareAdditionalFields('pages', FALSE);
		// Assemble expected result
		$expectedResult = 'SELECT tt_content.header, pages.title AS pages$title, tt_content.uid, tt_content.pid, ';
		$expectedResult .= 'pages.uid AS pages$uid, pages.pid AS pages$pid, tt_content.sys_language_uid';
		$expectedResult .= $additionalSelectFieldsForTtContent . $additionalSelectFieldsForPages;
		$expectedResult .= ' FROM tt_content AS tt_content INNER JOIN pages AS pages ON ' . $conditionForPages;
		$expectedResult .= 'AND (((pages.title LIKE \'%bar%\'))) WHERE (pages.uid = tt_content.pid) AND ';
		$expectedResult .= $conditionForTtContent . 'AND (((tt_content.header LIKE \'%foo%\')) AND ((pages.tstamp > \'' . mktime(0, 0, 0, 1, 1, 2010) . '\'))) ';

		// Define the filter to apply
		$filter = array(
			'filters' => array(
				0 => array(
					'table' => 'tt_content',
					'field' => 'header',
					'conditions' => array(
						0 => array(
							'operator' => 'like',
							'value' => array(
								'foo',
							)
						)
					)
				),
				1 => array(
					'table' => 'pages',
					'field' => 'title',
					'conditions' => array(
						0 => array(
							'operator' => 'like',
							'value' => array(
								'bar',
							)
						)
					)
				),
				2 => array(
					'table' => 'pages',
					'field' => 'tstamp',
					'conditions' => array(
						0 => array(
							'operator' => '>',
							'value' => mktime(0, 0, 0, 1, 1, 2010)
						)
					),
					'main' => TRUE
				)
			),
			'logicalOperator' => 'AND'
		);

		$query = 'SELECT header,pages.title FROM tt_content,pages WHERE pages.uid = tt_content.pid';
		$this->sqlParser->parseQuery($query);
		$this->sqlParser->setProviderData($this->settings);
		$this->sqlParser->addTypo3Mechanisms();
		$this->sqlParser->addFilter($filter);
		$actualResult = $this->sqlParser->buildQuery();

		$this->assertEquals($expectedResult, $actualResult, '***Expected***'.$expectedResult.'***Actual***'.$actualResult.'***');
	}

	/**
	 * Prepares the addition to the SELECT string necessary for any
	 * additional fields defined by a given test class.
	 *
	 * @param string $table Name of the table to use
	 * @param boolean $isMainTable True if the table is the main one, false otherwise
	 * @return string List of additional fields to add to SELECT statement
	 */
	protected function prepareAdditionalFields($table, $isMainTable = TRUE) {
		$additionalSelectFields = '';
		if (count($this->additionalFields) > 0) {
			foreach ($this->additionalFields as $field) {
				$additionalSelectFields .= ', ' . $table . '.' . $field;
					// If table is not the main one, add alias
				if (!$isMainTable) {
					$additionalSelectFields .= ' AS ' . $table . '$' . $field;
				}
			}
		}
		return $additionalSelectFields;
	}
}
