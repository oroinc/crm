<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;
use Symfony\Component\Form\AbstractType;

class OrderPlaceType extends AbstractType
{
    const NAME = 'oro_magento_order_place_form_type';

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
     * {@inheritdoc}
     */
    public function getParent()
    {
        return WorkflowTransitionType::class;
    }
}
