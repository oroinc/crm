<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveTableQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveMailChimpBundleAndAbandonedCartBundleConfigs implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $classNames = [
            'Oro\Bundle\MailChimpBundle\Entity\Campaign',
            'Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar',
            'Oro\Bundle\MailChimpBundle\Entity\MailChimpTransport',
            'Oro\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings',
            'Oro\Bundle\MailChimpBundle\Entity\MarketingListEmail',
            'Oro\Bundle\MailChimpBundle\Entity\Member',
            'Oro\Bundle\MailChimpBundle\Entity\MemberActivity',
            'Oro\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar',
            'Oro\Bundle\MailChimpBundle\Entity\StaticSegment',
            'Oro\Bundle\MailChimpBundle\Entity\StaticSegmentMember',
            'Oro\Bundle\MailChimpBundle\Entity\StaticSegmentMemberToRemove',
            'Oro\Bundle\MailChimpBundle\Entity\SubscribersList',
            'Oro\Bundle\MailChimpBundle\Entity\Template',
            'Oro\Bundle\AbandonedCartBundle\Entity\AbandonedCartCampaign',
            'Oro\Bundle\AbandonedCartBundle\Entity\AbandonedCartConversion',
        ];

        foreach ($classNames as $className) {
            if (!class_exists($className, false)) {
                $queries->addQuery(new RemoveTableQuery($className));

                $queries->addQuery(
                    new ParametrizedSqlMigrationQuery(
                        'DELETE FROM orocrm_channel_entity_name WHERE name = :name',
                        ['name' => $className],
                        ['name' => Types::STRING]
                    )
                );

                $queries->addQuery(
                    new ParametrizedSqlMigrationQuery(
                        'DELETE FROM oro_process_definition WHERE related_entity = :related_entity',
                        ['related_entity' => $className],
                        ['related_entity' => Types::STRING]
                    )
                );
            }
        }

        if (!class_exists('Oro\Bundle\MailChimpBundle\Transport\MailChimpTransport', true)) {
            $queries->addQuery(
                new ParametrizedSqlMigrationQuery(
                    'DELETE FROM oro_integration_channel WHERE type = :transport;',
                    ['transport' => 'mailchimp'],
                    ['transport' => Types::STRING]
                )
            );

            $this->dropColumns(
                $schema,
                'oro_integration_transport',
                ['orocrm_mailchimp_apikey', 'orocrm_mailchimp_act_up_int']
            );

            $this->dropForeignKeys(
                $schema,
                'orocrm_cmpgn_transport_stngs',
                ['FK_16E86BF27BC28329', 'FK_16E86BF27162EA00']
            );

            $this->dropIndexes(
                $schema,
                'orocrm_cmpgn_transport_stngs',
                ['IDX_16E86BF27BC28329', 'IDX_16E86BF27162EA00']
            );

            $this->dropColumns(
                $schema,
                'orocrm_cmpgn_transport_stngs',
                ['mailchimp_template_id', 'mailchimp_channel_id', 'mailchimp_receive_activities']
            );
        }
    }

    private function dropColumns(Schema $schema, string $tableName, array $columns): void
    {
        if (!$schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable($tableName);
        foreach ($columns as $column) {
            if ($table->hasColumn($column)) {
                $table->dropColumn($column);
            }
        }
    }

    private function dropForeignKeys(Schema $schema, string $tableName, array $foreignKeys): void
    {
        if (!$schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable($tableName);
        foreach ($foreignKeys as $foreignKey) {
            if ($table->hasForeignKey($foreignKey)) {
                $table->removeForeignKey($foreignKey);
            }
        }
    }

    private function dropIndexes(Schema $schema, string $tableName, array $indexes): void
    {
        if (!$schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable($tableName);
        foreach ($indexes as $index) {
            if ($table->hasIndex($index)) {
                $table->dropIndex($index);
            }
        }
    }
}
