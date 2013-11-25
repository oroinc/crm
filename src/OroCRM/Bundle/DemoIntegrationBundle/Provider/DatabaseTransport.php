<?php

namespace OroCRM\Bundle\DemoIntegrationBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;

use OroCRM\Bundle\DemoIntegrationBundle\Form\Type\DatabaseTransportSettingForm;

class DatabaseTransport implements TransportTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.demo_integration.db.transport.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return DatabaseTransportSettingForm::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\\Bundle\\DemoIntegrationBundle\\Entity\\DatabaseTransport';
    }
}
