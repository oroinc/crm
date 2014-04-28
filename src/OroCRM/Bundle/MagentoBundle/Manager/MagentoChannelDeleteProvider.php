<?php

namespace OroCRM\Bundle\MagentoBundle\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\ChannelDeleteProviderInterface;

use Oro\Bundle\WorkflowBundle\Model\EntityConnector;

use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class MagentoChannelDeleteProvider implements ChannelDeleteProviderInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var Channel */
    protected $channel;

    /** @var EntityConnector */
    protected $entityConnector;

    /**
     * @param EntityManager   $em
     * @param EntityConnector $entityConnector
     */
    public function __construct(EntityManager $em, EntityConnector $entityConnector)
    {
        $this->em = $em;
        $this->entityConnector = $entityConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupport($channelType)
    {
        return 'magento' == $channelType;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRelatedData(Channel $channel)
    {
        $this->channel = $channel;
        $this->removeWorkflowDefinitions('OroCRMMagentoBundle:Order')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Order')
            ->removeWorkflowDefinitions('OroCRMMagentoBundle:Cart')
            ->removeCarts()
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Customer')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Store')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:Website')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:CustomerGroup');
    }

    /**
     * Get channel id for current channel
     *
     * @return int
     */
    protected function getChannelId()
    {
        return $this->channel->getId();
    }

    /**
     * Remove cart records
     *
     * @return $this
     */
    protected function removeCarts()
    {
        $cartAddressId = [];
        $carts = new BufferedQueryResultIterator(
            $this->em->createQueryBuilder()
                ->select('c')
                ->from('OroCRMMagentoBundle:Cart', 'c')
                ->where('c.channel = :channel')
                ->setParameter('channel', $this->channel)
                ->getQuery()
        );
        foreach ($carts as $cart) {
            $this->pushInto($cart->getShippingAddress(), $cartAddressId)
                ->pushInto($cart->getBillingAddress(), $cartAddressId);
        }
        if (!empty($cartAddressId)) {
            $uniqueCartAddressIds = array_unique($cartAddressId);
            $this->em
                ->createQuery(
                    'DELETE FROM OroCRMMagentoBundle:CartAddress ca '.
                    'WHERE ca.id IN ' . $this->exprIn($uniqueCartAddressIds)
                )
                ->execute();
        }
        unset($uniqueCartAddressIds, $cartAddressId, $carts, $cart);
        $this->removeFromEntityByChannelId('OroCRMMagentoBundle:Cart');

        return $this;
    }

    /**
     * Get Related entities
     *
     * @param string $entityClassName
     * @return array
     */
    protected function getRelatedEntities($entityClassName)
    {
        return $this->em
            ->createQueryBuilder()
            ->select('e')
            ->from($entityClassName, 'e')
            ->where('e.channel = :channel')
            ->setParameter('channel', $this->channel)
            ->getQuery()
            ->getResult();
    }

    /**
     * Remove workflow records
     *
     * @param string $entityClassName
     * @return $this
     */
    protected function removeWorkflowDefinitions($entityClassName)
    {
        $entities = $this->getRelatedEntities($entityClassName);
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                if ($this->entityConnector->isWorkflowAware($entity)) {
                    $workflowItem = $this->entityConnector->getWorkflowItem($entity);
                    if ($workflowItem) {
                        $this->em->remove($workflowItem);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Remove records from given entity type related to channel
     *
     * @param string $entityClassName
     * @return $this
     */
    private function removeFromEntityByChannelId($entityClassName)
    {
        $this->em->createQuery('DELETE FROM ' . $entityClassName . ' e WHERE e.channel = ' . $this->getChannelId())
            ->execute();

        return $this;
    }

    /**
     * @param CartAddress|NULL $element
     * @param array            $array
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
    private function exprIn(array $array)
    {
        return '(' . implode(',', $array) . ')';
    }
}
