<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Datetime attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DateTimeType extends AbstractAttributeType
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->backendType = self::BACKEND_TYPE_DATETIME;
        $this->formType    = 'oro_datetime';
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
        $options['widget'] = 'single_text';
        $options['input'] = 'datetime';

        return $options;
    }
}
