<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class LoadStatusData extends AbstractTranslatableEntityFixture
{
    const CASE_STATUS_PREFIX = 'case_status';

    /**
     * @var array
     */
    protected $statusNames = array(
        1 => CaseStatus::STATUS_OPEN,
        2 => CaseStatus::STATUS_IN_PROGRESS,
        3 => CaseStatus::STATUS_RESOLVED,
        4 => CaseStatus::STATUS_CLOSED
    );

    /**
     * Load entities to DB
     *
     * @param ObjectManager $manager
     */
    protected function loadEntities(ObjectManager $manager)
    {
        $statusRepository = $manager->getRepository('OroCRMCaseBundle:CaseStatus');

        $translationLocales = $this->getTranslationLocales();

        foreach ($translationLocales as $locale) {
            foreach ($this->statusNames as $order => $statusName) {
                // get case status entity
                /** @var CaseStatus $caseStatus */
                $caseStatus = $statusRepository->findOneBy(array('name' => $statusName));
                if (!$caseStatus) {
                    $caseStatus = new CaseStatus($statusName);
                    $caseStatus->setOrder($order);
                }

                // set locale and label
                $statusLabel = $this->translate($statusName, static::CASE_STATUS_PREFIX, $locale);
                $caseStatus->setLocale($locale)
                    ->setLabel($statusLabel);

                // save
                $manager->persist($caseStatus);
            }

            $manager->flush();
        }
    }
}
