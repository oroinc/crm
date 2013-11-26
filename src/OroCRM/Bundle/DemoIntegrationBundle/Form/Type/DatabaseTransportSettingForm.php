<?php

namespace OroCRM\Bundle\DemoIntegrationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\IntegrationBundle\Provider\TransportTypeInterface;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class DatabaseTransportSettingForm extends AbstractType
{
    const NAME = 'orocrm_demo_integration_db_stranspot_setting';

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

                $oldPassword = $event->getForm()->get('password')->getData();
                if (empty($data['password']) && $oldPassword) {
                    // populate old password
                    $data['password'] = $oldPassword;
                } elseif (isset($data['password'])) {
                    $data['password'] = $encryptor->encryptData($data['password']);
                }

                $event->setData($data);
            }
        );

        $builder->add('host', 'text', ['label' => 'Host', 'required' => true]);
        $builder->add('login', 'text', ['label' => 'Login', 'required' => true]);
        $builder->add('password', 'password', ['label' => 'Password', 'required' => true]);
        $builder->add('dbName', 'text', ['label' => 'Database Name', 'required' => true]);
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
