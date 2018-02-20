<?php

namespace Oro\Bundle\MagentoBundle\Model\Action;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;

/**
 * Similar entities discovery
 *
 * Usage:
 * @automatic_discovery:
 *      entity: $.data
 *      attribute: $.matchedEntity
 */
class AutomaticDiscoveryAction extends AbstractAction
{
    const NAME = 'automatic_discovery';

    /** @var AutomaticDiscovery */
    protected $automaticDiscovery;

    /** @var string */
    protected $entity;

    /** @var string */
    protected $attribute;

    /**
     * @param ContextAccessor $contextAccessor
     * @param AutomaticDiscovery $automaticDiscovery
     */
    public function __construct(ContextAccessor $contextAccessor, AutomaticDiscovery $automaticDiscovery)
    {
        parent::__construct($contextAccessor);

        $this->automaticDiscovery = $automaticDiscovery;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $entity = $this->getEntity($context);
        $matchedEntity = $this->automaticDiscovery->discoverSimilar($entity);
        $this->contextAccessor->setValue($context, $this->attribute, $matchedEntity);
    }

    /**
     * @param mixed $context
     * @return object
     * @throws InvalidParameterException
     */
    protected function getEntity($context)
    {
        $entity = $this->contextAccessor->getValue($context, $this->entity);
        if (!is_object($entity)) {
            throw new InvalidParameterException(
                sprintf(
                    'Action "%s" expects object in parameter "entity", %s is given.',
                    self::NAME,
                    $this->getType($entity)
                )
            );
        }

        return $entity;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function getType($value)
    {
        if (is_object($value)) {
            return ClassUtils::getClass($value);
        }

        return gettype($value);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['entity'])) {
            throw new InvalidParameterException('Parameter "entity" is required.');
        }
        $this->entity = $options['entity'];

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Parameter "attribute" is required.');
        }
        $this->attribute = $options['attribute'];

        return $this;
    }
}
