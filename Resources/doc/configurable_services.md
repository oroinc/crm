Editable service (connector / job) configuration
------------------------------------------------

Make a service configuration editable means provide a way to :
- get a form to create / edit the expected service configuration
- process and store this configuration

Define the configuration form
-----------------------------

Create a form type in classic SF 2 way.

Here we just extending AbstractConfigurationType to ensure presence of basic fields as configuration description :
```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Form\Type;

use Oro\Bundle\DataFlowBundle\Form\Type\AbstractConfigurationType;

class CsvConnectorType extends AbstractConfigurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('charset', 'text', array('required' => true));
        $builder->add('delimiter', 'text', array('required' => true));
        $builder->add('enclosure', 'text', array('required' => true));
        $builder->add('escape', 'text', array('required' => true));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Acme\Bundle\DemoDataFlowBundle\Configuration\CsvConfiguration')
        );
    }

    public function getName()
    {
        return 'configuration_csv';
    }
}
```

Define form and form type as service :
```yaml
parameters:
    configuration.type.csv.class:              Acme\Bundle\DemoDataFlowBundle\Form\Type\CsvConnectorType

services:
    configuration.form.csv:
        class: Symfony\Component\Form\Form
        factory_method: createNamed
        factory_service: form.factory
        arguments: ["configuration_csv_form", "configuration_csv"]

    configuration.form.type.csv:
        class: %configuration.type.csv.class%
        tags:
            - { name: form.type, alias: configuration_csv}
```


Make the service (connector / job) editable
-------------------------------------------

We have to make our connector or job implements EditableConfigurationInterface and define required methods.

These methods allow to ask to a connector or a job which form and handler have to be used to manipulate its configuration.

Note that here we use a default form handler which come from dataflow, you can define your own too.

```php
<?php
namespace Acme\Bundle\DemoDataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\AbstractConnector;
use Oro\Bundle\DataFlowBundle\Configuration\EditableConfigurationInterface;

class CsvConnector extends AbstractConnector implements EditableConfigurationInterface
{
    public function getConfigurationFormServiceId()
    {
        return "configuration.form.csv";
    }

    public function getConfigurationFormHandlerServiceId()
    {
        return "oro_dataflow.form.handler.configuration";
    }
}
```
