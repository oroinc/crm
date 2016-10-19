<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

class LeadMailboxProcessProvider implements MailboxProcessProviderInterface
{
    const LEAD_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\Lead';
    const PROCESS_DEFINITION_NAME = 'convert_mailbox_email_to_lead';

    /** @var Registry */
    protected $registry;

    /** @var ServiceLink */
    private $securityLink;

    /**
     * @param Registry       $registry
     * @param ServiceLink    $securityLink
     */
    public function __construct(Registry $registry, ServiceLink $securityLink)
    {
        $this->registry = $registry;
        $this->securityLink = $securityLink;
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessDefinitionName()
    {
        return self::PROCESS_DEFINITION_NAME;
    }

    /**
     * @return EntityRepository
     */
    protected function getChannelRepository()
    {
        return $this->registry->getRepository('OroCRMChannelBundle:Channel');
    }
}
