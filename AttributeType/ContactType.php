<?php
namespace OroCRM\Bundle\ContactBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class ContactType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_attribute_contact';
    }
}
