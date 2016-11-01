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

class OpportunityExtension implements ExtendExtensionAwareInterface, NameGeneratorAwareInterface
{
    const OPPORTUNITY_TABLE_NAME = 'orocrm_sales_opportunity';

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
    public function addOpportunityCustomerAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null
    ) {
        $opportunityTable   = $schema->getTable(self::OPPORTUNITY_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
            $targetColumnName  = reset($primaryKeyColumns);
        }

        $options = new OroOptions();
        $options->set('sales_opportunity', 'enabled', true);
        $targetTable->addOption(OroOptions::KEY, $options);
        $targetClassName = $this->extendExtension->getEntityClassByTableName($targetTableName);
        $associationName        = ExtendHelper::buildAssociationName($targetClassName);
        
        $opportunityOptions = new OroOptions();
        $opportunityOptions->append('sales', 'customers', [$targetClassName => ['association_name' => $associationName]]);
        $opportunityTable->addOption(OroOptions::KEY, $opportunityOptions);
        $this->extendExtension->addManyToOneRelation(
            $schema,
            $opportunityTable,
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
