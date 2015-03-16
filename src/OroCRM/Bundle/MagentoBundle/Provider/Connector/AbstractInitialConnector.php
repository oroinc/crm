<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;

abstract class AbstractInitialConnector extends AbstractMagentoConnector implements InitialConnectorInterface
{
    /** @var string */
    protected $className;

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        if (!$this->className) {
            throw new \InvalidArgumentException(sprintf('Entity FQCN is missing for "%s" connector', $this->getType()));
        }

        return $this->className;
    }
}
