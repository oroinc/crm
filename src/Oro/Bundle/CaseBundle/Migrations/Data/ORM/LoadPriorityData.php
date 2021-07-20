<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class LoadPriorityData extends AbstractTranslatableEntityFixture
{
    const CASE_PRIORITY_PREFIX = 'case_priority';

    /**
     * @var array
     */
    protected $priorityNames = array(
        1 => CasePriority::PRIORITY_LOW,
        2 => CasePriority::PRIORITY_NORMAL,
        3 => CasePriority::PRIORITY_HIGH,
    );

    /**
     * Load entities to DB
     */
    protected function loadEntities(ObjectManager $manager)
    {
        $priorityRepository = $manager->getRepository('OroCaseBundle:CasePriority');

        $translationLocales = $this->getTranslationLocales();

        foreach ($translationLocales as $locale) {
            foreach ($this->priorityNames as $order => $priorityName) {
                /** @var CasePriority $casePriority */
                $casePriority = $priorityRepository->findOneBy(array('name' => $priorityName));
                if (!$casePriority) {
                    $casePriority = new CasePriority($priorityName);
                    $casePriority->setOrder($order);
                }

                // set locale and label
                $priorityLabel = $this->translate($priorityName, static::CASE_PRIORITY_PREFIX, $locale);
                $casePriority->setLocale($locale)
                    ->setLabel($priorityLabel);

                // save
                $manager->persist($casePriority);
            }

            $manager->flush();
        }
    }
}
