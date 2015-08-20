<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class LeadMailboxProcessProvider implements MailboxProcessProviderInterface
{
    const LEAD_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\Lead';

    /** @var Registry */
    protected $registry;
    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry, SecurityFacade $securityFacade)
    {
        $this->registry = $registry;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_sales_lead_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.sales.mailbox.process.lead.label';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(Mailbox $mailbox = null)
    {
        if (($mailbox === null) || (null === $organization = $mailbox->getOrganization())) {
            $organization = $this->securityFacade->getOrganization();
        }

        $qb = $this->getChannelRepository()->createQueryBuilder('c');

        return (bool) $qb
            ->select('COUNT(c.id)')
            ->join('c.entities', 'e')
            ->andWhere('e.name = :name')
            ->andWhere('c.status = :status')
            ->andWhere('c.owner = :owner')
            ->setParameter('name', static::LEAD_CLASS)
            ->setParameter('status', Channel::STATUS_ACTIVE)
            ->setParameter('owner', $organization)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return EntityRepository
     */
    protected function getChannelRepository()
    {
        return $this->registry->getRepository('OroCRMChannelBundle:Channel');
    }
}
