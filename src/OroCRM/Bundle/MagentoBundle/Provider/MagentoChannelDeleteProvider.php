<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\ChannelDeleteProviderInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class MagentoChannelDeleteProvider implements ChannelDeleteProviderInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var Channel */
    protected $channel;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedChannelType()
    {
        return 'magento';
    }

    /**
     * {@inheritdoc}
     */
    public function processDelete(Channel $channel)
    {
        $this->channel = $channel;

        $this
            ->removeFromEntityByChannelId('OroEmbeddedFormBundle:EmbeddedForm')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Order')
            ->removeCarts()
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Customer')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Store')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Website')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:CustomerGroup')
        ;

        $this->getEntityManager()->remove($channel);
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @return mixed
     */
    protected function getChannelId()
    {
        return $this->channel->getId();
    }

    /**
     * @return $this
     */
    protected function removeCarts()
    {
        $cartAddressId = [];
        $carts = $this
            ->getEntityManager()
            ->getRepository('OroCRMMagentoBundle:Cart')->findByChannel($this->getChannelId());

        foreach ($carts as $cart) {
            $this
                ->pushInto($cart->getShippingAddress(), $cartAddressId)
                ->pushInto($cart->getBillingAddress(), $cartAddressId)
            ;
        }

        $uniqueCartAddressIds = array_unique($cartAddressId);

        if (is_array($uniqueCartAddressIds)) {
            $this
                ->getEntityManager()
                ->createQuery(
                    'DELETE FROM OroCRMMagentoBundle:CartAddress ca '.
                    'WHERE ca.id IN ' . $this->inExpr($uniqueCartAddressIds)
                )
                ->execute();
        }

        unset($uniqueCartAddressIds, $cartAddressId, $carts, $cart);

        $this->removeFromEntityByChannelId('OroCRMMagentoBundle:Cart');

        return $this;
    }

    /**
     * @param string $entity
     *
     * @return $this
     */
    private function removeFromEntityByChannelId($entity)
    {
        $this
            ->getEntityManager()
            ->createQuery('DELETE FROM ' . $entity . ' e WHERE e.channel = ' . $this->getChannelId())
            ->execute();

        return $this;
    }

    /**
     * @param CartAddress|NULL $element
     * @param array $array
     *
     * @return $this
     */
    private function pushInto($element, &$array)
    {
        if (!empty($element)) {
            array_push($array, $element->getid());
        }
        return $this;
    }

    /**
     * @param array $array
     *
     * @return string
     */
    private function inExpr(array $array)
    {
        return '(' . implode(',', $array) . ')';
    }
}
