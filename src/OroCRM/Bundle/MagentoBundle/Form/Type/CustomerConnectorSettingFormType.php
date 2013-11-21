<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\IntegrationBundle\Form\Type\AbstractConnectorSettingFormType;

class CustomerConnectorSettingFormType extends AbstractConnectorSettingFormType
{
    const NAME = 'orocrm_magento_customer_connector_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
