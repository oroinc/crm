<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

/**
 * Loads case statuses.
 */
class LoadStatusData extends AbstractTranslatableEntityFixture
{
    private const TRANSLATION_PREFIX = 'case_status';

    #[\Override]
    protected function loadEntities(ObjectManager $manager): void
    {
        $caseStatusRepository = $manager->getRepository(CaseStatus::class);
        $translationLocales = $this->getTranslationLocales();
        $names = [
            CaseStatus::STATUS_OPEN => 1,
            CaseStatus::STATUS_IN_PROGRESS => 2,
            CaseStatus::STATUS_RESOLVED => 3,
            CaseStatus::STATUS_CLOSED => 4
        ];
        foreach ($translationLocales as $locale) {
            foreach ($names as $statusName => $order) {
                /** @var CaseStatus $caseStatus */
                $caseStatus = $caseStatusRepository->findOneBy(['name' => $statusName]);
                if (!$caseStatus) {
                    $caseStatus = new CaseStatus($statusName);
                    $caseStatus->setOrder($order);
                }

                $caseStatus->setLocale($locale);
                $caseStatus->setLabel($this->translate($statusName, self::TRANSLATION_PREFIX, $locale));
                $manager->persist($caseStatus);
            }
            $manager->flush();
        }
    }
}
