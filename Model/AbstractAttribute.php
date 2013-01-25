<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractAttribute implements TimestampableInterface
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
     * @var string $entityType
     */
    protected $entityType;

    /**
     * Kind of storage to store values
     * @var string $backendStorage
     */
    protected $backendStorage;

    /**
     * Kind of field to store values
     * @var string $backendType
     */
    protected $backendType;

    /**
     * Kind of form field to set value in form
     * @var string $frontendType
     */
    protected $frontendType;

    /**
     * @var datetime $created
     */
    protected $created;

    /**
     * @var datetime $created
     */
    protected $updated;

    /**
     * Is attribute is required
     * @var boolean $required
     */
    protected $required;

    /**
     * Is attribute value is required
     * @var boolean $unique
     */
    protected $unique;

    /**
     * Default attribute value
     * @var string $defaultValue
     */
    protected $defaultValue;

    /**
     * @var boolean $searchable
     */
    protected $searchable;

    /**
    * @var boolean $translatable
    */
    protected $translatable;

    /**
     * @var boolean $scopable
     */
    protected $scopable;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractAttribute
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AbstractAttribute
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set entity type
     *
     * @param string $entityType
     *
     * @return AbstractAttribute
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Set backend storage
     *
     * @param string $storage
     *
     * @return AbstractAttribute
     */
    public function setBackendStorage($storage)
    {
        $this->backendStorage = $storage;

        return $this;
    }

    /**
     * Get backend storage
     *
     * @return string
     */
    public function getBackendStorage()
    {
        return $this->backendStorage;
    }

    /**
     * Set backend type
     *
     * @param string $type
     *
     * @return AbstractAttribute
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
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
     * Set frontend type
     *
     * @param string $type
     *
     * @return AbstractAttribute
     */
    public function setFrontendType($type)
    {
        echo "CALL FRONTEND TYPE";
        $this->frontendType = $type;

        switch ($type) {
            case AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD:
                $this->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
                $this->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
                break;
            case AbstractAttributeType::FRONTEND_TYPE_DATE:
                $this->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
                $this->setBackendType(AbstractAttributeType::BACKEND_TYPE_DATE);
                break;
            case AbstractAttributeType::FRONTEND_TYPE_DATETIME:
                $this->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
                $this->setBackendType(AbstractAttributeType::BACKEND_TYPE_DATETIME);
                break;
            case AbstractAttributeType::FRONTEND_TYPE_LIST:
                $this->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
                $this->setBackendType(AbstractAttributeType::BACKEND_TYPE_OPTION);
                break;
            default:
                throw new \Exception('not yet implemented '.$type);
        }

        return $this;
    }

    /**
     * Get frontend type
     *
     * @return string
     */
    public function getFrontendType()
    {
        return $this->frontendType;
    }

    /**
     * Set required
     *
     * @param boolean $required
     *
     * @return AbstractAttribute
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required
     *
     * @return boolean $required
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set unique
     *
     * @param boolean $unique
     *
     * @return AbstractAttribute
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * Get unique
     *
     * @return boolean $unique
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Set default value
     *
     * @param string $default
     *
     * @return AbstractAttribute
     */
    public function setDefaultValue($default)
    {
        $this->defaultValue = $default;

        return $this;
    }

    /**
     * Get default value
     *
     * @return string $unique
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set searchable
     *
     * @param boolean $searchable
     *
     * @return AbstractAttribute
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Get searchable
     *
     * @return boolean $searchable
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * Set translatable
     *
     * @param boolean $translatable
     *
     * @return AbstractAttribute
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * Get translatable
     *
     * @return boolean $translatable
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Set scopable
     *
     * @param boolean $scopable
     *
     * @return AbstractAttribute
     */
    public function setScopable($scopable)
    {
        $this->scopable = $scopable;

        return $this;
    }

    /**
     * Get scopable
     *
     * @return boolean $scopable
     */
    public function getScopable()
    {
        return $this->scopable;
    }

    /**
     * Add option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function addOption(AbstractAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function removeOption(AbstractAttributeOption $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get options
     *
     * @return \ArrayAccess
     */
    public function getOptions()
    {
        return $this->options;
    }

}
