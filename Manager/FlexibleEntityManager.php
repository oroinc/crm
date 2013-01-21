<?php
namespace Oro\Bundle\FlexibleEntityBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttributeValue;
use Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleConfigurationException;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityManager extends SimpleEntityManager
{

    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Locale code (from config or choose by user)
     * @var string
     */
    protected $locale;

    /**
     * Scope code (from config or choose by user)
     * @var string
     */
    protected $scope;

    /**
     * Constructor
     *
     * @param ContainerInterface $container      service container
     * @param string             $entityName     entity name
     * @param ObjectManager      $storageManager optional storage manager, get default if not provided
     */
    public function __construct($container, $entityName, $storageManager = false)
    {
        parent::__construct($container, $entityName, $storageManager);
        // get flexible entity configuration
        $allFlexibleConfig = $this->container->getParameter('oro_flexibleentity.entities_config');
        $this->flexibleConfig = $allFlexibleConfig['entities_config'][$entityName];
    }

    /**
     * Get flexible entity config
     * @return array
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale()
    {
        if (!$this->locale) {
            // use default locale
            $this->locale = $this->flexibleConfig['default_locale'];
        }

        return $this->locale;
    }

    /**
     * Set locale code, to force it
     *
     * @param string $code
     *
     * @return FlexibleEntityManager
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope()
    {
        if (!$this->scope) {
            // use default scope
            $this->scope = $this->flexibleConfig['default_scope'];
        }

        return $this->scope;
    }

    /**
     * Set scope code, to force it
     *
     * @param string $code
     *
     * @return FlexibleEntityManager
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeName()
    {
        return $this->flexibleConfig['flexible_attribute_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getEntityAttributeName()
    {
        return $this->flexibleConfig['flexible_attribute_extended_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionName()
    {
        return $this->flexibleConfig['flexible_attribute_option_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionValueName()
    {
        return $this->flexibleConfig['flexible_attribute_option_value_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getEntityValueName()
    {
        return $this->flexibleConfig['flexible_entity_value_class'];
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityRepository()
    {
        $repo = $this->storageManager->getRepository($this->getEntityName());
        $repo->setFlexibleConfig($this->flexibleConfig);
        $repo->setLocale($this->getLocale());
        $repo->setScope($this->getScope());

        return $repo;
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityAttributeRepository()
    {
        if (!$this->getEntityAttributeName()) {
            throw new FlexibleConfigurationException($this->getEntityName().' has no flexible attribute extended class');
        }

        return $this->storageManager->getRepository($this->getEntityAttributeName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeOptionName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getAttributeOptionValueRepository()
    {
        return $this->storageManager->getRepository($this->getAttributeOptionValueName());
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getEntityValueRepository()
    {
        return $this->storageManager->getRepository($this->getEntityValueName());
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute
     */
    public function createAttribute()
    {
        $class = $this->getAttributeName();
        $object = new $class();
        $object->setEntityType($this->getEntityName());
        $object->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);

        return $object;
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption
     */
    public function createAttributeOption()
    {
        $class = $this->getAttributeOptionName();
        $object = new $class();
        $object->setLocale($this->getLocale());

        return $object;
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue
     */
    public function createAttributeOptionValue()
    {
        $class = $this->getAttributeOptionValueName();
        $object = new $class();
        $object->setLocale($this->getLocale());

        return $object;
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible
     */
    public function createEntity()
    {
        $class = $this->getEntityName();
        $object = new $class();
        $object->setLocale($this->getLocale());
        $object->setScope($this->getScope());

        return $object;
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleAttribute
     */
    public function createEntityAttribute()
    {
        if (!$this->getEntityAttributeName()) {
            throw new FlexibleConfigurationException($this->getEntityName().' has no flexible attribute extended class');
        }
        // build base attribute
        $attribute = $this->createAttribute();
        // build flexible attribute
        $class = $this->getEntityAttributeName();
        $object = new $class();
        $object->setAttribute($attribute);

        return $object;
    }

    /**
     * Return a new instance
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue
     */
    public function createEntityValue()
    {
        $class = $this->getEntityValueName();
        $object = new $class();
        $object->setLocale($this->getLocale());
        $object->setScope($this->getScope());

        return $object;
    }

}
