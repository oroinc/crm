<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Image attribute type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ImageType extends FileType
{

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name        = 'File';
        $this->backendType = self::BACKEND_TYPE_VARCHAR;
        $this->formType    = 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareFormOptions(AbstractAttribute $attribute)
    {
        $options = parent::prepareFormOptions($attribute);

        $options['mimeTypes'] = array('image/jpeg', 'image/png', 'image/gif');

        return $options;
    }
}
