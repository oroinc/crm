<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Form\EventListener\CustomerTypeSubscriber;

class CustomerType extends AbstractType
{
    const NAME = 'orocrm_magento_customer';

    /**
     * @var string
     */
    protected $customerClassName;

    /**
     * @var string
     */
    protected $customerAddressClassName;

    /**
     * @param string $customerClassName
     * @param string $customerAddressClassName
     */
    public function __construct($customerClassName, $customerAddressClassName)
    {
        $this->customerClassName = $customerClassName;
        $this->customerAddressClassName = $customerAddressClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isExisting = $builder->getData() && $builder->getData()->getId();

        $builder
            ->add('namePrefix', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.name_prefix.label'])
            ->add('firstName', 'text', ['label' => 'orocrm.magento.customer.first_name.label'])
            ->add('middleName', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.middle_name.label'])
            ->add('lastName', 'text', ['label' => 'orocrm.magento.customer.last_name.label'])
            ->add('nameSuffix', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.name_suffix.label'])
            ->add('gender', 'oro_gender', ['required' => false, 'label' => 'orocrm.magento.customer.gender.label'])
            ->add('birthday', 'oro_date', ['required' => false, 'label' => 'orocrm.magento.customer.birthday.label'])
            ->add('email', 'email', ['label' => 'orocrm.magento.customer.email.label'])
            ->add('vat', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.vat.label'])
            ->add(
                'dataChannel',
                'orocrm_magento_customer_channel_select',
                [
                    'label' => 'orocrm.magento.customer.data_channel.label',
                    'entities' => [$this->customerClassName],
                    'required' => true,
                    'disabled' => $isExisting,
                    'single_channel_mode' => false
                ]
            )
            ->add(
                'store',
                'orocrm_magento_store_select',
                [
                    'label' => 'orocrm.magento.customer.store.label',
                    'channel_field' => 'dataChannel',
                    'required' => true,
                    'disabled' => $isExisting
                ]
            )
            ->add(
                'group',
                'orocrm_magento_customer_group_select',
                [
                    'label' => 'orocrm.magento.customer.group.label',
                    'channel_field' => 'dataChannel',
                    'required' => true
                ]
            )
            ->add(
                'addresses',
                'oro_address_collection',
                [
                    'label' => 'orocrm.magento.customer.addresses.label',
                    'type' => 'orocrm_magento_customer_addresses',
                    'required' => true,
                    'options' => ['data_class' => $this->customerAddressClassName]
                ]
            )
            ->add('contact', 'orocrm_contact_select', ['label' => 'orocrm.magento.customer.contact.label'])
            ->add('account', 'orocrm_account_select', ['label' => 'orocrm.magento.customer.account.label']);

        if ($this->isPasswordSetAllowed($builder->getData())) {
            $builder->add(
                'generatedPassword',
                'text',
                [
                    'label' => 'orocrm.magento.customer.password.label',
                    'tooltip' => 'orocrm.magento.customer.password.tooltip',
                    'required' => false,
                    'constraints' => [new Length(['min' => 6])]
                ]
            );
        }

        $this->initFormEvents($builder);
        $builder->addEventSubscriber(new CustomerTypeSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->customerClassName,
                'intention' => 'magento_customer',
                'cascade_validation' => true,
                'validation_groups' => [Constraint::DEFAULT_GROUP, 'form']
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Allow to set password only for new customer.
     * Allow to set password for existing customer if Oro Bridge extension is installed.
     *
     * @param Customer|null $data
     * @return bool
     */
    protected function isPasswordSetAllowed($data)
    {
        if ($data && $data instanceof Customer && $data->getChannel() && $data->getChannel()->getTransport()) {
            /** @var MagentoSoapTransport $transport */
            $transport = $data->getChannel()->getTransport();

            return !$data->getId() || $transport->isSupportedExtensionVersion();
        }

        return true;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function initFormEvents(FormBuilderInterface $builder)
    {
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Customer $entity */
                $entity = $event->getData();
                $dataChannel = $entity->getDataChannel();
                if ($dataChannel) {
                    $entity->setChannel($dataChannel->getDataSource());
                }

                $store = $entity->getStore();
                if ($store) {
                    $entity->setWebsite($store->getWebsite());
                }

                if (!$entity->getAddresses()->isEmpty()) {
                    /** @var Address $address */
                    foreach ($entity->getAddresses() as $address) {
                        if (!$address->getChannel()) {
                            $address->setChannel($entity->getChannel());
                        }
                    }
                }
            }
        );
    }
}
