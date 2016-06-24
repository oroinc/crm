<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityRepository;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DashboardBundle\Entity\Widget;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class UpdateForecastWidgetOptions extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityRepository $repository */
        $repository = $manager->getRepository('Oro\Bundle\DashboardBundle\Entity\Widget');

        $qb = $repository->createQueryBuilder('w');
        $qb->andWhere('w.name = :name');
        $qb->setParameter('name', 'forecast_of_opportunities');
        $widgets = $qb->getQuery()->getResult();
        foreach ($widgets as $widget) {
            $this->processWidget($widget);
        }


        $manager->flush();
    }

    /**
     * Update old forecast_of_opportunities configuration
     *
     * @param Widget $widget
     */
    protected function processWidget(Widget $widget)
    {
        $needUpdate = false;
        $options    = $widget->getOptions();
        $owners     = [
            'roles'         => [],
            'users'         => [],
            'businessUnits' => []
        ];

        if (!empty($options['businessUnits'])) {
            if (is_array($options['businessUnits'])) {
                /** @var BusinessUnit $businessUnit */
                foreach ($options['businessUnits'] as $businessUnit) {
                    if ($businessUnit instanceof BusinessUnit) {
                        $owners['businessUnits'][] = $businessUnit->getId();
                        $needUpdate                = true;
                    }
                }
            }
            unset($options['businessUnits']);
        }

        if (!empty($options['owners'])) {
            if (is_array($options['owners'])) {
                /** @var User $user */
                foreach ($options['owners'] as $user) {
                    if ($user instanceof User) {
                        $owners['users'][] = $user->getId();
                        $needUpdate        = true;
                    }
                }
            }
            unset($options['owners']);
        }

        if ($needUpdate) {
            $options['owners'] = $owners;
            $widget->setOptions($options);
        }
    }
}
