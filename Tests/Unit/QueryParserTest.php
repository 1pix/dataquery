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

use Tesseract\Dataquery\Component\DataProvider;
use Tesseract\Dataquery\Parser\QueryParser;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for the Data Query query parser.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class QueryParserTest extends UnitTestCase
{
    /**
     * @var array List of globals to exclude (contain closures which cannot be serialized)
     */
    protected $backupGlobalsBlacklist = array('TYPO3_LOADED_EXT', 'TYPO3_CONF_VARS');

    /**
     * Provides fields to test for them being text or not.
     *
     * @return array
     */
    public function tablesAndFieldsProvider()
    {
        $fields = array(
            // Text (single line) field
            'tt_content.header' => array(
                    'table' => 'tt_content',
                    'field' => 'header',
                    'result' => true
            ),
            // Text (multi-line) field
            'tt_content.bodytext' => array(
                    'table' => 'tt_content',
                    'field' => 'bodytext',
                    'result' => true
            ),
            // No TCA, will default to be considered a text field
            'tt_content.crdate' => array(
                    'table' => 'tt_content',
                    'field' => 'bodytext',
                    'result' => true
            ),
            // Date and time, not a text field
            'tt_content.starttime' => array(
                    'table' => 'tt_content',
                    'field' => 'starttime',
                    'result' => false
            ),
            // Integer, not a date field
            'tt_content.CType' => array(
                    'table' => 'tt_content',
                    'field' => 'CType',
                    'result' => false
            ),
        );
        return $fields;
    }

    /**
     * Test the text detection routine.
     *
     * @param string $table Name of the table
     * @param string $field Name of the field
     * @param boolean $result The expected result
     * @test
     * @dataProvider tablesAndFieldsProvider
     */
    public function detectTextField($table, $field, $result)
    {
        $dataqueryWrapper = $this->getMock(
                DataProvider::class
        );
        /** @var QueryParser $parser */
        $parser = GeneralUtility::makeInstance(
                QueryParser::class,
                $dataqueryWrapper
        );
        self::assertEquals(
                $parser->isATextField(
                        $table,
                        $field
                ),
                $result
        );
    }
}
