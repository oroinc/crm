OroCRM
========================

Welcome to OroCRM an Open Source Client Relationship Management (CRM) tool.

This document contains information on how to download, install, and start
using OroCRM. For a more detailed explanation, see the [Installation]
chapter.

Requirements
------------

OroCRM requires Symfony 2.4, Doctrine 2 and PHP 5.4.4 or above.

Installation
------------

```bash
git clone https://github.com/orocrm/crm.git

curl -s https://getcomposer.org/installer | php

php composer.phar install
```

Run unit tests
--------------

To run unit tests of any bundles :

```bash
phpunit
```

Use as dependency in composer
-----------------------------

```yaml
    "require": {
        "oro/crm": "1.0.*",
    }
```
