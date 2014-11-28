<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Command\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class LoadEntitiesData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = [
        [
            'reference' => 'Channel.CustomerIdentity.CustomerIdentity',
            'recency' => 2
        ],
        [
            'reference' => 'Channel.CustomerChannel.Customer',
            'recency' => 2
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $now = new \DateTime();
            $entity = new Customer();
            $entity
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            $excludeProperties = ['reference'];
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            foreach ($data as $property => $value) {
                if (in_array($property, $excludeProperties)) {
                    continue;
                }
                $propertyAccessor->setValue($entity, $property, $value);
            }

            $this->setReference($data['reference'], $entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }
}
