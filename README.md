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

Create a new connector
----------------------

A minimal connector can be defined as following :
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

We declare it as a service :
```yaml
    connector.magento_catalog:
        class: Acme\Bundle\DemoDataFlowBundle\Connector\MagentoConnector
```

Configure a configuration
-------------------------

A configuration defines expected parameters required to use a service (connector or job).

It can be defined as following, here we use JMS Serializer to easily serialize and persist any kind of configuration.

```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Configuration;

use Oro\Bundle\DataFlowBundle\Configuration\AbstractConfiguration;
use JMS\Serializer\Annotation\Type;

class CsvConfiguration extends AbstractConfiguration
{

    /**
     * @Type("string")
     * @var string
     */
    public $delimiter = ';';

    // ...

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     *
     * @return CsvConfiguration
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }
    //...
}
```

Then you can instanciate to configure a service as :
```php
<?php
$configuration = new CsvConfiguration();
$configuration->setDelimiter(',');
$connector = $this->container->get('connector.magento_catalog');
$connector->configure($configuration);
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


