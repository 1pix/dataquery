.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developers:

Developer's Guide
-----------------

Hooks
^^^^^

There are two hooks that can be used to manipulate the Data Structure
produced by dataquery. They are similar but one is called before the
structure is stored into cache, and the second one is called every
time (i.e. either when the structure was freshly generated or when it
was read from cache).

postProcessDataStructure
  Called every time

postProcessDataStructureBeforeCache
  Called when a structure has
  been newly generated and is about to be stored into cache (note that
  this hook is called also if the structure is **not** written to
  cache)

Both hooks receive as arguments the full Data Structure as well as a
back-reference to the calling :code:`\Tesseract\Dataquery\Component\DataProvider` object.
They are expected to return a complete Data Structure even if they did
not perform any change.

Skeleton code for both hooks can be found in
:file:`Classes/Sample/DataQueryHook.php`.

Another hook is available for manipulating the tables and fields
information:

postProcessFieldInformation
  This hook is inside
  :code:`\Tesseract\Dataquery\Component\DataProvider::getTablesAndFields()`, a method which
  is called when "dataquery" provides Data Consumers with a list of
  available tables and fields while working within the TYPO3 backend
  (this is how, for example, "templatedisplay" knows which fields to
  map). This hook can be used when the data structure has been modified
  by one of the above hooks and such changes need to be known in the
  backend too. This is generally the case when the hooks change the
  tables and fields structure, so that these changed elements can be
  mapped properly.

Finally a hook can be used during cache hash calculation, for
manipulating the parameters used to calculate the hash:

processCacheHashParameters
  This hook is called inside
  :code:`\Tesseract\Dataquery\Component\DataProvider::calculateCacheHash()`. It receives as
  arguments the current cache parameters (an associative array) and a
  back-reference to the calling object (an instance of
  :code:`\Tesseract\Dataquery\Component\DataProvider`). It is expected to return the full
  array of cache parameters, whether it modified them or not. Classes
  using this hook must implement interface
  :code:`\Tesseract\Dataquery\Cache\CacheParametersProcessorInterface`.

