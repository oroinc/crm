<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Date attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DateType extends AbstractAttributeType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name        = 'Date';
        $this->backendType = self::BACKEND_TYPE_DATE;
        $this->fieldType   = 'date';
        $this->fieldOptions = array();
        $this->fieldOptions['widget'] = 'single_text';
        $this->fieldOptions['input'] = 'datetime';
        $this->fieldOptions['attr'] = array(
            'class' => 'datepicker input-small',
            'placeholder' => 'YYYY-MM-DD',
        );
    }

}
