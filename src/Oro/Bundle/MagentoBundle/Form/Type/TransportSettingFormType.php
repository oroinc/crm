<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class TransportSettingFormType extends AbstractType
{
    const NAME = 'oro_magento_transport_setting_form_type';

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var TypesRegistry */
    protected $registry;

    /** @var SettingsFormSubscriber */
    protected $subscriber;

    /**
     * @param MagentoTransportInterface $transport
     * @param SettingsFormSubscriber $subscriber
     * @param TypesRegistry $registry
     */
    public function __construct(
        MagentoTransportInterface $transport,
        SettingsFormSubscriber $subscriber,
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
        $builder->addEventSubscriber($this->subscriber);

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

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /**
         * @var $transport MagentoTransport
         */
        $transportEntity = $form->getData();
        if ($transportEntity instanceof MagentoTransport && $transportEntity->getId()) {
            $isExtensionInstalled = $transportEntity->getIsExtensionInstalled();
            $extensionVersion = $transportEntity->getExtensionVersion();
            $isSupportExtensionVersion = $isExtensionInstalled &&
                version_compare(
                    $transportEntity->getExtensionVersion(),
                    $this->transport->getRequiredExtensionVersion(),
                    'ge'
                );

            $view->vars['oroBridgeExtension'] = [
                'isExtensionInstalled' => $isExtensionInstalled,
                'isSupportExtensionVersion' => $isSupportExtensionVersion,
                'extensionVersion' => $extensionVersion
            ];
        }
    }
}
