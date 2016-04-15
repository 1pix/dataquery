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
 * This class provides some API methods related to FULLTEXT indexes
 *
 * @author Fabien Udriot (Cobweb) <typo3Tx_Dataquery_DatabaseAnalyser@cobweb.ch>
 * @author Francois Suter (Cobweb) <typo3Tx_Dataquery_DatabaseAnalyser@cobweb.ch>
 * @package TYPO3
 * @subpackage dataquery
 */
class DatabaseAnalyser
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseHandle;

    protected $indices = array();

    /**
     * Constructor
     *
     * @return DatabaseAnalyser
     */
    public function __construct()
    {
        $this->databaseHandle = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Returns the list of tables having a FULLTEXT index.
     *
     * @return array
     */
    public function getTables()
    {
        $tables = array();
        $query = '
			SELECT DISTINCT table_name
			FROM information_schema.STATISTICS
			WHERE index_type = \'FULLTEXT\'
				AND table_schema = \'' . TYPO3_db . '\'';

        $resource = $this->databaseHandle->sql_query($query);
        while ($row = $this->databaseHandle->sql_fetch_assoc($resource)) {
            $tables[] = $row['table_name'];
        }
        return $tables;
    }

    /**
     * Returns the fields composing the FULLTEXT index for the given table.
     *
     * @param string $table Name of the table
     * @return array
     */
    public function getFields($table)
    {
        // Check if indices were already fetched for given table
        if (isset($this->indices[$table])) {
            return $this->indices[$table];
        } else {
            $indices = array();
            $query = 'SELECT index_name, group_concat(DISTINCT column_name) AS fields
				FROM information_schema.STATISTICS
				WHERE index_type = \'FULLTEXT\'
					AND table_schema = \'' . TYPO3_db . '\'
					AND table_name = \'' . $table . '\'
				GROUP BY index_name
				ORDER BY index_name';

            $resource = $this->databaseHandle->sql_query($query);

            while ($row = $this->databaseHandle->sql_fetch_assoc($resource)) {
                if (!empty($row['fields'])) {
                    $fields = explode(',', $row['fields']);
                    $indices[$row['index_name']] = $table . '.' . implode(',' . $table . '.', $fields);
                }
            }
            $this->indices[$table] = $indices;
            return $indices;
        }
    }

    /**
     * Checks whether the given table has a FULLTEXT index or not.
     *
     * @param string $table Name of the table
     * @return boolean
     */
    public function hasIndex($table)
    {
        $indices = $this->getFields($table);
        return !empty($indices);
    }

    /**
     * Sets the list of tables and fields having fulltext indexing.
     *
     * Useful for unit testing.
     *
     * @param string $index Name of the index
     * @param string $table Name of the table
     * @param array $fields List of tables and fields
     */
    public function setIndices($index, $table, $fields)
    {
        $this->indices[$table] = array(
                $index => $fields
        );
    }
}
