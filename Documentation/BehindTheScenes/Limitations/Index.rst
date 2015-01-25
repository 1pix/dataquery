.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-limitations:

Limitations
^^^^^^^^^^^

The overlay mechanism described above adds other limitations beyond
the simple number of records eventually returned by Data Query. Indeed
it is not possible to build a multi-lingual full-text search. For a
detailed explanation, please refer to the "Text search" section of the
"Limitations" chapter in the manual of the "overlays" extension.

On the other hand Data Query goes beyond "overlays" limitation
regarding table joins. Data Query effectively "de-joins" tables,
overlays their records separately and joins them again after that.
This process is not entirely transparent however. In general database
relations created in a workspace will be built – quite obviously –
with the primary keys of the overlay records (and not the live ones).
In such a case, in order to be able to perform the join in SQL, it is
necessary to query the overlay records and not the live ones. This is
the reason for the "Directly get version overlays for tables" option
described above.

