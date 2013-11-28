<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;

class SoapTransportSettingFormType extends AbstractType
{
    const NAME = 'orocrm_magento_soap_transport_setting_form_type';

    /** @var TransportTypeInterface */
    protected $transport;

    /** @var SoapSettingsFormSubscriber */
    protected $subscriber;

    public function __construct(TransportTypeInterface $transport, SoapSettingsFormSubscriber $subscriber)
    {
        $this->transport  = $transport;
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);

        $builder->add('wsdlUrl', 'text', ['label' => 'SOAP WSDL Url', 'required' => true]);
        $builder->add('apiUser', 'text', ['label' => 'SOAP API User', 'required' => true]);
        $builder->add('apiKey', 'password', ['label' => 'SOAP API Key', 'required' => true]);
        // @TODO put default value here, when form updated via ajax
        $builder->add(
            'syncStartDate',
            'oro_date',
            [
                'label'    => 'Sync start date',
                'required' => true,
                'tooltip'  => 'Synchronization period start date is necessary'
                    . ' due to magento API do not provide possibility to paginate,'
                    . ' dates will be used for splitting data on batches.',
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
        );
        $builder->add(
            $builder->create('websites', 'hidden')
                ->addViewTransformer(new ArrayToJsonTransformer())
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
