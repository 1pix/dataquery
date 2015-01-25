.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _installation:

Installation
------------

Installation is pretty straightforward, but the extension is useless
on its own. It must be installed as part of the Tesseract project.

The only condition is that "dataquery" must be installed **after**
"datafilter", because it adds a field to the latter. If this order is
not respected in your installation, either modify the extension list
yourself (in :code:`typo3conf/LocalConfiguration.php`) or uninstall and
reinstall "dataquery".

The extension provides a single configuration option:

Cache limit
  Data Query comes with its own cache system, which is
  detailed later. The elements cached by Data Query may be quite large
  and may actually crash the database system. As such this option
  provides a way to limit the size of elements written to the cache
  table. Setting this option to 0 is equivalent to having no limit, but
  you should be cautious about this. See the chapter about cache for
  more details.
