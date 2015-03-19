<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtensionAwareInterface;

class AddTrackingIdentifierAssociation implements Migration, IdentifierEventExtensionAwareInterface
{
    /** @var IdentifierEventExtension */
    protected $extension;

    /**
     * @param IdentifierEventExtension $extension
     */
    public function setIdentifierEventExtension(IdentifierEventExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->extension->addIdentifierAssociation($schema, 'orocrm_magento_customer');
    }
}
