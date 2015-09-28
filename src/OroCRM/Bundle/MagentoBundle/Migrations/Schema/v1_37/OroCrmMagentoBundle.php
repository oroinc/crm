<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_37;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

class OroCrmMagentoBundle implements Migration, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

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
        self::enableActivityAssociations($schema);
        self::addActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * @param Schema $schema
     */
    public static function enableActivityAssociations(Schema $schema)
    {
        $options = new OroOptions();
        $options->set('activity', 'immutable', false);

        $schema->getTable('orocrm_magento_cart')->addOption(OroOptions::KEY, $options);
        $schema->getTable('orocrm_magento_order')->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_order');
        $activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_order');

        $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_cart');
        $activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_cart');
    }
}
