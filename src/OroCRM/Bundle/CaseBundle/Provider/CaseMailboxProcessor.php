<?php

namespace OroCRM\Bundle\CaseBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessorSettings;
use Oro\Bundle\EmailBundle\Provider\MailboxProcessorInterface;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;
use OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessorSettings as ProcessorEntity;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class CaseMailboxProcessor implements MailboxProcessorInterface
{
    /** @var ParameterBag */
    protected $settings;
    /** @var Registry */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
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
        $case = new CaseEntity();

        $case->setOwner($this->settings->get('owner'));
        $case->setAssignedTo($this->settings->get('assignTo'));
        $case->setStatus($this->settings->get('status'));
        $case->setPriority($this->settings->get('priority'));
        $case->setTags($this->settings->get('tags'));
        $case->setSource(
            $this->doctrine->getManager()->getReference('OroCRMCaseBundle:CaseSource', 'email')
        );

        $email = $emailUser->getEmail();

        $case->setSubject($email->getSubject());

        $this->fillDescription($case, $email);
        $contact = $this->fillRelatedContact($case, $email);
        $this->fillRelatedAccount($case, $contact);
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
     * Returns label of processor type.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'orocrm.case.mailbox_processor.label';
    }

    /**
     * Returns fully qualified class name of settings entity for this processor.
     *
     * @return string
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessorSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_case_mailbox_processor';
    }

    /**
     * @param CaseEntity $case
     * @param Email      $email
     */
    private function fillDescription(CaseEntity $case, Email $email)
    {
        if ($email->getEmailBody()->getBodyIsText()) {
            $case->setDescription($email->getEmailBody()->getBodyContent());
        }
    }

    /**
     * @param CaseEntity $case
     * @param Email      $email
     *
     * @return null|Contact
     */
    private function fillRelatedContact(CaseEntity $case, Email $email)
    {
        if (!$email->getFromEmailAddress()->hasOwner()) {
            return null;
        }

        $emailOwner = $email->getFromEmailAddress()->getOwner();

        if ($emailOwner instanceof Contact) {
            $case->setRelatedContact($emailOwner);
            return $emailOwner;
        }

        return null;
    }

    /**
     * @param CaseEntity   $case
     * @param Contact|null $owner
     */
    private function fillRelatedAccount(CaseEntity $case, $owner)
    {
        $accounts = $owner->getAccounts();

        if (count($accounts) == 1) {
            $case->setRelatedAccount($accounts->first());
        }
    }
}
