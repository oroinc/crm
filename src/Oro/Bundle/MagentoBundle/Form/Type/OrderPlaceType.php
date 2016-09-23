<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

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
        return WorkflowTransitionType::NAME;
    }
}
