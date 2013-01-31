<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Simple options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class OptionSimpleSelectType extends AbstractOptionType
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = 'Simple-option (select)';
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
        $options['expanded'] = false;
        $options['multiple'] = false;

        return $options;
    }
}
