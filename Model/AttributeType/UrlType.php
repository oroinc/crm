<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Url attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class UrlType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name        = 'Url';
        $this->backendType = self::BACKEND_TYPE_VARCHAR;
        $this->formType    = 'url';
    }

}
