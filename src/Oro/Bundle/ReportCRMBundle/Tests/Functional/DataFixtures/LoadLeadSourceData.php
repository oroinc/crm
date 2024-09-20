<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;

class LoadLeadSourceData extends AbstractFixture implements OrderedFixtureInterface
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
                Lead::INTERNAL_STATUS_CODE,
                ExtendHelper::buildEnumInternalId($name),
                $name,
                $priority++,
                $isDefault
            );
            $manager->persist($enumOption);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 290;
    }
}
