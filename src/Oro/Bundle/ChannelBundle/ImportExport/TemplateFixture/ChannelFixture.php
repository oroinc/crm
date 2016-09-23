<?php

namespace Oro\Bundle\ChannelBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\Channel';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Custom channel|custom');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Channel();
    }

    /**
     * @param string  $key
     * @param Channel $entity
     */
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
