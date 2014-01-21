<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class BatchFilterBag
{
    const FILTER_TYPE_SIMPLE  = 'filter';
    const FILTER_TYPE_COMPLEX = 'complex_filter';

    /** @var array applied filters */
    protected $filters;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Return applied configured fields
     *
     * @return array
     */
    public function getAppliedFilters()
    {
        return $this->filters;
    }

    /**
     * @param int|\stdClass $lastId
     * @param string        $idFieldName
     */
    public function addLastIdFilter($lastId, $idFieldName = 'entity_id')
    {
        $this->addComplexFilter(
            'lastid',
            [
                'key'   => $idFieldName,
                'value' => [
                    'key'   => 'gt',
                    'value' => $lastId,
                ],
            ]
        );
    }

    /**
     * @param boolean   $isInitMode
     * @param \DateTime $date
     * @param string    $format
     *
     * @return $this
     */
    public function addDateFilter($isInitMode, \DateTime $date, $format = 'Y-m-d H:i:s')
    {
        if ($isInitMode) {
            $dateField = 'created_at';
            $dateKey   = 'to';
        } else {
            $dateField = 'updated_at';
            $dateKey   = 'from';
        }

        $this->addComplexFilter(
            'date',
            [
                'key'   => $dateField,
                'value' => [
                    'key'   => $dateKey,
                    'value' => $date->format($format),
                ],
            ]
        );

        return $this;
    }

    /**
     * Add website or store filter, depending on $key param
     *
     * @param array  $websiteIds
     * @param string $key website_id or store_id
     *
     * @return $this
     */
    public function addWebsiteFilter($websiteIds, $key = 'website_id')
    {
        $key = in_array($key, ['website_id', 'store_id']) ? $key : 'website_id';

        $this->addComplexFilter(
            'website',
            [
                'key'   => $key,
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', $websiteIds)
                ]
            ]
        );

        return $this;
    }

    /**
     * @param string $name
     * @param array  $definition
     *
     * @return $this
     */
    public function addComplexFilter($name, $definition)
    {
        $this->addFilter($name, $definition, self::FILTER_TYPE_COMPLEX);

        return $this;
    }

    /**
     * @param string $name
     * @param array  $definition
     * @param string $filterType
     *
     * @return $this
     */
    public function addFilter($name, array $definition, $filterType = self::FILTER_TYPE_SIMPLE)
    {
        $filterType = in_array(
            $filterType,
            [self::FILTER_TYPE_SIMPLE, self::FILTER_TYPE_COMPLEX]
        ) ? $filterType : self::FILTER_TYPE_SIMPLE;

        $this->filters[$filterType][$name] = $definition;

        return $this;
    }

    /**
     * @param string $filterType
     * @param string $filterName
     *
     * @return $this
     */
    public function reset($filterType = null, $filterName = null)
    {
        if (is_null($filterType) && is_null($filterName) && isset($this->filters[$filterType])) {
            $this->filters = [
                'complex_filter' => [],
                'filter'         => [],
            ];
        } else {
            unset($this->filters[$filterType][$filterName]);
        }

        return $this;
    }
}
