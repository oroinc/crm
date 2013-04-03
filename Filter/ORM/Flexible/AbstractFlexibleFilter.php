<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

abstract class AbstractFlexibleFilter extends AbstractFilter implements FilterInterface
{
    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleRegistry;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @param FlexibleManagerRegistry $flexibleRegistry
     */
    public function __construct(FlexibleManagerRegistry $flexibleRegistry)
    {
        $this->flexibleRegistry = $flexibleRegistry;
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

        $this->flexibleManager = $this->flexibleRegistry->getManager($flexibleEntityName);
    }

    /**
     * Apply filter using flexible repository
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string $field
     * @param string $value
     * @param string $operator
     */
    protected function applyFlexibleFilter(ProxyQueryInterface $proxyQuery, $field, $value, $operator)
    {
        /** @var $proxyQuery ProxyQuery */
        $queryBuilder = $proxyQuery->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $field, $value, $operator);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array();
    }
}
