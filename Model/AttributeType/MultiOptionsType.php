<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Multi options attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MultiOptionsType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name         = 'Multi-options';
        $this->backendType  = self::BACKEND_TYPE_OPTION;
        $this->fieldName    = 'options';
        $this->fieldType    = 'entity';
        $this->fieldOptions = array(
            'expanded'      => true,
            'multiple'      => true,
            'class'         => 'OroFlexibleEntityBundle:AttributeOption'
        );

    }

    public function getFieldOptions($attribute)
    {
        $this->fieldOptions['query_builder'] = function(EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $this->fieldOptions;
    }
}
