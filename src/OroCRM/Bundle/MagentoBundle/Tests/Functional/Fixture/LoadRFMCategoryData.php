<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;

class LoadRFMCategoryData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var array
     */
    protected $data = [
        [
            'type' => 'recency',
            'index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_r_category_1'
        ],
        [
            'type' => 'recency',
            'index' => 1,
            'minValue' => 1,
            'maxValue' => 10,
            'reference' => 'rfm_r_category_2'
        ],
        [
            'type' => 'recency',
            'index' => 2,
            'minValue' => 10,
            'maxValue' => null,
            'reference' => 'rfm_r_category_3'
        ],
        [
            'type' => 'frequency',
            'index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_f_category_1'
        ],
        [
            'type' => 'frequency',
            'index' => 1,
            'minValue' => 1,
            'maxValue' => 2,
            'reference' => 'rfm_f_category_2'
        ],
        [
            'type' => 'frequency',
            'index' => 2,
            'minValue' => 2,
            'maxValue' => 100,
            'reference' => 'rfm_f_category_3'
        ],
        [
            'type' => 'frequency',
            'index' => 3,
            'minValue' => 100,
            'maxValue' => null,
            'reference' => 'rfm_f_category_4'
        ],
        [
            'type' => 'monetary',
            'index' => 0,
            'minValue' => 0,
            'maxValue' => 1,
            'reference' => 'rfm_m_category_1'
        ],
        [
            'type' => 'monetary',
            'index' => 1,
            'minValue' => 1,
            'maxValue' => 10,
            'reference' => 'rfm_m_category_2'
        ],
        [
            'type' => 'monetary',
            'index' => 2,
            'minValue' => 10,
            'maxValue' => 500,
            'reference' => 'rfm_m_category_3'
        ],
        [
            'type' => 'monetary',
            'index' => 3,
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
            $value['channel'] = $this->getReference('default_channel');
            $this->setEntityPropertyValues($entity, $value, ['reference']);
            $this->setReference($value['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
