<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Francois Suter (Cobweb) <typo3@cobweb.ch>
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
 * Interface which defines the method to implement when creating a hook to process cache parameters.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
interface tx_dataquery_cacheParametersProcessor {
	/**
	 * This method must be implemented for processing cache parameters.
	 *
	 * It receives a reference to the current cache parameters and a back-reference to the calling object.
	 * It is expected to return the cache parameters, modified or not.
	 *
	 * @param array $cacheParameters Current cache parameters
	 * @param tx_dataquery_wrapper $parentObject Back-reference to the calling object
	 * @return array Modified cache parameters
	 */
	public function processCacheParameters($cacheParameters, $parentObject);
}
?>