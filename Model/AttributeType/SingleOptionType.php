<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Single option attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class SingleOptionType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name         = 'Single option';
        $this->backendType  = self::BACKEND_TYPE_OPTION;
        $this->fieldName    = 'option';
        $this->fieldType    = 'entity';
        $this->fieldOptions = array(
            'expanded'      => true,
            'multiple'      => false,
            'class'         => 'OroFlexibleEntityBundle:AttributeOption'
        );
        /*
        $this->fieldOptions['query_builder'] = function(EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };*/
    }


    public function getFieldOptions($attribute)
    {
        $this->fieldOptions['query_builder'] = function(EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $this->fieldOptions;
    }
}
