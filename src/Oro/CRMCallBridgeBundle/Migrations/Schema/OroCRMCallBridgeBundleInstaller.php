<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;

use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\AddActivityAssociationContactUs;
use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\AddActivityAssociationMagento;
use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\AddActivityAssociationSales;
use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\AddActivityAssociationCase;
use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\UpdateContactUsDependencySchema;
use Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0\UpdateMagentoDependencySchema;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMCallBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    ContainerAwareInterface
{

    use ContainerAwareTrait;

    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
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
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if (!$this->container->hasParameter('installed')
            || !$this->container->getParameter('installed')) {
            AddActivityAssociationContactUs::addActivityAssociations($schema, $this->activityExtension);
            AddActivityAssociationMagento::addActivityAssociations($schema, $this->activityExtension);
            AddActivityAssociationSales::addActivityAssociations($schema, $this->activityExtension);
            AddActivityAssociationCase::addActivityAssociations($schema, $this->activityExtension);
        }
    }
}
