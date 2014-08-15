<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;



use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactBundle implements Migration, AttachmentExtensionAwareInterface
{
    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addPhotoToContact($schema, $this->attachmentExtension);
    }

    /**
     * @param Schema              $schema
     * @param AttachmentExtension $attachmentExtension
     */
    public static function addPhotoToContact(Schema $schema, AttachmentExtension $attachmentExtension)
    {
        $attachmentExtension->addFileRelation(
            $schema,
            'orocrm_contact',
            'picture',
            'image',
            [],
            2,
            58,
            58
        );
    }
}
