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
     * @var string
     */
    protected $parentFilterClass = null;

    /**
     * @var FilterInterface
     */
    protected $parentFilter;

    /**
     * @param FlexibleManagerRegistry $flexibleRegistry
     * @param FilterInterface|null $parentFilter
     * @throws \InvalidArgumentException If $parentFilter has invalid type
     */
    public function __construct(FlexibleManagerRegistry $flexibleRegistry, FilterInterface $parentFilter = null)
    {
        $this->flexibleRegistry = $flexibleRegistry;
        if ($this->parentFilterClass) {
            $this->parentFilter = $parentFilter;
            if (!$this->parentFilter instanceof $this->parentFilterClass) {
                throw new \InvalidArgumentException('Parent filter must be an instance of ' . $this->parentFilterClass);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($name, array $options = array())
    {
        parent::initialize($name, $options);
        $this->loadFlexibleManager();
    }

    /**
     * Gets flexible manager
     *
     * @return FlexibleManager
     * @throws \LogicException
     */
    protected function getFlexibleManager()
    {
        $this->loadFlexibleManager();
        return $this->flexibleManager;
    }

    protected function loadFlexibleManager()
    {
        if (!$this->flexibleManager) {
            $flexibleEntityName = $this->getOption('flexible_name');
            if (!$flexibleEntityName) {
                throw new \LogicException('Flexible entity filter must have flexible entity name.');
            }
            $this->flexibleManager = $this->flexibleRegistry->getManager($flexibleEntityName);
        }
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
        $entityRepository = $this->getFlexibleManager()->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $field, $value, $operator);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return $this->parentFilter ? $this->parentFilter->getDefaultOptions() : array();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOptions()
    {
        return $this->parentFilter ? $this->parentFilter->getTypeOptions() : parent::getTypeOptions();
    }
}
