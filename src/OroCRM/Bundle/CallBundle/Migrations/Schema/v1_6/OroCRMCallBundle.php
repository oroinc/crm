<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration, DatabasePlatformAwareInterface
{
    /** @var AbstractPlatform */
    protected $platform;

    /** {@inheritdoc} */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /** {@inheritdoc} */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_call');
        $column = $table->getColumn('duration');

        if ($this->platform instanceof PostgreSQL92Platform) {
            $queries->addPreQuery(
                'ALTER TABLE orocrm_call ALTER duration TYPE integer' .
                ' USING (EXTRACT(EPOCH FROM duration))::integer'
            );
        } else {
            $column->setType(Type::getType('duration'));
        }
        $column->setOptions(['comment' => '(DC2Type:duration)']);
    }
}
