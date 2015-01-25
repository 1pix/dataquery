.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-keywords:

Allowed keywords
^^^^^^^^^^^^^^^^

The following SQL keywords are allowed:

- SELECT

- FROM

- INNER JOIN

- LEFT JOIN

- RIGHT JOIN

- WHERE

- GROUP BY

- ORDER BY

- LIMIT

- OFFSET

On top of these keywords, it is possible to use:

- aliases (using "AS"), see `Advanced uses of aliases <behind-the-scenes-advanced-aliases>`

- the "ON" clause inside a JOIN.

- DISTINCT at the beginning of the list of SELECTed fields. See more
  about the use of DISTINCT below.

- ASC, DESC in the ORDER BY part.

- any function call in the list of SELECTed fields.
  More details in `SQL function calls <queries-functions>`.

- the alternate OFFSET syntax (i.e. you can use :code:`LIMIT x,y` or
  :code:`LIMIT y OFFSET x` ).

The following is **not** supported:

- static values in the SELECT part of the query

- using brackets around field names. Thus don't write
  :code:`SELECT (title) AS headline FROM tt\_news`, but
  :code:`SELECT title AS headline FROM tt\_news`


.. _queries-keywords-distinct:

Using DISTINCT
""""""""""""""

Using the DISTINCT keyword in your queries requires to pay attention
to a couple of peculiarities:

#. It is very likely that the DISTINCT field in your query is the actual
   primary key in your query. Assuming this, Data Query does not
   automatically add the "uid" field to this query (if it exists) to
   avoid messing up the effect of DISTINCT. Thus it is your
   responsibility to explicitly designate a "uid" in this case, using
   aliases. Example:

   .. code-block:: sql

      SELECT DISTINCT name AS uid FROM fe_users

#. In a more complex scenario using several fields with DISTINCT, you
   should build a concatenated string with all fields involved to be used
   as uid. Example:

   .. code-block:: sql

      SELECT DISTINCT name, username, CONCAT(name, username) AS uid FROM fe_users

Furthermore, please note that using DISTINCT will disable language
overlays (because of the limitations discussed in
:ref:`Behind the scenes <behind-the-scenes>`).

