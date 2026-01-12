<?php

namespace Oro\Bundle\ChannelBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

/**
 * Provides test fixture data for channel entities used in import/export testing.
 */
class ChannelFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    #[\Override]
    public function getEntityClass()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\Channel';
    }

    #[\Override]
    public function getData()
    {
        return $this->getEntityData('Custom channel|custom');
    }

    #[\Override]
    protected function createEntity($key)
    {
        return new Channel();
    }

    /**
     * @param string  $key
     * @param Channel $entity
     */
    #[\Override]
    public function fillEntityData($key, $entity)
    {
        list($name, $type) = explode('|', $key);

        $entity
            ->setName($name)
            ->setChannelType($type)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
    }
}
