
Create a new job
----------------

Job is a service and it's defined as following :
```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Job\AbstractJob;

class ImportAttributesJob extends AbstractJob
{

    public function run()
    {
        // do some stuff ...
    }
}

```

And configuration, notice that a job can be attached to one or many connectors :
```yaml
parameters:
    job.type.import_customer.class:            Acme\Bundle\DemoDataFlowBundle\Job\ImportCustomersJob

services:
    job.import_attributes:
        class: %job.type.import_attribute.class%
        arguments: [ @product_manager ]
        tags:
            - { name: oro_dataflow_job, connector: connector.magento_catalog}
```
