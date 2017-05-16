<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;

class TransportSettingFormType extends AbstractType
{
    const NAME = 'oro_magento_transport_setting_form_type';

    /** @var TransportInterface */
    protected $transport;

    /** @var TypesRegistry */
    protected $registry;

    /**
     * @param TransportInterface $transport
     * @param TypesRegistry $registry
     */
    public function __construct(
        TransportInterface $transport,
        TypesRegistry $registry
    ) {
        $this->transport  = $transport;
        $this->registry   = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'apiUrl',
            'text',
            ['label' => '', 'required' => true]
        );

        $builder->add(
            'apiUser',
            'text',
            ['label' => '', 'required' => true]
        );

        $builder->add(
            'apiKey',
            'password'
        );

        $builder->add(
            'guestCustomerSync',
            'checkbox',
            [
                'label' => 'oro.magento.magentotransport.guest_customer_sync.label',
                'tooltip' => 'oro.magento.magentotransport.guest_customer_sync.tooltip',
                'required' => false
            ]
        );
        $builder->add(
            'syncStartDate',
            'oro_date',
            [
                'label'      => 'oro.magento.magentotransport.sync_start_date.label',
                'required'   => true,
                'tooltip'    => 'oro.magento.magentotransport.sync_start_date.tooltip',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            ]
        );

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
            $builder->create('websites', 'hidden')
                ->addViewTransformer(new ArrayToJsonTransformer())
        );

        $builder->add(
            $builder
                ->create('isExtensionInstalled', 'hidden')
                ->addEventSubscriber(new ConnectorsFormSubscriber($this->registry))
        );

        $builder->add('magentoVersion', 'hidden')
            ->add('extensionVersion', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->transport->getSettingsEntityFQCN()]);
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
        return static::NAME;
    }
}
