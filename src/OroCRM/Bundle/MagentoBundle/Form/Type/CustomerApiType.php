<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\CustomerTypeSubscriber;

class CustomerApiType extends AbstractType
{
    const NAME = 'api_customer_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('namePrefix', 'text');
        $builder->add('firstName', 'text');
        $builder->add('middleName', 'text');
        $builder->add('lastName', 'text');
        $builder->add('nameSuffix', 'text');
        $builder->add('gender', 'oro_gender');
        $builder->add('birthday', 'oro_date');
        $builder->add('email', 'text');
        $builder->add('originId', 'text');

        $builder->add(
            'website',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:Website',
                'property' => 'name'
            ]
        );

        $builder->add(
            'store',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:Store',
                'property' => 'name'
            ]
        );

        $builder->add(
            'group',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:CustomerGroup',
                'property' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'dataChannel',
            'translatable_entity',
            [
                'class'    => 'OroCRMChannelBundle:Channel',
                'property' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'addresses',
            'oro_address_collection',
            [
                'label'    => '',
                'type'     => 'oro_typed_address',
                'required' => true,
                'options'  => ['data_class' => 'OroCRM\Bundle\MagentoBundle\Entity\Address']
            ]
        );

        $builder->add(
            'owner',
            'translatable_entity',
            [
                'class'    => 'Oro\Bundle\UserBundle\Entity\User',
                'property' => 'username',
                'required' => false
            ]
        );

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CustomerTypeSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'csrf_protection' => false
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
