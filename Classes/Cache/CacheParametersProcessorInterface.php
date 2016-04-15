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
 * Interface which defines the method to implement when creating a hook to process cache parameters.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
interface CacheParametersProcessorInterface
{
    /**
     * This method must be implemented for processing cache parameters.
     *
     * It receives a reference to the current cache parameters and a back-reference to the calling object.
     * It is expected to return the cache parameters, modified or not.
     *
     * @param array $cacheParameters Current cache parameters
     * @param \Tesseract\Dataquery\Component\DataProvider $parentObject Back-reference to the calling object
     * @return array Modified cache parameters
     */
    public function processCacheParameters($cacheParameters, $parentObject);
}
