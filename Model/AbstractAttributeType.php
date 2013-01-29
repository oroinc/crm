<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

use Symfony\Component\Console\Input\StringInput;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractAttributeType
{

    /**
     * Available backend storage
     * @var string
     */
    const BACKEND_STORAGE_ATTRIBUTE_VALUE = 'values';
    const BACKEND_STORAGE_FLAT_VALUE      = 'flatValues';

    /**
     * Available backend types
     * @var string
     */
    const BACKEND_TYPE_DATE     = 'date';
    const BACKEND_TYPE_DATETIME = 'datetime';
    const BACKEND_TYPE_DECIMAL  = 'decimal';
    const BACKEND_TYPE_INTEGER  = 'integer';
    const BACKEND_TYPE_OPTION   = 'options';
    const BACKEND_TYPE_TEXT     = 'text';
    const BACKEND_TYPE_VARCHAR  = 'varchar';

    /**
     * Available frontend types
     * @var string
     */
    const FRONTEND_TYPE_TEXT         = 'Text';
    const FRONTEND_TYPE_TEXTAREA     = 'TextArea';
    const FRONTEND_TYPE_MONEY        = 'Money';
    const FRONTEND_TYPE_METRIC       = 'Metric';
    const FRONTEND_TYPE_NUMBER       = 'Number';
    const FRONTEND_TYPE_INTEGER      = 'Integer';
    const FRONTEND_TYPE_DATE         = 'Date';
    const FRONTEND_TYPE_DATETIME     = 'DateTime';
    const FRONTEND_TYPE_EMAIL        = 'Email';
    const FRONTEND_TYPE_URL          = 'Url';
    const FRONTEND_TYPE_SIMPLECHOICE = 'SimpleChoice';
    const FRONTEND_TYPE_MULTICHOICE  = 'MultipleChoice';

    /**
     * Attribute name
     *
     * @var string
     */
    protected $name;

    /**
     * Field backend type, "varchar" by default
     *
     * @var string
     */
    protected $backendType = self::BACKEND_TYPE_VARCHAR;

    /**
     * Field type alias, "data" by default
     *
     * @var string
     */
    protected $fieldName = 'data';

    /**
     * Field type alias, "text" by default
     *
     * @var string
     */
    protected $fieldType = 'text';

    /**
     * Field type options
     *
     * @var string
     */
    protected $fieldOptions = array();

    /**
     * Get attribute type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get backend type
     *
     * @var string
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get field type (alias)
     *
     * @var string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Get field options
     *
     * @return array
     */
    public function getFieldOptions($attribute)
    {
        return $this->fieldOptions;
    }

}
