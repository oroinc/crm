<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Accounts;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\QueryConverter\YamlConverter;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ByOpportunitiesManager extends DatagridManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();

        $field->setName('username');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Username',
                'entity_alias' => 'u',
                'field_name'   => 'username',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('firstname');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'First name',
                'entity_alias' => 'u',
                'field_name'   => 'firstName',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('nameCount');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'Name count',
                'field_name'   => 'cnt',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'expression'   => 'COUNT(u.firstName)',
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('loginCount');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'Login count',
                'entity_alias' => 'u',
                'field_name'   => 'loginCount',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('api');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'API key',
                'entity_alias' => 'a',
                'field_name'   => 'apiKey',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => false,
                'filterable'   => true,
                'show_filter'  => false,
            )
        );

        $fieldsCollection->add($field);
    }

    /**
     * {@inheritdoc}
     */
    protected function createQuery()
    {
        $input     = Yaml::parse(file_get_contents(__DIR__ . '/../../Resources/config/reports.yml'));
        $converter = new YamlConverter();

        list($reportGroupName, $reportName) = array_slice(explode('-', $this->name), -2, 2);
        if (isset($input['reports'][$reportGroupName][$reportName])) {
            $qb = $converter->parse($input['reports'][$reportGroupName][$reportName], $this->entityManager);
            //$this->queryFactory->setQueryBuilder($qb);
        }

        return $this->queryFactory->createQuery();
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
