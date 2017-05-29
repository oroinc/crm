<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RestTransportSettingFormType extends AbstractTransportSettingFormType
{
    const NAME = 'oro_magento_rest_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'apiUrl',
            'text',
            ['label' => 'oro.magento.magentotransport.rest.api_url.label', 'required' => true]
        );

        $builder->add(
            'apiUser',
            'text',
            ['label' => 'oro.magento.magentotransport.rest.api_user.label', 'required' => true]
        );

        $builder->add(
            'apiKey',
            'password',
            [
                'label'       => 'oro.magento.magentotransport.rest.api_key.label',
                'required'    => true,
                'constraints' => [new NotBlank()]
            ]
        );
    }

    /**
     * {@inheritdoc}
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
