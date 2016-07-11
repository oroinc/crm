<?php

namespace OroCRM\Bundle\MagentoBundle\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\DeleteProviderInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowQueryHelper;

class MagentoDeleteProvider implements DeleteProviderInterface
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
    public function supports($channelType)
    {
        return 'magento' === $channelType;
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
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:CustomerGroup')
            ->removeFromEntityByChannelId('OroCRMMagentoBundle:NewsletterSubscriber');
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
        $cartTable = $this->em->getClassMetadata('OroCRMMagentoBundle:Cart')->getTableName();
        $shippingAddresses = sprintf(
            'SELECT sa.shipping_address_id FROM %s sa WHERE sa.channel_id = %s',
            $cartTable,
            $this->channel->getId()
        );
        $billingAddresses = sprintf(
            'SELECT ba.billing_address_id FROM %s ba WHERE ba.channel_id = %s',
            $cartTable,
            $this->channel->getId()
        );
        $this->em->getConnection()->executeQuery(
            sprintf(
                'Delete FROM %s WHERE id IN (%s) OR id IN (%s)',
                $this->em->getClassMetadata('OroCRMMagentoBundle:CartAddress')->getTableName(),
                $shippingAddresses,
                $billingAddresses
            )
        );

        $this->removeFromEntityByChannelId('OroCRMMagentoBundle:Cart');

        return $this;
    }

    /**
     * Remove workflow records
     *
     * @param string $entityClassName
     *
     * @return $this
     */
    protected function removeWorkflowDefinitions($entityClassName)
    {
        $subQuery = $this->em->createQueryBuilder()->select('workflowItem.id')->from($entityClassName, 'o');
        WorkflowQueryHelper::addQuery($subQuery);
        $subQuery
            ->where('o.channel=:channel')
            ->setParameter('channel', $this->channel);

        $ids = array_map(
            function (array $record) {
                return $record['id'];
            },
            $subQuery->getQuery()->getResult()
        );

        $qbDel = $this->em->createQueryBuilder()->delete()->from(WorkflowItem::class, 'wi');
        $qbDel->where($qbDel->expr()->in('wi.id', $ids));

        $qbDel->getQuery()->execute();

        return $this;
    }

    /**
     * Remove records from given entity type related to channel
     *
     * @param string $entityClassName
     *
     * @return $this
     */
    protected function removeFromEntityByChannelId($entityClassName)
    {
        $this->em->getConnection()->executeQuery(
            sprintf(
                'DELETE FROM %s WHERE channel_id=%s',
                $this->em->getClassMetadata($entityClassName)->getTableName(),
                $this->getChannelId()
            )
        );

        return $this;
    }
}
