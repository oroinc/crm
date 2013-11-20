<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;

class SoapTransportSettingFormType extends AbstractType
{
    const NAME = 'orocrm_magento_soap_transport_setting_form_type';

    /** @var TransportTypeInterface */
    protected $transport;

    public function __construct(TransportTypeInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('wsdlUrl', 'text', ['label' => 'WSDL Url', 'required' => true]);
        $builder->add('apiUser', 'text', ['label' => 'API User', 'required' => true]);
        $builder->add('apiKey', 'text', ['label' => 'API Key', 'required' => true]);
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
