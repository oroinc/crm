<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Form\Type\IsDisplayOrderNotesFormType;

class TransportSettingFormTypeWithIsDisplayOrderNotesStub extends AbstractType
{
    const NAME = 'oro_magento_soap_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            AbstractTransportSettingFormType::IS_DISPLAY_ORDER_NOTES_FIELD_NAME,
            IsDisplayOrderNotesFormType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
