<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class ProfileValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_profile_value';
    }
}
