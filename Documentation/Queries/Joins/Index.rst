.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-joins:

Join rules
^^^^^^^^^^

The following join types are recognized: INNER, LEFT and RIGHT.

Implicit table joins are recognized and transformed into INNER joins.
So the following query:

.. code-block:: sql

   SELECT * FROM tt_news, tx_dam WHERE …

would become:

.. code-block:: sql

   SELECT * FROM tt_news INNER JOIN tx_dam WHERE …

