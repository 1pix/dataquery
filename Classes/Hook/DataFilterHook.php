<?php
namespace Tesseract\Dataquery\Hook;

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

use Cobweb\Expressions\ExpressionParser;
use Tesseract\Datafilter\Component\DataFilter;
use Tesseract\Datafilter\PostprocessFilterInterface;
use Tesseract\Datafilter\PostprocessEmptyFilterCheckInterface;

/**
 * Class for hooking into datafilter to handle the tx_dataquery_sql field.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class DataFilterHook implements PostprocessFilterInterface, PostprocessEmptyFilterCheckInterface {

	/**
	 * Handles the tx_dataquery_sql field and adds it
	 * to the filter itself.
	 *
	 * @param DataFilter $filter A datafilter object
	 * @return void
	 */
	public function postprocessFilter(DataFilter $filter) {
		$filterData = $filter->getData();
		if (!empty($filterData['tx_dataquery_sql'])) {
			// Parse any expressions inside the additional sql field
			$additionalSQL = ExpressionParser::evaluateString($filterData['tx_dataquery_sql'], FALSE);
			$filterArray = $filter->getFilter();
			$filterArray['rawSQL'] = $additionalSQL;
			$filter->setFilter($filterArray);
		}
	}

	/**
	 * Modified the empty filter check to take the tx_dataquery_sql field into account.
	 *
	 * @param boolean $isEmpty Current value of the is filter empty flag
	 * @param DataFilter $filter The calling filter object
	 * @return boolean
	 */
	public function postprocessEmptyFilterCheck($isEmpty, DataFilter $filter) {
		$filterStructure = $filter->getFilter();
		return $isEmpty && empty($filterStructure['rawSQL']);
	}
}
