.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _queries-non-standard-keywords:

Non-standard SQL keywords
^^^^^^^^^^^^^^^^^^^^^^^^^

Data Query is open to support non-standard SQL keywords for increased
ease of use. Obviously these are not handled inside the SQL query, but
during processing of the recordset.

- MAX

  The MAX keyword can be used inside a JOIN statement and represents a
  limit applied to the joined records. It is useful – for example – to
  get a single record for each join. Example:

  .. code-block:: sql

     SELECT pages.uid, pages.title, tt_content.uid, tt_content_header FROM pages
     LEFT JOIN tt_content ON tt_content.pid = pages.uid MAX 1

  The above query will return the first content element found for each
  page, instead of all of them.

