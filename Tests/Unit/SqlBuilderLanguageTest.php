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

/**
 * Testcase for the Data Query query builder with a non-default language
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class SqlBuilderLanguageTest extends SqlBuilderTest
{

    /**
     * @var    string    Language-related SQL condition to apply to tt_content table
     */
    protected $baseLanguageConditionForTable = "(###TABLE###.sys_language_uid IN (0,-1) OR (###TABLE###.sys_language_uid = '2' AND ###TABLE###.l18n_parent = '0'))";

    /**
     * Sets up a different language
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Set a different language than default
        $GLOBALS['TSFE']->sys_language_content = 2;
    }
}
