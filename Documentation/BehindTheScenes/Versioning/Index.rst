.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-versioning:

Versioning
^^^^^^^^^^

Pretty much the same can be said for the management of versioning
(i.e. content created in workspaces). Getting the correct version of
records is about actually getting:

- the live version of records, overlaying them with any existing change,
  delete those that do not exist in the workspace anymore, move those
  that were moved in the workspace

- the records that were created in the workspace and exist only there
  for the time being

All this makes it impossible to correctly apply a limit, since after
getting the original records with the SQL statement, you may end up
with less records (those that were deleted). This particular case is
not taken into account by Data Query, so it is possible to stumble
upon an unexpected number of records. This could be improved in the
future if need arises. It has not been done now, because it would add
yet more processing and does not seem to be mission-critical.

