<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;

use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class LoadOriginData extends AbstractTranslatableEntityFixture
{
    const CASE_ORIGIN_PREFIX = 'case_origin';

    /**
     * @var array
     */
    protected $originNames = array(
        CaseOrigin::ORIGIN_PHONE,
        CaseOrigin::ORIGIN_EMAIL,
        CaseOrigin::ORIGIN_WEB,
        CaseOrigin::ORIGIN_OTHER
    );

    /**
     * Load entities to DB
     *
     * @param ObjectManager $manager
     */
    protected function loadEntities(ObjectManager $manager)
    {
        $originRepository = $manager->getRepository('OroCRMCaseBundle:CaseOrigin');

        $translationLocales = $this->getTranslationLocales();

        foreach ($translationLocales as $locale) {
            foreach ($this->originNames as $originName) {
                // get case origin entity
                /** @var CaseOrigin $caseOrigin */
                $caseOrigin = $originRepository->findOneBy(array('name' => $originName));
                if (!$caseOrigin) {
                    $caseOrigin = new CaseOrigin($originName);
                }

                // set locale and label
                $originLabel = $this->translate($originName, static::CASE_ORIGIN_PREFIX, $locale);
                $caseOrigin->setLocale($locale)
                    ->setLabel($originLabel);

                // save
                $manager->persist($caseOrigin);
            }

            $manager->flush();
        }
    }
}
