<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;

class SoapTransportSettingFormType extends AbstractType
{
    const NAME = 'orocrm_magento_soap_transport_setting_form_type';

    /** @var Mcrypt */
    protected $encryptor;

    /** @var TransportTypeInterface */
    protected $transport;

    public function __construct(TransportTypeInterface $transport, Mcrypt $encryptor)
    {
        $this->transport = $transport;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $encryptor = $this->encryptor;
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($encryptor) {
                $data = (array)$event->getData();

                $oldPassword = $event->getForm()->get('apiKey')->getData();
                if (empty($data['apiKey']) && $oldPassword) {
                    // populate old password
                    $data['apiKey'] = $oldPassword;
                } else {
                    $data['apiKey'] = $encryptor->encryptData($data['apiKey']);
                }

                $event->setData($data);
            }
        );

        $builder->add('label', 'text', ['label' => 'Label', 'required' => true]);
        $builder->add('wsdlUrl', 'text', ['label' => 'WSDL Url', 'required' => true]);
        $builder->add('apiUser', 'text', ['label' => 'API User', 'required' => true]);
        $builder->add('apiKey', 'password', ['label' => 'API Key', 'required' => true]);
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
