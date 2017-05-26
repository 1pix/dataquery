<?php
namespace Tesseract\Dataquery\Wizard;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Wizard for checking the validity and the results of a SQL query.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class QueryCheckWizard implements \TYPO3\CMS\Backend\Form\NodeInterface
{
    /**
     * @var array Field data
     */
    protected $fieldData = [];

    public function __construct(\TYPO3\CMS\Backend\Form\NodeFactory $nodeFactory, array $data)
    {
        $this->fieldData = $data;
    }

    /**
     * Renders the wizard itself.
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        // Get the wizard template
        $wizardTemplate = GeneralUtility::makeInstance(StandaloneView::class);
        $wizardTemplate->setTemplatePathAndFilename('EXT:dataquery/Resources/Private/Templates/QueryCheckWizard.html');

        // Register resources for wizard rendering
        return [
                'additionalJavaScriptPost' => [],
                'additionalJavaScriptSubmit' => [],
                'additionalHiddenFields' => [],
                'additionalInlineLanguageLabelFiles' => [],
                'stylesheetFiles' => [
                        'EXT:dataquery/Resources/Public/Styles/CheckWizard.css'
                ],
                'requireJsModules' => [
                        'TYPO3/CMS/Dataquery/QueryCheckWizard'
                ],
                'inlineData' => [],
                'html' => $wizardTemplate->render()
        ];
    }
}
