<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Money attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MoneyType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name        = 'Money';
        $this->backendType = self::BACKEND_TYPE_DECIMAL;
        $this->fieldType   = 'money';
    }
}
