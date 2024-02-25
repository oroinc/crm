<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
* Entity that represents Case Status
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_case_status')]
#[Gedmo\TranslationEntity(class: CaseStatusTranslation::class)]
#[Config(defaultValues: ['grouping' => ['groups' => ['dictionary']], 'dictionary' => ['virtual_fields' => ['label']]])]
class CaseStatus implements Translatable
{
    const STATUS_OPEN        = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED    = 'resolved';
    const STATUS_CLOSED      = 'closed';

    #[ORM\Id]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 16)]
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    protected ?string $name = null;

    #[ORM\Column(name: '`order`', type: Types::INTEGER)]
    protected ?int $order = null;

    #[ORM\Column(name: 'label', type: Types::STRING, length: 255)]
    #[Gedmo\Translatable]
    protected ?string $label = null;

    #[Gedmo\Locale]
    protected ?string $locale = null;

    /**
     * @param string $name
     */
    public function __construct($name)
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

    /**
     * @param string $label
     * @return CaseStatus
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param string $order
     * @return CaseStatus
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return CaseStatus
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->label;
    }

    /**
     * @param mixed $other
     * @return bool
     */
    public function isEqualTo($other)
    {
        if (!$other instanceof CaseStatus) {
            return false;
        }

        return $this->getName() == $other->getName();
    }
}
