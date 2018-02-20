<?php

namespace Oro\Bundle\MagentoBundle\Entity\Manager;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CartAddressApiEntityManager extends ApiEntityManager
{
    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var array */
    protected $addressTypes = [
        AddressType::TYPE_BILLING  => 'billingAddress',
        AddressType::TYPE_SHIPPING => 'shippingAddress'
    ];

    /**
     * @param Cart   $cart
     * @param string $type
     *
     * @return null
     */
    public function getSerializedAddress(Cart $cart, $type)
    {
        $address = $this->getAddress($cart, $type);

        if (null === $address) {
            return null;
        }

        $result = $this->entitySerializer->serializeEntities(
            [$address],
            $this->class,
            [
                'fields' => [
                    'country'      => ['fields' => 'iso2Code'],
                    'region'       => ['fields' => 'combinedCode'],
                    'channel'      => ['fields' => 'id']
                ]
            ]
        );

        return empty($result[0]) ? null : $result[0];
    }

    /**
     * @param Cart $cart
     * @param      $type
     *
     * @return CartAddress
     *
     * @throw UnexpectedTypeException
     */
    public function getAddress(Cart $cart, $type)
    {
        return $this->getPropertyAccessor()->getValue($cart, $this->addressTypes[$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeOne($id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(QueryBuilder $qb)
    {
        return $this->entitySerializer->serialize(
            $qb,
            [
                'fields' => [
                    'owner'        => ['fields' => 'id'],
                    'organization' => ['fields' => 'id']
                ]

            ]
        );
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
