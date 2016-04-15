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
 * Testcase for the Data Query query builder in the Draft workspace
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class SqlBuilderWorkspaceTest extends SqlBuilderTest
{

    /**
     * @var    string    Base SQL condition to apply to tt_content table
     */
    protected $baseConditionForTable = '###MINIMAL_CONDITION###';

    /**
     * @var string Absolute minimal condition applied to all TYPO3 requests, even in workspaces
     */
    protected $minimalConditionForTable = '###TABLE###.deleted=0';

    /**
     * @var string Condition on user groups found inside the base condition
     */
    protected $groupsConditionForTable = '';

    /**
     * @var    string    Base workspace-related SQL condition to apply to tt_content table
     */
    protected $baseWorkspaceConditionForTable = '(###TABLE###.t3ver_wsid=0 OR ###TABLE###.t3ver_wsid=42) AND ###TABLE###.pid<>-1';

    /**
     * @var    string    Additional workspace-related SQL condition to apply to tt_content table
     */
    protected $additionalWorkspaceConditionForTable = ' AND ((###TABLE###.t3ver_state <= 0 AND ###TABLE###.t3ver_oid = 0) OR (###TABLE###.t3ver_state = 0 AND ###TABLE###.t3ver_wsid = 42) OR (###TABLE###.t3ver_state = 1 AND ###TABLE###.t3ver_wsid = 42) OR (###TABLE###.t3ver_state = 3 AND ###TABLE###.t3ver_wsid = 42)) ';

    /**
     * @var    string    Full SQL condition (for tt_content) to apply to all queries. Will be based on the above components.
     */
    protected $fullConditionForTable = '(###BASE_CONDITION### AND ###WORKSPACE_CONDITION###) AND ###LANGUAGE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###';

    /**
     * @var    string    Full SQL condition except for languages
     */
    protected $noLanguagesConditionForTable = '(###BASE_CONDITION### AND ###WORKSPACE_CONDITION###)###ADDITIONAL_WORKSPACE_CONDITION###';

    /**
     * @var string Full condition is different for pages table, because language handling is delegated to separate table
     */
    protected $conditionForPagesTables = '(###MINIMAL_CONDITION### AND ###TABLE###.pid<>-1)###ADDITIONAL_WORKSPACE_CONDITION###';

    /**
     * Sets up the workspace preview environment
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Add version state to the SELECT fields
        $this->additionalFields[] = 't3ver_state';

        // Activate versioning preview
        $GLOBALS['TSFE']->sys_page->versioningPreview = true;
        // Save current workspace (should be the LIVE one really) and switch to dummy workspace
        $GLOBALS['BE_USER']->workspace = 42;
        $GLOBALS['TSFE']->sys_page->versioningWorkspaceId = 42;
    }

    /**
     * Provides various setups for all ignore flags
     * Also provides the corresponding expected WHERE clauses.
     *
     * Differs from parent as different conditions apply to workspaces.
     *
     * @return array
     */
    public function ignoreSetupProvider()
    {
        $setup = array(
                'ignore nothing' => array(
                        'ignore_setup' => array(
                                'ignore_enable_fields' => '0',
                                'ignore_time_for_tables' => '',
                                'ignore_disabled_for_tables' => 'pages',
                                'ignore_fegroup_for_tables' => 'tt_content'
                                // Tests that this is *not* ignore, because global ignore flag is 0
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
                        'condition' => '###LANGUAGE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
                ),
                // Ignore select enable fields, take 1: ignore all fields for all tables
                'ignore selected - all for all tables' => array(
                        'ignore_setup' => array(
                                'ignore_enable_fields' => '2',
                                'ignore_time_for_tables' => '*',
                                'ignore_disabled_for_tables' => '*',
                                'ignore_fegroup_for_tables' => '*'
                        ),
                        'condition' => '(###MINIMAL_CONDITION### AND ###WORKSPACE_CONDITION###) AND ###LANGUAGE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
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
                        'condition' => '(###MINIMAL_CONDITION### AND ###WORKSPACE_CONDITION###) AND ###LANGUAGE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
                ),
                // Ignore select enable fields, take 3: ignore time fields for all tables and hidden field for tt_content
                'ignore selected - time and disabled for tt_content' => array(
                        'ignore_setup' => array(
                                'ignore_enable_fields' => '2',
                                'ignore_time_for_tables' => '*',
                                'ignore_disabled_for_tables' => ', tt_content', // Weird but valid value (= tt_content)
                                'ignore_fegroup_for_tables' => 'pages' // Irrelevant, table "pages" is not in query
                        ),
                        'condition' => '(###MINIMAL_CONDITION######GROUP_CONDITION### AND ###WORKSPACE_CONDITION###) AND ###LANGUAGE_CONDITION######ADDITIONAL_WORKSPACE_CONDITION###'
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
}
