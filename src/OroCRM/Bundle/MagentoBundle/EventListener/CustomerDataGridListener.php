<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

class CustomerDataGridListener
{
    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getParameters();
        $this->addNewsletterSubscribers($config, $parameters);
        $this->convertJoinsToSubQueries($config, $parameters);
    }

    /**
     * @param DatagridConfiguration $config
     * @param ParameterBag          $parameters
     */
    protected function addNewsletterSubscribers(DatagridConfiguration $config, ParameterBag $parameters)
    {
        $config->addFilter(
            'isSubscriber',
            [
                'label' => 'orocrm.magento.datagrid.columns.is_subscriber.label',
                'type' => 'single_choice',
                'data_name' => 'isSubscriber',
                'options' => [
                    'field_options' => [
                        'choices' => [
                            'unknown' => 'orocrm.magento.datagrid.columns.is_subscriber.unknown',
                            'no' => 'orocrm.magento.datagrid.columns.is_subscriber.no',
                            'yes' => 'orocrm.magento.datagrid.columns.is_subscriber.yes'
                        ]
                    ]
                ]
            ]
        );

        $filters = $parameters->get(OrmFilterExtension::FILTER_ROOT_PARAM, []);
        if (empty($filters['isSubscriber'])) {
            return;
        }

        $query = $config->offsetGetByPath('[source][query]', []);
        foreach ($query['select'] as &$field) {
            if ($field === 'c.id') {
                $field = 'DISTINCT ' . $field;
                break;
            }
        }
        $query['select'][] = 'CASE WHEN'
            . ' transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
            . ' THEN (CASE WHEN IDENTITY(newsletterSubscribers.status) = \'1\' THEN \'yes\' ELSE \'no\' END)'
            . ' ELSE \'unknown\''
            . ' END as isSubscriber';

        $query['join']['left'][] = [
            'join' => 'c.channel',
            'alias' => 'channel'
        ];
        $query['join']['left'][] = [
            'join' => 'OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport',
            'alias' => 'transport',
            'conditionType' => 'WITH',
            'condition' => 'channel.transport = transport'
        ];
        $query['join']['left'][] = [
            'join' => 'c.newsletterSubscribers',
            'alias' => 'newsletterSubscribers'
        ];
        $config->offsetSetByPath('[source][query]', $query);
    }

    /**
     * @param DatagridConfiguration $config
     * @param ParameterBag          $parameters
     */
    protected function convertJoinsToSubQueries(DatagridConfiguration $config, ParameterBag $parameters)
    {
        // by a performance reasons, convert some joins to sub-queries
        $sorters = $parameters->get(OrmSorterExtension::SORTERS_ROOT_PARAM, []);
        if (empty($sorters['channelName'])) {
            $this->convertAssociationJoinToSubquery(
                $config,
                'dataChannel',
                'channelName',
                'OroCRM\Bundle\ChannelBundle\Entity\Channel'
            );
        }
        if (empty($sorters['websiteName'])) {
            $this->convertAssociationJoinToSubquery(
                $config,
                'cw',
                'websiteName',
                'OroCRM\Bundle\MagentoBundle\Entity\Website'
            );
        }
        if (empty($sorters['customerGroup'])) {
            $this->convertAssociationJoinToSubquery(
                $config,
                'cg',
                'customerGroup',
                'OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup'
            );
        }
    }

    /**
     * @param DatagridConfiguration $config
     * @param string                $joinAlias
     * @param string                $columnAlias
     * @param string                $joinEntityClass
     */
    private function convertAssociationJoinToSubquery(
        DatagridConfiguration $config,
        $joinAlias,
        $columnAlias,
        $joinEntityClass
    ) {
        list(
            $join,
            $joinPath,
            $selectExpr,
            $selectPath
            ) = $this->findJoinAndSelectByAliases($config, $joinAlias, $columnAlias);
        if (!$join || !$selectExpr) {
            return;
        }

        $subQuery = sprintf(
            'SELECT %1$s FROM %4$s AS %3$s WHERE %3$s = %2$s',
            $selectExpr,
            $join['join'],
            $joinAlias,
            $joinEntityClass
        );
        if (!empty($join['condition'])) {
            $subQuery .= sprintf(' AND %s', $join['condition']);
        }

        $config->offsetSetByPath($selectPath, sprintf('(%s) AS %s', $subQuery, $columnAlias));
        $config->offsetUnsetByPath($joinPath);
    }

    /**
     * @param DatagridConfiguration $config
     * @param string                $joinAlias
     * @param string                $columnAlias
     *
     * @return array [join, join path, select expression without column alias, select item path]
     */
    private function findJoinAndSelectByAliases(DatagridConfiguration $config, $joinAlias, $columnAlias)
    {
        list($join, $joinPath) = $this->findJoinByAlias($config, $joinAlias, '[source][query][join][left]');
        $selectExpr = null;
        $selectPath = null;
        if (null !== $join) {
            list($selectExpr, $selectPath) = $this->findSelectExprByAlias($config, $columnAlias);
        }

        return [$join, $joinPath, $selectExpr, $selectPath];
    }

    /**
     * @param DatagridConfiguration $config
     * @param string                $joinAlias
     * @param string                $joinsPath
     *
     * @return array [join, join path]
     */
    private function findJoinByAlias(DatagridConfiguration $config, $joinAlias, $joinsPath)
    {
        $foundJoin = null;
        $foundJoinPath = null;
        $joins = $config->offsetGetByPath($joinsPath, []);
        foreach ($joins as $key => $join) {
            if ($join['alias'] === $joinAlias) {
                $foundJoin = $join;
                $foundJoinPath = sprintf('%s[%s]', $joinsPath, $key);
                break;
            }
        }

        return [$foundJoin, $foundJoinPath];
    }

    /**
     * @param DatagridConfiguration $config
     * @param string                $columnAlias
     *
     * @return array [select expression without column alias, select item path]
     */
    private function findSelectExprByAlias(DatagridConfiguration $config, $columnAlias)
    {
        $foundSelectExpr = null;
        $foundSelectPath = null;
        $pattern = sprintf('#(?P<expr>.+?)\\s+AS\\s+%s#i', $columnAlias);
        $selects = $config->offsetGetByPath('[source][query][select]', []);
        foreach ($selects as $key => $select) {
            if (preg_match($pattern, $select, $matches)) {
                $foundSelectExpr = $matches['expr'];
                $foundSelectPath = sprintf('[source][query][select][%s]', $key);
                break;
            }
        }

        return [$foundSelectExpr, $foundSelectPath];
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return string|null
     */
    private function getRootAlias(DatagridConfiguration $config)
    {
        $fromPart = $config->offsetGetByPath('[source][query][from]', []);
        if (empty($fromPart)) {
            return null;
        }
        $from = reset($fromPart);

        return array_key_exists('alias', $from)
            ? $from['alias']
            : null;
    }
}
