<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

class OrderPlaceType extends AbstractType
{
    const NAME = 'orocrm_magento_order_place_form_type';

    /**
     * {@inheritdoc}
     */
    public function getName()
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
