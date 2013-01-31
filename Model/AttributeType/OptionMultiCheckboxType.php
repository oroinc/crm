<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Multi options (checkbox) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class OptionMultiCheckboxType extends AbstractOptionType
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->name      = 'Multi-options (checkbox)';
        $this->fieldName = 'options';
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
        $options['expanded'] = true;
        $options['multiple'] = true;

        return $options;
    }
}
