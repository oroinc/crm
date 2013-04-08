<?php
namespace Oro\Bundle\FlexibleEntityBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;
use Oro\Bundle\FlexibleEntityBundle\Event\FilterAttributeEvent;
use Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent;
use Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleValueEvent;
use Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleConfigurationException;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOptionValue;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeExtended;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Pim\Bundle\ProductBundle\Entity\ProductValue;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleManager implements TranslatableInterface, ScopableInterface
{
    /**
     * @var string
     */
    protected $flexibleName;

    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * @var ObjectManager $storageManager
     */
    protected $storageManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

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
     * @param string                   $flexibleName    Entity name
     * @param array                    $flexibleConfig  Global flexible entities configuration array
     * @param ObjectManager            $storageManager  Storage manager
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function __construct($flexibleName, $flexibleConfig, ObjectManager $storageManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->flexibleName    = $flexibleName;
        $this->flexibleConfig  = $flexibleConfig['entities_config'][$flexibleName];
        $this->storageManager  = $storageManager;
        $this->eventDispatcher = $eventDispatcher;
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
     * Get flexible init mode
     * @return array
     */
    public function getFlexibleInitMode()
    {
        return $this->flexibleConfig['flexible_init_mode'];
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
     * @return FlexibleManager
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
     * @return FlexibleManager
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }


    /**
     * Get object manager
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
     */
    public function getFlexibleName()
    {
        return $this->flexibleName;
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeName()
    {
        return $this->flexibleConfig['attribute_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeExtendedName()
    {
        return $this->flexibleConfig['attribute_extended_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionName()
    {
        return $this->flexibleConfig['attribute_option_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getAttributeOptionValueName()
    {
        return $this->flexibleConfig['attribute_option_value_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     * @return string
     */
    public function getFlexibleValueName()
    {
        return $this->flexibleConfig['flexible_value_class'];
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getFlexibleRepository()
    {
        $repo = $this->storageManager->getRepository($this->getFlexibleName());
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
    public function getAttributeExtendedRepository()
    {
        if (!$this->getAttributeExtendedName()) {
            throw new FlexibleConfigurationException(
                $this->getFlexibleName() .' has no flexible attribute extended class'
            );
        }

        return $this->storageManager->getRepository($this->getAttributeExtendedName());
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
    public function getFlexibleValueRepository()
    {
        return $this->storageManager->getRepository($this->getFlexibleValueName());
    }

    /**
     * Return a new instance
     *
     * @param AbstractAttributeType $type attribute type
     *
     * @return AbstractAttribute
     */
    public function createAttribute(AbstractAttributeType $type = null)
    {
        // create attribute
        $class = $this->getAttributeName();
        $object = new $class();
        $object->setEntityType($this->getFlexibleName());
        // add configuration related to the attribute type
        $object->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
        if ($type) {
            $object->setBackendType($type->getBackendType());
            $object->setAttributeType(get_class($type));
        }
        // dispatch event
        $event = new FilterAttributeEvent($this, $object);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_ATTRIBUTE, $event);

        return $object;
    }

    /**
     * Return a new instance
     * @return AbstractAttributeOption
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
     * @return AbstractAttributeOptionValue
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
     *
     * @return FlexibleInterface
     */
    public function createFlexible()
    {
        $class = $this->getFlexibleName();
        $object = new $class();
        $object->setLocale($this->getLocale());
        $object->setScope($this->getScope());
        // dispatch event
        $event = new FilterFlexibleEvent($this, $object);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_FLEXIBLE, $event);

        return $object;
    }

    /**
     * Return a new instance
     *
     * @param AbstractAttributeType $type attribute type
     *
     * @return AbstractAttributeExtended
     */
    public function createAttributeExtended(AbstractAttributeType $type = null)
    {
        if (!$this->getAttributeExtendedName()) {
            throw new FlexibleConfigurationException(
                $this->getFlexibleName() .' has no flexible attribute extended class'
            );
        }
        // build base attribute
        $attribute = $this->createAttribute($type);
        // build flexible attribute
        $class = $this->getAttributeExtendedName();
        $object = new $class();
        $object->setAttribute($attribute);

        return $object;
    }

    /**
     * Return a new instance
     * @return FlexibleValueInterface
     */
    public function createFlexibleValue()
    {
        $class = $this->getFlexibleValueName();
        $object = new $class();
        $object->setLocale($this->getLocale());
        $object->setScope($this->getScope());
        // dispatch event
        $event = new FilterFlexibleValueEvent($this, $object);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_VALUE, $event);

        return $object;
    }

    public function find($id)
    {
        $fr = $this->getFlexibleRepository();
        $fr->setLocale($this->getLocale());

        return $fr->findWithAttributes($id);
    }

    /**
     * Save a product in two phases :
     *   1) Persist and flush the entity as usual
     *   2)
     *     2.1) Force the reloading of the object (to be sure all values are loaded)
     *     2.2) Add the missing translatable attribute locale values
     *     2.3) Reflush to save these new values
     */
    public function save($product)
    {
        $this->storageManager->persist($product);
        $this->storageManager->flush();

        $this->storageManager->refresh($product);
        $this->addMissingTranslatableAttributeLocaleValue($product);
        $this->storageManager->flush();
    }

    /**
     * Add missing translatable attribute locale value
     *
     * It makes sure that if an attribute is translatable, then all values
     * in the locales defined by the entity activated languages exist.
     *
     * For example:
     *   An entity has french and english languages activated.
     *   It has a translatable attribute "name" with a value in french,
     *   but the value in english is not available.
     *   This method will create this value with an empty data.
     */
    private function AddMissingTranslatableAttributeLocaleValue($product)
    {
        $values         = $product->getValues();
        $languages      = $product->getLanguages();
        $attributes     = array();
        $missingLocales = array();

        foreach ($values as $value) {
            $attribute = $value->getAttribute();
            $attributes[$attribute->getCode()] = $attribute;
            if (true === $attribute->getTranslatable()) {
                if (!isset($missingLocales[$attribute->getCode()])) {
                    $missingLocales[$attribute->getCode()] = $languages->map(function ($language) {
                        return $language->getCode();
                    })->toArray();
                }

                foreach ($languages as $language) {
                    if ($language->getCode() === $value->getLocale()) {
                        $missingLocales[$attribute->getCode()] = array_diff($missingLocales[$attribute->getCode()], array($value->getLocale()));
                    }
                }
            }
        }

        foreach ($missingLocales as $attribute => $locales) {
            foreach ($locales as $locale) {
                $value = new ProductValue;
                $value->setLocale($locale);
                $value->setAttribute($attributes[$attribute]);

                $product->addValue($value);
            }
        }
    }
}
