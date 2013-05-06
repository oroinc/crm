<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class ContactValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_contact_value';
    }
}
