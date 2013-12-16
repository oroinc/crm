<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;
use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapConnectorsFormSubscriber;

class SoapTransportSettingFormType extends AbstractType
{
    const NAME = 'orocrm_magento_soap_transport_setting_form_type';

    /** @var TransportTypeInterface */
    protected $transport;

    /** @var SoapSettingsFormSubscriber */
    protected $subscriber;

    /** @var TypesRegistry */
    protected $registry;

    public function __construct(
        TransportTypeInterface $transport,
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
        $builder->addEventSubscriber($this->subscriber);

        $subscriber = new SoapConnectorsFormSubscriber($this->registry);

        $builder->add('wsdlUrl', 'text', ['label' => 'SOAP WSDL Url', 'required' => true]);
        $builder->add('apiUser', 'text', ['label' => 'SOAP API User', 'required' => true]);
        $builder->add('apiKey', 'password', ['label' => 'SOAP API Key', 'required' => true]);
        $builder->add(
            'syncStartDate',
            'oro_date',
            [
                'label'      => 'Sync start date',
                'required'   => true,
                'tooltip'    => 'Provide the start date you wish to import data from.',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            ]
        );
        $builder->add('check', 'button', ['label' => 'Check connection']);
        $builder->add(
            'websiteId',
            'choice',
            [
                'label'    => 'Website',
                'required' => true,
                'tooltip'  => 'List could be refreshed using connection settings filled above.',
            ]
        )->add(
            $builder->create('websites', 'hidden')
                ->addViewTransformer(new ArrayToJsonTransformer())
        )->add(
            $builder
                ->create('isExtensionInstalled', 'hidden')
                ->addEventSubscriber($subscriber)
        );
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
        return self::NAME;
    }
}
