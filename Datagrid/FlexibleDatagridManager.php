<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    private $flexibleManager;

    /**
     * @var string
     */
    private $flexibleManagerServiceId;

    /**
     * @var Attribute[]
     */
    protected $attributes;

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
     * @param FlexibleManager $flexibleManager
     * @param string $serviceId
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager, $serviceId)
    {
        $this->flexibleManager          = $flexibleManager;
        $this->flexibleManagerServiceId = $serviceId;

        $this->configureFlexibleManager();
    }

    /**
     * Configure flexible manager instance
     */
    private function configureFlexibleManager()
    {
        // TODO: somehow get locale and scope from parameters interface
        $this->flexibleManager->setLocale('en');
        $this->flexibleManager->setScope('ecommerce');
    }

    /**
     * Initialize flexible manager using entity class name
     *
     * @throws \LogicException
     */
    private function initializeFlexibleManager()
    {
        if (!($this->queryFactory instanceof EntityQueryFactory)) {
            throw new \LogicException('Query factory must be entity query factory.');
        }

        /** @var $queryFactory EntityQueryFactory */
        $queryFactory = $this->queryFactory;
        $className = $queryFactory->getClassName();

        $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');

        // validate configuration
        if (!isset($flexibleConfig['entities_config'][$className])
            || !isset($flexibleConfig['entities_config'][$className]['flexible_manager'])
        ) {
            throw new \LogicException('There is no flexible manager configuration for entity ' . $className . '.');
        }

        $flexibleManagerServiceId = $flexibleConfig['entities_config'][$className]['flexible_manager'];
        $this->setFlexibleManager(
            $this->container->get($flexibleManagerServiceId),
            $flexibleManagerServiceId
        );
    }

    /**
     * @return FlexibleManager
     */
    protected function getFlexibleManager()
    {
        if (!$this->flexibleManager) {
            $this->initializeFlexibleManager();
        }

        return $this->flexibleManager;
    }

    /**
     * @return string
     */
    protected function getFlexibleManagerServiceId()
    {
        if (!$this->flexibleManagerServiceId) {
            $this->initializeFlexibleManager();
        }

        return $this->flexibleManagerServiceId;
    }

    /**
     * @return Attribute[]
     */
    protected function getFlexibleAttributes()
    {
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->getFlexibleManager()->getAttributeRepository();
            $this->attributes = $attributeRepository->findBy(
                array('entityType' => $this->getFlexibleManager()->getFlexibleName())
            );
        }

        return $this->attributes;
    }
}
