<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;

class LoadAddressDiscoveryData extends AbstractFixture
{
    /**
     * @var array
     */
    protected static $data = [
        [
            'reference' => 'discovery_customer1',
            'email' => 'discovery@example.com',
            'firstName' => 'fn1',
            'lastName' => 'ln1',
            'addresses' => [
                [
                    'type' => 'billing',
                    'postalCode' => 123456
                ]
            ]
        ],
        [
            'reference' => 'discovery_customer2',
            'email' => 'discovery@example.com',
            'firstName' => 'fn1',
            'lastName' => 'ln2',
            'addresses' => [
                [
                    'type' => 'billing',
                    'postalCode' => 555555
                ]
            ]
        ],
        [
            'reference' => 'discovery_customer3',
            'email' => 'discovery@example.com',
            'firstName' => 'fn2',
            'lastName' => 'ln3',
            'addresses' => [
                [
                    'type' => 'shipping',
                    'postalCode' => 123456
                ]
            ]
        ],
        [
            'reference' => 'discovery_customer4',
            'email' => 'discovery@example.com',
            'firstName' => 'fn2',
            'lastName' => 'ln4',
            'addresses' => [
                [
                    'type' => 'shipping',
                    'postalCode' => 555555
                ]
            ]
        ],
        [
            'reference' => 'discovery_customer5',
            'email' => 'discovery@example.com',
            'firstName' => 'fn2',
            'lastName' => 'ln5',
            'addresses' => [
                [
                    'type' => 'billing',
                    'postalCode' => 123456
                ],
                [
                    'type' => 'shipping',
                    'postalCode' => 555555
                ]
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $addressTypeRepo = $manager->getRepository('OroAddressBundle:AddressType');

        foreach (self::$data as $item) {
            $customer = new Customer();
            $customer->setEmail($item['email']);
            $customer->setFirstName($item['firstName']);
            $customer->setLastName($item['lastName']);
            $customer->setCreatedAt(new \DateTime());
            $customer->setUpdatedAt($customer->getCreatedAt());

            foreach ($item['addresses'] as $addressData) {
                $address = new Address();
                $address->setPostalCode($addressData['postalCode'])
                    ->setFirstName($item['firstName'])
                    ->setLastName($item['lastName']);
                $address->addType($addressTypeRepo->findOneBy(['name' => $addressData['type']]));

                $customer->addAddress($address);
                $manager->persist($address);
            }

            $manager->persist($customer);
            $manager->flush();

            $this->setReference($item['reference'], $customer);
        }
    }
}
