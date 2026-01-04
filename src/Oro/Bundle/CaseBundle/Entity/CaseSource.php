<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
* Entity that represents Case Source
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_case_source')]
#[Gedmo\TranslationEntity(class: CaseSourceTranslation::class)]
#[Config(defaultValues: ['grouping' => ['groups' => ['dictionary']], 'dictionary' => ['virtual_fields' => ['label']]])]
class CaseSource implements Translatable
{
    public const SOURCE_EMAIL = 'email';
    public const SOURCE_PHONE = 'phone';
    public const SOURCE_WEB   = 'web';
    public const SOURCE_OTHER = 'other';

    #[ORM\Id]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 16)]
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    protected ?string $name = null;

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
     * @return CaseSource
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
     * Set locale
     *
     * @param string $locale
     * @return CaseSource
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
    #[\Override]
    public function __toString()
    {
        return (string)$this->label;
    }
}
