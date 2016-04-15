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

/**
 * Testcase for the Data Query wrapper (Data Provider)
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class DataProviderTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * Provides array(s) to sort
     *
     * @return array
     */
    public function stuffToSortProvider()
    {
        $stuffToSort = array(
                'standard case' => array(
                        'records' => array(
                                0 => array(
                                        'name' => 'Arthur Dent',
                                        'age' => '22'
                                ),
                                1 => array(
                                        'name' => 'Slartibartfast',
                                        'age' => '12'
                                ),
                                2 => array(
                                        'name' => 'Ford Prefect',
                                        'age' => '12'
                                ),
                                3 => array(
                                        'name' => 'Zaphod Beeblebrox',
                                        'age' => '1'
                                ),
                                4 => array(
                                        'name' => 'Prostetnic Vogon Jeltz',
                                        'age' => '2'
                                ),
                        ),
                        'result' => array(
                                0 => array(
                                        'name' => 'Zaphod Beeblebrox',
                                        'age' => '1'
                                ),
                                1 => array(
                                        'name' => 'Prostetnic Vogon Jeltz',
                                        'age' => '2'
                                ),
                                2 => array(
                                        'name' => 'Ford Prefect',
                                        'age' => '12'
                                ),
                                3 => array(
                                        'name' => 'Slartibartfast',
                                        'age' => '12'
                                ),
                                4 => array(
                                        'name' => 'Arthur Dent',
                                        'age' => '22'
                                ),
                        )
                )
        );
        return $stuffToSort;
    }

    /**
     * @param array $records Unsorted records
     * @param array $result Sorted records
     * @test
     * @dataProvider stuffToSortProvider
     */
    public function testSortingMethod($records, $result)
    {
        DataProvider::$sortingFields[0]['field'] = 'age';
        DataProvider::$sortingFields[1]['field'] = 'name';
        usort($records, array('\Tesseract\Dataquery\Component\DataProvider', 'sortRecordset'));
        self::assertEquals($result, $records);
    }
}
