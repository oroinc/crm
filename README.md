DataFlowBundle
==============

Deal with data import, export, transformation and mapping management

Main classes /  concepts
------------------------

This bundle uses some basic ETL classes to manipulate data :
- Extractors : to read data from csv file, xml file, excel file, dbal query, orm query, etc
- Tranformers : to convert data (a row / item or a value), as datetime or charset converters, callback converter, etc
- Loaders : to write data to csv, xml, excel file, database table (orm / dbal)

It provides a way to declare some connectors and jobs :
- Connector : a service which provides some jobs related to a system (for instance, Magento)
- Job : use readers, writers, transformers to process a business action (as import products from a csv file, export products to Magento, etc)


```yaml
    job.import_attributes:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\Job\ImportAttributesJob
        arguments: [ @configuration, @form, @product_manager ]
        tags:
            - { name: oro_dataflow_job, connector: connector.magento_catalog}
```

configure ou prepare (Parameters)



Create a new connector
----------------------

Connector is a service and it's define as following :
```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\AbstractConnector;

class MagentoConnector extends AbstractConnector
{
    public function configure(ConfigurationInterface $configuration)
    {
        // configure before to use ...
    }
}
```

And configuration :
```yaml
    connector.magento_catalog:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\MagentoConnector
```


Create a new job
----------------

Job is a service and it's defined as following :
```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Job\AbstractJob;

class ImportAttributesJob extends AbstractJob
{
    public function configure(ConfigurationInterface $configuration)
    {
        // configure before to use ...
    }

    public function run()
    {
        // do some stuff ...
    }
}

```

And configuration, notice that a job can be attached to one or many connectors :
```yaml
    job.import_attributes:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\Job\ImportAttributesJob
        tags:
            - { name: oro_dataflow_job, connector: connector.magento_catalog}
```

Connector registry
------------------

Registry allows to retrieve references to any connector, job and grab connector to job association :
```php
<?php
    $connectors = $this->container->get('oro_dataflow.connectors');
```

Define custom configuration
---------------------------
Each connector / job have to be configured before to be used.

AbstractConfiguration helps to define specialized configuration for any custom service (based on Symfony Config component).

This configuration can be validated.


