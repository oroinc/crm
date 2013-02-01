Flexible entity
===============

Areas of responsibilities :
- create a flexible entity with dynamic attribute management
- create flexible and attribute forms by using basic form type
- extend / customize your flexible for business needs

Based on classic Doctrine 2 classes, entity, repository, entity manager

Install
=======

To install for dev :

```bash
$ php composer.phar update --dev
```
To use as dependency, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "oro/FlexibleEntityBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/FlexibleEntityBundle.git",
            "branch": "master"
        }
    ]

```

Run unit tests
==============

```bash
$ phpunit --coverage-html=cov/
```

Main classes / concepts
=======================

- Attribute : the smallest entity, a name, a sku, a price, have a type and some configuration
- AttribueType : aims to configure basic attribute configuration as storage, rendering
- Flexible : the flexible entity, ie, an entity which support usage of dynamic attribute
- FlexibleValue : the value related to an entity and an attribute
- FlexibleManager :  the service which allows to easily manipulate flexible entity and provides "glue" between pieces
- FlexibleRepository : aims to build query on flexible storage 

How to use ?
============

- [Create a flexible entity](Resources/doc/flexible.md)
- [Use flexible repository](Resources/doc/repository.md)
- [Dive into flexible value](Resources/doc/value.md)
- [Create a flexible form](Resources/doc/form.md)
- [Customize manager and repository](Resources/doc/customize_manager.md)
- [Create an extended attribute](Resources/doc/extended_attribute.md)
- [Others customizations](Resources/doc/customize_others.md)

Enhancement
===========

- add new attributes when load an existing flexible
- add a default is_unique behavior
- deal with in, like, etc in queries 
- default fallback (locale, scope) in queries
