<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Abstract option attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractOptionType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->backendType = self::BACKEND_TYPE_OPTIONS;
        $this->formType    = 'entity';
    }

    /**
     * Get form type options
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    public function prepareFormOptions(AbstractAttribute $attribute)
    {
        $options = parent::prepareFormOptions($attribute);
        $options['empty_value']   = false;
        $options['class']         = 'OroFlexibleEntityBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $options;
    }
}
