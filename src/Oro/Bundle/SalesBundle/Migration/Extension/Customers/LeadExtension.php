<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

class LeadExtension implements ExtendExtensionAwareInterface, NameGeneratorAwareInterface
{
    const LEAD_TABLE_NAME = 'orocrm_sales_lead';

    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var ExtendDbIdentifierNameGenerator */
    protected $nameGenerator;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Adds the association between the target customer table and the opportunity table
     *
     * @param Schema $schema
     * @param string $targetTableName  Target entity table name
     * @param string $targetColumnName A column name is used to show related entity
     */
    public function addLeadCustomerAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null
    ) {
        $leadTable   = $schema->getTable(self::LEAD_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
            $targetColumnName  = reset($primaryKeyColumns);
        }

        $options = new OroOptions();
        $options->set('sales_lead', 'enabled', true);
        $targetTable->addOption(OroOptions::KEY, $options);
        $targetClassName = $this->extendExtension->getEntityClassByTableName($targetTableName);
        $associationName        = ExtendHelper::buildAssociationName($targetClassName);

        $leadOptions = new OroOptions();
        $leadOptions->append('sales', 'customers', [$targetClassName => ['association_name' => $associationName]]);
        $leadTable->addOption(OroOptions::KEY, $leadOptions);

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $leadTable,
            $associationName,
            $targetTable,
            $targetColumnName
        );
    }

    /**
     * Gets an association column name for opportunity relation
     *
     * @param string $targetTableName Target entity table name.
     *
     * @return string
     */
    public function getAssociationColumnName($targetTableName)
    {
        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName)
        );

        return $this->nameGenerator->generateRelationColumnName($associationName);
    }
}
