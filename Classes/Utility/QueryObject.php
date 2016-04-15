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

/**
 * Object containing information about the structure of a parsed query.
 *
 * NOTE: this object has no method. In some languages, it would be called a
 * structured type.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class QueryObject
{
    /**
     * Contains all components of the parsed query
     * @var array $structure
     */
    public $structure = array();

    /**
     * Name (or alias if defined) of the main query table, i.e. the first one in the FROM part of the query
     * @var string $mainTable
     */
    public $mainTable;

    /**
     * List of all subtables, i.e. tables in the JOIN statements
     * @var array $subtables
     */
    public $subtables = array();

    /**
     * The keys to this array are the aliases of the tables used in the query and they point to the true table names
     * @var array $aliases
     */
    public $aliases = array();

    /**
     * For each table, record with boolean values whether it has some base fields or not
     * @var array $hasBaseFields
     */
    public $hasBaseFields = array();

    /**
     * Array with all information of the fields used to order data
     * @var array $orderFields
     */
    public $orderFields = array();

    /**
     * List of aliases for all fields that have one, per table
     * @var array $fieldAliases
     */
    public $fieldAliases = array();

    /**
     * List of what field aliases map to (table, field and whether it's a function or not)
     * @var array $fieldAliasMappings
     */
    public $fieldAliasMappings = array();

    /**
     * List of placeholders for replacement by MATCH() statements
     * @var array
     */
    public $fulltextSearchPlaceholders = array();

    public function __construct()
    {
        // Initialize some values
        $this->structure['DISTINCT'] = false;
        $this->structure['SELECT'] = array();
        $this->structure['FROM'] = array();
        $this->structure['JOIN'] = array();
        $this->structure['WHERE'] = array();
        $this->structure['ORDER BY'] = array();
        $this->structure['GROUP BY'] = array();
    }
}
