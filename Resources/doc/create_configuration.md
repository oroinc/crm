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

Use a configuration
-------------------

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

Persist a configuration
-----------------------

A configuration can be serialized / unserialized in xml or json format.
```php
<?php
    $format = 'json';

    // serialize configuration
    $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
    $data = $serializer->serialize($configuration, $format);

    // unserialize
    $configuration = $serializer->deserialize($data, get_class($configuration), $format);


    $confData      = $configRepo->find($configurationId);
    $serializer    = \JMS\Serializer\SerializerBuilder::create()->build();
    $configuration = $serializer->deserialize(
        $confData->getData(), $confData->getTypeName(), $confData->getFormat()
    );
    $configuration->setId($confData->getId());
    $configuration->setDescription($confData->getDescription());
```

A configuration entity (OroDataFlowBundle:Configuration) allows to easily store / retrieve it from classic doctrine backend :
```php
    // to persist
    $configuration = new Configuration();
    $configuration->setDescription($configuration->getDescription());
    $configuration->setTypeName(get_class($configuration));
    $configuration->setFormat($format);
    $configuration->setData($data);
    $this->manager->persist($configuration);
    // retrieve one
    $repository = $this->manager->getRepository('OroDataFlowBundle:Configuration');
    $configuration = $repository->find($conEntity->getId());
    // retrieve all related to a configuration type
    $configurations = $repository->findBy(array('type' => get_class($configuration)));
```

Configuration can be equally stored to / retrieved from a file.
