DataFlowBundle
==============

Deal with data import, export, transformation and mapping management

Main classes /  concepts
------------------------

This bundle detects any declared application services which are related to import / export and allows to use them in a generic way.

It makes easy to create your own :
- Connector : a service which groups several jobs related to an external system (for instance, Magento)
- Job : a service which use read, transform, write data to process a business action (for instance, import products from a csv file)
- Configuration : to declare and validate required configuration of connector and job

Job uses some basic ETL classes to manipulate data :
- Extractors : to read data from csv file, xml file, excel file, dbal query, orm query, etc
- Tranformers : to convert data (a row / item or a value), as datetime or charset converters, callback converter, etc
- Loaders : to write data to csv, xml, excel file, database table (orm / dbal)

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
        "oro/DataFlowBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/DataFlowBundle.git",
            "branch": "master"
        }
    ]

```

Run unit tests
==============

```bash
$ phpunit --coverage-html=cov/
```

How to use ?
============

- [Create a connector](Resources/doc/create_connector.md)
- [Create a job](Resources/doc/create_job.md)
- [Create a configuration](Resources/doc/create_configuration.md)
- [Make your service configuration editable](Resources/doc/configurable_services.md)


TODO : refactor following in dedicated sections


Connector registry
------------------

Registry allows to retrieve references to any connector, job and grab connector to job association :
```php
<?php
    $connectors = $this->container->get('oro_dataflow.connectors');
```



