<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Oro\Bundle\LocaleBundle\Entity\AbstractTranslation;

/**
 * Represents Gedmo translation dictionary for CaseStatus entity.
 */
#[ORM\Entity(repositoryClass: TranslationRepository::class)]
#[ORM\Table(name: 'orocrm_case_status_trans')]
#[ORM\Index(columns: ['locale', 'object_class', 'field', 'foreign_key'], name: 'orocrm_case_status_trans_idx')]
class CaseStatusTranslation extends AbstractTranslation
{
    /**
     * @var string|null
     */
    #[ORM\Column(name: 'foreign_key', type: Types::STRING, length: 16)]
    protected $foreignKey;
}
