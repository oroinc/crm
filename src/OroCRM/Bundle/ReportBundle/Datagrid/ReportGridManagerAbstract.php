<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\QueryConverter\YamlConverter;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class ReportGridManagerAbstract extends DatagridManager
{
    /** @var EntityManager */
    protected $em;

    /** @var array|null */
    protected $reportDefinition;

    protected $defaultOptions = array(
        'type'         => FieldDescriptionInterface::TYPE_TEXT,
        'filter_type'  => FilterInterface::TYPE_STRING,
        'required'     => false,
        'sortable'     => true,
        'filterable'   => true,
        'show_filter'  => true,
    );

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

    /**
     * Add field to datagrid
     *
     * @param $name
     * @param $options
     * @param FieldDescriptionCollection $fieldCollection
     */
    public function addField($name, $options, FieldDescriptionCollection $fieldCollection)
    {
        $options = array_merge($this->defaultOptions, $options);

        $field = new FieldDescription();
        $field->setName($name);
        $field->setOptions($options);

        $fieldCollection->add($field);
    }
}
