.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-uid:

Mandatory field "uid"
^^^^^^^^^^^^^^^^^^^^^

All queries must have a field called "uid" (this is necessary for all
the processing that happens afterward, see "Behind the scenes"). It
can be either the real "uid" of a given table or some other field
using an alias, e.g.

.. code-block:: sql

   SELECT FROM_UNIXTIME(tstamp, '%Y') AS uid FROM tt_content

If no explicit "uid" field is found in the SELECT statement, Data
Query will try to add one automatically, which may have unexpected
results. As such it is useful to use the "Validate Query" button and
look at the query that Data Query rebuilt, to see if it really matches
what you expected.

