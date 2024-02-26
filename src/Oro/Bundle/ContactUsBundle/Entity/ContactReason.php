<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactUsBundle_Entity_ContactReason;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactReasonSelectType;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\FallbackTrait;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * Entity that represents contact reason
 *
 *
 * @method LocalizedFallbackValue getDefaultTitle()
 * @method setDefaultTitle(string $value)
 * @mixin OroContactUsBundle_Entity_ContactReason
 */
#[ORM\Entity(repositoryClass: ContactReasonRepository::class)]
#[ORM\Table(name: 'orocrm_contactus_contact_rsn')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[Config(
    routeName: 'oro_contactus_reason_index',
    defaultValues: [
        'grouping' => ['groups' => ['dictionary']],
        'grid' => ['default' => 'orcrm-contact-reasons-grid'],
        'form' => ['form_type' => ContactReasonSelectType::class, 'grid_name' => 'orcrm-contact-reasons-grid'],
        'security' => ['type' => 'ACL', 'permissions' => 'All', 'group_name' => '', 'category' => 'account_management']
    ]
)]
class ContactReason implements ExtendEntityInterface
{
    use SoftDeleteableEntity;
    use FallbackTrait;
    use ExtendEntityTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var Collection<int, LocalizedFallbackValue>
     */
    #[ORM\ManyToMany(targetEntity: LocalizedFallbackValue::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'orocrm_contactus_contact_rsn_t')]
    #[ORM\JoinColumn(name: 'contact_reason_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'localized_value_id', referencedColumnName: 'id', unique: true, onDelete: 'CASCADE')]
    protected ?Collection $titles = null;

    /**
     * @param null|string $defaultTitle
     */
    public function __construct($defaultTitle = null)
    {
        $this->titles = new ArrayCollection();
        $this->setDefaultTitle((string)$defaultTitle);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Collection|LocalizedFallbackValue[] $titles
     */
    public function setTitles($titles)
    {
        $this->titles = $titles;
        $defaultTitle = $this->getDefaultFallbackValue($this->titles);
        $this->setDefaultTitle((string)$defaultTitle);
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getDefaultFallbackValue($this->titles);
    }
}
