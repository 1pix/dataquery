.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-parsing-and-building:

Query parsing and query building
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The query that is entered in the "SQL Query" field is parsed by Data
Query into its individual components. It is split along the various
allowed keywords and each part can have whatever specific treatment is
necessary. This is particularly true for the SELECT part. Each field
is isolated and stored in an array. If the "\*" wildcard is used, it
is replaced by an explicit list of all fields from the given table.

In a second step Data Query makes sure that every necessary field is
indeed selected. This means it will add a "uid" field if it finds
none. It will also add language-related fields necessary for the
overlay process.

In the WHERE part, conditions are added to match all enable fields of
the given table, as defined in its TCA. Conditions for language
overlays are also added if necessary. The same goes for workspaces:
these are not directly supported (in the sense that the preview will
not be correct), but Data Query at least ensures that all records
selected belong to the live workspace.

This means that the user does not have to worry about all these
TYPO3-specific fields when writing a query. For example, a simple
query like:

.. code-block:: sql

   SELECT uid, title FROM tt_news

will be transformed into:

.. code-block:: sql

   SELECT tt_news.uid, tt_news.title, tt_news.pid AS tt_news$pid, tt_news.sys_language_uid AS tt_news$sys_language_uid
   FROM tt_news AS tt_news WHERE tt_news.deleted=0 AND tt_news.t3ver_state<=0 AND tt_news.hidden=0
   AND (tt_news.starttime<=1257863340) AND (tt_news.endtime=0 OR tt_news.endtime>1257863340)
   AND (tt_news.fe_group='' OR tt_news.fe_group IS NULL OR tt_news.fe_group='0'
   OR (tt_news.fe_group LIKE '%,0,%' OR tt_news.fe_group LIKE '0,%' OR tt_news.fe_group LIKE '%,0' OR tt_news.fe_group='0')
   OR (tt_news.fe_group LIKE '%,-1,%' OR tt_news.fe_group LIKE '-1,%' OR tt_news.fe_group LIKE '%,-1' OR tt_news.fe_group='-1'))
   AND (tt_news.sys_language_uid IN (0,-1)) AND tt_news.t3ver_oid = '0'

All conditions coming from filters are also added (see
:ref:`Queries and Data Filters <behind-the-scenes-datafilters>`),
as well as the additional SQL, if defined.

The ORDER clause also requires a special handling.
See :ref:`Translations, ordering and limits <behind-the-scenes-translations>`.

