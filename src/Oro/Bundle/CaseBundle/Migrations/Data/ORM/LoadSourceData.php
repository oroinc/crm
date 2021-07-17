<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class LoadSourceData extends AbstractTranslatableEntityFixture
{
    const CASE_SOURCE_PREFIX = 'case_source';

    /**
     * @var array
     */
    protected $sourceNames = array(
        CaseSource::SOURCE_PHONE,
        CaseSource::SOURCE_EMAIL,
        CaseSource::SOURCE_WEB,
        CaseSource::SOURCE_OTHER
    );

    /**
     * Load entities to DB
     */
    protected function loadEntities(ObjectManager $manager)
    {
        $sourceRepository = $manager->getRepository('OroCaseBundle:CaseSource');

        $translationLocales = $this->getTranslationLocales();

        foreach ($translationLocales as $locale) {
            foreach ($this->sourceNames as $sourceName) {
                // get case source entity
                /** @var CaseSource $caseSource */
                $caseSource = $sourceRepository->findOneBy(array('name' => $sourceName));
                if (!$caseSource) {
                    $caseSource = new CaseSource($sourceName);
                }

                // set locale and label
                $sourceLabel = $this->translate($sourceName, static::CASE_SOURCE_PREFIX, $locale);
                $caseSource->setLocale($locale)
                    ->setLabel($sourceLabel);

                // save
                $manager->persist($caseSource);
            }

            $manager->flush();
        }
    }
}
