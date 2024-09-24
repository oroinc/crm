<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

/**
 * Loads case priorities.
 */
class LoadPriorityData extends AbstractTranslatableEntityFixture
{
    private const TRANSLATION_PREFIX = 'case_priority';

    #[\Override]
    protected function loadEntities(ObjectManager $manager): void
    {
        $casePriorityRepository = $manager->getRepository(CasePriority::class);
        $translationLocales = $this->getTranslationLocales();
        $names = [
            CasePriority::PRIORITY_LOW => 1,
            CasePriority::PRIORITY_NORMAL => 2,
            CasePriority::PRIORITY_HIGH => 3
        ];
        foreach ($translationLocales as $locale) {
            foreach ($names as $priorityName => $order) {
                /** @var CasePriority $casePriority */
                $casePriority = $casePriorityRepository->findOneBy(['name' => $priorityName]);
                if (!$casePriority) {
                    $casePriority = new CasePriority($priorityName);
                    $casePriority->setOrder($order);
                }

                $casePriority->setLocale($locale);
                $casePriority->setLabel($this->translate($priorityName, self::TRANSLATION_PREFIX, $locale));
                $manager->persist($casePriority);
            }
            $manager->flush();
        }
    }
}
