.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-advanced-aliases:

Advanced uses of aliases
^^^^^^^^^^^^^^^^^^^^^^^^

Aliases can be used in the SQL query to modify where the data is
stored in the Data Structure (as explained above). It is possible to
"move" a field from one table to another. Let's take a look at the SQL
query that appears in the introductory screenshot:

.. code-block:: sql

   SELECT uid, title, COUNT(children.uid) AS pages.children FROM pages
   LEFT JOIN pages AS children ON children.pid = pages.uid
   WHERE children.uid IS NOT NULL AND pages.pid = 1
   ORDER BY pages.title ASC GROUP BY pages.uid

If you try to execute it as is you will get several SQL errors. It is
indeed not correct, but will be by the time Data Query has rewritten
it. Anyway the important point here is to look at the alias used for
:code:`COUNT(children.uid)`: "pages.children". What this will do is
to "move" the "children" column to the "pages" table.

The result of the above query will be something like:

+---+-----------+-----------------+----------------+
|   | pages$uid | pages$title     | pages$children |
+===+===========+=================+================+
| 0 | 1         | My first page   | 2              |
+---+-----------+-----------------+----------------+
| 1 | 5         | Some other page | 0              |
+---+-----------+-----------------+----------------+
|   | ...       |                 |                |
+---+-----------+-----------------+----------------+

To Data Query all fields now seem related to the "pages" table. This
will result in a Data Structure with no subtables. This is often very
convenient as it makes it easier to use the results (in
"templatedisplay" for example) and can save unnecessary loops on
subtables.

