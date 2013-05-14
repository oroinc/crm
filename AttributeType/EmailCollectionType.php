<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

class EmailCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_email_collection';
    }
}
