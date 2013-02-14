Define a configuration
----------------------

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

Then you can instanciate precedent object to configure a connector service as :
```php
<?php
$configuration = new CsvConfiguration();
$configuration->setDelimiter(',');
$connector = $this->container->get('connector.magento_catalog');
$connector->configure($configuration);

$jobConfiguration = new OtherConfiguration();
$job = $this->container->get('job.import_attributes');
$job->configure($configuration, $jobConfiguration);
```

You can also use classic Symfony validation (with yaml file for instance) to ensure the configuration validation.
