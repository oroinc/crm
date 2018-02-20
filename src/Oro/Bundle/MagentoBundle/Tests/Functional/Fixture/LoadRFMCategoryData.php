<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LoadRFMCategoryData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'category_type' => 'recency',
            'category_index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_r_category_1'
        ],
        [
            'category_type' => 'recency',
            'category_index' => 1,
            'minValue' => 1,
            'maxValue' => 10,
            'reference' => 'rfm_r_category_2'
        ],
        [
            'category_type' => 'recency',
            'category_index' => 2,
            'minValue' => 10,
            'maxValue' => null,
            'reference' => 'rfm_r_category_3'
        ],
        [
            'category_type' => 'frequency',
            'category_index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_f_category_1'
        ],
        [
            'category_type' => 'frequency',
            'category_index' => 1,
            'minValue' => 1,
            'maxValue' => 2,
            'reference' => 'rfm_f_category_2'
        ],
        [
            'category_type' => 'frequency',
            'category_index' => 2,
            'minValue' => 2,
            'maxValue' => 100,
            'reference' => 'rfm_f_category_3'
        ],
        [
            'category_type' => 'frequency',
            'category_index' => 3,
            'minValue' => 100,
            'maxValue' => null,
            'reference' => 'rfm_f_category_4'
        ],
        [
            'category_type' => 'monetary',
            'category_index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_m_category_1'
        ],
        [
            'category_type' => 'monetary',
            'category_index' => 1,
            'minValue' => 1,
            'maxValue' => 10,
            'reference' => 'rfm_m_category_2'
        ],
        [
            'category_type' => 'monetary',
            'category_index' => 2,
            'minValue' => 10,
            'maxValue' => 500,
            'reference' => 'rfm_m_category_3'
        ],
        [
            'category_type' => 'monetary',
            'category_index' => 3,
            'minValue' => 500,
            'maxValue' => null,
            'reference' => 'rfm_m_category_4'
        ]
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param object $entity
     * @param array $data
     * @param array $excludeProperties
     */
    public function setEntityPropertyValues($entity, array $data, array $excludeProperties = [])
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($data as $property => $value) {
            if (in_array($property, $excludeProperties)) {
                continue;
            }
            $propertyAccessor->setValue($entity, $property, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        foreach ($this->data as $value) {
            $entity = new RFMMetricCategory();
            $entity->setOwner($organization);
            /** @var Channel $channel */
            $channel = $this->getReference('default_channel');
            $value['channel'] = $channel->setData([RFMAwareInterface::RFM_STATE_KEY => true]);
            $this->setEntityPropertyValues($entity, $value, ['reference']);
            $this->setReference($value['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'
        ];
    }
}
