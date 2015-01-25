.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _behind-the-scenes-translations:

Translations, ordering and limits
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Translations are handled in TYPO3 using a concept called "overlays".
In this system records are first fetched in the default language and
then overlaid with their translations, coming from the same table
(most commonly) or from a different table (as is the case for the
"pages" table) depending on the chosen translation paradigm.

While this system has a lot of advantages, it also makes it difficult
to select a limited number of records in a non-default language,
sorted alphabetically. Indeed records are originally selected in the
default language. So alphabetical ordering cannot take place simply
during the SQL query. Furthermore some records may not exist as
translations, and others exist only in non-default languages. This
means that the total number of records cannot be known in advance for
translations. Again the limit cannot be applied directly to the SQL
query.

Data Query tries to make this part simpler too. When the current
language is not the default, it will get the records in the default
language, then get all the translations and perform the overlays as
appropriate. For the ordering, Data Query will check if the fields
being ordered on are alphanumeric fields or not. If the fields are not
alphanumeric (e.g. date fields or integer fields) they can be ordered
in the SQL statement, as overlays will not have an influence on them.
On the other hand if at least one of the fields selected for ordering
contains alphanumeric data, it will not be ordered using SQL. The
ordering is done after overlays have been applied.

.. note::

   For Data Query to work its magic, every field needs to be
   defined in the TCA. Be careful with the traditional "sorting" field,
   because it is normally not defined in the TCA, so Data Query will not
   consider it as an integer field. Adding a TCA definition for the
   "sorting" field solves this issue.

Limits are always applied on the resulting recordset and not in the
SQL, except if they are explicitly defined using the LIMIT keyword in
the SQL statement. So limits should be defined using data filters,
unless there's a good reason to use the SQL LIMIT.

The drawback of this approach is that it requires far more computing
power and memory than if the task could be delegated to the database
(hoping that the database is optimized for such operations). It is the
main reason why Data Query has its :ref:`own caching mechanism <behind-the-scenes-caching>`,
to avoid repeating operations.
