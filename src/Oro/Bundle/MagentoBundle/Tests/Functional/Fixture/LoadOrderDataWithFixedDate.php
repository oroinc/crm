<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

class LoadOrderDataWithFixedDate extends LoadRFMOrderData
{
    /**
     * @var array Orders
     */
    protected $orderData = [
        [
            'status' => 'done',
            'incrementId' => '1',
            'customerEmail' => 'customer@email.com',
            'created' => '2017-07-01 00:00:00',
            'discountAmount' => 4.40,
            'taxAmount' => 12.47,
            'shippingAmount' => 5,
            'totalPaidAmount' => 17.85,
            'subtotalAmount' => 15.5,
            'totalInvoicedAmount' => 11,
            'totalRefundedAmount' => 4,
            'totalCanceledAmount' => 0,
            'shippingMethod' => 'some unique shipping method',
            'remoteIp' => 'unique ip',
            'giftMessage' => 'some very unique gift message',
        ],
        [
            'status' => 'done',
            'incrementId' => '2',
            'customerEmail' => 'customer@email.com',
            'created' => '2017-07-04 23:59:59',
            'discountAmount' => 4.40,
            'taxAmount' => 12.47,
            'shippingAmount' => 5,
            'totalPaidAmount' => 17.85,
            'subtotalAmount' => 15.5,
            'totalInvoicedAmount' => 11,
            'totalRefundedAmount' => 4,
            'totalCanceledAmount' => 0,
            'shippingMethod' => 'some unique shipping method',
            'remoteIp' => 'unique ip',
            'giftMessage' => 'some very unique gift message'
        ],
        [
            'status' => 'done',
            'incrementId' => '3',
            'customerEmail' => 'customer@email.com',
            'created' => '2017-07-05 00:00:00',
            'discountAmount' => 4.40,
            'taxAmount' => 12.47,
            'shippingAmount' => 5,
            'totalPaidAmount' => 17.85,
            'subtotalAmount' => 17.85,
            'totalInvoicedAmount' => 11,
            'totalRefundedAmount' => 4,
            'totalCanceledAmount' => 0,
            'shippingMethod' => 'some unique shipping method',
            'remoteIp' => 'unique ip',
            'giftMessage' => 'some very unique gift message'
        ],
        [
            'status' => 'done',
            'incrementId' => '4',
            'customerEmail' => 'customer@email.com',
            'created' => '2017-06-30 23:59:59',
            'discountAmount' => 4.40,
            'taxAmount' => 12.47,
            'shippingAmount' => 5,
            'totalPaidAmount' => 17.85,
            'subtotalAmount' => 17.85,
            'totalInvoicedAmount' => 11,
            'totalRefundedAmount' => 4,
            'totalCanceledAmount' => 0,
            'shippingMethod' => 'some unique shipping method',
            'remoteIp' => 'unique ip',
            'giftMessage' => 'some very unique gift message'
        ],
    ];
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /**
         * @var $configManager ConfigManager
         */
        $configManager = $this->container->get('oro_config.user');
        $userManager = $this->container->get('oro_user.manager');
        $admin = $userManager->findUserByEmail(LoadAdminUserData::DEFAULT_ADMIN_EMAIL);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        foreach ($this->orderData as $data) {
            $entity = new Order();
            $entity->setOwner($admin);
            $entity->setOrganization($organization);
            $created = new \DateTime(
                $data['created'],
                new \DateTimeZone($configManager->get('oro_locale.timezone'))
            );
            $entity->setCreatedAt($created);
            $entity->setUpdatedAt($created);
            $data['channel'] = $this->getReference('integration');
            $data['dataChannel'] = $this->getReference('default_channel');
            $data['cart'] = $this->getReference('cart');
            $data['store'] = $this->getReference('store');
            $data['customer'] = $this->getReference('customer');
            $this->setEntityPropertyValues($entity, $data, ['created']);
            $manager->persist($entity);
        }
        $manager->remove($this->getReference('order'));
        $manager->flush();
    }
}
