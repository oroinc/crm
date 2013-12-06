<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;

class CartNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Cart;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Cart';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'id'          => $object->getId(),
                'customer_id' => $object->getCustomer() ? $object->getCustomer()->getId() : null,
                'email'       => $object->getEmail(),
                'store'       => $object->getStore() ? $object->getStore()->getCode() : null,
                'origin_id'   => $object->getOriginId(),
                'items_qty'   => $object->getItemsQty(),
                'grand_total' => $object->getGrandTotal(),
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $serializer = $this->serializer;
        $data         = is_array($data) ? $data : [];
        $dateTimeFormat = ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'];

        $data['cartItems'] = $serializer->denormalize(
            $data['cartItems'],
            'ArrayCollection<OroCRMMagentoBundle:CartItem>',
            $format,
            $context
        );
        $data['customer'] = $this->fillResultObject($data, $format, $context);
        $data['store'] = $serializer->denormalize($data['store'], CustomerNormalizer::STORE_TYPE, $format, $context);

        $data['createdAt'] = $serializer->denormalize(
            $data['createdAt'],
            'DateTime',
            $format,
            array_merge($context, $dateTimeFormat)
        );
        $data['updatedAt'] = $serializer->denormalize(
            $data['updatedAt'],
            'DateTime',
            $format,
            array_merge($context, $dateTimeFormat)
        );

        $cart = new Cart();
        $this->fillResultObject($cart, $data);

        return $cart;
    }

    /**
     * @param $data
     * @param $format
     * @param $context
     *
     * @return Customer
     */
    protected function denormalizeCustomer($data, $format, $context)
    {
        $customer = new Customer();
        $customer
            ->setOriginalId($data['customer']['originId'])
            ->setFirstName($data['customer']['firstname'])
            ->setLastName($data['customer']['lastname'])
            ->setGroup(
                $this->serializer->denormalize(
                    ['id' => $data['customer']['group_id']],
                    CustomerNormalizer::GROUPS_TYPE,
                    $format,
                    $context
                )
            );

        return $customer;
    }
}
