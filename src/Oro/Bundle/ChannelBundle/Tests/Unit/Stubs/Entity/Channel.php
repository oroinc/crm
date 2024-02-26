<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Channel
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $name = null;

    /**
     * @var Collection<int, EntityName>
     */
    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: EntityName::class)]
    protected ?Collection $entities = null;

    #[ORM\OneToOne(targetEntity: Integration::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'data_source_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Integration $dataSource = null;

    #[ORM\Column(name: 'status', type: Types::BOOLEAN, nullable: false)]
    protected ?bool $status = null;

    public function __construct()
    {
        $this->entities = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setEntities(array $entities)
    {
        $this->entities = new ArrayCollection($entities);
    }

    /**
     * @return ArrayCollection
     */
    public function getEntities()
    {
        return $this->entities;
    }

    public function setDataSource(Integration $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return Integration
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
}
