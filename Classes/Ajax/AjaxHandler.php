<?php
namespace Tesseract\Dataquery\Ajax;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tesseract\Dataquery\Parser\QueryParser;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class answers to AJAX calls from the 'dataquery' extension.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_dataquery
 */
class AjaxHandler
{

    /**
     * Returns the parsed query through dataquery parser
     * or error messages from exceptions should any have been thrown
     * during query parsing.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function validateAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = $request->getQueryParams();
        $query = $requestParameters['query'];

        $flashMessageQueue = GeneralUtility::makeInstance(
                FlashMessageQueue::class,
                'tx_datafilter_ajax'
        );
        $parsingSeverity = FlashMessage::OK;
        $executionSeverity = FlashMessage::OK;
        $executionMessage = '';
        $warningMessage = '';
        /** @var \TYPO3\CMS\Lang\LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];

        // Try parsing and building the query
        try {
            // Get the query to parse from the GET/POST parameters
            // Create an instance of the parser
            // NOTE: NULL is passed for the parent object as there's no controller in this context,
            // but that's a bit risky. Maybe extension "tesseract" could provide a dummy controller
            // (or some logic should be split: the query parser should not also be a query builder).
            /** @var $parser QueryParser */
            $parser = GeneralUtility::makeInstance(
                    QueryParser::class,
                    null
            );
            // Clean up and prepare the query string
            $query = $parser->prepareQueryString($query);
            // Parse the query
            // NOTE: if the parsing fails, an exception will be received, which is handled further down
            // The parser may return a warning, though
            $warningMessage = $parser->parseQuery($query);
            // Build the query
            $parsedQuery = $parser->buildQuery();
            // The query building completed, issue success message
            $parsingTitle = $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.success');
            $parsingMessage = $parsedQuery;

            // Force a LIMIT to 1 and try executing the query
            $parser->getSQLObject()->structure['LIMIT'] = 1;
            // Rebuild the query with the new limit
            $executionQuery = $parser->buildQuery();
            // Execute query and report outcome
            /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
            $databaseConnection = $GLOBALS['TYPO3_DB'];
            $res = $databaseConnection->sql_query($executionQuery);
            if ($res === false) {
                $executionSeverity = FlashMessage::ERROR;
                $errorMessage = $databaseConnection->sql_error();
                $executionMessage = sprintf(
                        $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.executionFailed'),
                        $errorMessage
                );
            } else {
                $executionMessage = $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.executionSuccessful');
            }
        } catch (\Exception $e) {
            // The query parsing failed, issue error message
            $parsingSeverity = FlashMessage::ERROR;
            $parsingTitle = $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.failure');
            $exceptionCode = $e->getCode();
            // Display "improved" exception message (if available)
            $parsingMessage = $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.exception-' . $exceptionCode);
            // If some unexpected exception occurred, display original message
            if (empty($parsingMessage)) {
                $parsingMessage = $e->getMessage();
            }
        }
        // Render parsing result as flash message
        /** @var $flashMessage FlashMessage */
        $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $parsingMessage,
                $parsingTitle,
                $parsingSeverity
        );
        $flashMessageQueue->enqueue($flashMessage);
        // If a warning was returned by the query parser, display it here
        if (!empty($warningMessage)) {
            $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $warningMessage,
                    $languageService->sL('LLL:EXT:dataquery/Resources/Private/Language/locallang.xlf:query.warning'),
                    FlashMessage::WARNING
            );
            $flashMessageQueue->enqueue($flashMessage);
        }
        // If the query was also executed, render execution result
        if (!empty($executionMessage)) {
            $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $executionMessage,
                    '',
                    $executionSeverity
            );
            $flashMessageQueue->enqueue($flashMessage);
        }
        // Send the response
        $response->getBody()->write(
                json_encode(
                        $flashMessageQueue->renderFlashMessages()
                )
        );
        return $response;
    }
}
