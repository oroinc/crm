<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItemsApiType extends OrderItemType
{
    const NAME = 'order_item_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('qty');
        $builder->remove('name');
        $builder->add('qty', 'number', ['required' => true]);
        $builder->add('name', 'text', [
             'required' => true,
             'constraints' => new Assert\NotBlank()
        ]);
        $builder->add('order', 'orocrm_order_select');

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem',
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
