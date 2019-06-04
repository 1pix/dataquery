<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "dataquery".
 *
 * Auto generated 06-06-2017 17:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
        'title' => 'SQL-based Data Provider - Tesseract project',
        'description' => 'Assembles a query on data stored in the TYPO3 CMS local database, automatically enforcing criteria like language, publication date, etc. More info on http://www.typo3-tesseract.com/',
        'category' => 'misc',
        'author' => 'Francois Suter (Cobweb)',
        'author_email' => 'typo3@cobweb.ch',
        'state' => 'stable',
        'uploadfolder' => 0,
        'createDirs' => '',
        'clearCacheOnLoad' => 0,
        'author_company' => '',
        'version' => '2.1.3',
        'constraints' =>
                [
                        'depends' =>
                                [
                                        'typo3' => '7.6.0-8.99.99',
                                        'tesseract' => '2.0.0-0.0.0',
                                        'datafilter' => '2.0.0-0.0.0',
                                        'overlays' => '3.0.0-0.0.0',
                                        'expressions' => '2.0.0-0.0.0',
                                ],
                        'conflicts' =>
                                [
                                ],
                        'suggests' =>
                                [
                                ],
                ],
];

