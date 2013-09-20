<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\QueryConverter\YamlConverter;

abstract class ReportGridManagerAbstract extends DatagridManager
{
    /** @var EntityManager */
    protected $em;

    /** @var array|null */
    protected $reportDefinition;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function createQuery()
    {
        $converter = new YamlConverter();

        if ($this->reportDefinition !== null) {
            $this->queryFactory->setQueryBuilder(
                $converter->parse($this->reportDefinition, $this->em)
            );
        }

        return $this->queryFactory->createQuery();
    }

    /**
     * Setter for definition array
     *
     * @param array $definition
     *
     * @return $this
     */
    public function setReportDefinitionArray(array $definition)
    {
        $this->reportDefinition = $definition;


        return $this;
    }
}
