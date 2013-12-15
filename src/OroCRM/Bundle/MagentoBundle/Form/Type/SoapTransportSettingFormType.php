<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorTypeInterface;
use OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use OroCRM\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;

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

        $registry = $this->registry;
        $closure  = function ($data, FormInterface $form) use ($registry) {
            if ($data != true
                && $form->getParent()
                && $form->getParent()->getConfig()->getType()->getInnerType() instanceof ChannelType
            ) {
                $connectors = $form->getParent()->get('connectors');
                if ($connectors) {
                    $config = $connectors->getConfig()->getOptions();
                    unset($config['choice_list']);
                    unset($config['choices']);
                } else {
                    $config = [];
                }

                if (array_key_exists('auto_initialize', $config)) {
                    $config['auto_initialize'] = false;
                }

                $types        = $registry->getRegisteredConnectorsTypes('magento');
                $allowedTypes = $types->filter(
                    function (ConnectorTypeInterface $connector) {
                        return !$connector instanceof ExtensionAwareInterface;
                    }
                );

                $allowedTypeKeys   = $allowedTypes->getKeys();
                $allowedTypeValues = $allowedTypes->map(
                    function (ConnectorTypeInterface $connector) {
                        return $connector->getLabel();
                    }
                )->toArray();

                $allowedTypesChoices = array_combine($allowedTypeKeys, $allowedTypeValues);
                $form->getParent()
                    ->add('connectors', 'choice', array_merge($config, ['choices' => $allowedTypesChoices]));
            }
        };

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
            $builder->create('isExtensionInstalled', 'hidden')
                ->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $event) use ($closure) {
                        $form = $event->getForm()->getParent();
                        $data = $event->getData();

                        $closure($data, $form);
                    }
                )->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    function (FormEvent $event) use ($closure) {
                        $form = $event->getForm()->getParent();
                        $data = $event->getData();

                        $closure($data, $form);
                    }
                )
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
