<?php
namespace Oro\Bundle\AddressBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Doctrine\ORM\EntityRepository;

class PhoneCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['expanded'] = true;
        $options['multiple'] = true;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address_phone_collection';
    }
}
