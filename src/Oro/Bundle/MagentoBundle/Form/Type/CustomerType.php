<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\CustomerTypeSubscriber;
use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class CustomerType extends AbstractType
{
    const NAME = 'oro_magento_customer';

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
            ->add('namePrefix', TextType::class, [
                'required' => false,
                'label' => 'oro.magento.customer.name_prefix.label'
            ])
            ->add('firstName', TextType::class, ['label' => 'oro.magento.customer.first_name.label'])
            ->add('middleName', TextType::class, [
                'required' => false,
                'label' => 'oro.magento.customer.middle_name.label'
            ])
            ->add('lastName', TextType::class, ['label' => 'oro.magento.customer.last_name.label'])
            ->add('nameSuffix', TextType::class, [
                'required' => false,
                'label' => 'oro.magento.customer.name_suffix.label'
            ])
            ->add('gender', GenderType::class, ['required' => false, 'label' => 'oro.magento.customer.gender.label'])
            ->add('birthday', OroDateType::class, [
                'required' => false,
                'label' => 'oro.magento.customer.birthday.label'
            ])
            ->add('email', EmailType::class, ['label' => 'oro.magento.customer.email.label'])
            ->add('vat', TextType::class, ['required' => false, 'label' => 'oro.magento.customer.vat.label'])
            ->add(
                'dataChannel',
                CustomerChannelSelectType::class,
                [
                    'label' => 'oro.magento.customer.data_channel.label',
                    'entities' => [$this->customerClassName],
                    'required' => true,
                    'disabled' => $isExisting,
                    'single_channel_mode' => false
                ]
            )
            ->add(
                'store',
                StoreSelectType::class,
                [
                    'label' => 'oro.magento.customer.store.label',
                    'channel_field' => 'dataChannel',
                    'required' => true,
                    'disabled' => $isExisting
                ]
            )
            ->add(
                'group',
                CustomerGroupSelectType::class,
                [
                    'label' => 'oro.magento.customer.group.label',
                    'channel_field' => 'dataChannel',
                    'required' => true
                ]
            )
            ->add(
                'addresses',
                AddressCollectionType::class,
                [
                    'label' => 'oro.magento.customer.addresses.label',
                    'entry_type' => CustomerAddressType::class,
                    'required' => true,
                    'entry_options' => ['data_class' => $this->customerAddressClassName]
                ]
            )
            ->add('contact', ContactSelectType::class, ['label' => 'oro.magento.customer.contact.label']);

        if ($this->isPasswordSetAllowed($builder->getData())) {
            $builder->add(
                'generatedPassword',
                TextType::class,
                [
                    'label' => 'oro.magento.customer.password.label',
                    'tooltip' => 'oro.magento.customer.password.tooltip',
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->customerClassName,
                'csrf_token_id' => 'magento_customer',
                'validation_groups' => [Constraint::DEFAULT_GROUP, 'form']
            ]
        );
    }

    /**
     * {@inheritdoc}
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
            /** @var MagentoTransport $transport */
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
