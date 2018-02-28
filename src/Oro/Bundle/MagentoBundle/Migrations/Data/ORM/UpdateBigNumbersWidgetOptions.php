<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Widget;

class UpdateBigNumbersWidgetOptions implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $widgets = $this->findBigNumbersWidgets($manager);
        array_map([$this, 'updateOptions'], $widgets);
        $manager->flush();
    }

    /**
     * @param Widget $widget
     */
    protected function updateOptions(Widget $widget)
    {
        $options = $widget->getOptions();
        if (!isset($options['subWidgets'])) {
            return;
        }

        $items = array_map(function ($subWidget) {
            return [
                'id'    => $subWidget,
                'show'  => true,
                'order' => 0,
            ];
        }, $options['subWidgets']);

        $options['subWidgets'] = [
            'items' => $items,
        ];

        $widget->setOptions($options);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Widget[]
     */
    protected function findBigNumbersWidgets(ObjectManager $manager)
    {
        return $manager->getRepository('OroDashboardBundle:Widget')
            ->findByName('big_numbers_widget');
    }
}
