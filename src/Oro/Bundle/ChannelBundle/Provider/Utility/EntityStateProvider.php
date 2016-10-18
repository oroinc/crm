<?php

namespace Oro\Bundle\ChannelBundle\Provider\Utility;

use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class EntityStateProvider
{
    /** @var StateProvider */
    protected $stateProvider;

    /** @var string */
    protected $namespace;

    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param StateProvider $stateProvider
     */
    public function setStateProvider(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @throws \RuntimeException
     * @throws \LogicException
     * @return bool
     */
    public function __call($method, $args)
    {
        if (!$this->stateProvider) {
            throw new \RuntimeException('Provider configured incorrectly, missed "state provider"');
        }

        if (preg_match('#(isEntity)(\w+)(Enabled)#', $method, $matches)) {
            $entityName = rtrim($this->namespace, '\\') . '\\' . $matches[2];
            if (class_exists($entityName)) {
                return $this->stateProvider->isEntityEnabled($entityName);
            } else {
                throw new \LogicException(sprintf('Unable to check state for "%s". Class does not exist', $entityName));
            }
        }
    }
}
