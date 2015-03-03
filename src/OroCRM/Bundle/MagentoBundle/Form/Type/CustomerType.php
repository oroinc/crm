<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class CustomerType extends AbstractType
{
    const NAME = 'orocrm_magento_customer';

    /**
     * @var string
     */
    protected $customerClassName;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('namePrefix', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.name_prefix.label'])
            ->add('firstName', 'text', ['label' => 'orocrm.magento.customer.first_name.label'])
            ->add('middleName', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.middle_name.label'])
            ->add('lastName', 'text', ['label' => 'orocrm.magento.customer.last_name.label'])
            ->add('nameSuffix', 'text', ['required' => false, 'label' => 'orocrm.magento.customer.name_suffix.label'])
            ->add('gender', 'oro_gender', ['required' => false, 'label' => 'orocrm.magento.customer.gender.label'])
            ->add('birthday', 'oro_date', ['required' => false, 'label' => 'orocrm.magento.customer.birthday.label'])
            ->add('email', 'email', ['label' => 'orocrm.magento.customer.email.label'])
            ->add('isActive', 'checkbox', ['label' => 'orocrm.magento.customer.is_active.label'])
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                [
                    'required' => true,
                    'label' => 'orocrm.magento.customer.data_channel.label',
                    'entities' => [$this->customerClassName],
                ]
            )
            ->add(
                'channel',
                'oro_integration_select',
                [
                    'required' => true,
                    'allowed_types' => [ChannelType::TYPE],
                    'label' => 'orocrm.magento.customer.channel.label',
                ]
            )
            ->add('website', 'choice', ['label' => 'orocrm.magento.customer.website.label'])
            ->add('store', 'choice', ['label' => 'orocrm.magento.customer.store.label'])
            ->add('group', 'choice', ['label' => 'orocrm.magento.customer.group.label'])
            ->add('contact', 'orocrm_contact_select', ['label' => 'orocrm.magento.customer.contact.label'])
            ->add('account', 'orocrm_account_select', ['label' => 'orocrm.magento.customer.account.label']);
    }

    /**
     * @param string $customerClassName
     */
    public function __construct($customerClassName)
    {
        $this->customerClassName = $customerClassName;
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
                'cascade_validation' => true
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
