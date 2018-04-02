<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\MagentoBundle\Form\EventListener\CustomerTypeSubscriber;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerApiType extends AbstractType
{
    const NAME = 'api_customer_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('namePrefix', TextType::class);
        $builder->add('firstName', TextType::class);
        $builder->add('middleName', TextType::class);
        $builder->add('lastName', TextType::class);
        $builder->add('nameSuffix', TextType::class);
        $builder->add('gender', GenderType::class);
        $builder->add('birthday', OroDateType::class);
        $builder->add('email', TextType::class);
        $builder->add('originId', TextType::class);

        $builder->add(
            'website',
            TranslatableEntityType::class,
            [
                'class' => 'OroMagentoBundle:Website',
                'choice_label' => 'name'
            ]
        );

        $builder->add(
            'store',
            TranslatableEntityType::class,
            [
                'class' => 'OroMagentoBundle:Store',
                'choice_label' => 'name'
            ]
        );

        $builder->add(
            'group',
            TranslatableEntityType::class,
            [
                'class' => 'OroMagentoBundle:CustomerGroup',
                'choice_label' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'dataChannel',
            TranslatableEntityType::class,
            [
                'class' => 'OroChannelBundle:Channel',
                'choice_label' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'addresses',
            AddressCollectionType::class,
            [
                'label'    => '',
                'entry_type'     => TypedAddressType::class,
                'required' => true,
                'entry_options'  => ['data_class' => 'Oro\Bundle\MagentoBundle\Entity\Address']
            ]
        );

        $builder->add(
            'owner',
            TranslatableEntityType::class,
            [
                'class'    => 'Oro\Bundle\UserBundle\Entity\User',
                'choice_label' => 'username',
                'required' => false
            ]
        );

        $builder->add(
            'account',
            AccountSelectType::class,
            [
                'label'       => 'oro.magento.customer.account.label',
                'required'    => true,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CustomerTypeSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'                    => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                'csrf_protection'               => false,
                'customer_association_disabled' => true,
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
}
