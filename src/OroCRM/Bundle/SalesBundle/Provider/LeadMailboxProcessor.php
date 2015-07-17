<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessorSettings;
use Oro\Bundle\EmailBundle\Provider\MailboxProcessorInterface;

use OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessorSettings as ProcessorEntity;

class LeadMailboxProcessor implements MailboxProcessorInterface
{
    /** @var ParameterBag */
    protected $settings;
    /** @var Translator */
    private $translator;
    /** @var Registry */
    private $doctrine;

    /**
     * @param Translator $translator
     * @param Registry   $doctrine
     */
    public function __construct(Translator $translator, Registry $doctrine)
    {
        $this->translator = $translator;
        $this->doctrine = $doctrine;
    }


    /**
     * {@inheritdoc}
     */
    public function configureFromEntity(MailboxProcessorSettings $processor)
    {
        $this->settings = $processor->getSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function process(EmailUser $emailUser)
    {
        $lead = new Lead();

        $email = $emailUser->getEmail();

        $lead->setOwner($this->settings->get('owner'));
        $lead->setDataChannel($this->settings->get('channel'));
        $lead->setSource($this->settings->get('source'));

        $lead->setName($email->getSubject());
        $lead->setEmail($email->getFromEmailAddress());

        $this->fillNotes($lead, $email);
        $this->fillNames($lead, $email);
        $this->fillB2BCustomer($lead, $email);
        $relatedContact = $this->fillRelatedContact($lead, $email);

        $this->doctrine->getManager()->persist($lead);

        $email->addActivityTarget($relatedContact);

        // Mark email as read
        $emailUser->setSeen(true);
        $this->doctrine->getManager()->persist($emailUser);
    }

    /**
     * Returns processor type.
     *
     * @return string
     */
    public function getType()
    {
        return ProcessorEntity::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.sales.mailbox_processor.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessorSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_sales_mailbox_processor_lead';
    }

    protected function fillNotes(Lead $lead, Email $email)
    {
        if ($email->getEmailBody()->getBodyIsText()) {
            $lead->setNotes($email->getEmailBody()->getBodyContent());
        }
    }

    protected function fillNames(Lead $lead, Email $email)
    {
        $address = $email->getFromEmailAddress();

        // If address has owner
        if ($address->hasOwner()) {
            $owner = $address->getOwner();
            $lead->setFirstName($owner->getFirstName());
            $lead->setLastName($owner->getLastName());
        } else {
            // If address only has email, and owner is unknown
            $email = $address->getEmail();
            // Split email by at sign, this is a valid email so both parts will be present
            $emailParts = explode('@', $email);
            // Set full email address as first name
            $lead->setFirstName($email);
            // Set domain as last name
            $lead->setLastName($emailParts[1]);
        }
    }

    protected function fillB2BCustomer(Lead $lead, Email $email)
    {
        $repo = $this->doctrine->getRepository('OroCRMSalesBundle:B2bCustomer');
        $qb = $repo->createQueryBuilder('customer');
        $qb->leftJoin('OroCRMAccountBundle:Account', 'a')
            ->leftJoin('OroCRMContactBundle', 'ac', Join::ON, 'a.defaultContact = ac.id')
            ->leftJoin('OroCRMContactBundle:Contact', 'c', Join::ON, 'customer.contact = c.id')
            ->where('c.email = :email')
            ->orWhere('ac.email = :email');

        $qb->setParameter('email', $email->getFromEmailAddress()->getEmail());

        $customers = $qb->getQuery()->getResult();

        if (count($customers) > 0) {
            $lead->setCustomer($customers[0]);
        }
    }

    protected function fillRelatedContact(Lead $lead, Email $email)
    {
        if (!$email->getFromEmailAddress()->hasOwner()) {
            return null;
        }

        $addressOwner = $email->getFromEmailAddress()->getOwner();

        if ($addressOwner instanceof Contact) {
            $lead->setContact($addressOwner);

            return $addressOwner;
        }

        return null;
    }
}
