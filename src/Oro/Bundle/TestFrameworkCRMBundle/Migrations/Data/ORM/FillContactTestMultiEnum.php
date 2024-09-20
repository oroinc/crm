<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TranslationBundle\Migrations\Data\ORM\LoadLanguageData;

/**
 * Fills test_multi_enum options for test environment.
 */
class FillContactTestMultiEnum extends AbstractFixture implements DependentFixtureInterface
{
    const CONTACT_FIELD_TEST_ENUM_CODE = 'test_multi_enum';

    /** @var array */
    protected $data = [
        'Bob Marley' => true,
        'Freddie Mercury' => false,
        'Chester Benington' => false
    ];

    public function load(ObjectManager $manager)
    {
        /** @var EnumOptionRepository $enumRepo */
        $enumRepo = $manager->getRepository(EnumOption::class);
        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumOption(
                self::CONTACT_FIELD_TEST_ENUM_CODE,
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
