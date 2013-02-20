<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter as AbstractORMFilter;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class AbstractFlexibleFilter extends AbstractORMFilter implements FilterInterface
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

        $flexibleManagerServiceId = $this->getOption('flexible_manager');
        if (!$flexibleManagerServiceId) {
            throw new \LogicException('Flexible entity filter must have flexible entity manager code.');
        }

        if (!$this->container->has($flexibleManagerServiceId)) {
            throw new \LogicException('There is no flexible entity service ' . $flexibleManagerServiceId . '.');
        }

        $this->flexibleManager = $this->container->get($flexibleManagerServiceId);
    }
}
