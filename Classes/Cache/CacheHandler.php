<?php
namespace Tesseract\Dataquery\Cache;

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
 * Cache management class for extension "dataquery".
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class CacheHandler
{

    /**
     * Clears the dataquery for selected pages only.
     *
     * @param array $parameters Parameters passed by DataHandler, including the pages to clear the cache for
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject Reference to the calling DataHandler object
     * @return void
     */
    public function clearCache($parameters, $parentObject)
    {
        // Clear the dataquery cache for all the pages passed to this method
        if (isset($parameters['pageIdArray']) && count($parameters['pageIdArray']) > 0) {
            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                    'tx_dataquery_cache',
                    'page_id IN (' . implode(',', $parameters['pageIdArray']) . ')'
            );
        }
    }
}
