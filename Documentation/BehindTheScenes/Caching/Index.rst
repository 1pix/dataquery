.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-caching:

Caching
^^^^^^^

As explained above, Data Query performs some rather intensive
calculations. In order to avoid repeating them needlessly, it
implements it own caching mechanism. Cache is written to the
:code:`tx\_dataquery\_cache` table. Entries are keyed to the page they
are related to (:code:`page\_id` field) and to the Data Query record
being executed (:code:`query\_id` field). Furthermore every entry in
this table is identified by a unique hash constructed from the
following components:

- the current filter applied to the query, if any

- the list of primary keys provided by the secondary provider, if any

- additional, optional parameters (this feature is currently not used)

- the current FE language (:code:`$GLOBALS['TSFE']->sys_language_content`)

- the current FE user groups, if any

The last two parameters ensure that the cache is stored correctly with
respect to language and FE user rights, since all this is handled
automatically by Data Query (as described above). The Data Structure
gets stored into cache as a serialized array. Storage will be aborted
if the size of the resulting string exceeds the cache limit, as
defined in the extension's configuration (see "Installation" above).
This mechanism ensures that the cache table does not grow out of
control, as this could not only slow the system, but even crash the
database server.

Every time Data Query needs to execute a query, it will first look if
it has an existing, up to date Data Structure in the cache. If it
does, it will get the cached data and unserialize it. Otherwise, it
will calculate a new Data Structure.

The duration of the cache is defined for each query as shown in the
:ref:`User Manual <user-manual>`. The default value is 86400 seconds (= 1 day).
Setting a value of "0" will disable caching for the query (see below).


.. _behind-the-scenes-caching-avoiding:

When to avoid caching
"""""""""""""""""""""

The basic reasons for using the Data Query cache is to avoid
calculating the whole Data Structure again if it has already been
done. This can be – for example – because a given search pattern has
recently been already used. Quite typically it is also used when
paginating through results. As Data Query handles the limits itself
(as explained above) it will re-use the same Data Structure when
paginating through records (the limit is not part of the cache key
hash; all records are stored in the cache).

There are however circumstances when this caching mechanism is
useless. When using a cached Display Controller (pi1), the resulting
content will be put into the TYPO3 cache along with the rest of the
page. When that page is called up again, it is served from the TYPO3
cache. Data Query is not called at all, its cache is not needed. In
such a case, the cache duration of relevant queries should be set to
"0" in order to avoid bloating the Data Query cache table with useless
entries.


.. _behind-the-scenes-caching-cleaning:

Cleaning up the cache
"""""""""""""""""""""

Whenever a query is modified the cache should be cleared, to ensure
that old data will not be served anymore. Data Query hooks into
TYPO3's cache clearing mechanism to simplify this task:

- when executing the "Clear all caches" command, the
  :code:`tx\_dataquery\_cache` table will be emptied

- when executing the "Clear page cache" command, all
  :code:`tx\_dataquery\_cache` entries related to that page will be
  deleted

However expired cache entries are **never** deleted, as TYPO3 does
not provide an automatic way to do this. Instead you should seriously
consider using an extension such as "cachecleaner" that makes it easy
delete expired records from any database table on a regular basis.

