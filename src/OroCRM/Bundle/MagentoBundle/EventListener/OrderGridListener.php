<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\AddressBundle\Datagrid\CountryDatagridHelper;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

/**
 * The aim of this listener is to replace sub-selects of country and region of order's billing addresses
 * with corresponding joins in case when address or region filters is activated.
 * Because of slow performance of the "magento-order-grid" grid query with joins without filtering by these columns.
 */
class OrderGridListener
{
    /** @var CountryDatagridHelper */
    protected $datagridHelper;

    /**
     * @param CountryDatagridHelper $datagridHelper
     */
    public function __construct(CountryDatagridHelper $datagridHelper)
    {
        $this->datagridHelper = $datagridHelper;
    }

    const COUNTRY_NAME_COLUMN = 'countryName';
    const REGION_NAME_COLUMN  = 'regionName';

    const SELECT_PATH  = '[source][query][select]';
    const FILTERS_PATH = '[filters][columns]';

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $datagrid = $event->getDatagrid();
        $config   = $event->getConfig();
        $columns  = $config->offsetGetByPath('[columns]', []);

        $countryPos     = array_search(self::COUNTRY_NAME_COLUMN, array_keys($columns));
        $regionPos      = array_search(self::REGION_NAME_COLUMN, array_keys($columns));
        $processFilters = [];
        if (false !== $countryPos) {
            $processFilters[$countryPos] = [
                'name'       => self::COUNTRY_NAME_COLUMN,
                'definition' => [
                    self::COUNTRY_NAME_COLUMN => $this->getCountryFilterDefinition(
                        $this->isFilterEnabled($datagrid->getParameters(), self::COUNTRY_NAME_COLUMN)
                    )
                ]
            ];
        }
        if (false !== $regionPos) {
            $processFilters[$regionPos] = [
                'name'       => self::REGION_NAME_COLUMN,
                'definition' => [
                    self::REGION_NAME_COLUMN => $this->getRegionFilterDefinition(
                        $this->isFilterEnabled($datagrid->getParameters(), self::REGION_NAME_COLUMN)
                    )
                ]
            ];
        }
        ksort($processFilters);

        foreach ($processFilters as $columnPos => $data) {
            $this->addToArrayByPos($config, self::FILTERS_PATH, $columnPos, $data['definition']);
        }

        $activeFilters = $this->getActiveFilters($datagrid);
        if (isset($activeFilters[self::COUNTRY_NAME_COLUMN]) || isset($activeFilters[self::REGION_NAME_COLUMN])) {
            $this->replaceAddressSelects($config);
        }
    }

    /**
     * @param DatagridInterface $datagrid
     *
     * @return array
     */
    protected function getActiveFilters(DatagridInterface $datagrid)
    {
        $parameters = $datagrid->getParameters();

        if ($parameters->has(ParameterBag::MINIFIED_PARAMETERS)) {
            $minifiedParameters = $parameters->get(ParameterBag::MINIFIED_PARAMETERS);
            $filters            = [];

            if (array_key_exists('f', $minifiedParameters)) {
                $filters = $minifiedParameters['f'];
            }

            return $filters;
        }

        return $parameters->get(OrmFilterExtension::FILTER_ROOT_PARAM, []);
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
                    'property'             => 'name',
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
     * @param ParameterBag $datagridParameters
     * @param string       $fieldName
     *
     * @return bool
     */
    protected function isFilterEnabled(ParameterBag $datagridParameters, $fieldName)
    {
        $enabled  = false;
        $minified = $datagridParameters->get(ParameterBag::MINIFIED_PARAMETERS);
        if (isset($minified['f']['__' . $fieldName])) {
            $enabled = (bool)$minified['f']['__' . $fieldName];
        }

        return $enabled;
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
        $select           = $config->offsetGetByPath(self::SELECT_PATH, []);
        $countryColumn    = array_filter($select, function ($val) {
            return strpos($val, ' as countryName') !== false;
        });
        $regionColumn     = array_filter($select, function ($val) {
            return strpos($val, ' as regionName') !== false;
        });
        $countryColumnPos = key($countryColumn);
        $regionColumnPos  = key($regionColumn);
        $addJoins         = false;
        if ($countryColumnPos) {
            $addJoins                  = true;
            $select[$countryColumnPos] = 'country.name as countryName';
        }
        if ($regionColumnPos) {
            $addJoins                 = true;
            $select[$regionColumnPos] = 'CONCAT(CASE WHEN address.regionText IS NOT NULL ' .
                'THEN address.regionText ELSE region.name END, \'\') as regionName';
        }
        if ($addJoins) {
            $addressJoins = [
                [
                    'join'          => 'o.addresses',
                    'alias'         => 'address',
                    'conditionType' => 'WITH',
                    'condition'     => 'address.id IN (SELECT oa.id FROM OroCRMMagentoBundle:OrderAddress oa '
                        . 'LEFT JOIN oa.types type WHERE type.name = \'billing\' OR type.name IS NULL)'
                ],
                ['join' => 'address.country', 'alias' => 'country'],
                ['join' => 'address.region', 'alias' => 'region']
            ];
            $config->offsetAddToArrayByPath('[source][query][join][left]', $addressJoins);
        }
        $config->offsetSetByPath(self::SELECT_PATH, $select);
    }
}
