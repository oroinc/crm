OroCRM
========================

Welcome to OroCRM an Open Source Client Relationship Management (CRM) tool.

This document contains information on how to download, install, and start
using OroCRM. For a more detailed explanation, see the [Installation]
chapter.

## Installation

OroCRM is a package that depends on Oro Platform and requires an application to run it.
A [crm-application](https://github.com/orocrm/crm-application) is an example of such application which
simplifies initial project setup and configuration.

## Use as dependency in composer

In order to define dependency on OroCRM in your project just add this to composer.json file:

```yaml
    "require": {
        "oro/crm": "1.0.*"
    }
```
