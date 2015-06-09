.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-comments:

Comments
^^^^^^^^

It is possible to comment lines in the query by starting them with a
:code:`#` or :code:`//` marker. Example:

.. code-block:: sql

   SELECT * FROM tt_content
   #WHERE header like '%foo%'

The second line in this query will be ignored.

