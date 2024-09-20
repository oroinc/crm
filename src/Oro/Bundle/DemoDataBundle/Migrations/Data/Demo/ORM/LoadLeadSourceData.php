<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TranslationBundle\Migrations\Data\ORM\LoadLanguageData;

/**
 * Loads lead_source enum options for Lead entities.
 */
class LoadLeadSourceData extends AbstractFixture implements DependentFixtureInterface
{
    protected array $data = [
        'Website' => false,
        'Direct Mail' => false,
        'Affiliate' => false,
        'Email Marketing' => false,
        'Outbound' => false,
        'Partner' => false
    ];

    public function load(ObjectManager $manager): void
    {
        /** @var EnumOptionRepository $enumRepo */
        $enumRepo = $manager->getRepository(EnumOption::class);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumOption(
                'lead_source',
                ExtendHelper::buildEnumInternalId($name),
                $name,
                $priority++,
                $isDefault
            );
            $manager->persist($enumOption);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [LoadLanguageData::class];
    }
}
