<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

/**
 * Loads case sources.
 */
class LoadSourceData extends AbstractTranslatableEntityFixture
{
    private const TRANSLATION_PREFIX = 'case_source';

    #[\Override]
    protected function loadEntities(ObjectManager $manager): void
    {
        $caseSourceRepository = $manager->getRepository(CaseSource::class);
        $translationLocales = $this->getTranslationLocales();
        $names = [
            CaseSource::SOURCE_PHONE,
            CaseSource::SOURCE_EMAIL,
            CaseSource::SOURCE_WEB,
            CaseSource::SOURCE_OTHER
        ];
        foreach ($translationLocales as $locale) {
            foreach ($names as $sourceName) {
                /** @var CaseSource $caseSource */
                $caseSource = $caseSourceRepository->findOneBy(['name' => $sourceName]);
                if (!$caseSource) {
                    $caseSource = new CaseSource($sourceName);
                }

                $caseSource->setLocale($locale);
                $caseSource->setLabel($this->translate($sourceName, self::TRANSLATION_PREFIX, $locale));
                $manager->persist($caseSource);
            }
            $manager->flush();
        }
    }
}
