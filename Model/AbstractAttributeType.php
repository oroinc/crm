<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;



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
     * Field type alias, "data" by default, mapped by attribute property or getter/setter
     *
     * @var string
     */
    protected $fieldName = 'data';

    /**
     * Form type alias, "text" by default
     *
     * @var string
     */
    protected $formType = 'text';

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
     * @return string
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
     * Get form type (alias)
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
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
        $options = array(
            'label'    => $attribute->getCode(),
            'required' => $attribute->getRequired()
        );

        return $options;
    }
}
