<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;

class SoapTransportSettingFormType extends TransportSettingFormType
{
    const NAME = 'oro_magento_soap_transport_setting_form_type';

    /** @var SoapSettingsFormSubscriber */
    protected $subscriber;

    /**
     * @param TransportInterface $transport
     * @param SoapSettingsFormSubscriber $subscriber
     * @param TypesRegistry $registry
     */
    public function __construct(
        TransportInterface $transport,
        SoapSettingsFormSubscriber $subscriber,
        TypesRegistry $registry
    ) {
        $this->transport  = $transport;
        $this->subscriber = $subscriber;
        $this->registry   = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber($this->subscriber);

        $builder->add(
            'apiUrl',
            'text',
            ['label' => 'oro.magento.magentotransport.soap.wsdl_url.label', 'required' => true]
        );
        $builder->add(
            'apiUser',
            'text',
            ['label' => 'oro.magento.magentotransport.soap.api_user.label', 'required' => true]
        );
        $builder->add(
            'apiKey',
            'password',
            [
                'label'       => 'oro.magento.magentotransport.soap.api_key.label',
                'required'    => true,
                'constraints' => [new NotBlank()]
            ]
        );
        $builder->add(
            'isWsiMode',
            'checkbox',
            ['label' => 'oro.magento.magentotransport.soap.is_wsi_mode.label', 'required' => false]
        );

        $builder->remove('check');
        $builder->remove('websiteId');

        // added because of field orders
        $builder->add(
            'check',
            'oro_magento_transport_check_button',
            [
                'label' => 'oro.magento.magentotransport.check_connection.label'
            ]
        );
        $builder->add(
            'websiteId',
            'oro_magento_website_select',
            [
                'label'    => 'oro.magento.magentotransport.website_id.label',
                'required' => true,
                'choices_as_values' => true
            ]
        );
        $builder->add(
            'adminUrl',
            'text',
            ['label' => 'oro.magento.magentotransport.admin_url.label', 'required' => false]
        );
    }
}
