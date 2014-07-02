<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\EmailOwnerConfigurationPass;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage;

class OroCRMContactBundle implements
    Migration,
    NameGeneratorAwareInterface,
    ExtendExtensionAwareInterface,
    ContainerAwareInterface,
    ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * @var ExtendDbIdentifierNameGenerator
     */
    protected $nameGenerator;

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @var EmailOwnerProviderStorage
     */
    protected $ownerProviderStorage;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->ownerProviderStorage = $container->get(EmailOwnerConfigurationPass::SERVICE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addActivityAssociations($schema, $this->activityExtension);

        // prepare select email:user sql
        $fromAndRecipients = '
            SELECT DISTINCT email_id, owner_id FROM (
                SELECT e.id as email_id, ea.{owner} as owner_id
                FROM oro_email_address ea
                    INNER JOIN oro_email e ON e.from_email_address_id = ea.id
                WHERE ea.{owner} IS NOT NULL
                UNION
                SELECT er.email_id as email_id, ea.{owner} as owner_id
                FROM oro_email_address ea
                    INNER JOIN oro_email_recipient er ON er.email_address_id = ea.id
                WHERE ea.{owner} IS NOT NULL
            ) as subq';
        $sourceClassName = $this->extendExtension->getEntityClassByTableName('oro_email');
        $targetClassName = $this->extendExtension->getEntityClassByTableName('orocrm_contact');

        $select = str_replace(
            '{owner}',
            'owner_contact_id',
            $fromAndRecipients
        );

        $this->assignActivities($sourceClassName, $targetClassName, $select, $queries);
    }

    /**
     * Enables Email activity for Contact entity
     *
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_contact');
    }

    /**
     * @param string   $sourceClassName
     * @param string   $targetClassName
     * @param string   $selectQuery
     * @param QueryBag $queries
     */
    public function assignActivities($sourceClassName, $targetClassName, $selectQuery, QueryBag $queries)
    {
        $associationName = ExtendHelper::buildAssociationName(
            $targetClassName,
            ActivityScope::ASSOCIATION_KIND
        );

        $tableName = $this->nameGenerator->generateManyToManyJoinTableName(
            $sourceClassName,
            $associationName,
            $targetClassName
        );

        $queries->addQuery(sprintf("INSERT INTO %s %s", $tableName, $selectQuery));
    }
}
