<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class AbstractFlexibleChildFilter extends AbstractFlexibleFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\FilterInterface';

    /**
     * @var FilterInterface
     */
    protected $parentFilter;

    /**
     * Restrict to create filter with $parentFilter of invalid type
     *
     * @param FlexibleManagerRegistry $flexibleRegistry
     * @param FilterInterface $parentFilter
     * @throws \InvalidArgumentException If $parentFilter has invalid type
     */
    public function __construct(FlexibleManagerRegistry $flexibleRegistry, FilterInterface $parentFilter)
    {
        $this->flexibleRegistry = $flexibleRegistry;
        $this->parentFilter = $parentFilter;
        if (!$this->parentFilter instanceof $this->parentFilterClass) {
            throw new \InvalidArgumentException('Parent filter must be an instance of ' . $this->parentFilterClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return $this->parentFilter->getDefaultOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return $this->parentFilter->getRenderSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOptions()
    {
        return $this->parentFilter->getTypeOptions();
    }
}
