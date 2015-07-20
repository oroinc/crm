<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
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

        // fill parameters from lead mailbox processor.
        $lead->setOwner($this->settings->get('owner'));
        $lead->setDataChannel($this->settings->get('channel'));
        $lead->setSource($this->settings->get('source'));

        // fill simple parameters
        $lead->setName($email->getSubject());
        $lead->setEmail($email->getFromEmailAddress()->getEmail());

        // fill complex parameters
        $this->fillNotes($lead, $email);
        $this->fillNames($lead, $email);
        $this->fillB2BCustomer($lead, $email);
        /*$relatedContact = */$this->fillRelatedContact($lead, $email);

        // Create activity using relatedContact

        // Mark email as read
        $emailUser->setSeen(true);

        $this->doctrine->getManager()->persist($lead);
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

    /**
     * Fills notes with email body, provided that it is in text form.
     *
     * @param Lead  $lead
     * @param Email $email
     */
    protected function fillNotes(Lead $lead, Email $email)
    {
        if ($email->getEmailBody()->getBodyIsText()) {
            $lead->setNotes($email->getEmailBody()->getBodyContent());
        }
    }

    /**
     * If address has owner, uses owner to fill names. Otherwise uses email.
     *
     * @param Lead  $lead
     * @param Email $email
     */
    protected function fillNames(Lead $lead, Email $email)
    {
        $address = $email->getFromEmailAddress();

        list($firstNameFromAddress, $lastNameFromAddress) = $this->getNamesFromAddressOwner($address);
        list($firstName, $lastName) = $this->getNamesFromEmail($address);
        $firstName = $firstNameFromAddress ?: $firstName;
        $lastName = $lastNameFromAddress ?: $lastName;

        $lead->setFirstName($firstName);
        $lead->setLastName($lastName);
    }

    /**
     * Returns first and last names extracted from address owner.
     *
     * @param EmailAddress $address
     *
     * @return array('firstName', 'lastName')
     */
    protected function getNamesFromAddressOwner(EmailAddress $address)
    {
        $firstName = $lastName = false;

        // If address has owner
        if ($address->hasOwner()) {
            $owner = $address->getOwner();
            if ($owner instanceof FirstNameInterface) {
                $firstName = $owner->getFirstName();
            }
            if ($owner instanceof LastNameInterface) {
                $lastName = $owner->getLastName();
            }
        }

        return [$firstName, $lastName];
    }

    /**
     * Returns first and last names from email address.
     *
     * @param EmailAddress $address
     *
     * @return array('firstName', 'lastName')
     */
    protected function getNamesFromEmail(EmailAddress $address)
    {
        // If address only has email, and owner is unknown
        $email = $address->getEmail();
        // Split email by at sign, this is a valid email so both parts will be present
        $emailParts = explode('@', $email);
        // Set first name to whole address
        $firstName = $email;
        // Set domain as last name
        $lastName  = $emailParts[1];

        return [$firstName, $lastName];
    }

    /**
     * Fills B2B Customer attribute of a lead.
     *
     * @param Lead  $lead
     * @param Email $email
     */
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

    /**
     * Fills related contact of a lead.
     *
     * @param Lead  $lead
     * @param Email $email
     *
     * @return null|Contact
     */
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
