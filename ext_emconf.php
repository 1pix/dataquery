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

$EM_CONF[$_EXTKEY] = array (
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
  'version' => '2.1.1',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '7.6.0-8.99.99',
      'tesseract' => '2.0.0-0.0.0',
      'datafilter' => '2.0.0-0.0.0',
      'overlays' => '3.0.0-0.0.0',
      'expressions' => '2.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
      'devlog' => '',
      'cachecleaner' => '',
    ),
  ),
  '_md5_values_when_last_written' => 'a:77:{s:9:"ChangeLog";s:4:"ac6e";s:11:"LICENSE.txt";s:4:"6404";s:9:"README.md";s:4:"5d1a";s:13:"composer.json";s:4:"e448";s:21:"ext_conf_template.txt";s:4:"7f9a";s:12:"ext_icon.png";s:4:"0390";s:17:"ext_localconf.php";s:4:"c39a";s:14:"ext_tables.php";s:4:"1d73";s:14:"ext_tables.sql";s:4:"5297";s:28:"Classes/Ajax/AjaxHandler.php";s:4:"87ee";s:30:"Classes/Cache/CacheHandler.php";s:4:"c634";s:51:"Classes/Cache/CacheParametersProcessorInterface.php";s:4:"23bc";s:34:"Classes/Component/DataProvider.php";s:4:"2a88";s:43:"Classes/Exception/InvalidQueryException.php";s:4:"a757";s:31:"Classes/Hook/DataFilterHook.php";s:4:"c862";s:33:"Classes/Parser/FulltextParser.php";s:4:"f5ed";s:30:"Classes/Parser/QueryParser.php";s:4:"deb4";s:28:"Classes/Parser/SqlParser.php";s:4:"e9d2";s:32:"Classes/Sample/DataQueryHook.php";s:4:"1185";s:35:"Classes/UserFunction/FormEngine.php";s:4:"59d8";s:36:"Classes/Utility/DatabaseAnalyser.php";s:4:"2dd1";s:31:"Classes/Utility/QueryObject.php";s:4:"8262";s:30:"Classes/Utility/SqlUtility.php";s:4:"c485";s:35:"Classes/Wizard/QueryCheckWizard.php";s:4:"24c9";s:36:"Configuration/Backend/AjaxRoutes.php";s:4:"021c";s:42:"Configuration/TCA/tx_dataquery_queries.php";s:4:"dc9b";s:42:"Configuration/TCA/Overrides/tt_content.php";s:4:"d951";s:53:"Configuration/TCA/Overrides/tx_datafilter_filters.php";s:4:"e1ad";s:26:"Documentation/Includes.txt";s:4:"c83c";s:23:"Documentation/Index.rst";s:4:"baad";s:26:"Documentation/Settings.yml";s:4:"93c2";s:39:"Documentation/BehindTheScenes/Index.rst";s:4:"7aea";s:55:"Documentation/BehindTheScenes/AdvancedAliases/Index.rst";s:4:"57b6";s:47:"Documentation/BehindTheScenes/Caching/Index.rst";s:4:"e49c";s:51:"Documentation/BehindTheScenes/DataFilters/Index.rst";s:4:"9ace";s:51:"Documentation/BehindTheScenes/Limitations/Index.rst";s:4:"7b3b";s:46:"Documentation/BehindTheScenes/Output/Index.rst";s:4:"24a8";s:58:"Documentation/BehindTheScenes/ParsingAndBuilding/Index.rst";s:4:"25e1";s:52:"Documentation/BehindTheScenes/Translations/Index.rst";s:4:"8dfa";s:50:"Documentation/BehindTheScenes/Versioning/Index.rst";s:4:"2608";s:34:"Documentation/Developers/Index.rst";s:4:"2de9";s:43:"Documentation/Images/AdditionalSqlField.png";s:4:"869f";s:40:"Documentation/Images/DataqueryRecord.png";s:4:"6069";s:51:"Documentation/Images/DataqueryRecordAdvancedTab.png";s:4:"bf2b";s:50:"Documentation/Images/DataqueryRecordGeneralTab.png";s:4:"975b";s:54:"Documentation/Images/DataqueryRecordIgnoreSettings.png";s:4:"ff07";s:42:"Documentation/Images/FulltextHelpField.png";s:4:"dde1";s:36:"Documentation/Installation/Index.rst";s:4:"5edf";s:36:"Documentation/Introduction/Index.rst";s:4:"74a1";s:37:"Documentation/KnownProblems/Index.rst";s:4:"66c3";s:31:"Documentation/Queries/Index.rst";s:4:"345f";s:45:"Documentation/Queries/AdditionalSql/Index.rst";s:4:"83a1";s:40:"Documentation/Queries/Comments/Index.rst";s:4:"0724";s:43:"Documentation/Queries/Expressions/Index.rst";s:4:"de18";s:40:"Documentation/Queries/FieldUid/Index.rst";s:4:"9295";s:40:"Documentation/Queries/Fulltext/Index.rst";s:4:"e4b9";s:41:"Documentation/Queries/Functions/Index.rst";s:4:"ac42";s:37:"Documentation/Queries/Joins/Index.rst";s:4:"75f8";s:40:"Documentation/Queries/Keywords/Index.rst";s:4:"157a";s:46:"Documentation/Queries/NonSqlKeywords/Index.rst";s:4:"e46b";s:28:"Documentation/User/Index.rst";s:4:"a35d";s:40:"Resources/Private/Language/locallang.xlf";s:4:"99fc";s:64:"Resources/Private/Language/locallang_csh_txdatafilterfilters.xlf";s:4:"5be0";s:63:"Resources/Private/Language/locallang_csh_txdataqueryqueries.xlf";s:4:"dbbc";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"dcf6";s:49:"Resources/Private/Templates/QueryCheckWizard.html";s:4:"dc9b";s:45:"Resources/Public/Icons/AddDataQueryWizard.png";s:4:"95bf";s:36:"Resources/Public/Icons/DataQuery.png";s:4:"0390";s:47:"Resources/Public/JavaScript/QueryCheckWizard.js";s:4:"e4c2";s:39:"Resources/Public/Styles/CheckWizard.css";s:4:"a8a4";s:31:"Tests/Unit/DataProviderTest.php";s:4:"a2dc";s:30:"Tests/Unit/QueryParserTest.php";s:4:"1693";s:36:"Tests/Unit/SqlBuilderDefaultTest.php";s:4:"881d";s:37:"Tests/Unit/SqlBuilderLanguageTest.php";s:4:"aef1";s:29:"Tests/Unit/SqlBuilderTest.php";s:4:"d6a1";s:38:"Tests/Unit/SqlBuilderWorkspaceTest.php";s:4:"12c1";s:28:"Tests/Unit/SqlParserTest.php";s:4:"4da4";}',
);

