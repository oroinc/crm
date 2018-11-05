<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveTableQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveMailChimpBundleAndAbandonedCartBundleConfigs implements Migration, DatabasePlatformAwareInterface
{
    /** @var AbstractPlatform */
    protected $platform;

    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

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
                        ['name' => Type::STRING]
                    )
                );

                $queries->addQuery(
                    new ParametrizedSqlMigrationQuery(
                        'DELETE FROM oro_process_definition WHERE related_entity = :related_entity',
                        ['related_entity' => $className],
                        ['related_entity' => Type::STRING]
                    )
                );
            }
        }

        if (!class_exists('Oro\Bundle\MailChimpBundle\Transport\MailChimpTransport', true)) {
            $queries->addQuery(
                new ParametrizedSqlMigrationQuery(
                    'DELETE FROM oro_integration_channel WHERE type = :transport;',
                    ['transport' => 'mailchimp'],
                    ['transport' => Type::STRING]
                )
            );
            $queries->addQuery('ALTER TABLE oro_integration_transport DROP orocrm_mailchimp_apikey;');
            $queries->addQuery('ALTER TABLE oro_integration_transport DROP orocrm_mailchimp_act_up_int;');
            if ($this->platform instanceof PostgreSqlPlatform) {
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP CONSTRAINT FK_16E86BF27BC28329;');
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP CONSTRAINT FK_16E86BF27162EA00;');
                $queries->addQuery('DROP INDEX IDX_16E86BF27162EA00;');
                $queries->addQuery('DROP INDEX IDX_16E86BF27BC28329;');
            } else {
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP FOREIGN KEY FK_16E86BF27BC28329;');
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP FOREIGN KEY FK_16E86BF27162EA00;');
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP INDEX IDX_16E86BF27BC28329;');
                $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP INDEX IDX_16E86BF27162EA00;');
            }
            $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP mailchimp_template_id;');
            $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP mailchimp_channel_id;');
            $queries->addQuery('ALTER TABLE orocrm_cmpgn_transport_stngs DROP mailchimp_receive_activities;');
        }
    }
}
