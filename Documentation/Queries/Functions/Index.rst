.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-functions:

SQL function calls
^^^^^^^^^^^^^^^^^^

It is possible to use any SQL function in the SELECT part of the
statement. Function calls are expected to be used with an alias. If
this is not the case, an alias will be automatically added. So the
following query:

.. code-block:: sql

   SELECT FROM_UNIXTIME(tstamp, '%Y') FROM tt_content...

would be transformed into:

.. code-block:: sql

   SELECT FROM_UNIXTIME(tstamp, '%Y') AS function_1 FROM tt_content...

Automatically generated aliases are called "function\_" plus the
position of the function inside the SELECT part (i.e. the first
function is "1", the second is "2", etc.).

Note: this feature has been tested with many different functions, but
there might be some particular where the SQL parser gets it wrong. If
you should stumble on such an issue, don't hesitate to report it.

