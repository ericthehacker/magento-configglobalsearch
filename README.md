# EW_ConfigGlobalSearch

## Overview and Usage

This Magento module adds System Configuration fields and groups to the global admin config.
Magento system config fields aren't always categorized in a way that is immediately obvious, so you can now
search for a field and be linked directly to its section.

If you consistently rely on n98-magerun's `config:search` functionality to find config fields, this allows you 
to do the same type of search directly in the admin.

![Screenshot of Magento admin global search with system config results](https://ericwie.se/assets/img/work/magento-configglobalsearch.png)


## Known Issues:

- I wrote this all in one evening. I would wait a day or two if I were you.
- ACL is untested. Section-specific ACL settings *may* not be honored currently.
- System config labels are currently untranslated.