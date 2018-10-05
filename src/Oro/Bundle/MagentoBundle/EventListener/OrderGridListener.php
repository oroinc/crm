<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\AddressBundle\Datagrid\CountryDatagridHelper;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;

/**
 * The aim of this listener is to replace sub-selects of country and region of order's billing addresses
 * with corresponding joins in case when address or region filters is activated.
 * Because of slow performance of the "magento-order-grid" grid query with joins without filtering by these columns.
 */
class OrderGridListener
{
    /** @var CountryDatagridHelper */
    protected $datagridHelper;

    /** @var DatagridStateProviderInterface */
    private $filtersStateProvider;

    /**
     * @param CountryDatagridHelper $datagridHelper
     * @param DatagridStateProviderInterface $filtersStateProvider
     */
    public function __construct(
        CountryDatagridHelper $datagridHelper,
        DatagridStateProviderInterface $filtersStateProvider
    ) {
        $this->datagridHelper = $datagridHelper;
        $this->filtersStateProvider = $filtersStateProvider;
    }

    const COUNTRY_NAME_COLUMN = 'countryName';
    const REGION_NAME_COLUMN  = 'regionName';

    const FILTERS_PATH = '[filters][columns]';

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $columnsIndexes = $this->getColumnsIndexes($config);

        // Adds filters to datagrid config.
        foreach ($columnsIndexes as $columnName => $index) {
            $this->addToArrayByPos(
                $config,
                self::FILTERS_PATH,
                $index,
                [$columnName => $this->getFilterDefinition($columnName)]
            );
        }

        $filtersState = $this->filtersStateProvider->getState($config, $event->getDatagrid()->getParameters());

        // Marks filters as enabled according to state.
        foreach ($columnsIndexes as $columnName => $index) {
            $config->offsetSetByPath(
                sprintf('%s[%s][enabled]', self::FILTERS_PATH, $columnName),
                $this->isFilterEnabled($filtersState, $columnName)
            );
        }

        if (isset($filtersState[self::COUNTRY_NAME_COLUMN]) || isset($filtersState[self::REGION_NAME_COLUMN])) {
            $this->replaceAddressSelects($config);
        }
    }

    /**
     * Find indexes of countryName and regionName columns.
     * Sorts columns as per their index.
     *
     * @param DatagridConfiguration $config
     *
     * @return array
     */
    private function getColumnsIndexes(DatagridConfiguration $config): array
    {
        $columns = $config->offsetGetByPath('[columns]', []);

        // Find indexes of countryName and regionName columns.
        $columnsIndexes = array_reduce(
            [self::COUNTRY_NAME_COLUMN, self::REGION_NAME_COLUMN],
            function (array $columnsIndexes, string $columnName) use ($columns) {
                $columnsIndexes[$columnName] = array_search($columnName, array_keys($columns), false);

                return $columnsIndexes;
            },
            []
        );

        // Removes absent columns - with index === false.
        $columnsIndexes = array_filter($columnsIndexes, '\strlen');

        // Sorts columns as per their index.
        asort($columnsIndexes);

        return $columnsIndexes;
    }

    /**
     * @param string $filterName
     *
     * @return array
     */
    private function getFilterDefinition(string $filterName): array
    {
        switch ($filterName) {
            case self::COUNTRY_NAME_COLUMN:
                return [
                    'type' => 'entity',
                    'data_name' => 'address.country',
                    'enabled' => false,
                    'options' => [
                        'field_options' => [
                            'class' => 'OroAddressBundle:Country',
                            'choice_label' => 'name',
                            'query_builder' => $this->datagridHelper->getCountryFilterQueryBuilder(),
                            'translatable_options' => false
                        ]
                    ]
                ];
                break;
            case self::REGION_NAME_COLUMN:
                return [
                    'type' => 'string',
                    'data_name' => 'regionName',
                    'enabled' => false,
                ];
        }

        throw new \InvalidArgumentException(sprintf('Fitler %s is not supported', $filterName));
    }

    /**
     * @param boolean $enabled
     *
     * @return array
     */
    protected function getCountryFilterDefinition($enabled)
    {
        return [
            'type'      => 'entity',
            'data_name' => 'address.country',
            'enabled'   => $enabled,
            'options'   => [
                'field_options' => [
                    'class'                => 'OroAddressBundle:Country',
                    'choice_label'         => 'name',
                    'query_builder'        => $this->datagridHelper->getCountryFilterQueryBuilder(),
                    'translatable_options' => false
                ]
            ]
        ];
    }

    /**
     * @param boolean $enabled
     *
     * @return array
     */
    protected function getRegionFilterDefinition($enabled)
    {
        return [
            'type'      => 'string',
            'data_name' => 'regionName',
            'enabled'   => $enabled
        ];
    }

    /**
     * @param array $filtersState
     * @param string $filterName
     *
     * @return bool
     */
    protected function isFilterEnabled(array $filtersState, string $filterName): bool
    {
        return !empty($filtersState['__' . $filterName]);
    }

    /**
     * @param DatagridConfiguration $config
     * @param string                $path
     * @param int                   $pos
     * @param array                 $value
     */
    protected function addToArrayByPos(DatagridConfiguration $config, $path, $pos, $value)
    {
        $array  = $config->offsetGetByPath($path, []);
        $slice1 = array_slice($array, 0, $pos, true);
        $slice2 = array_slice($array, $pos, count($array) - 1, true);
        $config->offsetSetByPath($path, $slice1 + $value + $slice2);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function replaceAddressSelects(DatagridConfiguration $config)
    {
        $query = $config->getOrmQuery();
        $select = $query->getSelect();
        $countryColumn = array_filter($select, function ($val) {
            return strpos($val, ' as countryName') !== false;
        });
        $regionColumn = array_filter($select, function ($val) {
            return strpos($val, ' as regionName') !== false;
        });
        $countryColumnPos = key($countryColumn);
        $regionColumnPos = key($regionColumn);
        $addJoins = false;
        if ($countryColumnPos) {
            $addJoins = true;
            $select[$countryColumnPos] = 'country.name as countryName';
        }
        if ($regionColumnPos) {
            $addJoins = true;
            $select[$regionColumnPos] = 'CONCAT(CASE WHEN address.regionText IS NOT NULL ' .
                'THEN address.regionText ELSE region.name END, \'\') as regionName';
        }
        if ($addJoins) {
            $query->addLeftJoin(
                'o.addresses',
                'address',
                'WITH',
                'address.id IN (SELECT oa.id FROM OroMagentoBundle:OrderAddress oa '
                . 'LEFT JOIN oa.types type WHERE type.name = \'billing\' OR type.name IS NULL)'
            );
            $query->addLeftJoin('address.country', 'country');
            $query->addLeftJoin('address.region', 'region');
        }
        $query->setSelect($select);
    }
}
