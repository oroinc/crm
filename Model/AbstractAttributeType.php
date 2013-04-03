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
     * Available backend storage, the flexible doctrine mapped field
     * @var string
     */
    const BACKEND_STORAGE_ATTRIBUTE_VALUE = 'values';

    /**
     * Available backend types, the value doctrine mapped field
     * @var string
     */
    const BACKEND_TYPE_DATE     = 'date';
    const BACKEND_TYPE_DATETIME = 'datetime';
    const BACKEND_TYPE_DECIMAL  = 'decimal';
    const BACKEND_TYPE_INTEGER  = 'integer';
    const BACKEND_TYPE_OPTIONS  = 'options';
    const BACKEND_TYPE_OPTION   = 'option';
    const BACKEND_TYPE_TEXT     = 'text';
    const BACKEND_TYPE_VARCHAR  = 'varchar';
    const BACKEND_TYPE_MEDIA    = 'media';
    const BACKEND_TYPE_METRIC   = 'metric';
    const BACKEND_TYPE_PRICE    = 'price';

    /**
     * Classes for AttributeType
     *
     * TODO : Avoid to hardcode basic types here !
     *
     * @staticvar string
     */
    const TYPE_DATE_CLASS              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType';
    const TYPE_INTEGER_CLASS           = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\IntegerType';
    const TYPE_MONEY_CLASS             = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType';
    const TYPE_NUMBER_CLASS            = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType';
    const TYPE_OPT_MULTI_CB_CLASS      = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType';
    const TYPE_OPT_MULTI_SELECT_CLASS  = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType';
    const TYPE_OPT_SINGLE_RADIO_CLASS  = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleRadioType';
    const TYPE_OPT_SINGLE_SELECT_CLASS = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType';
    const TYPE_TEXTAREA_CLASS          = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType';
    const TYPE_METRIC_CLASS            = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType';
    const TYPE_FILE_CLASS              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType';
    const TYPE_IMAGE_CLASS             = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType';
    const TYPE_FILE_URL_CLASS          = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileUrlType';
    const TYPE_IMAGE_URL_CLASS         = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageUrlType';
    const TYPE_TEXT_CLASS              = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType';
    const TYPE_BOOLEAN_CLASS           = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType';

    /**
     * Attribute name
     *
     * @var string
     */
    protected $name;

    /**
     * Field backend type, "varchar" by default, the doctrine mapping field, getter / setter to use for binding
     *
     * @var string
     */
    protected $backendType = self::BACKEND_TYPE_VARCHAR;

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
