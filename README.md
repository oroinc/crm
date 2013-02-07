DataFlowBundle
==============

Data import, export, transformation and mapping management

Main classes /  concepts
========================

This bundle provides bases classes to manipulate data :
- Extractors : to read data from csv file, xml file, excel file, dbal query, orm query, etc
- Tranformers : to convert data (row / item or value), as datetime converter, charset converter, object converter, callback converter (allow to easily define a simple transform), etc
- Loaders : to export / load data to csv, xml, excel file, database table (orm / dbal)

It provides a way to add some connectors as services and theirs related jobs :
- Connector : a service which define its own jobs to provide some useful business actions related to a system (for instance, Magento)
- Job : use readers, writers, transformers to process a business action (as import products from a csv file, export PIM product to Magento, etc)

Create new connector
====================

Connector is a service and it's define as following :

```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\AbstractConnector;

class MagentoConnector extends AbstractConnector
{

}
```

And configuration :

```yaml
    connector.magento_catalog:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\MagentoConnector
```


Create new job
==============

Job is a service and it's define as following :

```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Connector\Job;

use Oro\Bundle\DataFlowBundle\Connector\Job\AbstractJob;

class ImportAttributesJob extends AbstractJob
{

    public function configure($parameters)
    {
        // configure before to use ...
    }

    public function run()
    {
        // do some stuff ...
    }

}

```

And configuration :

```yaml
    job.import_attributes:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\Job\ImportAttributesJob
        arguments: [ @product_manager ]
        tags:
            - { name: oro_dataflow_job, connector: connector.magento_catalog}
```
