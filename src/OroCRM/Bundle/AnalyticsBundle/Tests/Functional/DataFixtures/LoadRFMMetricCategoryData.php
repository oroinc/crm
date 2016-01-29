<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class LoadRFMMetricCategoryData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $data = [
        'CustomerChannel.MonetaryCategory' => [
            'categoryType' => RFMMetricCategory::TYPE_MONETARY,
            'channel' => 'Channel.CustomerChannel',
            'categoryIndex' => 8,
            'maxValue' => 15,
            'minValue' => 5,
        ],
        'CustomerChannel.FrequencyCategory' => [
            'categoryType' => RFMMetricCategory::TYPE_FREQUENCY,
            'channel' => 'Channel.CustomerChannel',
            'categoryIndex' => 9,
            'maxValue' => 17,
            'minValue' => 1,
        ],
        'CustomerChannel.RecencyCategory' => [
            'categoryType' => RFMMetricCategory::TYPE_RECENCY,
            'channel' => 'Channel.CustomerChannel',
            'categoryIndex' => 10,
            'maxValue' => 16,
            'minValue' => 4,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [__NAMESPACE__ . '\LoadChannelData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $categoryReference => $data) {
            /** @var Channel $channel */
            $channel = $this->getReference($data['channel']);

            $entity = new RFMMetricCategory();
            $entity->setChannel($channel)
                ->setCategoryType($data['categoryType'])
                ->setCategoryIndex($data['categoryIndex'])
                ->setMaxValue($data['maxValue'])
                ->setMinValue($data['minValue'])
            ;

            $this->setReference($categoryReference, $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
