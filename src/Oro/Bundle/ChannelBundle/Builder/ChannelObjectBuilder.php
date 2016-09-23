<?php

namespace Oro\Bundle\ChannelBundle\Builder;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelObjectBuilder
{
    const CUSTOM_CHANNEL_TYPE = 'custom';

    /** @var string */
    protected $channelType;

    /** @var array */
    protected $entities = [];

    /** @var Organization */
    protected $owner;

    /** @var string */
    protected $name;

    /** @var Integration|null */
    protected $dataSource;

    /** @var bool */
    protected $status;

    /** @var bool */
    protected $populateEntities = false;

    /** @var EntityManager */
    protected $em;

    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var Channel */
    protected $channel;

    /** @var \DateTime */
    protected $createdAt;

    /**
     * @param EntityManager    $em
     * @param SettingsProvider $settingsProvider
     * @param Channel          $channel
     */
    public function __construct(EntityManager $em, SettingsProvider $settingsProvider, Channel $channel)
    {
        $this->em               = $em;
        $this->settingsProvider = $settingsProvider;
        $this->channel          = $channel;
        $this->channelType      = $channel->getChannelType();
        $this->dataSource       = $channel->getDataSource();
        $this->name             = $channel->getName();
        $this->owner            = $channel->getOwner();
        $this->entities         = $channel->getEntities();
        $this->status           = (bool) $channel->getStatus();
    }

    /**
     * @param null|string $type
     *
     * @return ChannelObjectBuilder
     */
    public function setChannelType($type)
    {
        $this->channelType = $type;

        return $this;
    }

    /**
     * @param string $entity
     * @param bool   $prepend
     */
    public function addEntity($entity, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->entities, $entity);
        } else {
            $this->entities[] = $entity;
        }

        $this->entities = array_unique($this->entities);
    }

    /**
     * @param string $entity
     */
    public function removeEntity($entity)
    {
        if (in_array($entity, $this->entities, true)) {
            unset($this->entities[array_search($entity, $this->entities, true)]);
        }
    }

    /**
     * @param array $entities
     *
     * @return ChannelObjectBuilder
     */
    public function setEntities(array $entities = null)
    {
        if (null === $entities) {
            $entities               = [];
            $this->populateEntities = true;
        } else {
            $this->populateEntities = false;
        }

        $this->entities = $entities;

        return $this;
    }

    /**
     * @param Organization $organization
     *
     * @return ChannelObjectBuilder
     */
    public function setOwner(Organization $organization = null)
    {
        $this->owner = $organization;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ChannelObjectBuilder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set data source object to channel
     *
     * @param Integration|null $dataSource
     *
     * @return ChannelObjectBuilder
     */
    public function setDataSource(Integration $dataSource = null)
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * @param bool $status
     *
     * @return ChannelObjectBuilder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns built channel
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return Channel
     */
    public function getChannel()
    {
        $type     = $this->getDefaultType();
        $name     = $this->getDefaultName($type);
        $identity = $this->settingsProvider->getCustomerIdentityFromConfig($type);
        if ($this->populateEntities) {
            $this->entities = $this->settingsProvider->getEntitiesByChannelType($type);
        }
        $this->addEntity($identity, true);

        $owner = $this->owner;
        if (!$owner) {
            $owner = $this->getDefaultOrganization();
        }

        $this->channel->setChannelType($type);
        $this->channel->setName($name);
        $this->channel->setOwner($owner);
        $this->channel->setCustomerIdentity($identity);
        $this->channel->setEntities($this->entities);
        $this->channel->setStatus($this->status);
        $this->channel->setDataSource($this->dataSource);
        if (null !== $this->createdAt) {
            // set created at only whn not nullable, otherwise update scenario will fail
            $this->channel->setCreatedAt($this->createdAt);
        }

        return $this->channel;
    }

    /**
     * @throws \LogicException
     * @return null|Organization
     */
    protected function getDefaultOrganization()
    {
        $repo    = $this->em->getRepository('OroOrganizationBundle:Organization');
        $default = $repo->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_ORGANIZATION]);

        if (!$default) {
            $default = $repo->createQueryBuilder('o')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$default) {
            throw new \LogicException('Unable to find organization owner for channel');
        }

        return $default;
    }

    /**
     * @return string
     */
    protected function getDefaultType()
    {
        return $this->channelType ?: self::CUSTOM_CHANNEL_TYPE;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getDefaultName($type)
    {
        return $this->name ?: ucfirst($type . ' channel');
    }
}
