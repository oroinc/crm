<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

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
        $this->formType     = 'entity';
        $this->fieldName    = 'options';
    }

    /**
     * Get form type options
     *
     * @return array
     */
    public function prepareFormOptions(AbstractAttribute $attribute)
    {
        $options = parent::prepareFormOptions($attribute);
        $options['expanded']      = true;
        $options['multiple']      = true;
        $options['class']         = 'OroFlexibleEntityBundle:AttributeOption';
        $options['query_builder'] = function(EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $options;
    }
}
