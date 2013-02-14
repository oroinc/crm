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
