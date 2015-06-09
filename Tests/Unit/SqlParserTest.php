<?php
namespace Tesseract\Dataquery\Tests\Unit;

/**
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

use Tesseract\Dataquery\Parser\SqlParser;

/**
 * Testcase for the Data Query SQL parser
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class SqlParserTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var SqlParser
	 */
	protected $parser;

	/**
	 * Sets up the test environment
	 *
	 * @return void
	 */
	public function setUp() {
		// NOTE: avoid using t3lib_div::makeInstance, to make sure no XCLASS interferes with the tests
		$this->parser = new SqlParser();
	}

	/**
	 * Cleans up the test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->parser);
	}

	/**
	 * Provides queries that can be parsed successfully
	 *
	 * @return array
	 */
	public function correctQueryProvider() {
		return array(
			'query with distinct' => array(
				'query' => 'SELECT DISTINCT CType AS uid FROM tt_content',
				'parsedStructure' => array(
					'DISTINCT' => TRUE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 'tt_content',
							'field' => 'CType',
							'fieldAlias' => 'uid',
							'function' => FALSE
						)
					),
					'FROM' => array('table' => 'tt_content', 'alias' => 'tt_content'),
					'JOIN' => array(),
					'WHERE' => array(),
					'ORDER BY' => array(),
					'GROUP BY' => array()
				)
			),
			'query with function calls' => array(
				'query' => 'SELECT uid, FROM_UNIXTIME(tstamp, \'%Y\') AS year, CONCAT(uid, \' in \', pid) FROM tt_content AS content',
				'parsedStructure' => array(
					'DISTINCT' => FALSE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 'content',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						),
						1 => array(
							'table' => 'tt_content',
							'tableAlias' => 'content',
							'field' => 'FROM_UNIXTIME(tstamp, \'%Y\')',
							'fieldAlias' => 'year',
							'function' => TRUE
						),
						2 => array(
							'table' => 'tt_content',
							'tableAlias' => 'content',
							'field' => 'CONCAT(uid, \' in \', pid)',
							'fieldAlias' => 'function_2',
							'function' => TRUE
						)
					),
					'FROM' => array('table' => 'tt_content', 'alias' => 'content'),
					'JOIN' => array(),
					'WHERE' => array(),
					'ORDER BY' => array(),
					'GROUP BY' => array()
				)
			),
			'query with implicit join' => array(
				'query' => 'SELECT t.uid, p.uid FROM tt_content AS t, pages AS p WHERE p.uid = t.pid',
				'parsedStructure' => array(
					'DISTINCT' => FALSE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 't',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						),
						1 => array(
							'table' => 'pages',
							'tableAlias' => 'p',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						)
					),
					'FROM' => array('table' => 'tt_content', 'alias' => 't'),
					'JOIN' => array(
						'p' => array(
							'type' => 'inner',
							'table' => 'pages',
							'alias' => 'p',
							'on' => ''
						)
					),
					'WHERE' => array(
						0 => 'p.uid = t.pid'
					),
					'ORDER BY' => array(),
					'GROUP BY' => array()
				)
			),
			'query with explicit join' => array(
				'query' => 'SELECT t.uid, p.uid FROM pages AS p LEFT JOIN tt_content AS t ON t.pid = p.uid',
				'parsedStructure' => array(
					'DISTINCT' => FALSE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 't',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						),
						1 => array(
							'table' => 'pages',
							'tableAlias' => 'p',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						)
					),
					'FROM' => array('table' => 'pages', 'alias' => 'p'),
					'JOIN' => array(
						't' => array(
							'type' => 'left',
							'table' => 'tt_content',
							'alias' => 't',
							'on' => 't.pid = p.uid'
						)
					),
					'WHERE' => array(),
					'ORDER BY' => array(),
					'GROUP BY' => array()
				)
			),
			'query with implicit limit and offset' => array(
				'query' => 'SELECT uid, header FROM tt_content LIMIT 10, 20',
				'parsedStructure' => array(
					'DISTINCT' => FALSE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 'tt_content',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						),
						1 => array(
							'table' => 'tt_content',
							'tableAlias' => 'tt_content',
							'field' => 'header',
							'fieldAlias' => '',
							'function' => FALSE
						)
					),
					'FROM' => array('table' => 'tt_content', 'alias' => 'tt_content'),
					'JOIN' => array(),
					'WHERE' => array(),
					'ORDER BY' => array(),
					'GROUP BY' => array(),
					'LIMIT' => 20,
					'OFFSET' => 10
				)
			),
			'query with explicit limit and offset' => array(
				'query' => 'SELECT uid, header FROM tt_content LIMIT 20 OFFSET 10',
				'parsedStructure' => array(
					'DISTINCT' => FALSE,
					'SELECT' => array(
						0 => array(
							'table' => 'tt_content',
							'tableAlias' => 'tt_content',
							'field' => 'uid',
							'fieldAlias' => '',
							'function' => FALSE
						),
						1 => array(
							'table' => 'tt_content',
							'tableAlias' => 'tt_content',
							'field' => 'header',
							'fieldAlias' => '',
							'function' => FALSE
						)
					),
					'FROM' => array('table' => 'tt_content', 'alias' => 'tt_content'),
					'JOIN' => array(),
					'WHERE' => array(),
					'ORDER BY' => array(),
					'GROUP BY' => array(),
					'LIMIT' => 20,
					'OFFSET' => 10
				)
			)
		);
	}

	/**
	 * Parses a number of successful SQL queries
	 *
	 * @param string $query The SQL query to parse
	 * @param array $parsedStructure The expected parsing result
	 * @test
	 * @dataProvider correctQueryProvider
	 */
	public function parseQuery($query, $parsedStructure) {
		$actualResult = $this->parser->parseSQL($query);
			// Check if the "structure" part if correct
		$this->assertEquals($parsedStructure, $actualResult->structure);
	}

	/**
	 * Test a simple SELECT query containing a wildcard selector
	 *
	 * This is tested separately from the others, as the wildcard has to be expanded to create
	 * the expected result
	 *
	 * @test
	 */
	public function parseQueryWithWildcard() {
		$query = 'SELECT * FROM tt_content';
		$expectedResult = array(
			'DISTINCT' => FALSE,
			'SELECT' => array(),
			'FROM' => array('table' => 'tt_content', 'alias' => 'tt_content'),
			'JOIN' => array(),
			'WHERE' => array(),
			'ORDER BY' => array(),
			'GROUP BY' => array()
		);
			// The wildcard will get expanded to include all fields for the given table
			// So get them and add them to the expected results
		$fieldInfo = $GLOBALS['TYPO3_DB']->admin_get_fields('tt_content');
		$fields = array_keys($fieldInfo);
			// Add all fields to the query structure
		foreach ($fields as $aField) {
			$expectedResult['SELECT'][] = array(
				'table' => 'tt_content',
				'tableAlias' => 'tt_content',
				'field' => $aField,
				'fieldAlias' => '',
				'function' => FALSE
			);
		}
		$actualResult = $this->parser->parseSQL($query);
			// Check if the "structure" part if correct
		$this->assertEquals($expectedResult, $actualResult->structure);
	}

	/**
	 * Provides queries that trigger parsing exceptions
	 *
	 * @return array
	 */
	public function wrongQueryProvider() {
		return array(
			'missing select' => array(
				'query' => '* FROM tt_content'
			),
			'missing from' => array(
				'query' => 'SELECT uid, header tt_content'
			),
			'missing table' => array(
				'query' => 'SELECT uid, header FROM'
			),
			'empty select' => array(
				'query' => 'SELECT FROM tt_content'
			),
			'unbalanced brackets' => array(
				'query' => 'SELECT FROM_UNIXTIME(tstamp, \'%Y\' AS year FROM tt_content'
			),
		);
	}

	/**
	 * Parses a number of erroneous SQL queries
	 *
	 * @param string $query The SQL query to parse
	 * @test
	 * @dataProvider wrongQueryProvider
	 * @expectedException \Tesseract\Dataquery\Exception\InvalidQueryException
	 */
	public function parseWrongQuery($query) {
		$this->parser->parseSQL($query);
	}
}
