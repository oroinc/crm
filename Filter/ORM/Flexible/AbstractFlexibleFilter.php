<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class AbstractFlexibleFilter extends AbstractFilter implements FilterInterface
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        parent::initialize($name, $options);

        $flexibleEntityName = $this->getOption('flexible_name');
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }

        $this->flexibleManager = $this->getFlexibleManager($flexibleEntityName);
    }

    /**
     * @param string $flexibleEntityName
     * @return FlexibleManager
     * @throws \LogicException
     */
    protected function getFlexibleManager($flexibleEntityName)
    {
        $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');

        // validate configuration
        if (!isset($flexibleConfig['entities_config'][$flexibleEntityName])
            || !isset($flexibleConfig['entities_config'][$flexibleEntityName]['flexible_manager'])
        ) {
            throw new \LogicException(
                'There is no flexible manager configuration for entity ' . $flexibleEntityName . '.'
            );
        }

        $flexibleManagerServiceId = $flexibleConfig['entities_config'][$flexibleEntityName]['flexible_manager'];
        return $this->container->get($flexibleManagerServiceId);
    }
}
