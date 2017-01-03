<?php

namespace Oro\Bundle\ChannelBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ChannelLimitationExtension extends AbstractExtension
{
    const CHANNEL_OPTIONS_PATH         = '[channel_limitation]';
    const CHANNEL_RELATION_OPTION_PATH = '[channel_limitation][channel_relation_path]';

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->isOrmDatasource()
            && $this->getParameters()->get('channelIds', false);
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $options           = $config->offsetGetByPath(self::CHANNEL_OPTIONS_PATH, []);
        $optionsNormalized = $this->validateConfiguration(
            new ChannelLimitationExtensionConfiguration(),
            ['root' => $options]
        );

        $config->offsetSetByPath(self::CHANNEL_OPTIONS_PATH, $optionsNormalized);
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var OrmDatasource $datasource */
        $path = $config->offsetGetByPath(self::CHANNEL_RELATION_OPTION_PATH);
        $queryBuilder = $datasource->getQueryBuilder();
        if (strpos($path, '.') !== false) {
            list($mainEntity, $relationName) = explode('.', $path);
            $mainEntity   = $this->ensureJoined($queryBuilder, $mainEntity);
            $relationName = $this->ensureJoined($queryBuilder, $relationName, $mainEntity);
        } else {
            $relationName = $path;
        }

        $channelIds   = explode(',', $this->getParameters()->get('channelIds'));

        $queryBuilder->andWhere($relationName . '.id in (:channelIds)');
        $queryBuilder->setParameter('channelIds', $channelIds);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $relationPath
     * @param null|string  $parentAlias
     *
     * @return string
     */
    protected function ensureJoined(QueryBuilder $queryBuilder, $relationPath, $parentAlias = null)
    {
        if (empty($relationPath)) {
            // main entity return real alias
            $aliases = $queryBuilder->getRootAliases();

            return reset($aliases);
        }

        $joinAlias    = false;
        $fullJoinPath = sprintf('%s.%s', $parentAlias, $relationPath);
        $joins        = $queryBuilder->getDQLPart('join');

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($joins, \RecursiveArrayIterator::CHILD_ARRAYS_ONLY),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var Join $join */
        foreach ($iterator as $join) {
            if ($join->getJoin() === $fullJoinPath) {
                $joinAlias = $join->getAlias();
            }
        }

        if (!$joinAlias) {
            $joinAlias = uniqid($relationPath);
            $queryBuilder->leftJoin($fullJoinPath, $joinAlias);
        }

        return $joinAlias;
    }
}
