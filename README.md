# EW_ConfigGlobalSearch

## Overview and Usage

This Magento module adds System Configuration fields and groups to the global admin search.
Magento system config fields aren't always categorized in a way that is immediately obvious, so you can now
search for a field and be linked directly to its section.

If you consistently rely on n98-magerun's `config:search` functionality to find config fields, this allows you 
to do the same type of search directly in the admin.

![Screenshot of Magento admin global search with system config results](https://ericwie.se/assets/img/work/magento-configglobalsearch-v2.png)

## Features

- Searches system config groups titles and individual fields names from admin global search.
- Search results link directly to associated system config section.
- Respects system config ACL -- results only contain sections for which the current admin user has permission to see.

## Known Issues:

- System config labels are currently untranslated.
