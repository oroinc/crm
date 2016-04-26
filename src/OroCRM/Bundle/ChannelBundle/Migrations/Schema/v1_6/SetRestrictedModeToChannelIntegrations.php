<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class SetRestrictedModeToChannelIntegrations implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateChannelIntegrationsMode(Channel::EDIT_MODE_RESTRICTED));
    }
}
