.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-datafilters:

Queries and Data Filters
^^^^^^^^^^^^^^^^^^^^^^^^

The main idea that underlies the whole Tesseract concept is to have
libraries of elements that can be reused and combined in different
ways. Part of this flexibility comes from the use of Data Filters. Via
a controller (like the Display Controller) a given Data Query can be
set in relation with a Data Filter. This makes it possible to reuse a
Data Query and change it dynamically via the filters.

Technically the filter structure created by the Data Filter is passed
to the Data Query by the controller using the
:code:`\Tesseract\Tesseract\Service\ConsumerBase::setDataFilter::setDataFilter()` method from the
base provider interface. The filter structure is then translated into
SQL by Data Query and added to the base query from the "SQL query"
field.


.. _behind-the-scenes-datafilters-alias:

Using aliases in filters
""""""""""""""""""""""""

It is normally not possible to use aliases in the WHERE clause.
However Data Query will recognize aliases used in Data Filters and map
them to the original field they represented. Imagine the following
query:

.. code-block:: sql

	SELECT FROM_UNIXTIME(tstamp, '%Y') AS year FROM tt_content

with the following Data Filter:

.. code-block:: text

	year = date:Y

(which would select all content element edited during the current
year). This will be (correctly) interpreted as:

.. code-block:: sql

	SELECT FROM_UNIXTIME(tstamp, '%Y') AS year FROM tt_content WHERE (FROM_UNIXTIME(tstamp, '%Y') = 2010)

(assuming the current year is 2010), instead of:

.. code-block:: sql

	SELECT FROM_UNIXTIME(tstamp, '%Y') AS year FROM tt_content WHERE (year = 2010)

which would cause a SQL syntax error.


.. _behind-the-scenes-datafilters-array-values:

Array values from filters
"""""""""""""""""""""""""

Imagine setting up a group of checkboxes like:

.. code-block:: html

	<input type="checkbox" name="tx_myext[foo][]" value="bob" />
	<input type="checkbox" name="tx_myext[foo][]" value="alice" />

Next imagine a filter like:

.. code-block:: text

	fe_users.name like gp:tx_myext|foo

The value returned will be an array. This is handled by Data Query by
creating a LIKE condition for each value and concatenating all these
conditions with a "OR" logical operator. So the above example would
result in the following SQL condition (assuming both checkboxes were
checked):

.. code-block:: sql

	(fe_users.name LIKE '%bob%' OR fe_users.name LIKE '%alice%')

It's not possible to change the logical operator to "AND" (this didn't
seem useful after thinking quite a bit about it; the whole reasoning
is outside of the scope of this manual; if you have a use case for
this, please open a feature request on Forge).

