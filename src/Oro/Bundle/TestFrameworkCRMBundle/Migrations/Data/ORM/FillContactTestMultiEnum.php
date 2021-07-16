<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class FillContactTestMultiEnum extends AbstractFixture
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
        $className = ExtendHelper::buildEnumValueClassName(self::CONTACT_FIELD_TEST_ENUM_CODE);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
