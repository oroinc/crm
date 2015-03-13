<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraint;

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
                'orocrm_channel_select_type',
                [
                    'label' => 'orocrm.magento.customer.data_channel.label',
                    'entities' => [$this->customerClassName],
                    'required' => true,
                    'disabled' => $isExisting
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
}
