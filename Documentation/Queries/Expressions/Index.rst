.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-expressions:

Expressions in queries
^^^^^^^^^^^^^^^^^^^^^^

It is possible to use `expressions <http://typo3.org/extensions/repository/view/expressions>`_
inside a query. Consider the following:

.. code-block:: sql

   SELECT cn_short_{config:language} FROM static_countries

The expression refers to the config.language TypoScript value. If that
value is "en", the query will select the "cn\_short\_en" field. If
it's "fr", it will be "cn\_short\_fr".

This is a very convenient way to dynamically modify a query.

Expressions can be used anywhere inside a query, but should **not**
be used in the WHERE clause. Instead Data Filters should be used in
this case. This is not a limitation per se, just a best practice.

For more information on expressions, please refer to the
`extension manual <http://docs.typo3.org/typo3cms/extensions/expressions/>`_.

