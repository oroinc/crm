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
     * Available frontend types
     * @var string
     */
    const FRONTEND_TYPE_TEXTFIELD = 'Text Field';
    const FRONTEND_TYPE_TEXTAREA  = 'Text Area';
    const FRONTEND_TYPE_PRICE     = 'Price';
    const FRONTEND_TYPE_DATE      = 'Date';
    const FRONTEND_TYPE_LIST      = 'List';
    const FRONTEND_TYPE_IMAGE     = 'Image';
    const FRONTEND_TYPE_FILE      = 'File';
}
